<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

use File;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\StreamInfo;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\AudioTransformOutput;
use UnregisteredLocalFile;

class AudioHandlerTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler
	 * @return void
	 */
	public function testConstructor() {
		$handler = new AudioHandler();

		$this->assertInstanceOf( AudioHandler::class, $handler );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getParamMap
	 * @return void
	 */
	public function testParamMap(): void {
		$handler = new AudioHandler();

		$this->assertIsArray( $handler->getParamMap() );
		$this->assertNotEmpty( $handler->getParamMap() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::validateParam
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::parseTimeString
	 * @return void
	 */
	public function testValidateParam(): void {
		$handler = new AudioHandler();

		$test = [
			[ 'width', 0, false ],
			[ 'width', 100, true ],
			[ 'width', -100, false ],
			[ 'width', '-100', false ],

			[ 'start', '1:30', true ],
			[ 'start', ':30', true ],
			[ 'start', '0:30', true ],
			[ 'start', '', false ],

			[ 'end', '1:30', true ],
			[ 'end', ':05', true ],
			[ 'end', '0', true ],
			[ 'end', '', false ],

			[ 'autoplay', null, true ],
			[ 'loop', null, true ],
			[ 'nocontrols', null, true ],

			[ 'gif', null, false ],
		];

		foreach ( $test as $toTest ) {
			$this->assertEquals( $toTest[2], $handler->validateParam( $toTest[0], $toTest[1] ) );
		}
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::makeParamString
	 * @return void
	 */
	public function testMakeParamString(): void {
		$handler = new AudioHandler();

		$this->assertEmpty( $handler->makeParamString( [] ) );
		$this->assertEmpty( $handler->makeParamString( [ 'foo' ] ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::parseParamString
	 * @return void
	 */
	public function testParseParamString(): void {
		$handler = new AudioHandler();

		$this->assertEmpty( $handler->parseParamString( '' ) );
		$this->assertEmpty( $handler->parseParamString( 'foo' ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsWidth(): void {
		$handler = new AudioHandler();

		$params = [
			'width' => 500,
		];

		$handler->normaliseParams( null, $params );

		$this->assertEquals( 500, $params['width'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsDefaultWidth(): void {
		$this->overrideConfigValues( [
			'EmbedVideoDefaultWidth' => 123,
		] );

		$handler = new AudioHandler();

		$params = [];

		$handler->normaliseParams( null, $params );

		$this->assertEquals( 123, $params['width'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsStart(): void {
		$handler = new AudioHandler();

		$params = [
			'start' => '1:30',
		];

		$handler->normaliseParams( null, $params );

		$this->assertArrayHasKey( 'start', $params );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsLeadingColonStart(): void {
		$handler = new AudioHandler();

		$params = [
			'start' => ':30',
		];

		$handler->normaliseParams( null, $params );

		$this->assertSame( 30, $params['start'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsInvalidStart(): void {
		$handler = new AudioHandler();

		$params = [
			'start' => 'foo',
		];

		$handler->normaliseParams( null, $params );

		$this->assertArrayNotHasKey( 'start', $params );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsEnd(): void {
		$handler = new AudioHandler();

		$params = [
			'end' => '1:30',
		];

		$handler->normaliseParams( null, $params );

		$this->assertArrayHasKey( 'end', $params );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsInvalidEnd(): void {
		$handler = new AudioHandler();

		$params = [
			'end' => 'fobar',
		];

		$handler->normaliseParams( null, $params );

		$this->assertArrayNotHasKey( 'end', $params );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::doTransform
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::normaliseParams
	 * @return void
	 */
	public function testNormaliseParamsDoTransform(): void {
		$handler = new AudioHandler();

		$file = UnregisteredLocalFile::newFromPath( '/tmp', 'video/mp4' );

		$transform = $handler->doTransform( $file, '', '', [] );

		$this->assertInstanceOf( AudioTransformOutput::class, $transform );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getDimensionsString
	 * @return void
	 */
	public function testGetDimensionString(): void {
		$handler = $this->getAudioHandlerWithoutProbe();

		$file = $this->getAudioFileMock( [ 'duration' => '10' ] );

		$this->assertEquals(
			wfMessage( 'embedvideo-audio-short-desc', 'duration:10' )->plain(),
			$handler->getDimensionsString( $file )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getDimensionsString
	 * @return void
	 */
	public function testGetDimensionStringEmpty(): void {
		$handler = $this->getAudioHandlerWithoutProbe();

		$file = $this->getAudioFileMock( [] );

		$this->assertEmpty( $handler->getDimensionsString( $file ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getShortDesc
	 * @return void
	 */
	public function testGetShortDesc(): void {
		$handler = $this->getAudioHandlerWithoutProbe();

		$file = $this->getAudioFileMock( [ 'duration' => '10' ], 1000 );

		$this->assertEquals(
			wfMessage( 'embedvideo-audio-short-desc', 'duration:10', 'size:1000' )->plain(),
			$handler->getShortDesc( $file )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getLongDesc
	 * @return void
	 */
	public function testGetLongDesc(): void {
		$handler = $this->getAudioHandlerWithoutProbe();

		$file = $this->getAudioFileMock(
			[
				'duration' => '10',
				'codec' => 'opus',
				'bitrate' => 100,
			],
			1000,
			'foo.ogg'
		);

		$this->assertEquals(
			'OGG, opus, duration:10, bitrate:100',
			$handler->getLongDesc( $file )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getLength
	 * @return void
	 */
	public function testGetLength(): void {
		$handler = new AudioHandler();

		$this->assertSame( 10.0, $handler->getLength( $this->getAudioFileMock( [ 'duration' => '10' ] ) ) );
		$this->assertSame( 0.0, $handler->getLength( $this->getAudioFileMock( [] ) ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getDimensionsString
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetSizeAndMetadataEmpty(): void {
		$handler = $this->getMockBuilder( AudioHandler::class )
			->onlyMethods( [ 'getFFProbeResult' ] )
			->getMock();

		$handler->expects( $this->once() )
			->method( 'getFFProbeResult' )
			->willReturn( [
				'stream' => false,
				'format' => false,
			] );

		$this->assertEquals( [ 'metadata' => [] ], $handler->getSizeAndMetadata( null, null ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetSizeAndMetadata(): void {
		$handler = $this->getMockBuilder( AudioHandler::class )
			->onlyMethods( [ 'getFFProbeResult' ] )
			->getMock();

		$handler->expects( $this->once() )
			->method( 'getFFProbeResult' )
			->willReturn( [
				'stream' => false,
				'format' => new FormatInfo( [
					'duration' => 10,
					'bit_rate' => 100,
				] ),
			] );

		$this->assertEquals(
			[ 'metadata' => [ 'duration' => 10, 'bitrate' => 100 ] ],
			$handler->getSizeAndMetadata( null, null )
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getSizeAndMetadata
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetSizeAndMetadataStreamAndInfo(): void {
		$handler = $this->getMockBuilder( AudioHandler::class )
			->onlyMethods( [ 'getFFProbeResult' ] )
			->getMock();

		$handler->expects( $this->once() )
			->method( 'getFFProbeResult' )
			->willReturn( [
				'stream' => new StreamInfo( [
					'codec_type' => 'video',
					'codec_name' => 'mp4',
					'codec_long_name' => 'video/mp4',
					'width' => 320,
					'height' => 160,
					'bits_per_raw_sample' => 1000,
					'bit_rate' => 1000,
				] ),
				'format' => new FormatInfo( [
					'duration' => 1000,
					'bit_rate' => 100,
				] ),
			] );

		$this->assertEquals( [
			'metadata' => [
				'duration' => 1000,
				'codec' => 'mp4',
				'bitdepth' => 1000,
				'bitrate' => 100,
			],
			'width' => 320,
			'height' => 160,
		], $handler->getSizeAndMetadata( null, null ) );
	}

	/**
	 * @param array $metadata
	 * @param int $size
	 * @param string $path
	 * @return File
	 */
	private function getAudioFileMock( array $metadata, int $size = 1000, string $path = 'foo.ogg' ) {
		$file = $this->getMockBuilder( File::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getMetadataItems', 'getMetadataItem', 'getSize', 'getPath' ] )
			->getMock();

		$file->method( 'getMetadataItems' )
			->willReturnCallback(
				static fn( array $keys ) => array_intersect_key( $metadata, array_fill_keys( $keys, true ) )
			);
		$file->method( 'getMetadataItem' )
			->willReturnCallback( static fn( string $key ) => $metadata[$key] ?? null );
		$file->method( 'getSize' )->willReturn( $size );
		$file->method( 'getPath' )->willReturn( $path );

		return $file;
	}

	/**
	 * @return AudioHandler
	 */
	private function getAudioHandlerWithoutProbe(): AudioHandler {
		$handler = new class extends AudioHandler {
			/**
			 * @param mixed $language
			 * @return void
			 */
			public function setContentLanguageForTest( $language ): void {
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
