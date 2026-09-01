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
	private const EMBED_URL_PREFIX = 'https://alugha.com/embed/web-player?v=';

	/**
	 * A bare UUID is accepted and produces the expected embed src.
	 */
	public function testValidIdProducesEmbedUrl(): void {
		$this->overrideConfigValue( 'EmbedVideoRequireConsent', true );

		$service = EmbedServiceFactory::newFromName( 'alugha', self::VALID_ID );
		$html = EmbedHtmlFormatter::toHtml( $service );

		$expectedConfig = 'data-mw-iframeconfig="{&quot;src&quot;:&quot;'
			. self::EMBED_URL_PREFIX . self::VALID_ID
			. '&quot;}"';
		$this->assertStringContainsString( $expectedConfig, $html );

		// With consent enabled, no iframe is rendered until the user clicks.
		$this->assertStringNotContainsString( '<iframe', $html );
	}

	/**
	 * A full Alugha embed URL is normalised to the same video id.
	 */
	public function testFullUrlIsParsed(): void {
		$url = self::EMBED_URL_PREFIX . self::VALID_ID;

		$service = EmbedServiceFactory::newFromName( 'alugha', $url );

		$this->assertStringContainsString( $url, EmbedHtmlFormatter::toHtml( $service ) );
	}
}
