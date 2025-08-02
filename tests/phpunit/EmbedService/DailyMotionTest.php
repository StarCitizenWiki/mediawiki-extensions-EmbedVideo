<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class DailyMotionTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'x1adiiw_archer-waking-up-as-h-jon-benjamin_shortfilms';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
    // phpcs:ignore Generic.Files.LineLength.TooLong
	private string $validUrlId = 'http://www.dailymotion.com/video/x1adiiw_archer-waking-up-as-h-jon-benjamin_shortfilms';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = '//www.daily-motion.com/videos/!null';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new DailyMotion( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new DailyMotion( $this->validId );

		$this->assertInstanceOf( DailyMotion::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new DailyMotion( $this->validUrlId );

		$this->assertInstanceOf( DailyMotion::class, $service );
		$this->assertEquals( 'x1adiiw', $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new DailyMotion( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\DailyMotion::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new DailyMotion( $this->validUrlId );

		$this->assertStringContainsString( '//www.dailymotion.com/embed/video/', $service->getUrl() );
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
		$parser->resetOutput();

		$out = EmbedVideo::parseEVU(
			$parser, new PPCustomFrame_Hash( $parser->getPreprocessor(), [] ), [
			$this->validUrlId
		] );

		$this->assertIsArray( $out );
		$this->assertCount( 3, $out );
		$this->assertStringContainsString(
			'x1adiiw',
			$out[0]
		);
	}
}
