<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Tidal;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class TidalAlbum extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'encrypted-media',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.tidal.com/albums/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'tidal';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 700;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 600;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#tidal\.com/album/([a-zA-Z0-9]+)#is',
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
		return 'https://tidal.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://tidal.com',
			'https://embed.tidal.com'
		];
	}
}
