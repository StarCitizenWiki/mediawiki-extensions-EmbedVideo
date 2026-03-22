<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Specials\SpecialRefreshEmbedVideoMetadata;
use MediaWiki\FileRepo\File\LocalFile;
use MediaWiki\FileRepo\LocalRepo;
use MediaWiki\FileRepo\RepoGroup;
use MediaWiki\Request\FauxRequest;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * @group Database
 * @group EmbedVideo
 * @covers \MediaWiki\Extension\EmbedVideo\Specials\SpecialRefreshEmbedVideoMetadata
 */
class SpecialRefreshEmbedVideoMetadataTest extends \SpecialPageTestBase {
	private RepoGroup $repoGroup;
	private LocalRepo $localRepo;

	protected function setUp(): void {
		parent::setUp();

		$this->repoGroup = $this->createMock( RepoGroup::class );
		$this->localRepo = $this->createMock( LocalRepo::class );
		$this->repoGroup->method( 'getLocalRepo' )->willReturn( $this->localRepo );
	}

	protected function newSpecialPage(): SpecialPage {
		return new SpecialRefreshEmbedVideoMetadata(
			$this->repoGroup,
			$this->getServiceContainer()->getTitleFactory()
		);
	}

	public function testMissingTargetShowsError(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [ 'embedvideo-refreshmetadata' => true ] );

		[ $html ] = $this->executeSpecialPage( '', null, 'qqx', $performer );

		$this->assertStringContainsString( 'embedvideo-refreshmetadata-missing-target', $html );
	}

	public function testPostedRefreshCallsUpgradeRow(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [ 'embedvideo-refreshmetadata' => true ] );

		$file = $this->createMock( LocalFile::class );
		$file->method( 'exists' )->willReturn( true );
		$file->method( 'isLocal' )->willReturn( true );
		$file->method( 'getRedirected' )->willReturn( null );
		$file->method( 'getHandler' )->willReturn( new AudioHandler() );
		$file->expects( $this->once() )->method( 'upgradeRow' );

		$this->localRepo->method( 'newFile' )->willReturn( $file );

		$request = new FauxRequest( [], true );

		[ $html ] = $this->executeSpecialPage( 'Test.ogg', $request, 'qqx', $performer );

		$this->assertStringContainsString( 'embedvideo-refreshmetadata-success', $html );
	}
}
