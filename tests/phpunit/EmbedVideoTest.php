<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use Exception;
use MediaWiki\Extension\EmbedVideo\EmbedVideo;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\PPCustomFrame_Hash;
use MediaWiki\Parser\PPFrame_Hash;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class EmbedVideoTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseArgs
	 * @return void
	 */
	public function testConstructor() {
		$ev = new EmbedVideo( null, [] );

		$this->assertInstanceOf( EmbedVideo::class, $ev );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVUYouTubeValid() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVU(
			$parser,
			$this->getFrame( $parser ),
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
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVUYouTubeInvalid() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVU(
			$parser,
			$this->getFrame( $parser ),
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
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVU
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVUEmpty() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVU(
			$parser,
			$this->getFrame( $parser ),
			[],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( 'errorbox', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVYouTubeValid() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
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
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVYouTubeValidCustomArgs() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
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

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVDimensionHeightOnly() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
			[
				'youtube',
				'https://youtube.com/?v=foobar',
				'dimensions=x200px'
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '"height":200', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVDimensionWidthOnly() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
			[
				'youtube',
				'https://youtube.com/?v=foobar',
				'dimensions=200px'
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '"width":200', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVTag() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVTag(
			'',
			[
				'service' => 'youtube',
				'id' => 'https://youtube.com/?v=foobar',
			],
			$parser,
			$this->getFrame( $parser ),
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::error
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVTagMissingService() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVTag(
			'',
			[
				'id' => 'https://youtube.com/?v=foobar',
			],
			$parser,
			$this->getFrame( $parser ),
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( wfMessage( 'embedvideo-error-missingparams' )->plain(), $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::error
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVTagIdInInput() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVTag(
			'https://youtube.com/?v=foobar',
			[
				'service' => 'youtube',
			],
			$parser,
			$this->getFrame( $parser ),
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString(
			'<figure class="embedvideo" data-service="youtube" data-iframeconfig',
			$output[0]
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @return void
	 * @throws Exception
	 */
	public function testParseArgsExample1() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
			[
				'youtube',
				'pSsYTj9kCHE'
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString(
			'<figure class="embedvideo" data-service="youtube" data-iframeconfig',
			$output[0]
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::setAlignment
	 * @return void
	 * @throws Exception
	 */
	public function testParseArgsExample2() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
			[
				'youtube',
				'https://www.youtube.com/watch?v=pSsYTj9kCHE',
				'1000',
				'right',
				'Example description',
				'frame',
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '"width":1000', $output[0] );
		$this->assertStringContainsString( '<figcaption>Example description</figcaption>', $output[0] );
		$this->assertStringContainsString( 'mw-halign-right', $output[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVTag
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEV
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::output
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::makeHtmlFormatConfig
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::setAlignment
	 * @return void
	 * @throws Exception
	 */
	public function testParseArgsExample6() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEV(
			$parser,
			$this->getFrame( $parser ),
			[
				'youtube',
				'pSsYTj9kCHE',
				'dimensions=320x320',
				'title=Title of the Embed',
			],
			false
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
		$this->assertStringContainsString( '"width":320,"height":320', $output[0] );
		$this->assertStringContainsString(
			'<div class="embedvideo-loader__title embedvideo-loader__title--manual"><a',
			$output[0]
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::parseEVL
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::init
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideo::addModules
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getIframeConfig
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getPrivacyPolicyUrl
	 * @return void
	 * @throws Exception
	 */
	public function testParseEVLYouTube() {
		$parser = $this->getParser();

		$output = EmbedVideo::parseEVL(
			$parser,
			$this->getFrame( $parser ),
			[
				'pSsYTj9kCHE',
				'text=Test Text'
			]
		);

		$this->assertIsArray( $output );
		$this->assertCount( 3, $output );
        // phpcs:ignore Generic.Files.LineLength.TooLong
		$this->assertStringContainsString( '<a data-iframeconfig="', $output[0] );
		$this->assertStringContainsString( 'Test Text', $output[0] );
	}

	/**
	 * Get a fresh parser
	 *
	 * @return Parser
	 * @throws Exception
	 */
	public function getParser(): Parser {
		$parser = $this->getServiceContainer()->getParserFactory()->create();
		$parser->setOptions( ParserOptions::newFromAnon() );
		$parser->clearState();
		$parser->setOutputType( Parser::OT_HTML );

		return $parser;
	}

	/**
	 * Get a frame
	 *
	 * @param Parser|null $parser
	 * @return PPFrame_Hash
	 * @throws Exception
	 */
	private function getFrame( ?Parser $parser ): PPFrame_Hash {
		if ( $parser === null ) {
			$parser = $this->getParser();
		}

		return new PPCustomFrame_Hash( $parser->getPreprocessor(), [] );
	}
}
