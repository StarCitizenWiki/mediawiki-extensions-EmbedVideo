<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\FFProbe;

use MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo;
use MediaWikiIntegrationTestCase;

class FormatInfoTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo
	 * @return void
	 */
	public function testConstructor() {
		$info = new FormatInfo( [] );

		$this->assertInstanceOf( FormatInfo::class, $info );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getFilePath
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getFilePath
	 * @return void
	 */
	public function testGetMissingField() {
		$info = new FormatInfo( [] );

		$this->assertFalse( $info->getFilePath() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getField
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getFilePath
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getDuration
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getBitRate
	 * @return void
	 */
	public function testGetAllMissingFields() {
		$info = new FormatInfo( [] );

		$this->assertFalse( $info->getFilePath() );
		$this->assertFalse( $info->getDuration() );
		$this->assertFalse( $info->getBitRate() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getDuration
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\FFProbe\FormatInfo::getField
	 * @return void
	 */
	public function testGetField() {
		$info = new FormatInfo( [ 'duration' => 200 ] );

		$this->assertEquals( 200, $info->getDuration() );
	}
}
