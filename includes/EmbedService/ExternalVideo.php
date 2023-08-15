<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\ExternalVideoTransformOutput;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use Message;
use UnregisteredLocalFile;

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
	protected function getIdRegex(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getContentType(): ?string {
		return 'video';
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
		if ( isset( $this->properties['likeEmbed'] ) ) {
			unset( $this->properties['poster'] );
		}

		$service = new ExternalVideoTransformOutput(
			// This is just 'some' file that won't be used any further
			UnregisteredLocalFile::newFromPath( '/tmp', 'video/mp4' ),
			$this->properties
		);
		$service->setUrl( $this->id );

		return $service->toHtml();
	}
}
