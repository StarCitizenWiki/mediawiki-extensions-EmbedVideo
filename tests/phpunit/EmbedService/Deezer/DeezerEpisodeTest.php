<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Deezer;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DeezerEpisodeTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '772582731';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.deezer.com/en/episode/772582731';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.deezer.com/episode/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DeezerEpisode( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DeezerEpisode( $this->validId );

		$this->assertInstanceOf( DeezerEpisode::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DeezerEpisode( $this->validUrlId );

		$this->assertInstanceOf( DeezerEpisode::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DeezerEpisode( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DeezerEpisode( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.deezer.com/widget/auto/episode/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerEpisode::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new DeezerEpisode( $this->validUrlId );
		$this->assertEquals( 'deezer', $service->getServiceKey() );
	}
}
