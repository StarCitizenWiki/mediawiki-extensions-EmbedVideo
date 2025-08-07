<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Tidal;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class TidalVideoTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '36707521';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://tidal.com/video/36707521';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://tidal.com/en/video/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new TidalVideo( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new TidalVideo( $this->validId );

		$this->assertInstanceOf( TidalVideo::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new TidalVideo( $this->validUrlId );

		$this->assertInstanceOf( TidalVideo::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new TidalVideo( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new TidalVideo( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.tidal.com/videos/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalVideo::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new TidalVideo( $this->validUrlId );
		$this->assertEquals( 'tidal', $service->getServiceKey() );
	}
}
