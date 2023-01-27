<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media;

use Exception;
use File;
use MediaTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Title;

class VideoHandler extends AudioHandler {
	/**
	 * @inheritDoc
	 *
	 * @return array
	 */
	public function getParamMap(): array {
		return array_merge( parent::getParamMap(), [
			'gif' => 'gif',
			'cover' => 'poster',
			'poster' => 'poster',
			'lazy' => 'lazy',
			'title' => 'title',
			'description' => 'description',
			'autoresize' => 'autoresize',
		] );
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
		if ( $name === 'width' || $name === 'height' ) {
			return $value > 0;
		}

		if ( in_array( $name, [ 'poster', 'gif', 'muted', 'title', 'description', 'lazy', 'autoresize' ] ) ) {
			return true;
		}

		return parent::validateParam( $name, $value );
	}

	/**
	 * Changes the parameter array as necessary, ready for transformation.
	 * Should be idempotent.
	 * Returns false if the parameters are unacceptable and the transform should fail
	 *
	 * @param File $image File
	 * @param array $params Parameters
	 * @return bool Success
	 */
	public function normaliseParams( $image, &$params ): bool {
		parent::normaliseParams( $image, $params );

		if ( isset( $params['poster'] ) ) {
			$title = Title::newFromText( $params['poster'], NS_FILE );

			if ( $title !== null && $title->exists() ) {
				$coverFile = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
				$transform = $coverFile->transform( [ 'width' => $params['width'] ] );

				try {
					$params['posterUrl'] = wfExpandUrl( $transform->getUrl() );
				} catch ( Exception $e ) {
					unset( $params['poster'], $params['posterUrl'] );
				}
			} else {
				unset( $params['poster'] );
			}
		}

		if ( isset( $params['lazy'] ) ) {
			$params['lazy'] = true;
		} else {
			$params['lazy'] = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'EmbedVideo' )
				->get( 'EmbedVideoLazyLoadLocalVideos' );
		}

		// Note: MediaHandler declares getImageSize with a local path, but we don't need it here.
		[ $width, $height ] = $this->getImageSize( $image, '' );

		if ( $width === 0 && $height === 0 ) {
			// Force a reset.
			$width = 640;
			$height = 360;
		}

		if ( isset( $params['width'] ) &&
			isset( $params['height'] ) &&
			$params['width'] > 0 &&
			$params['height'] === $params['width'] ) {
			// special allowance for square video embeds needed by some wikis,
			// otherwise forced 16:9 ratios are followed.
			return true;
		}

		if ( isset( $params['width'] ) && $params['width'] > 0 && $params['width'] < $width ) {
			$params['width'] = (int)$params['width'];

			if ( !isset( $params['height'] ) ) {
				// Page embeds do not specify thumbnail height so correct it here based on aspect ratio.
				$params['height'] = round( $height / $width * $params['width'] );
			}
		} else {
			$params['width'] = $width;
		}

		if ( isset( $params['height'] ) && $params['height'] > 0 && $params['height'] < $height ) {
			$params['height'] = (int)$params['height'];
		} else {
			$params['height'] = $height;
		}

		if ( $width > 0 && $params['width'] > 0 &&
			( $height / $width ) !== ( $params['height'] / $params['width'] ) ) {
			$params['height'] = round( $height / $width * $params['width'] );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getImageSize( $file, $path ): array {
		[
			'stream' => $stream,
		] = $this->getFFProbeResult( $file );

		if ( $stream !== false ) {
			return [
				$stream->getWidth(),
				$stream->getHeight(),
				0,
				sprintf( 'width="%s" height="%s"', $stream->getWidth(), $stream->getHeight() ),
				'bits' => $stream->getBitDepth()
			];
		}

		return [ 0, 0, 0, 'width="0" height="0"', 'bits' => 0 ];
	}

	/**
	 * Get a MediaTransformOutput object representing the transformed output. Does the
	 * transform unless $flags contains self::TRANSFORM_LATER.
	 *
	 * @param File $file The image object
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

		$styledLocalFiles = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'EmbedVideo' )
			->get( 'EmbedVideoUseEmbedStyleForLocalVideos' );

		$request = RequestContext::getMain();
		$useEmbedTransform = false;
		if ( $request !== null && $request->getTitle() !== null ) {
			$useEmbedTransform = $request->getTitle()->isContentPage();

			// Always preload page is file
			if ( $request->getTitle()->getNamespace() === NS_FILE ) {
				$params['lazy'] = false;
			}
		}

		// If local files are globally styled AND no gif or autoplay parameter is set
		if ( $useEmbedTransform && $styledLocalFiles === true &&
			!( isset( $params['gif'] ) || isset( $params['autoplay'] ) ) ) {
			return new VideoEmbedTransformOutput( $file, $params );
		}

		return new VideoTransformOutput( $file, $params );
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
		] = $this->getFFProbeResult( $file );

		if ( $format === false || $stream === false ) {
			return parent::getDimensionsString( $file );
		}

		return wfMessage(
			'embedvideo-video-short-desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$stream->getWidth(),
			$stream->getHeight()
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
		] = $this->getFFProbeResult( $file );

		if ( $format === false || $stream === false ) {
			return self::getGeneralShortDesc( $file );
		}

		return wfMessage(
			'embedvideo-video-short-desc',
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$stream->getWidth(),
			$stream->getHeight(),
			$this->contentLanguage->formatSize( $file->getSize() )
		)->text();
	}

	/**
	 * Long description. Shown under image on image description page surrounded by ().
	 *
	 * @param File $file
	 * @return string
	 */
	public function getLongDesc( $file ): string {
		[
			'stream' => $stream,
			'format' => $format,
		] = $this->getFFProbeResult( $file );

		if ( $format === false || $stream === false ) {
			return self::getGeneralLongDesc( $file );
		}

		$extension = pathinfo( $file->getPath(), PATHINFO_EXTENSION );

		return wfMessage(
			'embedvideo-video-long-desc',
			strtoupper( $extension ),
			$stream->getCodecName(),
			$this->contentLanguage->formatTimePeriod( $format->getDuration() ),
			$stream->getWidth(),
			$stream->getHeight(),
			$this->contentLanguage->formatBitrate( $format->getBitRate() )
		)->text();
	}
}
