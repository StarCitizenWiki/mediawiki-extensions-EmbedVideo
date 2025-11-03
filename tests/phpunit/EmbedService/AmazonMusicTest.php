<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AmazonMusicTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'B0D9WK6RYX';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://music.amazon.com/albums/B0D9WK6RYX';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://amazon.com/albums/B0D9WK6RYX';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new AmazonMusic( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new AmazonMusic( $this->validId );

		$this->assertInstanceOf( AmazonMusic::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new AmazonMusic( $this->validUrlId );

		$this->assertInstanceOf( AmazonMusic::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new AmazonMusic( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new AmazonMusic( $this->validUrlId );

		$this->assertStringContainsString( 'https://music.amazon.com/embed/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AmazonMusic::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new AmazonMusic( $this->validUrlId );
		$this->assertEquals( 'amazonmusic', $service->getServiceKey() );
	}
}
