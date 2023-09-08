<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\Ccc;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class CccTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'rc3-791680-introducing_utk_web_a_web_developer_s_view_on_firmware';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://media.ccc.de/v/rc3-791680-introducing_utk_web_a_web_developer_s_view_on_firmware';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new Ccc( $this->validId );

		$this->assertInstanceOf( Ccc::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Ccc( $this->validUrlId );

		$this->assertInstanceOf( Ccc::class, $service );
		$this->assertEquals(
			'rc3-791680-introducing_utk_web_a_web_developer_s_view_on_firmware',
			$service->parseVideoID( $this->validUrlId )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Ccc::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Ccc( $this->validUrlId );

		$this->assertStringContainsString( '//media.ccc.de/v/', $service->getUrl() );
	}
}
