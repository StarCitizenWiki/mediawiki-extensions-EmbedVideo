<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Fetcher;

use JsonException;

/**
 * Base class for omebed services
 */
abstract class Oembed extends FetcherBase {

	/**
	 * Parse the oembed result into a common format
	 *
	 * @param string|null $httpBody
	 * @return array
	 */
	protected function parseResult( ?string $httpBody ): array {
		try {
			$parsed = json_decode( $httpBody ?? '', true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $e ) {
			$parsed = [];
		}

		return [
			'title' => $parsed['title'] ?? null,
			'thumbnail' => $parsed['thumbnail_url'] ?? null,
		];
	}
}
