<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic;

final class AppleMusicArtist extends AppleMusicAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.music.apple.com/artist/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#embed\.music\.apple\.com/artist/([0-9]+)#is',
			'#music\.apple\.com/us/artist/(?:[a-zA-Z0-9-]+)/([0-9]+)#is',
			'#music\.apple\.com/artist/([0-9]+)#is',
		];
	}
}
