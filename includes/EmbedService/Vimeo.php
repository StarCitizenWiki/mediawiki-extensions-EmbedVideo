<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Vimeo extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	protected $urlArgs = [
		'dnt' => 'true',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//player.vimeo.com/video/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 640 / 360;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 640;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 360;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#vimeo\.com/([\d]+)#is',
			'#vimeo\.com/channels/[\d\w-]+/([\d]+)#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([\d]+)$#is'
		];
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
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://vimeo.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://vimeo.com',
			'https://i.vimeocdn.com',
			'https://player.vimeo.com'
		];
	}
}
