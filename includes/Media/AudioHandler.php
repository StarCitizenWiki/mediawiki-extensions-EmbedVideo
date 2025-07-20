<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media;

use MediaHandler;
use MediaTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput;
use MediaWiki\FileRepo\File\File;
use MediaWiki\MediaWikiServices;
use stdClass;
use Wikimedia\FileBackend\FSFile\FSFile;

class AudioHandler extends MediaHandler {
	protected $contentLanguage;

	public function __construct() {
		$this->contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();
	}

	/**
	 * @inheritDoc
	 */
	protected function useLegacyMetadata() {
		return false;
	}

	/**
	 * Get an associative array mapping magic word IDs to parameter names.
	 * Will be used by the parser to identify parameters.
	 *
	 * @return array
	 */
	public function getParamMap(): array {
		return [
			'img_width'	=> 'width',
			'ev_start' => 'start',
			'ev_end' => 'end',
			'autoplay' => 'autoplay',
			'loop' => 'loop',
			'nocontrols' => 'nocontrols',
			'muted'	=> 'muted',
			'class'	=> 'class',
		];
	}

	/**
	 * Validate a thumbnail parameter at parse time.
	 * Return true to accept the parameter, and false to reject it.
	 * If you return false, the parser will do something quiet and forgiving.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return bool
	 */
	public function validateParam( $name, $value ): bool {
		if ( $name === 'width' ) {
			return $value > 0;
		}

		if ( $name === 'start' || $name === 'end' ) {
			return $this->parseTimeString( $value ) !== false;
		}

		if ( in_array( $name, [ 'autoplay', 'loop', 'nocontrols', 'class' ], true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Parse a time string into seconds.
	 * strtotime() will not handle this nicely since 1:30 could be one minute and thirty seconds
	 * OR one hour and thirty minutes.
	 *
	 * @param string $time Time formatted as one of: ss, :ss, mm:ss, hh:mm:ss, or dd:hh:mm:ss
	 * @return false|float|int Integer seconds or false for a bad format.
	 */
	public function parseTimeString( $time ) {
		$parts = explode( ':', $time );

		if ( $parts === false || empty( $parts[0] ?? '' ) || !is_numeric( $parts[0] ?? null ) ) {
			return false;
		}
		$parts = array_reverse( $parts );

		$magnitude = [ 1, 60, 3600, 86400 ];
		$seconds = 0;

		foreach ( $parts as $index => $part ) {
			$seconds += (int)$part * $magnitude[$index];
		}

		return $seconds;
	}

	/**
	 * Merge a parameter array into a string appropriate for inclusion in filenames
	 *
	 * @param array $params Array of parameters that have been through normaliseParams.
	 * @return string
	 */
	public function makeParamString( $params ): string {
		// Width does not matter to video or audio.
		return '';
	}

	/**
	 * Parse a param string made with makeParamString back into an array
	 *
	 * @param string $string The parameter string without file name (e.g. 122px)
	 * @return mixed Array of parameters or false on failure.
	 */
	public function parseParamString( $string ): array {
		// Nothing to parse.  See makeParamString above.
		return [];
	}

	/**
	 * Changes the parameter array as necessary, ready for transformation.
	 * Should be idempotent.
	 * Returns false if the parameters are unacceptable and the transform should fail
	 *
	 * @param stdClass|File $image
	 * @param array &$params
	 * @return bool Success
	 */
	public function normaliseParams( $image, &$params ): bool {
		global $wgEmbedVideoDefaultWidth;

		if ( isset( $params['width'] ) && $params['width'] > 0 ) {
			$params['width'] = (int)$params['width'];
		} else {
			$params['width'] = $wgEmbedVideoDefaultWidth;
		}

		if ( isset( $params['start'] ) ) {
			$params['start'] = $this->parseTimeString( $params['start'] );
			if ( $params['start'] === false ) {
				unset( $params['start'] );
			}
		}

		if ( isset( $params['end'] ) ) {
			$params['end'] = $this->parseTimeString( $params['end'] );
			if ( $params['end'] === false ) {
				unset( $params['end'] );
			}
		}

		$params['page'] = 1;
		$params['img-class'] = $params['img-class'] ?? $params['class'] ?? null;

		return true;
	}

	/**
	 * Get a MediaTransformOutput object representing the transformed output. Does the
	 * transform unless $flags contains self::TRANSFORM_LATER.
	 *
	 * @param File $file The file object
	 * @param string $dstPath Filesystem destination path
	 * @param string $dstUrl Destination URL to use in output HTML
	 * @param array $params Arbitrary set of parameters validated by $this->validateParam()
	 *                          Note: These parameters have *not* gone through
	 *                          $this->normaliseParams()
	 * @param int $flags A bitfield, may contain self::TRANSFORM_LATER
	 * @return MediaTransformOutput
	 */
	public function doTransform( $file, $dstPath, $dstUrl, $params, $flags = 0 ) {
		$this->normaliseParams( $file, $params );

		return new AudioTransformOutput( $file, $params );
	}

	/**
	 * Shown in file history box on image description page.
	 *
	 * @param File $file
	 * @return string Dimensions
	 */
	public function getDimensionsString( $file ): string {
		[
			'stream' => $stream,
			'format' => $format,
		] = $this->getFFProbeResult( $file, 'a:0' );

		if ( $format === false || $stream === false ) {
			return parent::getDimensionsString( $file );
		}

		return wfMessage(
			'embedvideo-audio-short-desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() )
		)->text();
	}

	/**
	 * Short description. Shown on Special:Search results.
	 *
	 * @param File $file
	 * @return string
	 */
	public function getShortDesc( $file ): string {
		[
			'stream' => $stream,
			'format' => $format,
		] = $this->getFFProbeResult( $file, 'a:0' );

		if ( $format === false || $stream === false ) {
			return self::getGeneralShortDesc( $file );
		}

		return wfMessage(
			'embedvideo-audio-short-desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$this->contentLanguage->formatSize( $file->getSize() )
		)->text();
	}

	/**
	 * Long description. Shown under image on image description page surounded by ().
	 *
	 * @param File $file
	 * @return string
	 */
	public function getLongDesc( $file ): string {
		[
			'stream' => $stream,
			'format' => $format,
		] = $this->getFFProbeResult( $file, 'a:0' );

		if ( $format === false || $stream === false ) {
			return self::getGeneralLongDesc( $file );
		}

		$extension = pathinfo( $file->getPath(), PATHINFO_EXTENSION );

		return wfMessage(
			'embedvideo-audio-long-desc',
			strtoupper( $extension ),
			$stream->getCodecName(),
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$this->contentLanguage->formatBitrate( $format->getBitRate() )
		)->text();
	}

	/**
	 * @inheritDoc
	 */
	public function getSizeAndMetadata( $state, $path ) {
		[
			'stream' => $stream,
			'format' => $format,
		] = $this->getFFProbeResult( $path );

		$data = [
			'metadata' => [],
		];

		if ( $stream !== false && $stream !== null ) {
			$data['metadata'] = [
				'duration' => $stream->getDuration(),
				'codec' => $stream->getCodecName(),
				'bitdepth' => $stream->getBitDepth(),
			];

			if ( !empty( $stream->getWidth() ) ) {
				$data['width'] = $stream->getWidth();
				$data['height'] = $stream->getHeight();
			}
		}

		if ( $format !== false && $format !== null ) {
			$data['bits'] = $format->getBitRate();
		}

		return $data;
	}

	/**
	 * Runs FFProbe and caches results in the Main WAN Object cache
	 *
	 * @param string|FSFile|File $file The file to work on
	 * @param string $select Video / Audio track to select
	 * @return array
	 */
	protected function getFFProbeResult( $file, string $select = 'v:0' ): array {
		$path = $file;

		if ( $file instanceof File ) {
			$path = $file->getLocalRefPath();
		} elseif ( $file instanceof FSFile ) {
			$path = $file->getPath();
		}

		if ( $file === false ) {
			return [
				'stream' => false,
				'format' => false,
			];
		}

		$probe = new FFProbe( $path, $file );

		return [
			'stream' => $probe->getStream( $select ),
			'format' => $probe->getFormat()
		];
	}
}
