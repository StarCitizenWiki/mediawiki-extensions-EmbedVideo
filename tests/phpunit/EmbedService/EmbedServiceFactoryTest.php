<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedVideoException;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class EmbedServiceFactoryTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory::newFromName
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testNewFromNameExists() {
		$this->assertInstanceOf(
			ArchiveOrg::class,
			EmbedServiceFactory::newFromName( 'archiveorg', 'foo' )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory::newFromName
	 * @return void
	 */
	public function testNewFromNameNotExists() {
		$this->expectException( EmbedVideoException::class );

		EmbedServiceFactory::newFromName( 'foo-service', '' );
	}
}
