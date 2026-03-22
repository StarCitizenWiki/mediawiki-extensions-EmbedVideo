<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Specials;

use Exception;
use LocalFile;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use RepoGroup;

class SpecialRefreshEmbedVideoMetadata extends UnlistedSpecialPage {
	/**
	 * @param RepoGroup $repoGroup
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private RepoGroup $repoGroup,
		private TitleFactory $titleFactory
	) {
		parent::__construct( 'RefreshEmbedVideoMetadata', 'embedvideo-refreshmetadata' );
	}

	/**
	 * @return bool
	 */
	public function doesWrites(): bool {
		return true;
	}

	/**
	 * @param string|null $par
	 * @return void
	 */
	public function execute( $par ): void {
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

	/**
	 * Build the confirmation form used to refresh stored metadata.
	 *
	 * @param Title $title
	 * @param LocalFile $file
	 * @return HTMLForm
	 */
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

	/**
	 * Refresh metadata for the selected local file.
	 *
	 * @param Title $title
	 * @param LocalFile $file
	 * @return Status
	 */
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

	/**
	 * Resolve the requested file title.
	 *
	 * @param string|null $target
	 * @return Title|null
	 */
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

	/**
	 * Check whether the target file can be refreshed.
	 *
	 * @param mixed $file
	 * @return bool
	 */
	private function isRefreshableFile( mixed $file ): bool {
		return $file instanceof LocalFile
			&& $file->exists()
			&& $file->isLocal()
			&& $file->getRedirected() === null
			&& $file->getHandler() instanceof AudioHandler;
	}
}
