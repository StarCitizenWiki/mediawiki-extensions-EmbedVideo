<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Podbean extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://www.podbean.com/player-v2/?i=%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^https?://(?:www\.)?podbean\.com/player-v2/\?(?:[^\#]*&)*i=([A-Za-z0-9-]{6,40})#i',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([A-Za-z0-9-]{6,40})$#i',
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
		return 'https://www.podbean.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.podbean.com',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getAspectRatio(): ?float {
		return 4 / 1;
	}
}
