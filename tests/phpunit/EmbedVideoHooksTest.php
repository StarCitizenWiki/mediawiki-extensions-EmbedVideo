<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use Exception;
use LocalFile;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedVideoHooks;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\FileRepo\LocalRepo;
use MediaWiki\Message\Message;
use MediaWiki\Output\OutputPage;
use MediaWiki\Skin\SkinTemplate;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;
use RepoGroup;

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
			$this->getServiceContainer()->getRepoGroup()
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
			$this->getServiceContainer()->getRepoGroup()
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
			$this->getServiceContainer()->getRepoGroup()
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
			$this->getServiceContainer()->getRepoGroup()
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
			$this->getServiceContainer()->getRepoGroup()
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
	 * @covers \MediaWiki\Extension\EmbedVideo\EmbedVideoHooks::onSkinTemplateNavigation__Universal
	 */
	public function testAddsRefreshMetadataActionForLocalFile(): void {
		$title = $this->getServiceContainer()->getTitleFactory()->newFromText( 'Test.ogg', NS_FILE );
		$user = $this->createMock( User::class );
		$user->method( 'isAllowed' )->with( 'embedvideo-refreshmetadata' )->willReturn( true );

		$file = $this->createMock( LocalFile::class );
		$file->method( 'exists' )->willReturn( true );
		$file->method( 'isLocal' )->willReturn( true );
		$file->method( 'getRedirected' )->willReturn( null );
		$file->method( 'getHandler' )->willReturn( new AudioHandler() );

		$localRepo = $this->createMock( LocalRepo::class );
		$localRepo->method( 'newFile' )->with( $title )->willReturn( $file );

		$repoGroup = $this->createMock( RepoGroup::class );
		$repoGroup->method( 'getLocalRepo' )->willReturn( $localRepo );

		$message = $this->createMock( Message::class );
		$message->method( 'text' )->willReturn( 'Refresh metadata' );

		$skin = $this->createMock( SkinTemplate::class );
		$skin->method( 'getTitle' )->willReturn( $title );
		$skin->method( 'getUser' )->willReturn( $user );
		$skin->method( 'msg' )->with( 'embedvideo-refreshmetadata-tab' )->willReturn( $message );

		$hooks = new EmbedVideoHooks(
			$this->getServiceContainer()->getConfigFactory(),
			$repoGroup
		);

		$links = [ 'actions' => [] ];
		$hooks->onSkinTemplateNavigation__Universal( $skin, $links );

		$this->assertArrayHasKey( 'embedvideo-refreshmetadata', $links['actions'] );
		$this->assertStringContainsString(
			'Special:RefreshEmbedVideoMetadata/Test.ogg',
			$links['actions']['embedvideo-refreshmetadata']['href']
		);
	}

}
