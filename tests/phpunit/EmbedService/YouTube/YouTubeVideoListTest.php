<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\YouTube;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class YouTubeVideoListTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'pSsYTj9kCHE';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '!Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://youtube.com/?playlist=pSsYTj9kCHE';

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

		new YouTubeVideoList( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new YouTubeVideoList( $this->validId );

		$this->assertInstanceOf( YouTubeVideoList::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new YouTubeVideoList( $this->validUrlId );

		$this->assertInstanceOf( YouTubeVideoList::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new YouTubeVideoList( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList::getUrl
	 * @return void
	 */
	public function testUrl() {
		$service = new YouTubeVideoList( $this->validUrlId );
		$service->setUrlArgs( 'playlist=pSsYTj9kCHE,pSsYTj9kCHE' );

		$this->assertStringContainsString( '//www.youtube-nocookie.com/embed/', $service->getUrl() );
		$this->assertStringContainsString( urlencode( 'pSsYTj9kCHE,pSsYTj9kCHE' ), $service->getUrl() );
	}
}
