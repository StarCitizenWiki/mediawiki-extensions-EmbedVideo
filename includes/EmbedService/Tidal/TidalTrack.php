<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Tidal;

final class TidalTrack extends TidalAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://embed.tidal.com/tracks/%1$s';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#tidal\.com/track/([a-zA-Z0-9]+)#is',
		];
	}
}