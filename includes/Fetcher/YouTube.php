<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Fetcher;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube as YouTubeService;
use MediaWiki\MediaWikiServices;

class YouTube extends Oembed {

	protected string $baseURL = 'https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=';

	/**
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function execute(): array {
		$service = new YouTubeService( $this->id );

		$response = MediaWikiServices::getInstance()->getHttpRequestFactory()->get(
			$this->baseURL . $service->getId()
		);

		return $this->parseResult( $response );
	}
}
