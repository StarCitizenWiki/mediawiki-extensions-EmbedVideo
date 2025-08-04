<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class ArchiveOrgTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'electricsheep-flock-244-80000-6';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://archive.org/embed/electricsheep-flock-244-80000-6';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://archive.org/video/#!';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new ArchiveOrg( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new ArchiveOrg( $this->validId );

		$this->assertInstanceOf( ArchiveOrg::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new ArchiveOrg( $this->validUrlId );

		$this->assertInstanceOf( ArchiveOrg::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new ArchiveOrg( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new ArchiveOrg( $this->validUrlId );

		$this->assertStringContainsString( '//archive.org/embed', $service->getUrl() );
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
			$this->validId,
			$out[0]
		);
	}
}
