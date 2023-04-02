<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class SoundCloud extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'scrolling' => 'no',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		// phpcs:ignore Generic.Files.LineLength.TooLong
		return 'https://w.soundcloud.com/player/?url=%1$s&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true';
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 2.666667;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 186;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 496;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^(https://soundcloud\.com/.+?/.+?)$#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [];
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
		return 'https://soundcloud.com/pages/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://w.soundcloud.com'
		];
	}
}
