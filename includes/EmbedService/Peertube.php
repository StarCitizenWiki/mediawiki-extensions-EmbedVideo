<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Peertube extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'fullscreen',
        'style' => 'border:0px;',
		'sandbox' => 'allow-same-origin allow-scripts allow-popups allow-forms',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://peertube.tv/videos/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'peertube';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 560;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 315;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^https?://(?:www\.)?peertube\.tv/videos/embed/([a-zA-Z0-9]+)#is',
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
