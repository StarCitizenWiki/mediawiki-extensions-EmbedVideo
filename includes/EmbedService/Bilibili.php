<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Bilibili extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//player.bilibili.com/player.html?bvid=%1$s';
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
			'#bilibili\.com/(?:BV|AV)([\d\w]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^(?:BV|AV)([\d\w]+)$#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): ?string {
		return 'video';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.bilibili.tv/en/privacy-policy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://bilibili.com',
			'https://player.bilibili.com',
		];
	}

	/**
	 * Add '&page=1' if not set through parser
	 *
	 * @return string
	 */
	public function getUrl(): string {
		$page = 'page=1';
		if ( $this->getUrlArgs() !== false ) {
			$page = $this->getUrlArgs();
		}

		return sprintf( '%s&%s', parent::getUrl(), $page );
	}
}
