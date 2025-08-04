<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedService\LocalVideo;
use MediaWiki\Extension\EmbedVideo\Media\TransformOutput\VideoTransformOutput;
use MediaWiki\Html\Html;
use MediaWikiIntegrationTestCase;
use UnregisteredLocalFile;

/**
 * @group EmbedVideo
 */
class EmbedHtmlFormatterTest extends MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testToHtmlNoConsent() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => false,
		] );

		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$this->assertStringContainsString( '<figure class="embedvideo"', EmbedHtmlFormatter::toHtml( $service ) );
		$this->assertStringContainsString( 'iframe', EmbedHtmlFormatter::toHtml( $service ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testToHtmlConsent() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

        // phpcs:ignore Generic.Files.LineLength.TooLong
		$this->assertStringContainsString( 'data-iframeconfig=\'{"src":"//archive.org/embed/foo"}\'', EmbedHtmlFormatter::toHtml( $service ) );
		$this->assertStringNotContainsString( '<iframe', EmbedHtmlFormatter::toHtml( $service ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testToHtmlNoConsentCustomArgs() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => false,
		] );

		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );
		$output = EmbedHtmlFormatter::toHtml( $service, [
			'class' => 'customCssClass',
			'description' => 'Foo Bar',
		] );

		$this->assertStringContainsString( 'customCssClass', $output );
		$this->assertStringContainsString( '<figcaption>Foo Bar</figcaption>', $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testMakeIFrameNoConsent() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => false,
		] );

		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$this->assertStringContainsString( '<iframe', EmbedHtmlFormatter::makeIframe( $service ) );
		$this->assertStringContainsString( '</iframe>', EmbedHtmlFormatter::makeIframe( $service ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::toHtml
	 * @return void
	 */
	public function testMakeIFrameConsent() {
		$this->overrideConfigValues( [
			'EmbedVideoRequireConsent' => true,
		] );

		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$this->assertEmpty( EmbedHtmlFormatter::makeIframe( $service ) );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeThumbHtml
	 * @return void
	 */
	public function testMakeThumbHtml() {
		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$output = EmbedHtmlFormatter::makeThumbHtml( $service );

		$this->assertEmpty( $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeThumbHtml
	 * @return void
	 */
	public function testMakeThumbHtmlLocalVideo() {
		$service = new LocalVideo( new VideoTransformOutput(
			UnregisteredLocalFile::newFromPath( '/tmp', 'video/mp4' ),
			[]
		), [] );

		$output = EmbedHtmlFormatter::makeThumbHtml( $service );

		$this->assertEquals( '<div class="embedvideo-thumbnail"></div>', $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeThumbHtml
	 * @return void
	 */
	public function testMakeThumbHtmlExternalVideo() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'foo',
		] );

		$service = EmbedServiceFactory::newFromName( 'external', 'foo' );

		$output = EmbedHtmlFormatter::makeThumbHtml( $service );

		$this->assertEquals( '<div class="embedvideo-thumbnail"></div>', $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeTitleHtml
	 * @return void
	 */
	public function testMakeTitleHtml() {
		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );
		$service->setTitle( 'Foo' );

		$output = EmbedHtmlFormatter::makeTitleHtml( $service );

		$link = Html::element( 'a', [
			'target' => '_blank',
			'href' => $service->getUrl(),
			'rel' => 'noopener noreferrer nofollow'
		], $service->getTitle() );

		$this->assertEquals(
			sprintf(
				'<div class="embedvideo-loader__title embedvideo-loader__title--manual">%s</div>',
				$link
			),
			$output
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeTitleHtml
	 * @return void
	 */
	public function testMakeTitleHtmlEmpty() {
		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$output = EmbedHtmlFormatter::makeTitleHtml( $service );

		$this->assertEmpty( $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makeConsentContainerHtml
	 * @return void
	 */
	public function testMakeConsentContainerHtml() {
		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$output = EmbedHtmlFormatter::makeConsentContainerHtml( $service );

		$this->assertStringStartsWith( '<div class="embedvideo-consent" data-show-privacy-notice="', $output );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makePrivacyPolicyLink
	 * @return void
	 */
	public function testMakePrivacyPolicyLink() {
		$service = EmbedServiceFactory::newFromName( 'archiveorg', 'foo' );

		$output = EmbedHtmlFormatter::makePrivacyPolicyLink( $service );

		$this->assertStringContainsString(
			sprintf(
				'<a href="%s" rel="nofollow,noopener" target="_blank" class="embedvideo-privacyNotice__link">',
				$service->getPrivacyPolicyUrl()
			),
			$output
		);
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter::makePrivacyPolicyLink
	 * @return void
	 */
	public function testMakePrivacyPolicyLinkNoLink() {
		$this->overrideConfigValues( [
			'AllowExternalImagesFrom' => 'foo',
		] );

		$service = EmbedServiceFactory::newFromName( 'external', 'foo' );

		$output = EmbedHtmlFormatter::makePrivacyPolicyLink( $service );

		$this->assertEmpty( $output );
	}
}
