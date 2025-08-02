<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class BandcampTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '1003592798';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	// phpcs:ignore Generic.Files.LineLength.TooLong
	private string $validUrlId = 'https://bandcamp.com/EmbeddedPlayer/album=1003592798/size=large/bgcol=181a1b/linkcol=056cc4/tracklist=false/transparent=true/';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://bandcamp.com/EmbeddedPlayer/song=Foo/';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new Bandcamp( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new Bandcamp( $this->validId );

		$this->assertInstanceOf( Bandcamp::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Bandcamp( $this->validUrlId );

		$this->assertInstanceOf( Bandcamp::class, $service );
		$this->assertEquals(
			$this->validId,
			$service->parseVideoID( $this->validUrlId )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new Bandcamp( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Bandcamp::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Bandcamp( $this->validUrlId );

		$this->assertStringContainsString( '//bandcamp.com/EmbeddedPlayer/album=', $service->getUrl() );
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
