<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

use LocalFile;
use MediaTransformOutput;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use RepoGroup;

/**
 * @group EmbedVideo
 */
class VideoHandlerTest extends \MediaWikiIntegrationTestCase {
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
	 * @return void
	 */
	public function testNormaliseParamsSmallerWidth() {
		$this->overrideConfigValues( [
			'EmbedVideoLazyLoadLocalVideos' => 'custom-val',
		] );

		$handler = new VideoHandler();
		$params = [ 'width' => 1280 ];

		$file = $this->getVideoFileMock();

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 1280, $params['width'] );
		$this->assertEquals( 720, $params['height'] );
		$this->assertEquals( 'custom-val', $params['lazy'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsCalculatedSize() {
		$this->overrideConfigValues( [
			'EmbedVideoDefaultWidth' => 1280,
		] );

		$handler = new VideoHandler();
		$params = [];

		$file = $this->getVideoFileMock( [], 0, 0 );

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 1280, $params['width'] );
		$this->assertEquals( 720, $params['height'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsSquareVideo() {
		$handler = new VideoHandler();
		$params = [
			'width' => 200,
			'height' => 200,
		];

		$file = $this->getVideoFileMock( [], 200, 200 );

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 200, $params['width'] );
		$this->assertEquals( 200, $params['height'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsDifferentAspectRatio() {
		$handler = new VideoHandler();
		$params = [
			'width' => 500,
			'height' => 250,
		];

		$file = $this->getVideoFileMock();

		$handler->normaliseParams( $file, $params );

		$this->assertEquals( 500, $params['width'] );
		$this->assertEquals( 281, (int)$params['height'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsPoster() {
		$this->markTestSkipped( 'Somehow: Class "MediaWiki\\Title\\TitleFactory" does not exist on MW1_39' );

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

		$output = $this->getMockBuilder( MediaTransformOutput::class )->getMock();
		$output->expects( $this->once() )->method( 'getUrl' )->willReturn( 'http://localhost' );

		$file = $this->getMockBuilder( LocalFile::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'transform', 'getWidth', 'getHeight', 'getFullUrl' ] )
			->getMock();
		$file->expects( $this->once() )
			->method( 'transform' )
			->willReturn( $output );
		$file->method( 'getWidth' )->willReturn( 1920 );
		$file->method( 'getHeight' )->willReturn( 1080 );
		$file->method( 'getFullUrl' )->willReturn( 'https://example.org/foo.mp4' );

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
	 * @return void
	 */
	public function testDoTransform() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => false,
		] );

		$handler = new VideoHandler();
		$file = $this->getVideoFileMock();

		$this->assertInstanceOf( VideoTransformOutput::class, $handler->doTransform( $file, '', '', [] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testDoTransformStyled() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$title = $this->getTitleMock( NS_MAIN );

		$this->setMwGlobals( [
			'wgTitle' => $title,
		] );

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( $title );

		$handler = new VideoHandler();
		$file = $this->getVideoFileMock();

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $handler->doTransform( $file, '', '', [] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testDoTransformStyledOnFilePage(): void {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
			'EmbedVideoLazyLoadLocalVideos' => true,
		] );

		$title = $this->getTitleMock( NS_FILE );

		$this->setMwGlobals( [
			'wgTitle' => $title,
		] );

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( $title );

		$handler = new VideoHandler();
		$file = $this->getVideoFileMock();
		$output = $handler->doTransform( $file, '', '', [] );
		$parameters = ( new \ReflectionProperty(
			\MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput::class,
			'parameters'
		) );

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $output );
		$this->assertFalse( $parameters->getValue( $output )['lazy'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testDoTransformStyledWithoutRequestTitle(): void {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$this->setMwGlobals( [
			'wgTitle' => null,
		] );

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( null );

		$handler = new VideoHandler();
		$file = $this->getVideoFileMock();

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $handler->doTransform( $file, '', '', [] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::normaliseParams
	 * @return void
	 */
	public function testDoTransformStyledNotWhenGif() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$title = $this->getTitleMock( NS_MAIN );

		$this->setMwGlobals( [
			'wgTitle' => $title,
		] );

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( $title );

		$handler = new VideoHandler();
		$file = $this->getVideoFileMock();

		$this->assertInstanceOf(
			VideoTransformOutput::class,
			$handler->doTransform( $file, '', '', [ 'gif' => true ] )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getDimensionsString
	 * @return void
	 */
	public function testGetDimensionsString() {
		$handler = $this->getVideoHandlerWithoutProbe();

		$file = $this->getVideoFileMock( [ 'duration' => '10' ], 640, 320 );

		$result = $handler->getDimensionsString( $file );

		$this->assertStringContainsString( 'duration:10', $result );
		$this->assertStringContainsString( '640', $result );
		$this->assertStringContainsString( '320', $result );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getDimensionsString
	 * @return void
	 */
	public function testGetDimensionsStringMissingMetadata() {
		$handler = $this->getVideoHandlerWithoutProbe();

		$file = $this->getVideoFileMock( [], 640, 320 );

		$result = $handler->getDimensionsString( $file );

		$this->assertStringNotContainsString( 'duration:', $result );
		$this->assertStringContainsString( '640', $result );
		$this->assertStringContainsString( '320', $result );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getShortDesc
	 * @return void
	 */
	public function testGetShortDesc() {
		$handler = $this->getVideoHandlerWithoutProbe();

		$file = $this->getVideoFileMock( [ 'duration' => '10' ], 640, 320, 1000 );

		$result = $handler->getShortDesc( $file );

		$this->assertStringContainsString( 'duration:10', $result );
		$this->assertStringContainsString( '640', $result );
		$this->assertStringContainsString( '320', $result );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getLongDesc
	 * @return void
	 */
	public function testGetLongDesc() {
		$handler = $this->getVideoHandlerWithoutProbe();

		$file = $this->getVideoFileMock(
			[
				'duration' => '10',
				'codec' => 'h264',
				'bitrate' => 100,
			],
			640,
			320,
			1000,
			'foo.mp4'
		);

		$result = $handler->getLongDesc( $file );

		$this->assertStringContainsString( 'MP4', $result );
		$this->assertStringContainsString( 'h264', $result );
		$this->assertStringContainsString( 'duration:10', $result );
		$this->assertStringContainsString( '640', $result );
		$this->assertStringContainsString( '320', $result );
		$this->assertStringContainsString( 'bitrate:100', $result );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\VideoHandler::getLength
	 * @return void
	 */
	public function testGetLength() {
		$handler = new VideoHandler();

		$this->assertSame( 10.0, $handler->getLength( $this->getVideoFileMock( [ 'duration' => '10' ] ) ) );
		$this->assertSame( 0.0, $handler->getLength( $this->getVideoFileMock() ) );
	}

	/**
	 * @param array $metadata
	 * @param int $width
	 * @param int $height
	 * @param int $size
	 * @param string $path
	 * @return LocalFile
	 */
	private function getVideoFileMock(
		array $metadata = [],
		int $width = 1920,
		int $height = 1080,
		int $size = 1000,
		string $path = 'foo.mp4'
	): LocalFile {
		$file = $this->getMockBuilder( LocalFile::class )
			->disableOriginalConstructor()
			->onlyMethods( [
				'getMetadataItems',
				'getMetadataItem',
				'getWidth',
				'getHeight',
				'getSize',
				'getPath',
				'getFullUrl',
			] )
			->getMock();

		$file->method( 'getMetadataItems' )
			->willReturnCallback(
				static fn( array $keys ) => array_intersect_key( $metadata, array_fill_keys( $keys, true ) )
			);
		$file->method( 'getMetadataItem' )
			->willReturnCallback( static fn( string $key ) => $metadata[$key] ?? null );
		$file->method( 'getWidth' )->willReturn( $width );
		$file->method( 'getHeight' )->willReturn( $height );
		$file->method( 'getSize' )->willReturn( $size );
		$file->method( 'getPath' )->willReturn( $path );
		$file->method( 'getFullUrl' )->willReturn( 'https://example.org/' . basename( $path ) );

		return $file;
	}

	/**
	 * @param int $namespace
	 * @return Title
	 */
	private function getTitleMock( int $namespace ): Title {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getNamespace' ] )
			->getMock();

		$title->method( 'getNamespace' )->willReturn( $namespace );

		return $title;
	}

	/**
	 * @return VideoHandler
	 */
	private function getVideoHandlerWithoutProbe(): VideoHandler {
		$handler = new class extends VideoHandler {
			/**
			 * @param object $language
			 * @return void
			 */
			public function setContentLanguageForTest( object $language ): void {
				$this->contentLanguage = $language;
			}

			/**
			 * @inheritDoc
			 */
			protected function getFFProbeResult( $file, ?string $select = null ): array {
				throw new \LogicException( 'FFProbe should not be called during metadata-only render methods.' );
			}
		};

		$handler->setContentLanguageForTest(
			new class {
				public function formatTimePeriod( $seconds ): string {
					return "duration:$seconds";
				}

				public function formatSize( $size ): string {
					return "size:$size";
				}

				public function formatBitrate( $bitrate ): string {
					return "bitrate:$bitrate";
				}

				public function commaList( array $list ): string {
					return implode( ', ', $list );
				}
			}
		);

		return $handler;
	}
}
