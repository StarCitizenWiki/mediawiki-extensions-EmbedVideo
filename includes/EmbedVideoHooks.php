<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\Hook\ArticlePurgeHook;
use MWException;
use OutputPage;
use Parser;
use Skin;
use WikiPage;

/**
 * EmbedVideo
 * EmbedVideo Hooks
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 */

class EmbedVideoHooks implements ParserFirstCallInitHook, BeforePageDisplayHook, ArticlePurgeHook {
	/**
	 * Adds the appropriate audio and video handlers
	 *
	 * @return void
	 */
	public static function setup(): void {
		global $wgFileExtensions, $wgMediaHandlers, $wgEmbedVideoDefaultWidth,
			   $wgEmbedVideoEnableAudioHandler, $wgEmbedVideoEnableVideoHandler, $wgEmbedVideoAddFileExtensions;

		if ( !isset( $wgEmbedVideoDefaultWidth ) &&
			( isset( $_SERVER['HTTP_X_MOBILE'] ) && $_SERVER['HTTP_X_MOBILE'] === 'true' ) &&
			$_COOKIE['stopMobileRedirect'] !== 1 ) {
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
	 */
	public function onParserFirstCallInit( $parser ): void {
		try {
			$parser->setFunctionHook(
				'ev',
				[ EmbedVideo::class, 'parseEV' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setHook( 'embedvideo', [ EmbedVideo::class, 'parseEVTag' ] );
		} catch ( MWException $e ) {
			wfLogWarning( $e->getMessage() );
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EmbedVideo' );
		$enabledServices = $config->get( 'EmbedVideoEnabledServices' );
		$checkEnabledServices = !empty( $enabledServices );

		foreach ( EmbedServiceFactory::getAvailableServices() as $service ) {
			try {
				$name = $service::getServiceName();

				// Skip disabled services
				if ( $checkEnabledServices && !in_array( $name, $enabledServices, true ) ) {
					continue;
				}

				$parser->setHook( $name, [ EmbedVideo::class, "parseTag{$name}" ] );
			} catch ( MWException $e ) {
				wfLogWarning( $e->getMessage() );
			}
		}
	}

	/**
	 * Adds required modules if $wgEmbedVideoUseEmbedStyleForLocalVideos is true
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EmbedVideo' );

		if ( $config->get( 'EmbedVideoUseEmbedStyleForLocalVideos' ) === true ) {
			$out->addModuleStyles( [ 'ext.embedVideo.styles' ] );
			$out->addModules( [ 'ext.embedVideo.overlay' ] );
		}
	}

	/**
	 * Purges possible FFProbe results from the main cache
	 *
	 * @param WikiPage $wikiPage
	 * @return void
	 */
	public function onArticlePurge( $wikiPage ): void {
		if ( $wikiPage->getTitle() === null || $wikiPage->getTitle()->getNamespace() !== NS_FILE ) {
			return;
		}

		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $wikiPage->getTitle() );
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();

		// The last part is only ever a:0 or v:0
		$audioKey = $cache->makeGlobalKey( 'EmbedVideo', 'ffprobe', $file->getSha1(), 'a:0' );
		$videoKey = $cache->makeGlobalKey( 'EmbedVideo', 'ffprobe', $file->getSha1(), 'v:0' );

		$cache->delete( $audioKey );
		$cache->delete( $videoKey );

		wfDebugLog(
			'EmbedVideo',
			sprintf(
				'Purging FFProbe Cache for %s',
				$wikiPage->getTitle()->getBaseText()
			)
		);
	}
}
