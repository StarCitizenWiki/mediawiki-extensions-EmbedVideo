<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use Exception;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedVideoHooks;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\FileRepo\File\LocalFile;
use MediaWiki\FileRepo\RepoGroup;
use MediaWiki\ObjectCache\WANObjectCache;
use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use Wikimedia\ObjectCache\HashBagOStuff;

/**
 * @group EmbedVideo
 */
class EmbedVideoHooksTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks
	 * @return void
	 * @throws Exception
	 */
	public function testConstructor() {
		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->getRepoGroup(),
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$this->assertInstanceOf( EmbedVideoHooks::class, $hooks );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onBeforePageDisplay
	 * @return void
	 * @throws Exception
	 */
	public function testNotAddModules() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => false,
		] );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->getRepoGroup(),
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$page = new OutputPage( RequestContext::getMain() );

		$hooks->onBeforePageDisplay( $page, $page->getSkin() );

		$this->assertEmpty( $page->getModules() );
		$this->assertEmpty( $page->getModuleStyles() );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onBeforePageDisplay
	 * @return void
	 * @throws Exception
	 */
	public function testAddModules() {
		$this->overrideConfigValues( [
			'EmbedVideoUseEmbedStyleForLocalVideos' => true,
		] );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->getRepoGroup(),
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$page = new OutputPage( RequestContext::getMain() );

		$hooks->onBeforePageDisplay( $page, $page->getSkin() );

		$this->assertNotEmpty( $page->getModules() );
		$this->assertNotEmpty( $page->getModuleStyles() );

		$this->assertEquals( 'ext.embedVideo.overlay', $page->getModules()[0] );
		$this->assertEquals( 'ext.embedVideo.styles', $page->getModuleStyles()[0] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::setup
	 * @return void
	 */
	public function testAddAudioHandler() {
		global $wgMediaHandlers;

		$this->overrideConfigValues( [
			'EmbedVideoEnableAudioHandler' => true,
		] );

		EmbedVideoHooks::setup();

		$this->assertEquals( AudioHandler::class, $wgMediaHandlers['audio/ogg'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::setup
	 * @return void
	 */
	public function testNotAddAudioHandler() {
		$this->markTestSkipped( 'Can\'t disable extension setup function' );

		global $wgMediaHandlers, $wgEmbedVideoEnableAudioHandler;

		$this->setMwGlobals( [
			'$wgEmbedVideoEnableAudioHandler' => false,
		] );
		$wgEmbedVideoEnableAudioHandler = false;

		EmbedVideoHooks::setup();

		$this->assertNotEquals( AudioHandler::class, $wgMediaHandlers['audio/ogg'] );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onParserFirstCallInit
	 * @return void
	 * @throws Exception
	 */
	public function testAddFunctionHooks() {
		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->getRepoGroup(),
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$parser = $this->getServiceContainer()->getParser();

		$hooks->onParserFirstCallInit( $parser );

		$names = array_map( fn( $service ) => $service::getServiceName(), EmbedServiceFactory::getAvailableServices() );
		$tags = $parser->getTags();

		foreach ( $names as $service ) {
			$this->assertContains( $service, $tags );
		}
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onParserFirstCallInit
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory::getAvailableServices
	 * @return void
	 * @throws Exception
	 */
	public function testAddFunctionHooksPartial() {
		$this->overrideConfigValues( [
			'EmbedVideoEnabledServices' => [
				'youtube'
			]
		] );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$this->getServiceContainer()->getRepoGroup(),
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$parser = $this->getServiceContainer()->getParser();

		$hooks->onParserFirstCallInit( $parser );

		$names = array_map( fn( $service ) => $service::getServiceName(), EmbedServiceFactory::getAvailableServices() );
		$tags = $parser->getTags();

		foreach ( $names as $service ) {
			if ( $service === 'youtube' ) {
				$this->assertContains( $service, $tags );
			} else {
				$this->assertNotContains( $service, $tags );
			}
		}
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onArticlePurge
	 * @return void
	 * @throws Exception
	 */
	public function testOnArticlePurgeHooksNotAFile() {
		$repo = $this->getMockBuilder( RepoGroup::class )->disableOriginalConstructor()->getMock();

		$repo->expects( $this->never() )->method( 'findFile' );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$repo,
			$this->getServiceContainer()->getMainWANObjectCache()
		);

		$title = Title::newFromText( 'OnArticlePurge' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );

		$hooks->onArticlePurge( $page );
	}

	/**
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onArticlePurge
	 * @return void
	 * @throws Exception
	 */
	public function testOnArticlePurgeHooksFile() {
		$file = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();
		$file->expects( $this->exactly( 2 ) )
			->method( 'getSha1' )
			->willReturn( sha1( 'foo' ) );

		$repo = $this->getMockBuilder( RepoGroup::class )->disableOriginalConstructor()->getMock();
		$repo->expects( $this->once() )
			->method( 'findFile' )
			->willReturn( $file );

		$cache = $this->getMockBuilder( WANObjectCache::class )
			->setConstructorArgs( [
				[ 'cache' => new HashBagOStuff() ],
			] )->getMock();
		$cache->expects( $this->exactly( 2 ) )
			->method( 'makeGlobalKey' )
			->willReturn( 'foo' );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$repo,
			$cache
		);

		$title = Title::newFromText( 'OnArticlePurge.jpg', NS_FILE );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );

		$hooks->onArticlePurge( $page );
	}
}
