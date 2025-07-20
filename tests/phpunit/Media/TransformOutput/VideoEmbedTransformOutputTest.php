<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\Media\TransformOutput;

use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput;
use MediaWiki\FileRepo\File\UnregisteredLocalFile;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class VideoEmbedTransformOutputTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput
	 * @return void
	 */
	public function testConstructor() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$this->assertInstanceOf( VideoEmbedTransformOutput::class, $out );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoEmbedTransformOutput::toHtml
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testToHtml() {
		$out = new VideoEmbedTransformOutput(
			UnregisteredLocalFile::newFromPath( '/dev/null', 'image/jpeg' ),
			[]
		);

		$out = $out->toHtml();

		$this->assertStringContainsString( '<video src="', $out );
		$this->assertStringContainsString( '<figure class="embedvideo" data-service="local-embed"', $out );
	}
}
