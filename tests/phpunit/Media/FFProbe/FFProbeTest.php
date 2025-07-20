<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\FFProbe;

use Exception;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo;
use MediaWiki\Extension\EmbedVideo\Media\FFProbe\StreamInfo;
use MediaWiki\FileRepo\File\UnregisteredLocalFile;
use MediaWiki\Shell\Command;
use MediaWiki\Shell\CommandFactory;
use MediaWikiIntegrationTestCase;
use Shellbox\Command\UnboxedResult;
use Wikimedia\AtEase\AtEase;

/**
 * @group EmbedVideo
 */
class FFProbeTest extends MediaWikiIntegrationTestCase {
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
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe
	 * @return void
	 */
	public function testConstructor() {
		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertInstanceOf( FFProbe::class, $probe );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getFilePath
	 * @return void
	 */
	public function testLoadMetadata() {
		$this->overrideConfigValues( [
			'CommandLineMode' => false,
		] );

		$this->mockFFProbeCommand();

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertTrue( $probe->loadMetaData() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 */
	public function testLoadMetadataCLI() {
		$this->overrideConfigValues( [
			'CommandLineMode' => true,
		] );

		$this->mockFFProbeCommand();

		$cache = $this->getMockBuilder( \WANObjectCache::class )->disableOriginalConstructor()->getMock();
		$cache->expects( $this->never() )->method( 'makeGlobalKey' );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertTrue( $probe->loadMetaData() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 */
	public function testLoadMetadataNoFfProbe() {
		$this->overrideConfigValues( [
			'FFProbeLocation' => null,
		] );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertFalse( $probe->loadMetaData() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getStream
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 * @throws Exception
	 */
	public function testLoadMetadataInvalidProbeResult() {
		$result = new UnboxedResult();
		$result->stdout( '<false>' );

		$this->mockFFProbeCommand( $result );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		AtEase::suppressWarnings();
		$this->assertFalse( $probe->getStream( 'v:0' ) );
		AtEase::restoreWarnings();
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getFormat
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 * @throws Exception
	 */
	public function testGetFormat() {
		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'format' => [
				'filename' => 'foobar',
			]
		] ) );

		$this->mockFFProbeCommand( $result );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$info = $probe->getFormat();

		$this->assertInstanceOf( FormatInfo::class, $info );
		$this->assertEquals( 'foobar', $info->getFilePath() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getFormat
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 * @throws Exception
	 */
	public function testGetFormatFalse() {
		$result = new UnboxedResult();
		$result->stdout( '[]' );

		$this->mockFFProbeCommand( $result );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertFalse( $probe->getFormat() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getStream
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 * @throws Exception
	 */
	public function testGetStream() {
		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => 'video',
					'codec_name' => 'some-name',
				],
			]
		] ) );

		$this->mockFFProbeCommand( $result );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$info = $probe->getStream( 'v:0' );

		$this->assertInstanceOf( StreamInfo::class, $info );
		$this->assertEquals( 'some-name', $info->getCodecName() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::getStream
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::loadMetaData
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FFProbe::invokeFFProbe
	 * @return void
	 * @throws Exception
	 */
	public function testGetStreamCodecFalse() {
		$result = new UnboxedResult();
		$result->stdout( json_encode( [
			'streams' => [
				[
					'codec_type' => false,
					'codec_name' => 'some-name',
				],
				[
					'codec_type' => false,
					'codec_name' => 'some-name',
				],
			]
		] ) );

		$this->mockFFProbeCommand( $result );

		$probe = new FFProbe( 'foo', UnregisteredLocalFile::newFromPath( '/dev/null', 'video/mp4' ) );

		$this->assertFalse( $probe->getStream( 'v:0' ) );
	}

	/**
	 * Mock the invocations used by invokeFFProbe
	 *
	 * @param UnboxedResult|null $result
	 * @return void
	 * @throws Exception
	 */
	private function mockFFProbeCommand( ?UnboxedResult $result = null ) {
		if ( $result === null ) {
			$result = new UnboxedResult();
			$result->stdout( json_encode( [
				'streams' => [],
				'format' => [],
			] ) );
		}

		$commandMock = $this->getMockBuilder( Command::class )->disableOriginalConstructor()->getMock();
		$commandMock->expects( $this->once() )->method( 'params' );
		$commandMock->expects( $this->once() )->method( 'unsafeParams' );
		$commandMock->expects( $this->once() )->method( 'execute' )->willReturn( $result );

		$shellMock = $this->getMockBuilder( CommandFactory::class )->disableOriginalConstructor()->getMock();
		$shellMock->expects( $this->once() )->method( 'create' )->willReturn( $commandMock );

		$this->setService( 'ShellCommandFactory', $shellMock );
	}

}
