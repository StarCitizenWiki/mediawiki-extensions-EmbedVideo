<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic;

final class AppleMusicTrack extends AppleMusicAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.music.apple.com/song/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#embed\.music\.apple\.com/song/([0-9]+)#is',
			'#music\.apple\.com/us/song/(?:[a-zA-Z0-9-]+)/([0-9]+)#is',
			'#music\.apple\.com/song/([0-9]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 175;
	}
}