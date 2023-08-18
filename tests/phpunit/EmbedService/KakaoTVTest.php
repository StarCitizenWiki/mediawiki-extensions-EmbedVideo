<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class KakaoTVTest extends MediaWikiIntegrationTestCase {

	/**
	 * A valid ID
	 * @var string
	 */
	private string $validId = '301157950';

	/**
	 * An invalid id
	 * @var string
	 */
	private string $invalidId = '<Foo>';

	/**
	 * A valid url containing an id
	 * @var string
	 */
	private string $validUrlId = 'https://play-tv.kakao.com/embed/player/cliplink/301157950';

	/**
	 * An invalid url
	 * @var string
	 */
	private string $invalidUrlId = 'https://play-tv.kakao.com/embed/301a157950';

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @return void
	 */
	public function testInvalidId() {
		$this->expectException( InvalidArgumentException::class );

		new KakaoTV( $this->invalidId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getIdRegex
	 * @return void
	 */
	public function testValidId() {
		$service = new KakaoTV( $this->validId );

		$this->assertInstanceOf( KakaoTV::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getIdRegex
	 * @return void
	 */
	public function testValidUrlId() {
		$service = new KakaoTV( $this->validUrlId );

		$this->assertInstanceOf( KakaoTV::class, $service );
		$this->assertSame( '301157950', $service->parseVideoID( $this->validUrlId ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getIdRegex
	 * @return void
	 */
	public function testInvalidUrlId() {
		$this->expectException( InvalidArgumentException::class );
		new KakaoTV( $this->invalidUrlId );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::parseVideoID
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrl
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getUrlRegex
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\KakaoTV::getIdRegex
	 * @return void
	 */
	public function testUrl() {
		$service = new KakaoTV( $this->validUrlId );

		$this->assertStringContainsString( '//play-tv.kakao.com/embed/player/cliplink/', $service->getUrl() );
	}
}
