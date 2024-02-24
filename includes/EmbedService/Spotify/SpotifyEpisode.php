<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Spotify;

class SpotifyEpisode extends SpotifyAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://open.spotify.com/episode/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#open\.spotify\.com/episode/([a-zA-Z0-9]+)#is',
		];
	}
}
