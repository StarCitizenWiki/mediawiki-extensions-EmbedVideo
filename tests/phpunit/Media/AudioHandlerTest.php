<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

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
			[ 'start', '', false ],

			[ 'end', '1:30', true ],
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
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetDimensionString(): void {
		$handler = $this->getMockBuilder( AudioHandler::class )
			->onlyMethods( [ 'getFFProbeResult' ] )
			->getMock();

		$handler->expects( $this->once() )
			->method( 'getFFProbeResult' )
			->willReturn( [
				'stream' => new StreamInfo( [] ),
				'format' => new FormatInfo( [
					'duration' => 10,
				] ),
			] );

		$file = UnregisteredLocalFile::newFromPath( '/tmp', 'video/mp4' );

		$this->assertIsString( $handler->getDimensionsString( $file ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getDimensionsString
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\AudioHandler::getFFProbeResult
	 * @return void
	 */
	public function testGetDimensionStringEmpty(): void {
		$handler = $this->getMockBuilder( AudioHandler::class )
			->onlyMethods( [ 'getFFProbeResult' ] )
			->getMock();

		$handler->expects( $this->once() )
			->method( 'getFFProbeResult' )
			->willReturn( [
				'stream' => false,
				'format' => false,
			] );

		$file = UnregisteredLocalFile::newFromPath( '/tmp', 'video/mp4' );

		$this->assertEmpty( $handler->getDimensionsString( $file ) );
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
					'bit_rate' => 100,
				] ),
			] );

		$this->assertEquals( [ 'metadata' => [], 'bits' => 100 ], $handler->getSizeAndMetadata( null, null ) );
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
					'duration' => 1000,
					'bit_rate' => 1000,
				] ),
				'format' => new FormatInfo( [
					'bit_rate' => 100,
				] ),
			] );

		$this->assertEquals( [
			'metadata' => [
				'duration' => 1000,
				'codec' => 'mp4',
				'bitdepth' => 1000,
			],
			'bits' => 100,
			'width' => 320,
			'height' => 160,
		], $handler->getSizeAndMetadata( null, null ) );
	}
}
