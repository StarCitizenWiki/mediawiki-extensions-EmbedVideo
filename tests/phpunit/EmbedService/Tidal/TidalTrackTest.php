<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Tidal;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class TidalTrackTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '105701544';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://tidal.com/track/105701544';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://tidal.com/en/track/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new TidalTrack( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new TidalTrack( $this->validId );

		$this->assertInstanceOf( TidalTrack::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new TidalTrack( $this->validUrlId );

		$this->assertInstanceOf( TidalTrack::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new TidalTrack( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new TidalTrack( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.tidal.com/tracks/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Tidal\TidalTrack::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new TidalTrack( $this->validUrlId );
		$this->assertEquals( 'tidal', $service->getServiceKey() );
	}
}
