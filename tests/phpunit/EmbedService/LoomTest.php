<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Loom;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class LoomTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'e5b8c04bca094dd8a5507925ab887002';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.loom.com/share/e5b8c04bca094dd8a5507925ab887002';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.loom.com/share/e5b8c04bca094dd8925ab887002';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new Loom( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new Loom( $this->validId );

		$this->assertInstanceOf( Loom::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Loom( $this->validUrlId );

		$this->assertInstanceOf( Loom::class, $service );
		$this->assertEquals( 'e5b8c04bca094dd8a5507925ab887002', $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new Loom( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Loom::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Loom( $this->validUrlId );

		$this->assertStringContainsString( '//www.loom.com/embed/', $service->getUrl() );
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
			$this->validUrlId
		] );

		$this->assertIsArray( $out );
		$this->assertCount( 3, $out );
		$this->assertStringContainsString(
			$this->validId,
			$out[0]
		);
	}
}
