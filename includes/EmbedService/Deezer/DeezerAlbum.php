<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Deezer;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class DeezerAlbum extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'encrypted-media',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.deezer.com/widget/auto/album/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'deezer';
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
	public function getDefaultHeight(): int {
		return 300;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#www\.deezer\.com/us/album/([a-zA-Z0-9]+)#is',
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
		return 'https://www.deezer.com/legal/personal-datas';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.deezer.com',
			'https://widget.deezer.com'
		];
	}
}
