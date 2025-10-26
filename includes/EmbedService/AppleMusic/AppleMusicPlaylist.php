<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic;

class AppleMusicPlaylist extends AppleMusicAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.music.apple.com/playlist/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#embed\.music\.apple\.com/playlist/([a-zA-Z0-9]+)#is',
		];
	}
}
