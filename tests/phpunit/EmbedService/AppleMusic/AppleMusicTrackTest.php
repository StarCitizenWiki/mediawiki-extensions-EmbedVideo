<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\AppleMusic;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AppleMusicTrackTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '1498293072';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://music.apple.com/us/song/high-score-summer/1498293072';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://music.apple.com/us/album/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new AppleMusicTrack( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new AppleMusicTrack( $this->validId );

		$this->assertInstanceOf( AppleMusicTrack::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new AppleMusicTrack( $this->validUrlId );

		$this->assertInstanceOf( AppleMusicTrack::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new AppleMusicTrack( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new AppleMusicTrack( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.music.apple.com/song/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicTrack::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new AppleMusicTrack( $this->validUrlId );
		$this->assertEquals( 'applemusic', $service->getServiceKey() );
	}
}
