<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class KakaoTV extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//play-tv.kakao.com/embed/player/cliplink/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#play-tv.kakao\.com/embed/player/cliplink/((?:[a-zA-Z]{2})?[\d]+)#is',
			'#tv.kakao\.com/channel/\d+/cliplink/((?:[a-zA-Z]{2})?[\d]+)#is',
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
		return 'https://www.kakao.com/policy/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://tv.kakao.com',
		];
	}
}
