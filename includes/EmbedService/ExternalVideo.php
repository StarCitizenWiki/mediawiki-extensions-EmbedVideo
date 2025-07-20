<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWiki\FileRepo\File\UnregisteredLocalFile;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;

final class ExternalVideo extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function parseVideoID( $id ): string {
		try {
			return parent::parseVideoID( $id );
		} catch ( InvalidArgumentException $e ) {
			throw new InvalidArgumentException( ( new Message( 'embedvideo-error-url-not-whitelisted' ) )->text() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		$nomatch = [ '<nomatch>' ];
		$allow = MediaWikiServices::getInstance()->getMainConfig()->get( MainConfigNames::AllowExternalImagesFrom );

		// If no external urls are whitelisted we return a regex that won't match any urls
		if ( empty( $allow ) ) {
			return $nomatch;
		}

		if ( !is_array( $allow ) ) {
			$allow = [ $allow ];
		}

		$isAllowed = false;
		foreach ( $allow as $url ) {
			$isAllowed = $isAllowed || str_contains( $this->unparsedId, $url );
		}

		return $isAllowed ? [] : $nomatch;
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			$this->id,
		];
	}

	/**
	 * Returns a <video> Element
	 *
	 * @return string
	 */
	public function __toString() {
		$file = new class( false, false, $this->id, 'video/mp4' ) extends UnregisteredLocalFile {
			/**
			 * Constructor using the $path arg as the url to the video
			 *
			 * @param bool $title
			 * @param bool $repo
			 * @param string|bool $path
			 * @param string|bool $mime
			 */
			public function __construct( $title = false, $repo = false, $path = false, $mime = false ) {
				parent::__construct( $title, $repo, $path, $mime );
				$this->url = $path;
			}

			/**
			 * Full URL to the external video
			 * @return string
			 */
			public function getUrl() {
				return $this->path;
			}
		};

		$service = new VideoTransformOutput(
			// This is just 'some' file that won't be used any further
			$file,
			[
				'width' => $this->getWidth(),
				'height' => $this->getHeight(),
				'lazy' => false,
			]
		);

		return $service->toHtml();
	}
}
