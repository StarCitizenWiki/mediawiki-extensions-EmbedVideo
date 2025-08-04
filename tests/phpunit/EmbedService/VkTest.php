<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Vk;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class VkTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '-22822305_456241864';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://vkvideo.ru/video-22822305_456241864';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://dev.vk.com/en/widgets/video';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new Vk( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new Vk( $this->validId );

		$this->assertInstanceOf( Vk::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Vk( $this->validUrlId );

		$this->assertInstanceOf( Vk::class, $service );
		$this->assertSame( '-22822305', $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new Vk( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Vk::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Vk( $this->validUrlId );

		$this->assertStringContainsString( 'https://vkvideo.ru/video_ext', $service->getUrl() );
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
			'oid=-22822305&id=456241864',
			$out[0]
		);
	}
}
