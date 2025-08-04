<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class SoundCloudTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://soundcloud.com/some-link';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new SoundCloud( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new SoundCloud( $this->validId );

		$this->assertInstanceOf( SoundCloud::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertInstanceOf( SoundCloud::class, $service );
		$this->assertEquals( $this->validUrlId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new SoundCloud( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertStringContainsString( 'https://w.soundcloud.com/player/?url=', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getBaseUrl
	 * @return void
	 */
	public function testGetBaseUrl() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertEquals(
            // phpcs:ignore Generic.Files.LineLength.TooLong
			'https://w.soundcloud.com/player/?url=%1$s&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true',
			$service->getBaseUrl()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getAspectRatio
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getDefaultWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getDefaultHeight
	 * @return void
	 */
	public function testGetAspectRatio() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertEquals(
			round( $service->getDefaultWidth() / $service->getDefaultHeight(), 2 ),
			round( $service->getAspectRatio(), 2 )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getAspectRatio
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getDefaultWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getDefaultHeight
	 * @return void
	 */
	public function testGetContentType() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertEquals( 'audio', $service->getContentType() );
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
