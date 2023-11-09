<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Bandcamp extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 200;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 400;
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		if ( empty( $this->urlArgs ) ) {
			// phpcs:ignore Generic.Files.LineLength.TooLong
			return '//bandcamp.com/EmbeddedPlayer/album=%1$s/size=large/bgcol=181a1b/linkcol=056cc4/artwork=small/transparent=true/';
		}

		return '//bandcamp.com/EmbeddedPlayer/album=%1$s/';
	}

	/**
	 * @inheritDoc
	 */
	public function getUrl(): string {
		if ( $this->getUrlArgs() !== false ) {
			unset( $this->urlArgs['autoplay'] );

			$args = array_shift( $this->urlArgs );
			return sprintf( '%s&%s', sprintf( $this->getBaseUrl(), $this->getId() ), $args ?? '' );
		}

		return parent::getUrl();
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#bandcamp\.com\/EmbeddedPlayer\/album=(\d+)(?:[\/\w=]+)?#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#(?:album=)?(\d+)(?:[\/\w=]+)?#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://bandcamp.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://bandcamp.com',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getContentType(): ?string {
		return 'audio';
	}
}
