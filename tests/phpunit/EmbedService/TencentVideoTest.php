<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Extension\EmbedVideo\EmbedVideoException;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class TencentVideoTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'v0362qi3ghq';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid page url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://v.qq.com/x/page/v0362qi3ghq.html';

	/**
	 * A valid cover url containing an id
	 * @var string
	 */
	private string $validCoverUrlId = 'https://v.qq.com/x/cover/mzc00200mp8vo9b/v0362qi3ghq.html';

	/**
	 * A valid iframe player url containing an id
	 * @var string
	 */
	private string $validPlayerUrlId = 'https://v.qq.com/txp/iframe/player.html?vid=v0362qi3ghq&amp;autoplay=1';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://v.qq.com/x/cover/mzc00200mp8vo9b.html';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( EmbedVideoException::class );

		new TencentVideo( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new TencentVideo( $this->validId );

		$this->assertInstanceOf( TencentVideo::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new TencentVideo( $this->validUrlId );

		$this->assertInstanceOf( TencentVideo::class, $service );
		$this->assertSame( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testValidCoverUrlId() {
		$service = new TencentVideo( $this->validCoverUrlId );

		$this->assertSame( $this->validId, $service->parseVideoID( $this->validCoverUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testValidPlayerUrlId() {
		$service = new TencentVideo( $this->validPlayerUrlId );

		$this->assertSame( $this->validId, $service->parseVideoID( $this->validPlayerUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( EmbedVideoException::class );
		new TencentVideo( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new TencentVideo( $this->validUrlId );

		$this->assertSame(
			'//v.qq.com/txp/iframe/player.html?vid=v0362qi3ghq',
			$service->getUrl()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\TencentVideo::getCSPUrls
	 * @return void
	 */
	public function testCspUrls() {
		$service = new TencentVideo( $this->validId );

		$this->assertContains( 'https://v.qq.com', $service->getCSPUrls() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory::newFromName
	 * @return void
	 */
	public function testFactoryAliases() {
		$this->assertInstanceOf(
			TencentVideo::class,
			EmbedServiceFactory::newFromName( 'tencentvideo', $this->validId )
		);
		$this->assertInstanceOf(
			TencentVideo::class,
			EmbedServiceFactory::newFromName( 'v.qq', $this->validUrlId )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @return void
	 * @throws Exception
	 */
	public function testEvu(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$parser->clearState();

		$out = EmbedVideo::parseEVU(
			$parser,
			new PPCustomFrame_Hash( $parser->getPreprocessor(), [] ),
			[ $this->validUrlId ]
		);

		$this->assertIsArray( $out );
		$this->assertCount( 2, $out );
		$this->assertStringContainsString(
			$this->validId,
			$parser->getStripState()->unstripNoWiki( $out[0] )
		);
	}
}
