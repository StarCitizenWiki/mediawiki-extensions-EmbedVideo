<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWikiIntegrationTestCase;
use UnregisteredLocalFile;

/**
 * @group EmbedVideo
 */
class LocalVideoTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo::setTitle
	 * @return void
	 */
	public function testConstructor() {
		$service = new LocalVideo(
			new VideoTransformOutput( UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ), [] ),
			[]
		);

		$this->assertInstanceOf( LocalVideo::class, $service );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo::getDefaultWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo::getDefaultHeight
	 * @return void
	 */
	public function testGetWidthHeight() {
		$output = $this->getMockBuilder( VideoTransformOutput::class )->disableOriginalConstructor()->getMock();
		$output->expects( $this->once() )->method( 'getWidth' )->willReturn( 600 );
		$output->expects( $this->once() )->method( 'getHeight' )->willReturn( 300 );

		$service = new LocalVideo( $output, [] );

		$this->assertEquals( 600, $service->getWidth() );
		$this->assertEquals( 300, $service->getHeight() );
	}
}
