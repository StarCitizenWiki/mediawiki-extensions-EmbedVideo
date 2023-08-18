<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWikiIntegrationTestCase;
use ParserOptions;
use ParserTestMockParser;
use PPCustomFrame_Hash;
use PPFrame_Hash;

class EmbedVideoTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVUYouTubeValid() {
		$parser = $this->getServiceContainer()->getParser();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$frame = new PPFrame_Hash( $parser->getPreprocessor() );

		$output = EmbedVideo::parseEVU(
			$parser,
			$frame,
			[
				'https://youtube.com/?v=foobar',
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '<figure class="embedvideo" data-service="youtube"', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVUYouTubeInvalid() {
		$parser = $this->getServiceContainer()->getParser();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$frame = new PPFrame_Hash( $parser->getPreprocessor() );

		$output = EmbedVideo::parseEVU(
			$parser,
			$frame,
			[
				'https://youtu.be/foobar',
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringNotContainsString( '<figure class="embedvideo" data-service="youtube"', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVYouTubeValid() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$parser = $this->getServiceContainer()->getParser();

		$parser->setOptions( ParserOptions::newFromAnon() );
		$frame = new PPCustomFrame_Hash( $parser->getPreprocessor(), [] );

		$output = EmbedVideo::parseEV(
			new ParserTestMockParser(),
			$frame,
			[
				'youtube',
				'https://youtube.com/?v=foobar',
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
        // phpcs:ignore Generic.Files.LineLength.TooLong
		$this->assertStringContainsString( '<figure class="embedvideo" data-service="youtube" data-iframeconfig=\'{"src":"//www.youtube-nocookie.com/embed/foobar?autoplay=1"}\' style="width:640px">', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVYouTubeValidCustomArgs() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$parser = $this->getServiceContainer()->getParser();

		$parser->setOptions( ParserOptions::newFromAnon() );
		$frame = new PPCustomFrame_Hash( $parser->getPreprocessor(), [] );

		$output = EmbedVideo::parseEV(
			new ParserTestMockParser(),
			$frame,
			[
				'youtube',
				'https://youtube.com/?v=foobar',
				'dimensions=200x200px'
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '"width":200,"height":200', $output[0] );
	}
}
