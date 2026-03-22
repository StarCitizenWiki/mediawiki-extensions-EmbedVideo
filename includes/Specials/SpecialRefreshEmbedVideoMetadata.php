<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Specials;

use Exception;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\FileRepo\File\LocalFile;
use MediaWiki\FileRepo\RepoGroup;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class SpecialRefreshEmbedVideoMetadata extends UnlistedSpecialPage {
	public function __construct(
		private RepoGroup $repoGroup,
		private TitleFactory $titleFactory
	) {
		parent::__construct( 'RefreshEmbedVideoMetadata', 'embedvideo-refreshmetadata' );
	}

	public function doesWrites() {
		return true;
	}

	public function execute( $par ) {
		$this->checkReadOnly();
		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();
		$out->addModuleStyles( 'mediawiki.codex.messagebox.styles' );

		$title = $this->getTargetTitle( $par ?? $this->getRequest()->getText( 'target' ) );
		if ( !$title ) {
			$out->addHTML( Html::errorBox(
				$this->msg( 'embedvideo-refreshmetadata-missing-target' )->escaped()
			) );
			return;
		}

		$out->addBacklinkSubtitle( $title );

		$file = $this->repoGroup->getLocalRepo()->newFile( $title );
		if ( !$this->isRefreshableFile( $file ) ) {
			$out->addHTML( Html::errorBox(
				$this->msg( 'embedvideo-refreshmetadata-invalid-target' )->escaped()
			) );
			return;
		}

		$result = $this->getRefreshForm( $title, $file )->show();
		if ( $result instanceof Status && $result->isGood() ) {
			$out->addHTML( Html::successBox( (string)$result->getValue() ) );
			$out->addReturnTo( $title );
		}
	}

	private function getRefreshForm( Title $title, LocalFile $file ): HTMLForm {
		return HTMLForm::factory( 'ooui', [], $this->getContext() )
			->setAction( $this->getPageTitle( $title->getDBkey() )->getLocalURL() )
			->setId( 'mw-embedvideo-refreshmetadata-form' )
			->setSubmitTextMsg( 'embedvideo-refreshmetadata-submit' )
			->setSubmitCallback(
				function ( array $data, HTMLForm $form ) use ( $title, $file ) {
					return $this->submitRefreshMetadata( $title, $file );
				}
			)
			->addPreHtml(
				$this->msg(
					'embedvideo-refreshmetadata-intro',
					$title->getPrefixedText()
				)->parseAsBlock()
			);
	}

	private function submitRefreshMetadata( Title $title, LocalFile $file ): Status {
		try {
			$file->upgradeRow();
			return Status::newGood(
				$this->msg(
					'embedvideo-refreshmetadata-success',
					$title->getPrefixedText()
				)->escaped()
			);
		} catch ( Exception $e ) {
			return Status::newFatal( 'embedvideo-refreshmetadata-failed', $e->getMessage() );
		}
	}

	private function getTargetTitle( ?string $target ): ?Title {
		if ( !$target ) {
			return null;
		}

		$title = $this->titleFactory->newFromText( $target, NS_FILE );
		if ( !$title || $title->getNamespace() !== NS_FILE ) {
			return null;
		}

		return $title;
	}

	private function isRefreshableFile( mixed $file ): bool {
		return $file instanceof LocalFile
			&& $file->exists()
			&& $file->isLocal()
			&& $file->getRedirected() === null
			&& $file->getHandler() instanceof AudioHandler;
	}
}
