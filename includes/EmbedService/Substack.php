<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Substack extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://%1$s.substack.com/embed/p/%2$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#(\w+)\.substack\.com\/p\/([\w-]+)#is',
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
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://substack.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [];
	}
}
