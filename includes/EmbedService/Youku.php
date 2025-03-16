<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Youku extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'allowfullscreen' => 'true',
	];

	/**
	 * @inheritDoc
	 */
	public function getServiceKey(): string {
		return 'youku';
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//player.youku.com/embed/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#id_([\d\w-]+).html#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^(?:id_)?([\d\w-]+)$#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://terms.alicdn.com/legal-agreement/terms/suit_bu1_unification/suit_bu1_unification202005141916_91107.html';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://youku.com',
			'https://player.youku.com',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getUrl(): string {
		return sprintf( $this->getBaseUrl(), $this->getId() );
	}
}
