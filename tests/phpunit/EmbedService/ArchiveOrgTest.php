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

	/**
	 * A valid ID with a sub-file path (archive.org item containing multiple files)
	 * @var string
	 */
	private string $validIdWithSubFile = '2024-12-21-18-15-xjtv-2/动画片_2024-12-20_19_24_xjtv2.mp4';

	/**
	 * A valid ID with a sub-file path containing URL-encoded characters
	 * @var string
	 */
	private string $validIdWithEncodedSubFile = 'electricsheep-flock-244-80000-6/00244%3D80126%3D79911%3D79912.avi';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testValidIdWithSubFile() {
		$service = new ArchiveOrg( $this->validIdWithSubFile );

		$this->assertInstanceOf( ArchiveOrg::class, $service );
		$this->assertEquals( $this->validIdWithSubFile, $service->getId() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testValidIdWithEncodedSubFile() {
		$service = new ArchiveOrg( $this->validIdWithEncodedSubFile );

		$this->assertInstanceOf( ArchiveOrg::class, $service );
		$this->assertEquals( $this->validIdWithEncodedSubFile, $service->getId() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testValidUrlWithSubFile() {
		$service = new ArchiveOrg(
			'https://archive.org/embed/2024-12-21-18-15-xjtv-2/动画片_2024-12-20_19_24_xjtv2.mp4'
		);

		$this->assertInstanceOf( ArchiveOrg::class, $service );
		$this->assertEquals( $this->validIdWithSubFile, $service->getId() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @return void
	 */
	public function testValidDetailsUrlWithSubFile() {
		$service = new ArchiveOrg(
			'https://archive.org/details/electricsheep-flock-244-80000-6/00244%3D80126%3D79911%3D79912.avi'
		);

		$this->assertInstanceOf( ArchiveOrg::class, $service );
		$this->assertEquals( $this->validIdWithEncodedSubFile, $service->getId() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testUrlWithSubFile() {
		$service = new ArchiveOrg( $this->validIdWithSubFile );

		$this->assertStringContainsString( '//archive.org/embed/', $service->getUrl() );
		$this->assertStringContainsString( '2024-12-21-18-15-xjtv-2/动画片_2024-12-20_19_24_xjtv2.mp4', $service->getUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\ArchiveOrg::getIdRegex
	 * @return void
	 */
	public function testUrlWithEncodedSubFile() {
		$service = new ArchiveOrg( $this->validIdWithEncodedSubFile );

		$this->assertStringContainsString( '//archive.org/embed/', $service->getUrl() );
		$this->assertStringContainsString( '00244%3D80126%3D79911%3D79912.avi', $service->getUrl() );
	}
}
