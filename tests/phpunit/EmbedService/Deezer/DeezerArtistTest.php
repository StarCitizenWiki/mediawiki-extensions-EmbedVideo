<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Deezer;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DeezerArtistTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '12051760';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.deezer.com/en/artist/12051760';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.deezer.com/artist/CK9C';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DeezerArtist( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DeezerArtist( $this->validId );

		$this->assertInstanceOf( DeezerArtist::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DeezerArtist( $this->validUrlId );

		$this->assertInstanceOf( DeezerArtist::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DeezerArtist( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DeezerArtist( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.deezer.com/widget/auto/artist/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerArtist::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new DeezerArtist( $this->validUrlId );
		$this->assertEquals( 'deezer', $service->getServiceKey() );
	}
}
