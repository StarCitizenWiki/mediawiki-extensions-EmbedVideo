<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Extension\EmbedVideo\EmbedVideoException;
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
		$this->expectException( EmbedVideoException::class );

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
		$this->expectException( EmbedVideoException::class );
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
	 * The player options must be real query parameters (the HTML layer escapes '&' once), and the
	 * track URL must be percent-encoded inside the player's url= parameter.
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrl
	 * @return void
	 */
	public function testUrlHasRealQueryParameters() {
		$service = new SoundCloud( 'https://soundcloud.com/forss/flickermood' );
		$url = $service->getUrl();

		$this->assertStringNotContainsString( '&amp;', $url );
		$this->assertStringStartsWith(
			'https://w.soundcloud.com/player/?url=https%3A%2F%2Fsoundcloud.com%2Fforss%2Fflickermood&auto_play=false',
			$url
		);
		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );
		$this->assertSame( 'https://soundcloud.com/forss/flickermood', $query['url'] );
		$this->assertSame( 'true', $query['visual'] );
	}

	/**
	 * Editor URL arguments are still appended (4.2.0's AbstractEmbedService::getUrl did).
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrl
	 * @return void
	 */
	public function testUrlKeepsEditorArguments() {
		$service = new SoundCloud( 'https://soundcloud.com/forss/flickermood' );
		$service->setUrlArgs( 'color=ff5500&show_artwork=false' );
		parse_str( parse_url( $service->getUrl(), PHP_URL_QUERY ), $query );

		$this->assertSame( 'ff5500', $query['color'] );
		$this->assertSame( 'false', $query['show_artwork'] );
		$this->assertSame( 'https://soundcloud.com/forss/flickermood', $query['url'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getBaseUrl
	 * @return void
	 */
	public function testGetBaseUrl() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertEquals(
            // phpcs:ignore Generic.Files.LineLength.TooLong
			'https://w.soundcloud.com/player/?url=%1$s&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&visual=true',
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
		$parser->clearState();

		$out = EmbedVideo::parseEVU(
			$parser, new PPCustomFrame_Hash( $parser->getPreprocessor(), [] ), [
			$this->validUrlId
		] );

		$this->assertIsArray( $out );
		$this->assertCount( 2, $out );
		// The track URL is a query parameter of the player URL, so it appears percent-encoded.
		$this->assertStringContainsString(
			rawurlencode( $this->validId ),
			$parser->getStripState()->unstripNoWiki( $out[0] )
		);
	}
}
