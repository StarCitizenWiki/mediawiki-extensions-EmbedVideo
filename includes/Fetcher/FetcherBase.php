<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Fetcher;

abstract class FetcherBase {

	/**
	 * @var string The embed id submitted by the user
	 */
	protected string $id;

	/**
	 * @var string The base url pointing to the data service
	 */
	protected string $baseUrl;

	public function __construct( string $id ) {
		$this->id = $id;
	}

	/**
	 * Execute the request. This method (should) instantiate the corresponding embed service and parse the id
	 * Based on the parsed video id, the services data api should be called
	 * This method should return a parsed based on 'parseResult'
	 *
	 * @see self::parseResult()
	 *
	 * @return array
	 */
	abstract public function execute(): array;

		/**
		 * Parse the 'raw' http body into a common format
		 * This assumes the returned array contains a 'title' and 'thumbnail' key
		 *
		 * @param string $httpBody
		 * @return array
		 */
	abstract protected function parseResult( string $httpBody ): array;
}
