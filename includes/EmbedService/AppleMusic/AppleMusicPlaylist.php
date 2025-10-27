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
			'#embed\.music\.apple\.com/playlist/(pl.[0-9]+)#is',
			'#music\.apple\.com/us/playlist/(?:[a-zA-Z0-9-]+)/(pl.[a-zA-Z0-9]+)#is',
			'#music\.apple\.com/playlist/(pl.[0-9]+)#is',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getIdRegex(): array {
		return [
			'#^(pl.[a-zA-Z0-9]+)$#is'
		];
	}
}
