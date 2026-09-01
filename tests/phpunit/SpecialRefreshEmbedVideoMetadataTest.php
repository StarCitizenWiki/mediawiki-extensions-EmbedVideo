<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Tests;

use LocalFile;
use LocalRepo;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Specials\SpecialRefreshEmbedVideoMetadata;
use MediaWiki\Request\FauxRequest;
use MediaWiki\SpecialPage\SpecialPage;
use PermissionsError;
use RepoGroup;

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

	public function testGetRestrictionReturnsRequiredRight(): void {
		$this->assertSame( 'embedvideo-refreshmetadata', $this->newSpecialPage()->getRestriction() );
	}

	public function testUserWithoutRightIsDenied(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [] );

		$this->expectException( PermissionsError::class );
		$this->executeSpecialPage( '', null, 'qqx', $performer );
	}

	public function testUserWithoutRightCannotRefreshMetadata(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [] );

		// The permission check throws before the file is ever looked up, so these
		// expectations are not reached today. They are here to fail if the check is
		// ever moved below the form handling.
		$file = $this->createMock( LocalFile::class );
		$file->method( 'exists' )->willReturn( true );
		$file->method( 'isLocal' )->willReturn( true );
		$file->method( 'getRedirected' )->willReturn( null );
		$file->method( 'getHandler' )->willReturn( new AudioHandler() );
		$file->expects( $this->never() )->method( 'upgradeRow' );

		$this->localRepo->method( 'newFile' )->willReturn( $file );

		$this->expectException( PermissionsError::class );
		$this->executeSpecialPage( 'Test.ogg', new FauxRequest( [], true ), 'qqx', $performer );
	}

	public function testMissingTargetShowsError(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [ 'embedvideo-refreshmetadata' ] );

		[ $html ] = $this->executeSpecialPage( '', null, 'qqx', $performer );

		$this->assertStringContainsString( 'embedvideo-refreshmetadata-missing-target', $html );
	}

	public function testPostedRefreshCallsUpgradeRow(): void {
		$performer = $this->getMutableTestUser()->getUser();
		$this->overrideUserPermissions( $performer, [ 'embedvideo-refreshmetadata' ] );

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
