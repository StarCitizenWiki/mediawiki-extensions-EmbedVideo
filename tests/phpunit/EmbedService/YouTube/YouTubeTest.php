<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\YouTube;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class YouTubeTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'pSsYTj9kCHE';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '!Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://youtube.com/?v=pSsYTj9kCHE';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://youtube.com/embed/videoid';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new YouTube( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new YouTube( $this->validId );

		$this->assertInstanceOf( YouTube::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new YouTube( $this->validUrlId );

		$this->assertInstanceOf( YouTube::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new YouTube( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new YouTube( $this->validUrlId );

		$this->assertStringContainsString( '//www.youtube-nocookie.com/embed/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getIdRegex
	 * @return void
	 */
	public function testShortUrl() {
		$service = new YouTube( 'https://youtu.be/0123video' );

		$this->assertStringContainsString( '//www.youtube-nocookie.com/embed/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::__toString
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeIframe
	 * @return void
	 */
	public function testToString() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => false,
		] );

		$service = new YouTube( 'https://youtu.be/0123video' );

		$this->assertStringContainsString( '<iframe', (string)$service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::__toString
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeIframe
	 * @return void
	 */
	public function testToStringEmptyOnConsent() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$service = new YouTube( 'https://youtu.be/0123video' );

		$this->assertEmpty( (string)$service );
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
