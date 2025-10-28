<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz;

final class QobuzTrack extends QobuzAlbum {
	/**
	 * @inheritDoc
	 */
	public function getBaseUrl(): string {
		return 'https://widget.qobuz.com/track/%1$s?zone=US-en&display=compact';
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlRegex(): array {
		return [
			'#widget\.qobuz\.com/track/([a-zA-Z0-9]+)\?zone=US-en#is',
			'#open\.qobuz\.com/track/([a-zA-Z0-9]+)#is'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getDefaultHeight(): int {
		return 100;
	}
}