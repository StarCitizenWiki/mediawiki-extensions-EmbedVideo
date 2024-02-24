<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Wistia extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//fast.wistia.net/embed/iframe/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'/(?:.+)?(?:wistia\.com|wi\.st)\/(?:medias|embed)\/(.*)/'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#([\w]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://wistia.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://wistia.com',
			'https://fast.wistia.com',
		];
	}
}
