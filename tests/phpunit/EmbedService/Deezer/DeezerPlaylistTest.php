<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Deezer;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DeezerPlaylistTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '1282495565';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.deezer.com/en/playlist/1282495565';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.deezer.com/playlist/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DeezerPlaylist( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DeezerPlaylist( $this->validId );

		$this->assertInstanceOf( DeezerPlaylist::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DeezerPlaylist( $this->validUrlId );

		$this->assertInstanceOf( DeezerPlaylist::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DeezerPlaylist( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DeezerPlaylist( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.deezer.com/widget/auto/playlist/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerPlaylist::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new DeezerPlaylist( $this->validUrlId );
		$this->assertEquals( 'deezer', $service->getServiceKey() );
	}
}
