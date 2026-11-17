<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MockHttpTrait;

/**
 * The related-videos overlay module is added only for YouTube embeds and only with the flag on.
 *
 * @group EmbedVideo
 * @group Database
 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo
 */
class YouTubeOverlayModuleTest extends MediaWikiIntegrationTestCase {
	use MockHttpTrait;

	private function output( string $wikitext ) {
		$parser = $this->getServiceContainer()->getParserFactory()->create();
		return $parser->parse( $wikitext, Title::makeTitle( NS_MAIN, 'Overlay test' ),
			ParserOptions::newFromAnon() );
	}

	private function modules( string $wikitext ): array {
		return $this->output( $wikitext )->getModules();
	}

	public function testAddedForYouTubeWithFlag(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', true );

		$this->assertContains( 'ext.embedVideo.youtubeOverlay', $this->modules( '{{#ev:youtube|pSsYTj9kCHE}}' ) );
		$this->assertContains( 'ext.embedVideo.youtubeOverlay',
			$this->modules( '<youtube>https://www.youtube.com/watch?v=pSsYTj9kCHE</youtube>' ) );
	}

	public function testNotAddedWithoutFlag(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', false );

		$this->assertNotContains( 'ext.embedVideo.youtubeOverlay', $this->modules( '{{#ev:youtube|pSsYTj9kCHE}}' ) );
	}

	public function testNotAddedForOtherServices(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', true );

		$this->assertNotContains( 'ext.embedVideo.youtubeOverlay', $this->modules( '{{#ev:vimeo|76979871}}' ) );
	}

	public function testAddedForPlaylistsAndVideoLists(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', true );

		$this->assertContains( 'ext.embedVideo.youtubeOverlay',
			$this->modules( '{{#ev:youtubeplaylist|PLY-aGAsWjAU_nHYm8hDKBnS5sqLjf6IE_}}' ) );
		$this->assertContains( 'ext.embedVideo.youtubeOverlay',
			$this->modules( '{{#ev:youtubevideolist|pSsYTj9kCHE|||||playlist=afpRzcAAZVM,gMEHZPZTAVc}}' ) );
	}

	public function testNotAddedForOEmbed(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', true );
		// the oEmbed service fetches YouTube's own embed HTML: answer with a minimal oEmbed document
		$this->installMockHttp( $this->makeFakeHttpRequest( json_encode( [
			'type' => 'video',
			'version' => '1.0',
			'html' => '<iframe src="https://www.youtube.com/embed/pSsYTj9kCHE"></iframe>',
			'width' => 640,
			'height' => 360,
		] ) ) );

		$this->assertNotContains(
			'ext.embedVideo.youtubeOverlay',
			$this->modules( '{{#ev:youtubeoembed|pSsYTj9kCHE}}' )
		);
	}

	public function testApiScriptIsAllowedByCsp(): void {
		$this->overrideConfigValue( 'EmbedVideoYouTubeHideRelated', true );

		$this->assertContains( 'https://www.youtube.com',
			$this->output( '{{#ev:youtube|pSsYTj9kCHE}}' )->getExtraCSPScriptSrcs() );
	}
}
