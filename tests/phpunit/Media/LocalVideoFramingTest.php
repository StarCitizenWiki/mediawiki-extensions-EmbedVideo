<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

use LocalFile;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Linker\Linker;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * Integration tests verifying that local videos render correctly inside
 * thumb/frame contexts via the full Linker::makeThumbLink2 pipeline.
 *
 * @group EmbedVideo
 */
class LocalVideoFramingTest extends MediaWikiIntegrationTestCase {

	/**
	 * Returns a LocalFile mock that behaves like a 1280×720 .webm video.
	 *
	 * @param string $name
	 * @return LocalFile
	 */
	private function getVideoFileMock( string $name = 'Test.webm' ): LocalFile {
		$file = $this->getMockBuilder( LocalFile::class )
			->disableOriginalConstructor()
			->onlyMethods( [
				'exists',
				'getHandler',
				'getWidth',
				'getHeight',
				'getSize',
				'getPath',
				'getExtension',
				'getFullUrl',
				'getMetadataItems',
				'getMetadataItem',
				'transform',
				'isVectorized',
				'mustRender',
			] )
			->getMock();

		$file->method( 'exists' )->willReturn( true );
		$file->method( 'getHandler' )->willReturn( new VideoHandler() );
		$file->method( 'getWidth' )->willReturn( 1280 );
		$file->method( 'getHeight' )->willReturn( 720 );
		$file->method( 'getSize' )->willReturn( 500_000 );
		$file->method( 'getPath' )->willReturn( '/tmp/' . $name );
		$file->method( 'getExtension' )->willReturn( 'webm' );
		$file->method( 'getFullUrl' )->willReturn( 'https://example.org/' . $name );
		$file->method( 'getMetadataItems' )->willReturn( [] );
		$file->method( 'getMetadataItem' )->willReturn( null );
		$file->method( 'isVectorized' )->willReturn( false );
		$file->method( 'mustRender' )->willReturn( false );

		// Delegate transform() to the real VideoHandler so we exercise doTransform()
		$file->method( 'transform' )->willReturnCallback( static function ( $params ) use ( $file ) {
			$handler = $file->getHandler();
			return $handler->doTransform( $file, '', '', $params );
		} );

		return $file;
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 *
	 * When a video is rendered with |thumb|, the output should:
	 * 1. Have exactly one <figure> (core's frame), not nested figures
	 * 2. Have a <video> with height:auto so it scales proportionally
	 * 3. Have the mw-file-element class on the video
	 */
	public function testThumbVideoFraming(): void {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$file = $this->getVideoFileMock();
		$title = Title::makeTitle( NS_FILE, 'Test.webm' );

		$html = Linker::makeThumbLink2(
			$title,
			$file,
			[
				'align' => 'center',
				'caption' => 'Test Video',
				'thumbnail' => true,
			],
			[
				'width' => 640,
			]
		);

		// Core wraps in a <figure typeof="mw:File/Thumb">
		$this->assertStringContainsString( 'mw:File/Thumb', $html, 'Core should provide a thumb figure' );
		$this->assertStringContainsString( 'Test Video', $html, 'Caption should be present' );

		// The video should use height: auto, not a fixed pixel height
		$this->assertStringContainsString( 'height: auto', $html,
			'Video style should use height:auto for proportional scaling' );
		$this->assertStringNotContainsString( 'height: 360px', $html,
			'Video style should NOT have fixed pixel height' );

		// The embed-style path should NOT generate a second <figure>
		// Count <figure occurrences — there should be exactly one
		$this->assertSame( 1, substr_count( $html, '<figure' ),
			'Should have exactly one <figure> (core\'s), not nested figures from EmbedVideo' );

		// The embed overlay should still be present for the styled path
		$this->assertStringContainsString( 'embedvideo-localEmbedStyle', $html,
			'Local embed style overlay should be present' );

		// The video element should be present
		$this->assertStringContainsString( '<video', $html, 'A <video> element should be present' );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput::getStyle
	 *
	 * Same test with EmbedVideoUseEmbedStyleForLocalVideos=false (plain VideoTransformOutput path)
	 */
	public function testThumbVideoFramingWithoutEmbedStyle(): void {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => false,
		] );

		$file = $this->getVideoFileMock();
		$title = Title::makeTitle( NS_FILE, 'Test.webm' );

		$html = Linker::makeThumbLink2(
			$title,
			$file,
			[
				'align' => 'center',
				'caption' => 'Test Video',
				'thumbnail' => true,
			],
			[
				'width' => 640,
			]
		);

		$this->assertStringContainsString( 'mw:File/Thumb', $html );
		$this->assertStringContainsString( 'height: auto', $html,
			'Plain video path should also use height:auto' );
		$this->assertStringNotContainsString( 'height: 360px', $html );
		$this->assertSame( 1, substr_count( $html, '<figure' ),
			'Should have exactly one <figure>' );
		$this->assertStringContainsString( '<video', $html );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 *
	 * When a video is rendered with |frame| (no scaling), framing should still be correct.
	 */
	public function testFrameVideoFraming(): void {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$file = $this->getVideoFileMock();
		$title = Title::makeTitle( NS_FILE, 'Test.webm' );

		$html = Linker::makeThumbLink2(
			$title,
			$file,
			[
				'align' => 'right',
				'caption' => 'Framed video',
				'framed' => true,
			],
			[
				'width' => 1280,
			]
		);

		$this->assertStringContainsString( 'mw:File/Frame', $html,
			'Should use Frame (not Thumb) typeof' );
		$this->assertSame( 1, substr_count( $html, '<figure' ),
			'Should have exactly one <figure>' );
		$this->assertStringContainsString( 'height: auto', $html );
		$this->assertStringContainsString( '<video', $html );
	}
}
