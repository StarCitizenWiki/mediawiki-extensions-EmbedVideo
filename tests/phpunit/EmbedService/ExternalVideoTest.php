<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class ExternalVideoTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::parseVideoID
	 * @return void
	 */
	public function testWhitelistedUrl() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'foo',
		] );

		$service = EmbedServiceFactory::newFromName( 'external', 'foo' );

		$this->assertInstanceOf( ExternalVideo::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::parseVideoID
	 * @return void
	 */
	public function testNonWhitelistedUrl() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'bar',
		] );

		$this->expectException( InvalidArgumentException::class );
		EmbedServiceFactory::newFromName( 'external', 'foo' );
	}
}
