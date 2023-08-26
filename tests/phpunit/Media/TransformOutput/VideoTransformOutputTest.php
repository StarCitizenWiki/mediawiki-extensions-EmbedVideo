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
}
