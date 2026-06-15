<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\Alugha
 */
class AlughaTest extends MediaWikiIntegrationTestCase {

  private const VALID_ID = 'b8fe2460-81e1-11eb-8b27-65de6c3aea52';

  /**
   * A bare UUID is accepted and produces the expected embed src.
   */
  public function testValidIdProducesEmbedUrl(): void {
	$this->overrideConfigValue( 'EmbedVideoRequireConsent', true );

	$service = EmbedServiceFactory::newFromName( 'alugha', self::VALID_ID );
	$html = EmbedHtmlFormatter::toHtml( $service );

	$this->assertStringContainsString(
	  'data-mw-iframeconfig=\'{"src":"//alugha.com/embed/web-player?v=' . self::VALID_ID . '"}\'',
	  $html
	);

	// With consent enabled, no iframe is rendered until the user clicks.
	$this->assertStringNotContainsString( '<iframe', $html );
  }

  /**
   * A full Alugha embed URL is normalised to the same video id.
   */
  public function testFullUrlIsParsed(): void {
	$service = EmbedServiceFactory::newFromName(
	  'alugha',
	  'https://alugha.com/embed/web-player?v=' . self::VALID_ID
	);

	$this->assertStringContainsString(
	  '//alugha.com/embed/web-player?v=' . self::VALID_ID,
	  EmbedHtmlFormatter::toHtml( $service )
	);
  }
}
