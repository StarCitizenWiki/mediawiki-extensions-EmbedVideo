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
	public function getContentType(): ?string {
		return 'video';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://www.bilibili.com/blackboard/privacy-pc.html';
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
	 * @inheritDoc
	 */
	public function getUrl(): string {
		if ( $this->getUrlArgs() !== false ) {
			return sprintf( '%s&%s', sprintf( $this->getBaseUrl(), $this->getId() ), $this->getUrlArgs() );
		}

		return sprintf( $this->getBaseUrl(), $this->getId() );
	}
}
