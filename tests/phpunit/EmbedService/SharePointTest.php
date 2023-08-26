<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\SharePoint;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class SharePointTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://sub.sharepoint.com/sites/anything.mp4';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://sub.sharepoint.com/anything.mp4';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SharePoint::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SharePoint::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new SharePoint( $this->validUrlId );

		$this->assertInstanceOf( SharePoint::class, $service );
		$this->assertEquals( $this->validUrlId, $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SharePoint::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\SharePoint::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new SharePoint( $this->invalidUrlId );
	}
}
