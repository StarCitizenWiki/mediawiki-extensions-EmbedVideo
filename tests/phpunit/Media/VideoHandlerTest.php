<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

use Exception;
use LocalFile;
use MediaTransformOutput;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Shell\Command;
use MediaWiki\Shell\CommandFactory;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use RepoGroup;
use Shellbox\Command\UnboxedResult;
use Wikimedia\AtEase\AtEase;

/**
 * @group EmbedVideo
 */
class VideoHandlerTest extends \MediaWikiIntegrationTestCase {
	/**
	 * Set FFProbe to an existing invalid location
	 * @return void
	 */
	protected function setUp(): void {
		$this->overrideConfigValues( [
			'FFProbeLocation' => '/dev/null',
		] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getParamMap
	 * @return void
	 */
	public function testParamMap(): void {
		$handler = new VideoHandler();

		$this->assertIsArray( $handler->getParamMap() );
		$this->assertNotEmpty( $handler->getParamMap() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::validateParam
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::parseTimeString
	 * @return void
	 */
	public function testValidateParam(): void {
		$handler = new VideoHandler();

		$test = [
			[ 'width', 0, false ],
			[ 'width', 100, true ],
			[ 'width', -100, false ],
			[ 'width', '-100', false ],

			[ 'start', '1:30', true ],
			[ 'start', '', false ],

			[ 'end', '1:30', true ],
			[ 'end', '', false ],

			[ 'autoplay', null, true ],
			[ 'loop', null, true ],
			[ 'nocontrols', null, true ],
			[ 'poster', null, true ],
			[ 'gif', null, true ],
			[ 'muted', null, true ],
			[ 'title', null, true ],
			[ 'description', null, true ],
			[ 'lazy', null, true ],
			[ 'autoresize', null, true ],

			[ 'explode', null, false ],
		];

		foreach ( $test as $toTest ) {
			$this->assertEquals( $toTest[2], $handler->validateParam( $toTest[0], $toTest[1] ) );
		}
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testNormaliseParamsSmallerWidth() {
		$this->overrideConfigValues( [
			'EmbedVideoLazyLoadLocalVideos' => 'custom-val',
		] );

		$handler = new VideoHandler();

		$params = [
			'width' => 1280,
		];

		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => 'video',
					'width' => 1920,
					'height' => 1080,
				]
			],
			'format' => [],
		] ) );

		$this->mockFFProbeCommand( $result );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$file->method( 'getLocalRefPath' )->willReturn( '/dev/null' );

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 1280, $params['width'] );
		$this->assertEquals( 720, $params['height'] );
		$this->assertEquals( 'custom-val', $params['lazy'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testNormaliseParamsCalculatedSize() {
		$this->overrideConfigValues( [
			'EmbedVideoDefaultWidth' => 1280,
		] );

		$handler = new VideoHandler();
		$params = [];

		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => 'video',
					'width' => 0,
					'height' => 0,
				]
			],
			'format' => [],
		] ) );

		$this->mockFFProbeCommand( $result );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 1280, $params['width'] );
		$this->assertEquals( 720, $params['height'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testNormaliseParamsSquareVideo() {
		$handler = new VideoHandler();
		$params = [
			'width' => 200,
			'height' => 200,
		];

		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => 'video',
					'width' => 200,
					'height' => 200,
				]
			],
			'format' => [],
		] ) );

		$this->mockFFProbeCommand( $result );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 200, $params['width'] );
		$this->assertEquals( 200, $params['height'] );
	}

	/**
	 * Video: 16/9
	 * Given: 1/2
	 *
	 * Recalculate height to fit original aspect
	 *
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testNormaliseParamsDifferentAspectRatio() {
		$handler = new VideoHandler();
		$params = [
			'width' => 500,
			'height' => 250,
		];

		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => 'video',
					'width' => 1920,
					'height' => 1080,
				]
			],
			'format' => [],
		] ) );

		$this->mockFFProbeCommand( $result );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 500, $params['width'] );
		// Actually 281.25
		$this->assertEquals( 281, (int)$params['height'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testNormaliseParamsPoster() {
		$this->markTestSkipped( 'Somehow: Class "MediaWiki\Title\TitleFactory" does not exist on MW1_39' );

		$this->overrideConfigValues( [
			'EmbedVideoLazyLoadLocalVideos' => 'custom-val',
		] );

		$handler = new VideoHandler();

		$params = [
			'poster' => 'ExamplePoster.jpg',
		];

		$titleMock = $this->getMockBuilder( Title::class )->disableOriginalConstructor()->getMock();
		$titleMock->method( 'exists' )->willReturn( true );

		$titleFactoryMock = $this->getMockBuilder( TitleFactory::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'newFromText' ] )
			->getMock();
		$titleFactoryMock->expects( $this->once() )
			->method( 'newFromText' )
			->with( 'ExamplePoster.jpg', NS_FILE )
			->willReturn( $titleMock );

		$this->mockFFProbeCommand();

		$output = $this->getMockBuilder( MediaTransformOutput::class )->getMock();
		$output->expects( $this->once() )->method( 'getUrl' )->willReturn( 'http://localhost' );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();
		$file->expects( $this->once() )
			->method( 'transform' )
			->willReturn( $output );
		$file->method( 'getLocalRefPath' )->willReturn( '/dev/null' );

		$repoMock = $this->getMockBuilder( RepoGroup::class )->disableOriginalConstructor()->getMock();
		$repoMock->expects( $this->once() )
			->method( 'findFile' )
			->willReturn( $file );

		$urlMock = $this->getMockBuilder( UrlUtils::class )->disableOriginalConstructor()->getMock();
		$urlMock->expects( $this->once() )
			->method( 'expand' )
			->with( 'http://localhost' )
			->willReturn( 'expanded-url' );

		$this->setService( 'RepoGroup', $repoMock );
		$this->setService( 'TitleFactory', $titleFactoryMock );
		$this->setService( 'UrlUtils', $urlMock );

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 'expanded-url', $params['posterUrl'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testDoTransform() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => false,
		] );

		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertInstanceOf( VideoTransformOutput::class, $handler->doTransform( $file, '', '', [] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testDoTransformStyled() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$title = Title::newFromText( 'Main_Page' );

		$this->setMwGlobals( [
			'wgTitle' => $title,
		] );

		RequestContext::resetMain();

		$context = RequestContext::getMain();
		$context->setTitle( $title );

		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $handler->doTransform( $file, '', '', [] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testDoTransformStyledNotWhenGif() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$title = Title::newFromText( 'Main_Page' );

		$this->setMwGlobals( [
			'wgTitle' => $title,
		] );

		RequestContext::resetMain();

		$context = RequestContext::getMain();
		$context->setTitle( $title );

		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$handler->normaliseParams( $file, $params );

		$this->assertInstanceOf(
			VideoTransformOutput::class,
			$handler->doTransform( $file, '', '', [ 'gif' => true ] )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getDimensionsString
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetDimensionsString() {
		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$msg = $handler->getDimensionsString( $file );

		$this->assertEquals(
			$msg,
			wfMessage(
				'embedvideo-video-short-desc',
				$this->getServiceContainer()->getContentLanguage()->formatTimePeriod( false ),
				640,
				320
			)->plain()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getDimensionsString
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetDimensionsStringInvalidResult() {
		$handler = new VideoHandler();

		$result = new UnboxedResult();
		$result->stdout( '<' );

		$this->mockFFProbeCommand( $result );

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		AtEase::suppressWarnings();
		$msg = $handler->getDimensionsString( $file );
		AtEase::restoreWarnings();

		$this->assertEmpty( $msg );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getShortDesc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetShortDesc() {
		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();

		$msg = $handler->getShortDesc( $file );

		$this->assertEquals(
			$msg,
			wfMessage(
				'embedvideo-video-short-desc',
				$this->getServiceContainer()->getContentLanguage()->formatTimePeriod( false ),
				640,
				320,
				1000
			)->plain()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getLongDesc
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetLongDesc() {
		$handler = new VideoHandler();

		$this->mockFFProbeCommand();

		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();
		$file->expects( $this->once() )->method( 'getPath' )->willReturn( 'foo.mp4' );

		$msg = $handler->getLongDesc( $file );

		$this->assertEquals(
			$msg,
			wfMessage(
				'embedvideo-video-long-desc',
				'MP4',
				'',
				$this->getServiceContainer()->getContentLanguage()->formatTimePeriod( false ),
				640,
				320,
				$this->getServiceContainer()->getContentLanguage()->formatBitrate( false ),
			)->plain()
		);
	}

	/**
	 * Mock the invocations used by invokeFFProbe
	 *
	 * @param UnboxedResult|null $result
	 * @return void
	 * @throws Exception
	 */
	private function mockFFProbeCommand( ?UnboxedResult $result = null ): void {
		if ( $result === null ) {
			$result = new UnboxedResult();
			$result->stdout( json_encode( [
				'streams' => [
					[
						'codec_type' => 'video',
						'width' => 640,
						'height' => 320,
					]
				],
				'format' => [],
			] ) );
		}

		$commandMock = $this->getMockBuilder( Command::class )->disableOriginalConstructor()->getMock();
		$commandMock->expects( $this->atLeast( 1 ) )->method( 'params' );
		$commandMock->expects( $this->atLeast( 1 ) )->method( 'unsafeParams' );
		$commandMock->expects( $this->atLeast( 1 ) )->method( 'execute' )->willReturn( $result );

		$shellMock = $this->getMockBuilder( CommandFactory::class )->disableOriginalConstructor()->getMock();
		$shellMock->expects( $this->atLeast( 1 ) )->method( 'create' )->willReturn( $commandMock );

		$this->setService( 'ShellCommandFactory', $shellMock );
	}
}
