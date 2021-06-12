<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MWException;
use Parser;

/**
 * EmbedVideo
 * EmbedVideo Hooks
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 */

class EmbedVideoHooks implements ParserFirstCallInitHook {
	/**
	 * Adds the appropriate audio and video handlers
	 *
	 * @return void
	 */
	public static function setup(): void {
		global $wgFileExtensions, $wgMediaHandlers, $wgEmbedVideoDefaultWidth,
			   $wgEmbedVideoEnableAudioHandler, $wgEmbedVideoEnableVideoHandler, $wgEmbedVideoAddFileExtensions;

		if ( !isset( $wgEmbedVideoDefaultWidth ) && ( isset( $_SERVER['HTTP_X_MOBILE'] ) && $_SERVER['HTTP_X_MOBILE'] === 'true' ) && $_COOKIE['stopMobileRedirect'] !== 1 ) {
			// Set a smaller default width when in mobile view.
			$wgEmbedVideoDefaultWidth = 320;
		}

		$audioHandler = AudioHandler::class;
		$videoHandler = VideoHandler::class;

		if ( $wgEmbedVideoEnableAudioHandler ) {
			$wgMediaHandlers['application/ogg']		= $audioHandler;
			$wgMediaHandlers['audio/flac']			= $audioHandler;
			$wgMediaHandlers['audio/ogg']			= $audioHandler;
			$wgMediaHandlers['audio/mpeg']			= $audioHandler;
			$wgMediaHandlers['audio/mp4']			= $audioHandler;
			$wgMediaHandlers['audio/wav']			= $audioHandler;
			$wgMediaHandlers['audio/webm']			= $audioHandler;
			$wgMediaHandlers['audio/x-flac']		= $audioHandler;
		}

		if ( $wgEmbedVideoEnableVideoHandler ) {
			$wgMediaHandlers['video/mp4']			= $videoHandler;
			$wgMediaHandlers['video/ogg']			= $videoHandler;
			$wgMediaHandlers['video/quicktime']		= $videoHandler;
			$wgMediaHandlers['video/webm']			= $videoHandler;
			$wgMediaHandlers['video/x-matroska']	= $videoHandler;
		}

		if ( $wgEmbedVideoAddFileExtensions ) {
			$wgFileExtensions[] = 'flac';
			$wgFileExtensions[] = 'mkv';
			$wgFileExtensions[] = 'mov';
			$wgFileExtensions[] = 'mp3';
			$wgFileExtensions[] = 'mp4';
			$wgFileExtensions[] = 'oga';
			$wgFileExtensions[] = 'ogg';
			$wgFileExtensions[] = 'ogv';
			$wgFileExtensions[] = 'wav';
			$wgFileExtensions[] = 'webm';
		}
	}

	/**
	 * Sets up this extension's parser functions.
	 *
	 * @param Parser $parser Parser object passed as a reference.
	 * @return bool true
	 * @throws MWException
	 */
	public function onParserFirstCallInit( $parser ): bool {
		$parser->setFunctionHook(
			'ev',
			'MediaWiki\\Extension\\EmbedVideo\\EmbedVideo::parseEV',
			Parser::SFH_OBJECT_ARGS
		);

		return true;
	}
}
