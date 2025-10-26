<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Qobuz;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class QobuzTrackTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '359452978';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://open.qobuz.com/track/359452978';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://play.qobuz.com/album/123';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new QobuzTrack( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new QobuzTrack( $this->validId );

		$this->assertInstanceOf( QobuzTrack::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new QobuzTrack( $this->validUrlId );

		$this->assertInstanceOf( QobuzTrack::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new QobuzTrack( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new QobuzTrack( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.qobuz.com/track/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Qobuz\QobuzTrack::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new QobuzTrack( $this->validUrlId );
		$this->assertEquals( 'qobuz', $service->getServiceKey() );
	}
}
