<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\YouTube;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;

class YouTube extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'modestbranding' => 1,
		'allowfullscreen' => 'true',
	];

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'youtube';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#v=([\d\w-]+)(?:&\S+?)?#is',
			'#youtu\.be/([\d\w-]+)#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([\d\w-]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://www.youtube-nocookie.com/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.youtube.com/howyoutubeworks/user-settings/privacy/';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.youtube-nocookie.com',
			'https://i.ytimg.com'
		];
	}
}
