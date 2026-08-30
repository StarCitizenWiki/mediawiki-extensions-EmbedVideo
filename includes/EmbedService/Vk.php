<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Vk extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://vkvideo.ru/video_ext.php?oid=%1$s&id=%2$s&hd=2';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#vkvideo\.ru/video(-?\d+)_(-?\d+)?#is',
			'#vk\.com/video(-?\d+)_(-?\d+)?#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#(-?\d+)_(-?\d+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://vk.com/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://vkvideo.ru',
			'https://vk.com',
		];
	}
}
