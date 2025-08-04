<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Deezer;

class DeezerPlaylist extends DeezerAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.deezer.com/widget/auto/playlist/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#www\.deezer\.com/en/playlist/([a-zA-Z0-9]+)#is',
		];
	}
}
