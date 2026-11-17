<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class SoundCloud extends AbstractEmbedService {
	/**
	 * @inheritDoc
	 */
	protected $additionalIframeAttributes = [
		'scrolling' => 'no',
	];

	/**
	 * The track URL is a query parameter of the player URL: percent-encode it. The HTML layer
	 * escapes the URL once, so the base URL uses plain '&'.
	 *
	 * @return string
	 */
	public function getUrl(): string {
		$url = sprintf( $this->getBaseUrl(), rawurlencode( $this->getId() ) );
		$args = $this->getUrlArgs();
		return $args === false ? $url : wfAppendQuery( $url, $args );
	}

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		// phpcs:ignore Generic.Files.LineLength.TooLong
		return 'https://w.soundcloud.com/player/?url=%1$s&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&visual=true';
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultWidth(): int {
		return 186;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 496;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^(https://soundcloud\.com(?:/[\w\-\.]+){2,5})/?(?:\?.*)?$#is',
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
		return 'https://soundcloud.com/pages/privacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://i1.sndcdn.com',
			'https://w.soundcloud.com',
		];
	}
}
