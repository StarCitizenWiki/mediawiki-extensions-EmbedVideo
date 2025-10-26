<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\AppleMusic;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AppleMusicPlaylistTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'pl.f4d106fed2bd41149aaacabb233eb5eb';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://music.apple.com/us/playlist/todays-hits/pl.f4d106fed2bd41149aaacabb233eb5eb';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://music.apple.com/us/track/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new AppleMusicPlaylist( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new AppleMusicPlaylist( $this->validId );

		$this->assertInstanceOf( AppleMusicPlaylist::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new AppleMusicPlaylist( $this->validUrlId );

		$this->assertInstanceOf( AppleMusicPlaylist::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new AppleMusicPlaylist( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new AppleMusicPlaylist( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.music.apple.com/playlist/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicPlaylist::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new AppleMusicPlaylist( $this->validUrlId );
		$this->assertEquals( 'applemusic', $service->getServiceKey() );
	}
}
