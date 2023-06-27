<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo\Media\FFProbe;

use ConfigException;
use Exception;
use File;
use FSFile;
use JsonException;
use MediaWiki\MediaWikiServices;
use MediaWiki\ProcOpenError;
use MediaWiki\Shell\Shell;
use MediaWiki\ShellDisabledError;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

class FFProbe {
    /**
     * MediaWiki File
     *
     * @var File|FSFile
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
     * @param File|FSFile $file MediaWiki File
     * @return void
     */
    public function __construct( $file ) {
        $this->file = $file;
    }

    /**
     * Return the entire cache of metadata.
     *
     * @param string $select The selected audio/video stream
     * @return bool Flag if loading did succeed
     */
    public function loadMetaData(string $select = 'v:0' ): bool {
        if ( $this->file instanceof FSFile ) {
            $cacheKey = $this->file->getSha1Base36();
        } else {
            $cacheKey = $this->file->getSha1();
        }

        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $cacheKey = $cache->makeGlobalKey( 'EmbedVideo', 'ffprobe', $cacheKey, $select );

        $result = $cache->getWithSetCallback(
            $cacheKey,
            // FSFiles are usually only present for uploads(?), only "real" files are relevant
            $this->file instanceof File ? ExpirationAwareness::TTL_INDEFINITE : ExpirationAwareness::TTL_MINUTE,
            function ( $old, &$ttl ) {
                if ( $this->file instanceof FSFile ) {
                    $title = 'Newly uploaded file';
                } else {
                    $title = $this->file->getTitle();
                    $title = $title !== null ? $title->getBaseText() : 'Untitled file';
                }

                wfDebugLog(
                    'EmbedVideo',
                    sprintf( 'Writing FFProbe Cache for %s', $title )
                );

                $result = $this->invokeFFProbe();

                if ( $result === false ) {
                    $ttl = ExpirationAwareness::TTL_UNCACHEABLE;
                    return $old;
                }

                return $this->metadata;
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
        if ( $this->file instanceof FSFile ) {
            return $this->file->getPath();
        }

        return $this->file->getLocalRefPath();
    }

    /**
     * Invoke ffprobe on the command line.
     *
     * @return bool Success
     */
    private function invokeFFProbe(): bool {
        try {
            $ffprobeLocation = MediaWikiServices::getInstance()
                ->getConfigFactory()
                ->makeConfig( 'EmbedVideo' )
                ->get( 'FFProbeLocation' );
        } catch ( ConfigException $e ) {
            return false;
        }

        if ( Shell::isDisabled() || $ffprobeLocation === false || !file_exists( $ffprobeLocation ) ) {
            $this->metadata = [];
            return false;
        }

        $command = Shell::command( $ffprobeLocation );

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
            $this->metadata = [];
            return false;
        }

        if ( is_array( $json ) ) {
            $this->metadata = $json;
        } else {
            $this->metadata = [];
            return false;
        }

        return true;
    }
}
