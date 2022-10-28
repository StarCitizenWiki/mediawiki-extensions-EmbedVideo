<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Niconico extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//embed.nicovideo.jp/watch/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 16 / 9;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultWidth(): int {
		return 640;
	}

	/**
	 * @inheritDoc
	 */
	protected function getDefaultHeight(): int {
		return 360;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#nicovideo\.jp/watch/((?:[a-zA-Z]{2})?[\d]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^((?:[a-zA-Z]{2})?[\d]+)$#is',
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
		return 'https://en.dwango.co.jp/terms/privacy/';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.nicovideo.jp',
			'https://embed.nicovido.jp',
		];
	}
}
