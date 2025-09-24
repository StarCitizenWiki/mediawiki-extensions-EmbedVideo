<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService;

final class Aparat extends AbstractEmbedService {

	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://www.aparat.com/video/video/embed/videohash/%1$s/vt/frame';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#^https?://(?:www\.)?aparat\\.com/v/([A-Za-z0-9]{6,8})#i',
			'#^https?://(?:www\.)?aparat\\.com/video/video/embed/videohash/([A-Za-z0-9]{6,8})/vt/frame#i',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^([A-Za-z0-9]{6,8})$#',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getCSPUrls(): array {
		return [
			'https://www.aparat.com',
		];
	}
}
