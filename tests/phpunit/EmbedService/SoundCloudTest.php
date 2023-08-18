<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class SoundCloudTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://soundcloud.com/skrillex/skrillex-rick-ross-purple-lamborghini';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://soundcloud.com/some-link';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new SoundCloud( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new SoundCloud( $this->validId );

		$this->assertInstanceOf( SoundCloud::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertInstanceOf( SoundCloud::class, $service );
		$this->assertEquals( $this->validUrlId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new SoundCloud( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SoundCloud::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new SoundCloud( $this->validUrlId );

		$this->assertStringContainsString( 'https://w.soundcloud.com/player/?url=', $service->getUrl() );
	}
}