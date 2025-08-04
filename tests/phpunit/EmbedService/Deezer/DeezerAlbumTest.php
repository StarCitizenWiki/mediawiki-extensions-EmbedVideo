<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Deezer;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DeezerAlbumTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '15684526';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.deezer.com/en/album/15684526';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.deezer.com/album/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DeezerAlbum( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DeezerAlbum( $this->validId );

		$this->assertInstanceOf( DeezerAlbum::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DeezerAlbum( $this->validUrlId );

		$this->assertInstanceOf( DeezerAlbum::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DeezerAlbum( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DeezerAlbum( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.deezer.com/widget/auto/album/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerAlbum::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new DeezerAlbum( $this->validUrlId );
		$this->assertEquals( 'deezer', $service->getServiceKey() );
	}
}