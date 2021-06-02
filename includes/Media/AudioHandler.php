<?php
/**
 * EmbedVideo
 * AudioHandler Class
 *
 * @author  Alexia E. Smith
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media;

use File;
use MediaHandler;
use MediaTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput;
use MediaWiki\MediaWikiServices;

class AudioHandler extends MediaHandler {
	protected $contentLanguage;

	public function __construct() {
		$this->contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();
	}

	/**
	 * Get an associative array mapping magic word IDs to parameter names.
	 * Will be used by the parser to identify parameters.
	 */
	public function getParamMap(): array {
		return [
			'img_width'	=> 'width',
			'ev_start'	=> 'start',
			'ev_end'	=> 'end',
			'cover' => 'cover',
		];
	}

	/**
	 * Validate a thumbnail parameter at parse time.
	 * Return true to accept the parameter, and false to reject it.
	 * If you return false, the parser will do something quiet and forgiving.
	 *
	 * @access public
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

		return false;
	}

	/**
	 * Parse a time string into seconds.
	 * strtotime() will not handle this nicely since 1:30 could be one minute and thirty seconds OR one hour and thirty minutes.
	 *
	 * @access public
	 * @param  string	Time formatted as one of: ss, :ss, mm:ss, hh:mm:ss, or dd:hh:mm:ss
	 * @return false|float|int Integer seconds or false for a bad format.
	 */
	public function parseTimeString( $time ) {
		$parts = explode( ":", $time );
		if ( $parts === false ) {
			return false;
		}
		$parts = array_reverse( $parts );

		$magnitude = [ 1, 60, 3600, 86400 ];
		$seconds = 0;
		foreach ( $parts as $index => $part ) {
			$seconds += $part * $magnitude[$index];
		}
		return $seconds;
	}

	/**
	 * Merge a parameter array into a string appropriate for inclusion in filenames
	 *
	 * @access public
	 * @param  array	Array of parameters that have been through normaliseParams.
	 * @return string
	 */
	public function makeParamString( $parameters ): string {
		return ''; // Width does not matter to video or audio.
	}

	/**
	 * Parse a param string made with makeParamString back into an array
	 *
	 * @access public
	 * @param  string	The parameter string without file name (e.g. 122px)
	 * @return mixed Array of parameters or false on failure.
	 */
	public function parseParamString( $string ): array {
		return []; // Nothing to parse.  See makeParamString above.
	}

	/**
	 * Changes the parameter array as necessary, ready for transformation.
	 * Should be idempotent.
	 * Returns false if the parameters are unacceptable and the transform should fail
	 *
	 * @access public
	 * @param  object	File
	 * @param  array	Parameters
	 * @return bool Success
	 */
	public function normaliseParams( $file, &$parameters ): bool {
		global $wgEmbedVideoDefaultWidth;

		if ( isset( $parameters['width'] ) && $parameters['width'] > 0 ) {
			$parameters['width'] = (int)$parameters['width'];
		} else {
			$parameters['width'] = $wgEmbedVideoDefaultWidth;
		}

		if ( isset( $parameters['start'] ) ) {
			$parameters['start'] = $this->parseTimeString( $parameters['start'] );
			if ( $parameters['start'] === false ) {
				unset( $parameters['start'] );
			}
		}

		if ( isset( $parameters['end'] ) ) {
			$parameters['end'] = $this->parseTimeString( $parameters['end'] );
			if ( $parameters['end'] === false ) {
				unset( $parameters['end'] );
			}
		}

		$parameters['page'] = 1;

		return true;
	}

	/**
	 * Get an image size array like that returned by getimagesize(), or false if it
	 * can't be determined.
	 *
	 * This function is used for determining the width, height and bitdepth directly
	 * from an image. The results are stored in the database in the img_width,
	 * img_height, img_bits fields.
	 *
	 * @note If this is a multipage file, return the width and height of the
	 *  first page.
	 *
	 * @access public
	 * @param File $file The file object, or false if there isn't one
	 * @param string $path The filename
	 * @return mixed An array following the format of PHP getimagesize() internal function or false if not supported.
	 */
	public function getImageSize( $file, $path ) {
		return false;
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
	 * @access public
	 * @param File $file
	 * @return string Dimensions
	 */
	public function getDimensionsString( $file ): string {
		$probe = new FFProbe( $file );

		$format = $probe->getFormat();
		$stream = $probe->getStream( "a:0" );

		if ( $format === false || $stream === false ) {
			return parent::getDimensionsString( $file );
		}

		return wfMessage(
			'ev_audio_short_desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() )
		)->text();
	}

	/**
	 * Short description. Shown on Special:Search results.
	 *
	 * @access public
	 * @param File $file
	 * @return string
	 */
	public function getShortDesc( $file ): string {
		$probe = new FFProbe( $file );

		$format = $probe->getFormat();
		$stream = $probe->getStream( "a:0" );

		if ( $format === false || $stream === false ) {
			return self::getGeneralShortDesc( $file );
		}

		return wfMessage(
			'ev_audio_short_desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$this->contentLanguage->formatSize( $file->getSize() )
		)->text();
	}

	/**
	 * Long description. Shown under image on image description page surounded by ().
	 *
	 * @access public
	 * @param File $file
	 * @return string
	 */
	public function getLongDesc( $file ): string {
		$probe = new FFProbe( $file );

		$format = $probe->getFormat();
		$stream = $probe->getStream( "a:0" );

		if ( $format === false || $stream === false ) {
			return self::getGeneralLongDesc( $file );
		}

		$extension = pathinfo( $file->getLocalRefPath(), PATHINFO_EXTENSION );

		return wfMessage(
			'ev_audio_long_desc',
			strtoupper( $extension ),
			$stream->getCodecName(),
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$this->contentLanguage->formatBitrate( $format->getBitRate() )
		)->text();
	}
}
