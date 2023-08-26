<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\YouTube;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class YouTubeOEmbedTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '012-foo-123';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '!Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://youtube.com/?v=012-foo-123';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://youtube.com/embed/videoid';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new YouTubeOEmbed( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new YouTubeOEmbed( $this->validId );

		$this->assertInstanceOf( YouTubeOEmbed::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new YouTubeOEmbed( $this->validUrlId );

		$this->assertInstanceOf( YouTubeOEmbed::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new YouTubeOEmbed( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeOEmbed::getUrl
	 * @return void
	 */
	public function testUrl() {
		$service = new YouTubeOEmbed( $this->validUrlId );

		$this->assertStringContainsString( 'https://www.youtube.com/oembed?url=', $service->getUrl() );
	}
}
