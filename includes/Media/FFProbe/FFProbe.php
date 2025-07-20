<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\FFProbe;

use Exception;
use JsonException;
use MediaWiki\Config\ConfigException;
use MediaWiki\FileRepo\File\File;
use MediaWiki\MediaWikiServices;
use MediaWiki\ProcOpenError;
use MediaWiki\Settings\SettingsBuilder;
use MediaWiki\Shell\Shell;
use MediaWiki\ShellDisabledError;
use Wikimedia\FileBackend\FSFile\FSFile;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

class FFProbe {
	/**
	 * MediaWiki File
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * @var FSFile|File|string
	 */
	private $file;

	/**
	 * Meta Data Cache
	 *
	 * @var array
	 */
	private $metadata;

	/**
	 * Main Constructor
	 *
	 * @param string $filename MediaWiki File name
	 * @param FSFile|File|string $file
	 * @return void
	 */
	public function __construct( $filename, $file ) {
		$this->filename = $filename;
		$this->file = $file;
	}

	/**
	 * Return the entire cache of metadata.
	 *
	 * @param string $select The selected audio/video stream
	 * @return bool Flag if loading did succeed
	 */
	public function loadMetaData( string $select = 'v:0' ): bool {
		// If this is in a maintenance call context, don't use the cache
		if ( isset( $GLOBALS['wgSettings'] ) && $GLOBALS['wgSettings'] instanceof SettingsBuilder ) {
			$isMaintenance = $GLOBALS['wgSettings']->getConfig()->get( 'CommandLineMode' );
			if ( $isMaintenance ) {
				$metadata = $this->invokeFFProbe();
				$this->metadata = $metadata;

				return is_array( $metadata );
			}
		}

		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		$cacheKey = $cache->makeGlobalKey( 'EmbedVideo', 'ffprobe', $this->filename, $select );
		$ttl = ( $this->file instanceof File || is_string( $this->file ) )
			? ExpirationAwareness::TTL_INDEFINITE : ExpirationAwareness::TTL_MINUTE;

		$result = $cache->getWithSetCallback(
			$cacheKey,
			$ttl,
			function ( $old, &$ttl ) {
				$result = $this->invokeFFProbe();

				if ( $result === null ) {
					$ttl = ExpirationAwareness::TTL_UNCACHEABLE;
					return $old;
				}

				return $result;
			}
		);

		if ( is_array( $result ) ) {
			$this->metadata = [
				'streams' => $result['streams'],
				'format' => $result['format'],
			];

			return true;
		}

		return false;
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
		$this->loadMetaData( $select );

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
