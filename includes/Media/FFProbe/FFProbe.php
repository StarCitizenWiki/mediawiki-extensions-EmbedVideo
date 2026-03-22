<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\FFProbe;

use Exception;
use JsonException;
use MediaWiki\Config\ConfigException;
use MediaWiki\Exception\ProcOpenError;
use MediaWiki\Exception\ShellDisabledError;
use MediaWiki\FileRepo\File\File;
use MediaWiki\MediaWikiServices;
use MediaWiki\Shell\Shell;
use Wikimedia\FileBackend\FSFile\FSFile;

class FFProbe {
	/**
	 * MediaWiki File
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Meta Data Cache
	 *
	 * @var array|null
	 */
	private $metadata;

	/**
	 * @var bool
	 */
	private $metadataLoaded = false;

	/**
	 * Main Constructor
	 *
	 * @param string $filename MediaWiki File name
	 * @param FSFile|File|string $file
	 * @return void
	 */
	public function __construct( $filename, $file ) {
		$this->filename = $filename;
	}

	/**
	 * Return the entire cache of metadata.
	 *
	 * @return bool Flag if loading did succeed
	 */
	public function loadMetaData(): bool {
		if ( $this->metadataLoaded ) {
			return is_array( $this->metadata );
		}

		$this->metadataLoaded = true;

		return $this->setMetadata( $this->invokeFFProbe() );
	}

	/**
	 * Get a selected stream.  Follows ffmpeg's stream selection style.
	 *
	 * @param string $select Stream identifier
	 * Examples:
	 *		"v:0" - Select the first video stream
	 * 		"a:1" - Second audio stream
	 * 		"i:0" - First stream, whatever it is.
	 * 		"s:2" - Third subtitle
	 * 		"d:0" - First generic data stream
	 * 		"t:1" - Second attachment
	 * @return false|StreamInfo StreamInfo object or false if does not exist.
	 */
	public function getStream( string $select ) {
		$this->loadMetaData();

		$types = [
			'v'	=> 'video',
			'a'	=> 'audio',
			'i'	=> false,
			's'	=> 'subtitle',
			'd'	=> 'data',
			't'	=> 'attachment'
		];

		if ( !isset( $this->metadata['streams'] ) ) {
			return false;
		}

		[ $type, $index ] = explode( ":", $select );
		$index = (int)$index;

		$type = ( $types[$type] ?? false );

		$i = 0;
		foreach ( $this->metadata['streams'] as $stream ) {
			if ( $type !== false && isset( $stream['codec_type'] ) ) {
				if ( $index === $i && $stream['codec_type'] === $type ) {
					return new StreamInfo( $stream );
				}
			}
			if ( $type === false || $stream['codec_type'] === $type ) {
				$i++;
			}
		}
		return false;
	}

	/**
	 * Get the FormatInfo object.
	 *
	 * @return false|FormatInfo FormatInfo object or false if does not exist.
	 */
	public function getFormat() {
		$this->loadMetaData();

		if ( !isset( $this->metadata['format'] ) ) {
			return false;
		}

		return new FormatInfo( $this->metadata['format'] );
	}

	/**
	 * @return bool|string
	 */
	private function getFilePath() {
		return $this->filename;
	}

	/**
	 * @param array|null $result
	 * @return bool
	 */
	private function setMetadata( ?array $result ): bool {
		if ( !is_array( $result ) ) {
			$this->metadata = null;
			return false;
		}

		$this->metadata = [
			'streams' => $result['streams'] ?? null,
			'format' => $result['format'] ?? null,
		];

		return true;
	}

	/**
	 * Invoke ffprobe on the command line.
	 *
	 * @return array|null Success
	 */
	private function invokeFFProbe(): ?array {
		try {
			$ffprobeLocation = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'EmbedVideo' )
				->get( 'FFProbeLocation' );
		} catch ( ConfigException $e ) {
			return null;
		}

		if ( Shell::isDisabled() || empty( $ffprobeLocation ) || !file_exists( $ffprobeLocation ) ) {
			return null;
		}

		$command = MediaWikiServices::getInstance()->getShellCommandFactory()->create();
		$command->params( $ffprobeLocation );

		$command->unsafeParams( [
			'-v quiet',
			'-print_format json',
			'-show_format',
			'-show_streams',
			Shell::escape( $this->getFilePath() ),
		] );

		try {
			$result = $command->execute();

			$json = json_decode( $result->getStdout(), true, 512, JSON_THROW_ON_ERROR );
		} catch ( Exception | JsonException | ShellDisabledError | ProcOpenError $e ) {
			wfLogWarning( $e->getMessage() );
			return null;
		}

		if ( is_array( $json ) ) {
			return $json;
		}

		return null;
	}
}
