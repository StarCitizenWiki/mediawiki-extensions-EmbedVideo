<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Spotify;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class SpotifyArtistTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '0YC192cP3KPCRWx8zr8MfZ';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://open.spotify.com/artist/0YC192cP3KPCRWx8zr8MfZ';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://open.spotify.com/album/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new SpotifyArtist( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new SpotifyArtist( $this->validId );

		$this->assertInstanceOf( SpotifyArtist::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new SpotifyArtist( $this->validUrlId );

		$this->assertInstanceOf( SpotifyArtist::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new SpotifyArtist( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new SpotifyArtist( $this->validUrlId );

		$this->assertStringContainsString( 'https://open.spotify.com/embed/artist/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Spotify\SpotifyArtist::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new SpotifyArtist( $this->validUrlId );
		$this->assertEquals( 'spotify', $service->getServiceKey() );
	}
}
