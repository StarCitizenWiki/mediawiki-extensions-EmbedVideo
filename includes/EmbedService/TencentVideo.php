<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class TencentVideo extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'allowfullscreen' => 'true',
	];

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return '//v.qq.com/txp/iframe/player.html?vid=%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			// https://v.qq.com/x/cover/<cid>/<vid>.html
			'#v\.qq\.com/x/cover/[\da-z]+/([\da-z]+)\.html#is',
			// https://v.qq.com/x/page/<vid>.html
			'#v\.qq\.com/x/page/([\da-z]+)\.html#is',
			// https://v.qq.com/txp/iframe/player.html?vid=<vid>
			// https://v.qq.com/iframe/player.html?vid=<vid>
			'#v\.qq\.com/(?:txp/)?iframe/player\.html\?[^"\s]*\bvid=([\da-z]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([\da-z]+)$#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPrivacyPolicyUrl(): ?string {
		return 'https://privacy.qq.com/document/preview/3fab9c7fc1424ebda42c3ce488322c8a';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://v.qq.com',
		];
	}
}
