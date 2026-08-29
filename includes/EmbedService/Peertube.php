<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Peertube extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://peertube.tv/videos/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^https?://(?:www\.)?peertube\.tv/embed/([a-zA-Z0-9]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([a-zA-Z0-9]+)$#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://framasoft.org/en/legals/';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
            'https://peertube.tv',
			'https://www.peertube.tv',
		];
	}
}