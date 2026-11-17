<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\YouTube;

use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubePlaylist;
use MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTubeVideoList;
use MediaWikiIntegrationTestCase;

/**
 * $wgEmbedVideoYouTubeHideRelated: YouTube embeds get the IFrame Player API arguments the
 * related-videos overlay needs (enablejsapi, rel, origin).
 *
 * @group EmbedVideo
 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\YouTube\YouTube::getUrlArgs
 */
class YouTubeHideRelatedTest extends MediaWikiIntegrationTestCase {

	private const SERVER = 'https://en.example.org';

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			'CanonicalServer' => self::SERVER,
			'EmbedVideoYouTubeHideRelated' => true,
		] );
	}

	/**
	 * @param AbstractEmbedService $service
	 * @return array<string, string> the query of the service's embed URL
	 */
	private function query( AbstractEmbedService $service ): array {
		parse_str( (string)parse_url( $service->getUrl(), PHP_URL_QUERY ), $query );
		return $query;
	}

	public function testFlagOnAddsApiArguments(): void {
		$query = $this->query( new YouTube( 'pSsYTj9kCHE' ) );

		$this->assertSame( '1', $query['enablejsapi'] );
		$this->assertSame( '0', $query['rel'] );
		$this->assertSame( self::SERVER, $query['origin'] );
	}

	public function testEditorArgumentsAreNotDuplicated(): void {
		$service = new YouTube( 'pSsYTj9kCHE' );
		$service->setUrlArgs( 'rel=0&enablejsapi=1&start=30' );
		$url = $service->getUrl();

		$this->assertSame( 1, substr_count( $url, 'enablejsapi=' ) );
		$this->assertSame( 1, substr_count( $url, 'rel=' ) );
		$this->assertSame( '30', $this->query( $service )['start'] );
	}

	public function testEditorOriginIsReplacedByTheWikiServer(): void {
		// a hand-typed origin for another host silently disables the IFrame API on this one
		$service = new YouTube( 'pSsYTj9kCHE' );
		$service->setUrlArgs( 'rel=0&enablejsapi=1&origin=https://other.example.org' );
		$url = $service->getUrl();

		$this->assertSame( 1, substr_count( $url, 'origin=' ) );
		$this->assertSame( self::SERVER, $this->query( $service )['origin'] );
	}

	public function testFlagOffLeavesUrlUnchanged(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', false );

		$this->assertSame(
			'https://www.youtube-nocookie.com/embed/pSsYTj9kCHE',
			( new YouTube( 'pSsYTj9kCHE' ) )->getUrl()
		);
	}

	public function testPlaylistAndVideoListGetTheArguments(): void {
		$playlist = $this->query( new YouTubePlaylist( 'PLY-aGAsWjAU_nHYm8hDKBnS5sqLjf6IE_' ) );
		$this->assertSame( '1', $playlist['enablejsapi'] );
		$this->assertSame( 'PLY-aGAsWjAU_nHYm8hDKBnS5sqLjf6IE_', $playlist['list'] );

		$list = new YouTubeVideoList( 'pSsYTj9kCHE' );
		$list->setUrlArgs( 'playlist=afpRzcAAZVM,gMEHZPZTAVc' );
		$query = $this->query( $list );
		$this->assertSame( '1', $query['enablejsapi'] );
		$this->assertSame( 'afpRzcAAZVM,gMEHZPZTAVc', $query['playlist'] );
	}

	public function testHtmlEscapedEditorArgumentsAreUnderstood(): void {
		// wikitext editors sometimes type the separators as &amp;
		$service = new YouTube( 'pSsYTj9kCHE' );
		$service->setUrlArgs( 'rel=0&amp;enablejsapi=1&amp;start=30' );
		$url = $service->getUrl();

		$this->assertStringNotContainsString( 'amp%3B', $url );
		$this->assertSame( 1, substr_count( $url, 'enablejsapi=' ) );
		$this->assertSame( '30', $this->query( $service )['start'] );
	}
}
