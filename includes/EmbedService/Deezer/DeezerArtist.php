<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Deezer;

final class DeezerArtist extends DeezerAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.deezer.com/widget/auto/artist/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#www\.deezer\.com/us/artist/([a-zA-Z0-9]+)#is',
		];
	}
}
