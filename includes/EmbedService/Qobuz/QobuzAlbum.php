<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class QobuzAlbum extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'encrypted-media',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.qobuz.com/album/%1$s?zone=US-en';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'qobuz';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 378;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 390;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#widget\.qobuz\.com/album/([a-zA-Z0-9]+)?zone=US-en#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([a-zA-Z0-9]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getContentType(): ?string {
		return 'audio';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.qobuz.com/us-en/discover/legals/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [];
	}
}
