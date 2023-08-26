<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Spotify;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class TwitchClipTest extends MediaWikiIntegrationTestCase {

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
	private string $validUrlId = 'https://twitch.tv/channel/clip/012-foo-123';

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

		new TwitchClip( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new TwitchClip( $this->validId );

		$this->assertInstanceOf( TwitchClip::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new TwitchClip( $this->validUrlId );

		$this->assertInstanceOf( TwitchClip::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new TwitchClip( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Twitch\TwitchClip::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new TwitchClip( $this->validUrlId );

		$this->assertStringContainsString( 'https://clips.twitch.tv/embed?autoplay=false&clip=', $service->getUrl() );
		$this->assertStringContainsString( 'parent=', $service->getUrl() );
	}
}
