<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Spotify;

class SpotifyShow extends SpotifyAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://open.spotify.com/show/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#open\.spotify\.com/show/([a-zA-Z0-9]+)#is',
		];
	}
}
