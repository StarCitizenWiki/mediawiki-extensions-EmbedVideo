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
		$this->assertStringContainsString( '<figure class="embedvideo', $out );
		$this->assertStringContainsString( 'data-service="local-embed"', $out );
		$this->assertStringContainsString( 'embedvideo--local-embed-style', $out );
		$this->assertStringNotContainsString( 'embedvideo-consent', $out );
		$this->assertStringNotContainsString( 'width: px', $out );
		$this->assertStringNotContainsString( 'height: px', $out );
		$this->assertMatchesRegularExpression(
			'/<div class="embedvideo-wrapper"[^>]*>.*<video\s/s',
			$out
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeLocalVideoEmbedStyleHtml
	 * Ensures that when core provides framing options (thumb/frame), no nested <figure>
	 * is generated — only a wrapper div with overlay + video.
	 * @return void
	 */
	public function testToHtmlWithCoreOptions() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[ 'width' => 640, 'height' => 360, 'title' => 'Test Video' ]
		);

		$html = $out->toHtml( [ 'img-class' => 'mw-file-element' ] );

		// Should NOT contain a <figure> wrapper (core provides the frame)
		$this->assertStringNotContainsString( '<figure', $html );
		// Should contain the wrapper div with embed style class
		$this->assertStringContainsString( 'embedvideo-wrapper', $html );
		$this->assertStringContainsString( 'embedvideo--local-embed-style', $html );
		// Should contain the local embed style overlay
		$this->assertStringContainsString( 'embedvideo-localEmbedStyle', $html );
		// Should contain the video with the core-provided class
		$this->assertStringContainsString( 'mw-file-element', $html );
		$this->assertStringContainsString( '<video', $html );
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
