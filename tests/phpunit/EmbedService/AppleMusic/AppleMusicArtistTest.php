<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\AppleMusic;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AppleMusicArtistTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '925515043';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://music.apple.com/us/artist/the-midnight/925515043';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://music.apple.com/us/albums/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new AppleMusicArtist( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new AppleMusicArtist( $this->validId );

		$this->assertInstanceOf( AppleMusicArtist::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new AppleMusicArtist( $this->validUrlId );

		$this->assertInstanceOf( AppleMusicArtist::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new AppleMusicArtist( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new AppleMusicArtist( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.music.apple.com/artist/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicArtist::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new AppleMusicArtist( $this->validUrlId );
		$this->assertEquals( 'applemusic', $service->getServiceKey() );
	}
}
