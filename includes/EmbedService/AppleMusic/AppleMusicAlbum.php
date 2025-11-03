<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class AppleMusicAlbum extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'encrypted-media',
		'style' => 'border-radius:10px;'
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.music.apple.com/album/%1$s?theme=auto';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'applemusic';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 660;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 450;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#embed\.music\.apple\.com/album/([0-9]+)#is',
			'#music\.apple\.com/us/album/(?:[a-zA-Z0-9-]+)/([0-9]+)#is',
			'#music\.apple\.com/album/([0-9]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([0-9]+)$#is'
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
		return 'https://www.apple.com/legal/privacy/data/en/apple-music/';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [];
	}
}
