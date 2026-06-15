<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo;
use MediaWiki\Extension\EmbedVideo\EmbedVideoException;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class ExternalVideoTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::getUrlRegex
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
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::getUrlRegex
	 * @return void
	 */
	public function testNonWhitelistedUrl() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'bar',
		] );

		$this->expectException( EmbedVideoException::class );
		EmbedServiceFactory::newFromName( 'external', 'foo' );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::getBaseUrl
	 * @return void
	 */
	public function testGetBaseUrl() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'foo',
		] );

		$service = EmbedServiceFactory::newFromName( 'external', 'foo' );

		$this->assertEmpty( $service->getBaseUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ExternalVideo::getCSPUrls
	 * @return void
	 */
	public function testGetCspUrls() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'foo',
		] );

		$service = EmbedServiceFactory::newFromName( 'external', 'foo' );

		$this->assertEquals( [ 'foo' ], $service->getCSPUrls() );
	}
}
