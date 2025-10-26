<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Qobuz;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class QobuzAlbumTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'a1nkwok5snthb';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://play.qobuz.com/album/a1nkwok5snthb';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://play.qobuz.com/track/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new QobuzAlbum( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new QobuzAlbum( $this->validId );

		$this->assertInstanceOf( QobuzAlbum::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new QobuzAlbum( $this->validUrlId );

		$this->assertInstanceOf( QobuzAlbum::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new QobuzAlbum( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new QobuzAlbum( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.qobuz.com/album/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzAlbum::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new QobuzAlbum( $this->validUrlId );
		$this->assertEquals( 'qobuz', $service->getServiceKey() );
	}
}
