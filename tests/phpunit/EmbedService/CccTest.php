<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedService\Ccc;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
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
	// phpcs:ignore Generic.Files.LineLength.TooLong
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

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @return void
	 * @throws Exception
	 */
	public function testEvu(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$parser->resetOutput();

		$out = EmbedVideo::parseEVU(
			$parser, new PPCustomFrame_Hash( $parser->getPreprocessor(), [] ), [
			'https://media.ccc.de/v/rc3-791680-introducing_utk_web_a_web_developer_s_view_on_firmware'
		] );

		$this->assertIsArray( $out );
		$this->assertCount( 3, $out );
		$this->assertStringContainsString(
			'media.ccc.de/v/rc3-791680-introducing_utk_web_a_web_developer_s_view_on_firmware',
			$out[0]
		);
	}
}
