<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use UnregisteredLocalFile;

/**
 * @group EmbedVideo
 */
class VideoTransformOutputTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput
	 * @return void
	 */
	public function testConstructor() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$this->assertInstanceOf( VideoTransformOutput::class, $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlNoParams() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$out = $out->toHtml();

		$this->assertStringStartsWith( '<video src="', $out );
		$this->assertStringContainsString( 'controls', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlNoControls() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'nocontrols' => true,
			]
		);

		$out = $out->toHtml();

		$this->assertStringStartsWith( '<video src="', $out );
		$this->assertStringNotContainsString( 'controls', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlFullParams() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'width' => 200,
				'height' => 200,
				'page' => 1,
				'autoplay' => 1,
				'loop' => 1,
				'start' => 1,
				'end' => 100,
				'descriptionUrl' => 'FooDesc',
			]
		);

		$out = $out->toHtml( [ 'valign' => 'middle' ] );
		$this->assertStringStartsWith( '<video src="', $out );
		$this->assertStringContainsString( 'controls', $out );
		$this->assertStringContainsString( 'autoplay', $out );
		$this->assertStringContainsString( 'loop', $out );
		$this->assertStringContainsString( 'width="200"', $out );
		$this->assertStringContainsString( 'vertical-align', $out );
		$this->assertStringContainsString( 'FooDesc', $out );
		$this->assertStringContainsString( '#t=', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlFullParamsGif() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'width' => 200,
				'height' => 200,
				'page' => 1,
				'autoplay' => 1,
				'loop' => 1,
				'start' => 1,
				'end' => 100,
				'descriptionUrl' => 'FooDesc',
				'gif' => 1,
			]
		);

		$out = $out->toHtml();
		$this->assertStringStartsWith( '<video src="', $out );
		$this->assertStringNotContainsString( 'controls', $out );
		$this->assertStringContainsString( 'autoplay', $out );
		$this->assertStringContainsString( 'loop', $out );
		$this->assertStringContainsString( 'playsinline', $out );
		$this->assertStringContainsString( 'width="200"', $out );
		$this->assertStringContainsString( 'FooDesc', $out );
		$this->assertStringContainsString( '#t=', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlParamsLazy() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'lazy' => true,
			]
		);

		$out = $out->toHtml( [ 'valign' => 'middle' ] );
		$this->assertStringStartsWith( '<video src="', $out );
		$this->assertStringContainsString( 'preload="none"', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * Ensures that in gallery contexts, width/height attributes are omitted
	 * and style does not include fixed width/height when 'no-dimensions' is set.
	 * @return void
	 */
	public function testToHtmlNoDimensions() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'width' => 200,
				'height' => 100,
			]
		);

		$html = $out->toHtml( [ 'no-dimensions' => true ] );
		$this->assertStringStartsWith( '<video src="', $html );
		$this->assertStringNotContainsString( 'width="', $html );
		$this->assertStringNotContainsString( 'height="', $html );
		$this->assertStringContainsString( 'style="', $html );
		$this->assertStringContainsString( 'max-width: 100%', $html );
		$this->assertStringNotContainsString( 'width: 200px', $html );
		$this->assertStringNotContainsString( 'height: 100px', $html );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::toHtml
	 * Ensures override-width/override-height behave like gallery sizing flags.
	 * @return void
	 */
	public function testToHtmlOverrideDimensions() {
		$out = new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'width' => 320,
				'height' => 180,
			]
		);

		$html = $out->toHtml( [ 'override-width' => 427.0, 'override-height' => 240.0 ] );
		$this->assertStringStartsWith( '<video src="', $html );
		$this->assertStringNotContainsString( 'width="', $html );
		$this->assertStringNotContainsString( 'height="', $html );
		$this->assertStringContainsString( 'max-width: 100%', $html );
		$this->assertStringNotContainsString( 'width: 320px', $html );
		$this->assertStringNotContainsString( 'height: 180px', $html );
	}
}
