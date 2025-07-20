<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput;
use MediaWiki\FileRepo\File\UnregisteredLocalFile;

/**
 * @group EmbedVideo
 */
class AudioTransformOutputTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput
	 * @return void
	 */
	public function testConstructor() {
		$out = new AudioTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$this->assertInstanceOf( AudioTransformOutput::class, $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlNoParams() {
		$out = new AudioTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$out = $out->toHtml();

		$this->assertStringStartsWith( '<audio src="', $out );
		$this->assertStringContainsString( 'controls', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlNoControls() {
		$out = new AudioTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[
				'nocontrols' => true,
			]
		);

		$out = $out->toHtml();

		$this->assertStringStartsWith( '<audio src="', $out );
		$this->assertStringNotContainsString( 'controls', $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getSrc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getStyle
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::getDescription
	 * @return void
	 */
	public function testToHtmlFullParams() {
		$out = new AudioTransformOutput(
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
		$this->assertStringStartsWith( '<audio src="', $out );
		$this->assertStringContainsString( 'controls', $out );
		$this->assertStringContainsString( 'autoplay', $out );
		$this->assertStringContainsString( 'loop', $out );
		$this->assertStringContainsString( 'width="200"', $out );
		$this->assertStringContainsString( 'vertical-align', $out );
		$this->assertStringContainsString( 'FooDesc', $out );
		$this->assertStringContainsString( '#t=', $out );
	}
}
