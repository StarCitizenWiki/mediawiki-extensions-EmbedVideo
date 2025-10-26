<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class AmazonMusic extends AbstractEmbedService {
	protected $additionalIframeAttributes = [
		'allow' => 'encrypted-media',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://music.amazon.com/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'amazonmusic';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 798;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 352;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#music\.amazon\.com/embed/([a-zA-Z0-9]+)#is',
			
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
		return 'https://www.amazon.com/gp/help/customer/display.html?nodeId=G201380010';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [];
	}
}
