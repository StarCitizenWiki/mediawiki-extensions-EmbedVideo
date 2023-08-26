<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Niconico;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class NiconicoTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'sm40807360';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://embed.nicovideo.jp/watch/sm40807360';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://nicovideo.jp/video/40807360';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new Niconico( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new Niconico( $this->validId );

		$this->assertInstanceOf( Niconico::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Niconico( $this->validUrlId );

		$this->assertInstanceOf( Niconico::class, $service );
		$this->assertEquals( 'sm40807360', $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new Niconico( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Niconico::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Niconico( $this->validUrlId );

		$this->assertStringContainsString( '//embed.nicovideo.jp/watch/', $service->getUrl() );
	}
}
