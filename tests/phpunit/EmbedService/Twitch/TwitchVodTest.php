<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Spotify;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class TwitchVodTest extends MediaWikiIntegrationTestCase {

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
	private string $validUrlId = 'https://twitch.tv/videos/012-foo-123';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://twitch.tv/!vid#eo';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new TwitchVod( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new TwitchVod( $this->validId );

		$this->assertInstanceOf( TwitchVod::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new TwitchVod( $this->validUrlId );

		$this->assertInstanceOf( TwitchVod::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new TwitchVod( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchVod::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new TwitchVod( $this->validUrlId );

		$this->assertStringContainsString( 'https://player.twitch.tv/?autoplay=false&video=', $service->getUrl() );
		$this->assertStringContainsString( 'parent=', $service->getUrl() );
	}
}
