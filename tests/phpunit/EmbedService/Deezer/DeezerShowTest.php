<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\Deezer;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DeezerShowTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '1129782';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = 'Foo-Bar';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://www.deezer.com/show/1129782';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://www.deezer.com/show/1234567890';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DeezerShow( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DeezerShow( $this->validId );

		$this->assertInstanceOf( DeezerShow::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DeezerShow( $this->validUrlId );

		$this->assertInstanceOf( DeezerShow::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DeezerShow( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DeezerShow( $this->validUrlId );

		$this->assertStringContainsString( 'https://widget.deezer.com/widget/auto/show/', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Deezer\DeezerShow::getServiceKey
	 * @return void
	 */
	public function testServiceKey() {
		$service = new DeezerShow( $this->validUrlId );
		$this->assertEquals( 'deezer', $service->getServiceKey() );
	}
}