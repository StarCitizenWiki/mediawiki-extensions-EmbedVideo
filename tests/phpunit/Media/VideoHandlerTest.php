<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media;

use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;

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

		foreach ( $test as $test ) {
			$this->assertEquals( $test[2], $handler->validateParam( $test[0], $test[1] ) );
		}
	}
}
