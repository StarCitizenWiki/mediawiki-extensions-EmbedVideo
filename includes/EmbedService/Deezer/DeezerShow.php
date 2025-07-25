<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Deezer;

class DeezerShow extends DeezerAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.deezer.com/widget/show/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#www\.deezer\.com/us/show/([a-zA-Z0-9]+)#is',
		];
	}
}
