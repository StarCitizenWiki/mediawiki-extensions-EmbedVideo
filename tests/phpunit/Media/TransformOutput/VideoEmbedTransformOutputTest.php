<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput;
use MediaWikiIntegrationTestCase;
use UnregisteredLocalFile;

/**
 * @group EmbedVideo
 */
class VideoEmbedTransformOutputTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput
	 * @return void
	 */
	public function testConstructor() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testToHtml() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$out = $out->toHtml();

		$this->assertStringContainsString( '<video src="', $out );
		$this->assertStringContainsString( '<figure class="embedvideo" data-service="local-embed"', $out );
		$this->assertStringContainsString( 'embedvideo--local-embed-style', $out );
		$this->assertStringNotContainsString( 'embedvideo-consent', $out );
		$this->assertMatchesRegularExpression(
			'/<div class="embedvideo-wrapper"[^>]*>.*<video\s/s',
			$out
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeLocalVideoEmbedStyleHtml
	 * @return void
	 */
	public function testToHtmlIncludesPassiveLocalEmbedStyle() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[ 'title' => 'Example local title' ]
		);

		$out = $out->toHtml();

		$this->assertStringContainsString( 'embedvideo-localEmbedStyle', $out );
		$this->assertStringContainsString( 'embedvideo-loader__title', $out );
		$this->assertStringContainsString( 'Example local title', $out );
		$this->assertStringContainsString( 'embedvideo-loader__fakeButton', $out );
		$this->assertStringContainsString( 'embedvideo-loader__service', $out );
	}
}
