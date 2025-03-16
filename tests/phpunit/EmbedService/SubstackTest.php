<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\Substack;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class SubstackTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = 'gregreese';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://gregreese.substack.com/p/recent-study-shows-self-assembly';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://substack.com/p/foo';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new Substack( $this->validUrlId );

		$this->assertInstanceOf( Substack::class, $service );
		$this->assertEquals( $this->validId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new Substack( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Substack::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new Substack( $this->validUrlId );

		$this->assertStringContainsString( '//gregreese.substack.com/embed/p', $service->getUrl() );
	}
}
