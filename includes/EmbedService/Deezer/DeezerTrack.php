<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Deezer;

final class DeezerTrack extends DeezerAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.deezer.com/widget/embed/track/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#www\.deezer\.com/us/track/([a-zA-Z0-9]+)#is',
		];
	}
}
