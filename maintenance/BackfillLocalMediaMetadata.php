<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Maintenance;

use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\FileRepo\File\FileSelectQueryBuilder;
use MediaWiki\Maintenance\Maintenance;
use Throwable;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class BackfillLocalMediaMetadata extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Backfill persisted EmbedVideo metadata for current local audio and video files.'
		);
		$this->setBatchSize( 200 );
		$this->requireExtension( 'EmbedVideo' );

		$this->addOption( 'start', 'File name to start with.', false, true );
		$this->addOption( 'end', 'File name to end with.', false, true );
		$this->addOption(
			'sleep',
			'Time to sleep between batches, in seconds. Default: 0.',
			false,
			true
		);
		$this->addOption(
			'verbose',
			'Output one line per processed file.',
			false,
			false,
			'v'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$dbw = $this->getDB( DB_PRIMARY );
		$repo = $this->getServiceContainer()->getRepoGroup()->getLocalRepo();
		$verbose = $this->hasOption( 'verbose' );
		$sleep = (int)$this->getOption( 'sleep', 0 );
		$batchSize = (int)$this->getBatchSize();
		$failures = [];
		$updated = 0;
		$skipped = 0;

		if ( $batchSize <= 0 ) {
			$this->fatalError( 'Batch size is too low.', 12 );
		}

		$queryBuilderTemplate = $this->newQueryBuilderTemplate( $dbw, $batchSize );

		$start = $this->getOption( 'start', false );
		if ( $start !== false ) {
			$queryBuilderTemplate->andWhere( $dbw->expr( 'img_name', '>=', $start ) );
		}

		$end = $this->getOption( 'end', false );
		if ( $end !== false ) {
			$queryBuilderTemplate->andWhere( $dbw->expr( 'img_name', '<=', $end ) );
		}

		$batchCondition = [];

		do {
			$queryBuilder = clone $queryBuilderTemplate;
			$res = $queryBuilder->andWhere( $batchCondition )
				->caller( __METHOD__ )
				->fetchResultSet();

			if ( !$res->numRows() ) {
				break;
			}

			$row1 = $res->current();
			$this->output(
				"Processing next {$res->numRows()} row(s) starting with {$row1->img_name}.\n"
			);
			$res->rewind();

			$lastName = null;
			$this->beginTransactionRound( __METHOD__ );
			foreach ( $res as $row ) {
				$lastName = $row->img_name;

				try {
					$file = $repo->newFileFromRow( $row );
					$handler = $file->getHandler();

					if ( !( $handler instanceof AudioHandler ) ) {
						$skipped++;
						if ( $verbose ) {
							$this->output( "Skipping File:$lastName; handler is not EmbedVideo.\n" );
						}
						continue;
					}

					$file->upgradeRow();

					if ( $file->getUpgraded() ) {
						$updated++;
						if ( $verbose ) {
							$this->output( "Backfilled File:$lastName.\n" );
						}
					} else {
						$skipped++;
						if ( $verbose ) {
							$this->output( "Skipping File:$lastName; file could not be refreshed.\n" );
						}
					}
				} catch ( Throwable $e ) {
					$failures[] = "File:$lastName failed: {$e->getMessage()}";
				}
			}
			$this->commitTransactionRound( __METHOD__ );

			if ( $lastName !== null ) {
				$batchCondition = [ $dbw->expr( 'img_name', '>', $lastName ) ];
			}

			if ( $sleep > 0 ) {
				sleep( $sleep );
			}
		} while ( $res->numRows() === $batchSize );

		$this->output(
			"\nFinished backfilling EmbedVideo metadata. "
			. "$updated file(s) refreshed, $skipped file(s) skipped, "
			. count( $failures ) . " file(s) failed.\n"
		);

		if ( $failures ) {
			$this->output( "\nFailures:\n" . implode( "\n", $failures ) . "\n" );
			return false;
		}

		return true;
	}

	private function newQueryBuilderTemplate(
		IReadableDatabase $dbw,
		int $batchSize
	): FileSelectQueryBuilder {
		return FileSelectQueryBuilder::newForFile( $dbw )
			->where( $dbw->orExpr( [
				$dbw->expr( 'img_major_mime', '=', [ 'audio', 'video' ] ),
				$dbw->andExpr( [
					$dbw->expr( 'img_major_mime', '=', 'application' ),
					$dbw->expr( 'img_minor_mime', '=', 'ogg' ),
				] ),
			] ) )
			->orderBy( 'img_name', SelectQueryBuilder::SORT_ASC )
			->limit( $batchSize );
	}
}

$maintClass = BackfillLocalMediaMetadata::class;
require_once RUN_MAINTENANCE_IF_MAIN;
