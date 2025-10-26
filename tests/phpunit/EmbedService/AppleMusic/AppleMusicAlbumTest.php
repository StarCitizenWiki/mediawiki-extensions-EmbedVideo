<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\AppleMusic;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AppleMusicAlbumTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '1758766090';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://music.apple.com/us/album/kids/1758766090';

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

		new AppleMusicAlbum( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new AppleMusicAlbum( $this->validId );

		$this->assertInstanceOf( AppleMusicAlbum::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new AppleMusicAlbum( $this->validUrlId );

		$this->assertInstanceOf( AppleMusicAlbum::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new AppleMusicAlbum( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new AppleMusicAlbum( $this->validUrlId );

		$this->assertStringContainsString( 'https://embed.music.apple.com/album/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AppleMusic\AppleMusicAlbum::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new AppleMusicAlbum( $this->validUrlId );
		$this->assertEquals( 'applemusic', $service->getServiceKey() );
	}
}
