<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use Exception;
use MediaWiki\Extension\EmbedVideo\OEmbed;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Status\Status;
use MWHttpRequest;

/**
 * @group EmbedVideo
 */
class OEmbedTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::get
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::newFromRequest
	 * @return void
	 */
	public function testConstructor() {
		$this->mockHttp();

		$oe = OEmbed::newFromRequest( 'foo' );

		$this->assertInstanceOf( OEmbed::class, $oe );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::get
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::newFromRequest
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getHtml
	 * @return void
	 */
	public function testGetHtml() {
		$this->mockHttp( json_encode( [
			'html' => '<iframe></iframe>',
		] ) );

		$oe = OEmbed::newFromRequest( 'foo' );

		$this->assertInstanceOf( OEmbed::class, $oe );
		$this->assertEquals( '<iframe></iframe>', $oe->getHtml() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::get
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::newFromRequest
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getTitle
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getAuthorName
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getAuthorUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getProviderName
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getProviderUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getHeight
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getThumbnailWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getThumbnailHeight
	 * @return void
	 */
	public function testGetAllFalse() {
		$this->mockHttp();

		$methods = [
			'getTitle',
			'getAuthorName',
			'getAuthorUrl',
			'getProviderName',
			'getProviderUrl',
			'getWidth',
			'getHeight',
			'getThumbnailWidth',
			'getThumbnailHeight',
		];

		$oe = OEmbed::newFromRequest( 'foo' );

		foreach ( $methods as $method ) {
			$this->assertFalse( $oe->{$method}() );
		}
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::get
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::newFromRequest
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getTitle
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getAuthorName
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getAuthorUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getProviderName
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getProviderUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getHeight
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getThumbnailWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\OEmbed::getThumbnailHeight
	 * @return void
	 */
	public function testGetAll() {
		$data = [
			'getTitle' => 'title',
			'getAuthorName' => 'author_name',
			'getAuthorUrl' => 'author_url',
			'getProviderName' => 'provider_name',
			'getProviderUrl' => 'provider_url',
			'getWidth' => 640,
			'getHeight' => 320,
			'getThumbnailWidth' => 600,
			'getThumbnailHeight' => 300,
		];

		$this->mockHttp( json_encode( [
			'title' => 'title',
			'author_name' => 'author_name',
			'author_url' => 'author_url',
			'provider_name' => 'provider_name',
			'provider_url' => 'provider_url',
			'width' => 640,
			'height' => 320,
			'thumbnail_width' => 600,
			'thumbnail_height' => 300,
		] ) );

		$oe = OEmbed::newFromRequest( 'foo' );

		foreach ( array_keys( $data ) as $method ) {
			$this->assertEquals( $data[$method], $oe->{$method}() );
		}
	}

	/**
	 * Mock HTTP
	 *
	 * @param string $return
	 * @return void
	 * @throws Exception
	 */
	private function mockHttp( string $return = '[]' ) {
		$req = $this->getMockBuilder( MWHttpRequest::class )->disableOriginalConstructor()->getMock();
		$req->expects( $this->once() )->method( 'execute' )->willReturn( Status::newGood() );
		$req->expects( $this->once() )->method( 'getContent' )->willReturn( $return );

		$mock = $this->getMockBuilder( HttpRequestFactory::class )->disableOriginalConstructor()->getMock();
		$mock->expects( $this->once() )->method( 'create' )->willReturn( $req );

		$this->setService( 'HttpRequestFactory', $mock );
	}
}
