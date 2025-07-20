<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use MediaWiki\Api\ApiUsageException;
use MediaWiki\Tests\Api\ApiTestCase;

/**
 * @group EmbedVideo
 */
class ApiEmbedVideoTest extends ApiTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\ApiEmbedVideo::execute
	 * @return void
	 * @throws ApiUsageException
	 */
	public function testYouTube() {
		$params = [
			'action' => 'embedvideo',
			'service' => 'youtube',
			'id' => 'pSsYTj9kCHE',
		];

		$ret = $this->doApiRequest( $params );

		$this->assertIsArray( $ret );
		$this->assertIsArray( $ret[0] );

		$this->assertArrayHasKey( 'embedvideo', $ret[0] );
		$this->assertArrayHasKey( 'html', $ret[0]['embedvideo'] );

		$data = $ret[0]['embedvideo']['html'];

		$this->assertStringContainsString( 'data-service="youtube"', $data );
	}
}
