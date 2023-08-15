<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class SharePoint extends AbstractEmbedService {
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
		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^(https://[\w-]+.sharepoint.com/sites/.+\.\w+)$#is',
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
		return 'video';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://privacy.microsoft.com/en-us/privacystatement';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://*.sharepoint.com'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getUrl(): string {
	 return $this->id;
	}
}
