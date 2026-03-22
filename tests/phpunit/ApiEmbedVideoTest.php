<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use MediaWiki\Api\ApiMain;
use MediaWiki\Api\ApiUsageException;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EmbedVideo\ApiEmbedVideo;
use MediaWiki\Tests\Api\ApiTestCase;

/**
 * @group EmbedVideo
 */
class ApiEmbedVideoTest extends ApiTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\ApiEmbedVideo::getAllowedParams
	 * @return void
	 */
	public function testGetAllowedParamsIncludesVerticalAlignment() {
		$api = new ApiEmbedVideo( new ApiMain( new RequestContext() ), 'embedvideo' );

		$this->assertArrayHasKey( 'valignment', $api->getAllowedParams() );
	}

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

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\ApiEmbedVideo::execute
	 * @return void
	 * @throws ApiUsageException
	 */
	public function testYouTubeForwardsUrlArgsAndVerticalAlignment() {
		$params = [
			'action' => 'embedvideo',
			'service' => 'youtube',
			'id' => 'pSsYTj9kCHE',
			'urlargs' => 'start=32',
			'valignment' => 'top',
		];

		$ret = $this->doApiRequest( $params );
		$data = $ret[0]['embedvideo']['html'];

		$this->assertStringContainsString( 'start=32', $data );
		$this->assertStringContainsString( 'mw-valign-top', $data );
	}
}
