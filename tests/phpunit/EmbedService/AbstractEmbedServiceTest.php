<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests\EmbedService\YouTube;

use InvalidArgumentException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;
use MediaWikiIntegrationTestCase;

/**
 * @group EmbedVideo
 */
class AbstractEmbedServiceTest extends MediaWikiIntegrationTestCase {
	private AbstractEmbedService $service;

	protected function setUp(): void {
		$service = new class( '' ) extends AbstractEmbedService {
			/**
			 * @inheritDoc
			 */
			public function getWidth() {
				return 600;
			}

			/**
			 * @inheritDoc
			 */
			public function getHeight() {
				return 300;
			}

			/**
			 * @inheritDoc
			 */
			public function getId(): string {
				return '<some-id>';
			}

			/**
			 * @inheritDoc
			 */
			public function getContentType(): ?string {
				return '<content-type>';
			}

			/**
			 * @inheritDoc
			 */
			public function getPrivacyPolicyUrl(): ?string {
				return '<privacy-url>';
			}

			/**
			 * @inheritDoc
			 */
			public function getPrivacyPolicyShortText(): ?string {
				return '<privacy-text>';
			}

			/**
			 * @inheritDoc
			 */
			public function getBaseUrl(): string {
				return '<base-url>';
			}

			/**
			 * @inheritDoc
			 */
			public function getServiceKey(): string {
				return '<anon-service>';
			}
		};

		$this->service = $this->getMockForAbstractClass( AbstractEmbedService::class, [ '' ] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getDefaultWidth
	 * @return void
	 */
	public function testGetWidth() {
		$this->assertEquals( 640, $this->service->getWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getHeight
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getDefaultHeight
	 * @return void
	 */
	public function testGetHeight() {
		$this->assertEquals( 360, $this->service->getHeight() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getId
	 * @return void
	 */
	public function testGetId() {
		$this->assertEmpty( $this->service->getId() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getContentType
	 * @return void
	 */
	public function testGetContentType() {
		$this->assertEquals( 'video', $this->service->getContentType() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getPrivacyPolicyUrl
	 * @return void
	 */
	public function testGetPrivacyPolicyUrl() {
		$this->assertNull( $this->service->getPrivacyPolicyUrl() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getPrivacyPolicyShortText
	 * @return void
	 */
	public function testGetPrivacyPolicyShortText() {
		$this->assertNull( $this->service->getPrivacyPolicyShortText() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getPrivacyPolicyShortText
	 * @return void
	 */
	public function testGetAutoplayParameter() {
		$this->assertEquals( [ 'autoplay' => 1 ], $this->service->getAutoplayParameter() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getServiceKey
	 * @return void
	 */
	public function testGetServiceKey() {
		$this->assertStringContainsString( 'abstractembedservice', $this->service->getServiceKey() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getServiceName
	 * @return void
	 */
	public function testGetServiceName() {
		$this->assertStringContainsString( 'abstractembedservice', $this->service::getServiceName() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getDefaultWidth
	 * @return void
	 */
	public function testGetDefaultWidth() {
		$this->assertEquals( 640, $this->service->getDefaultWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getDefaultHeight
	 * @return void
	 */
	public function testGetDefaultHeight() {
		$this->assertEquals( 360, $this->service->getDefaultHeight() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getCSPUrls
	 * @return void
	 */
	public function testGetCspUrls() {
		$this->assertEmpty( $this->service->getCSPUrls() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getWidth
	 * @return void
	 */
	public function testSetWidth() {
		$this->service->setWidth( 450 );
		$this->assertEquals( 450, $this->service->getWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getWidth
	 * @return void
	 */
	public function testSetWidthNull() {
		$this->overrideConfigValues( [
			'EmbedVideoDefaultWidth' => 123,
		] );

		$this->service->setWidth();
		$this->assertEquals( 123, $this->service->getWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getWidth
	 * @return void
	 */
	public function testSetWidthGreaterMaxWidth() {
		$this->overrideConfigValues( [
			'EmbedVideoMaxWidth' => 120,
		] );

		$this->service->setWidth( 450 );
		$this->assertEquals( 120, $this->service->getWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getWidth
	 * @return void
	 */
	public function testSetWidthLowerMinWidth() {
		$this->overrideConfigValues( [
			'EmbedVideoMinWidth' => 120,
		] );

		$this->service->setWidth( 12 );
		$this->assertEquals( 120, $this->service->getWidth() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setHeight
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getHeight
	 * @return void
	 */
	public function testSetHeight() {
		$this->service->setHeight( 450 );
		$this->assertEquals( 450, $this->service->getHeight() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setWidth
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setHeight
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getHeight
	 * @return void
	 */
	public function testSetHeightNull() {
		$this->service->setWidth( 1920 );
		$this->service->setHeight();
		$this->assertEquals( 1080, $this->service->getHeight() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrlArgs
	 * @return void
	 */
	public function testGetUrlArgsEmpty() {
		$this->assertFalse( $this->service->getUrlArgs() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrlArgs
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setUrlArgs
	 * @return void
	 */
	public function testSetGetUrlArgs() {
		$this->assertTrue( $this->service->setUrlArgs( [
			'foo' => 'bar',
		] ) );

		$this->assertEquals( http_build_query( [ 'foo' => 'bar' ] ), $this->service->getUrlArgs() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setUrlArgs
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrlArgs
	 * @return void
	 */
	public function testSetUrlArgsEmpty() {
		$this->assertTrue( $this->service->setUrlArgs( [] ) );
		$this->assertFalse( $this->service->getUrlArgs() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setUrlArgs
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrlArgs
	 * @return void
	 */
	public function testSetUrlArgsStringValid() {
		$this->assertTrue( $this->service->setUrlArgs( 'foo=bar&baz=qux' ) );
		$this->assertEquals( http_build_query( [ 'foo' => 'bar', 'baz' => 'qux' ] ), $this->service->getUrlArgs() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setUrlArgs
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getUrlArgs
	 * @return void
	 */
	public function testSetUrlArgsStringWithEmpty() {
		$this->assertTrue( $this->service->setUrlArgs( 'foo=bar&baz=' ) );
		$this->assertEquals( http_build_query( [ 'foo' => 'bar' ] ), $this->service->getUrlArgs() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::addIframeAttribute
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getIframeAttributes
	 * @return void
	 */
	public function testSetGetIframeAttributes() {
		$this->service->addIframeAttribute( 'foo', 'bar' );

		$this->assertArrayHasKey( 'foo', $this->service->getIframeAttributes() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setTitle
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getTitle
	 * @return void
	 */
	public function testSetGetTitle() {
		$this->service->setTitle( 'FooTitle' );

		$this->assertEquals( 'FooTitle', $this->service->getTitle() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setTitle
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getTitle
	 * @return void
	 */
	public function testSetGetTitleEmpty() {
		$this->service->setTitle( null );

		$this->assertNull( $this->service->getTitle() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::setLocalThumb
	 * @return void
	 */
	public function testSetLocalThumb() {
		$this->expectException( InvalidArgumentException::class );
		$this->service->setLocalThumb( 'FooFile.jpg' );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService::getLocalThumb
	 * @return void
	 */
	public function testGetLocalThumb() {
		$this->assertNull( $this->service->getLocalThumb() );
	}
}
