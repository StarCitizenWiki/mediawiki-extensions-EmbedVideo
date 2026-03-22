<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigFactory;
use MediaWiki\Exception\MWException;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Skin\Skin;
use MediaWiki\Skin\SkinTemplate;
use MediaWiki\SpecialPage\SpecialPage;
use LocalFile;
use RepoGroup;

/**
 * EmbedVideo
 * EmbedVideo Hooks
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 */

class EmbedVideoHooks implements
	ParserFirstCallInitHook,
	BeforePageDisplayHook,
	SkinTemplateNavigation__UniversalHook
{

	private Config $config;

	/**
	 * @param ConfigFactory $factory
	 * @param RepoGroup $repoGroup
	 */
	public function __construct(
		ConfigFactory $factory,
		private RepoGroup $repoGroup
	) {
		$this->config = $factory->makeConfig( 'EmbedVideo' );
	}

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
			$parser->setHook( 'embedvideo', [ EmbedVideo::class, 'parseEVTag' ] );

			$parser->setFunctionHook(
				'ev',
				[ EmbedVideo::class, 'parseEV' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setFunctionHook(
				'evt',
				[ EmbedVideo::class, 'parseEV' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setFunctionHook(
				'evu',
				[ EmbedVideo::class, 'parseEVU' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setFunctionHook(
				'evl',
				[ EmbedVideo::class, 'parseEVL' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setFunctionHook(
				'vlink',
				[ EmbedVideo::class, 'parseEVL' ],
				Parser::SFH_OBJECT_ARGS
			);

			$parser->setHook( 'evlplayer', [ EmbedVideo::class, 'parseEVLTag' ] );
			$parser->setHook( 'vplayer', [ EmbedVideo::class, 'parseEVLTag' ] );
		} catch ( MWException $e ) {
			wfLogWarning( $e->getMessage() );
		}

		$enabledServices = $this->config->get( 'EmbedVideoEnabledServices' );
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
		if ( $this->config->get( 'EmbedVideoUseEmbedStyleForLocalVideos' ) === true ) {
			$out->addModuleStyles( [ 'ext.embedVideo.styles' ] );
			$out->addModules( [ 'ext.embedVideo.overlay' ] );
		}
	}

	/**
	 * Adds a file-page action for explicitly refreshing stored metadata.
	 *
	 * @param SkinTemplate $sktemplate
	 * @param array &$links
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		$title = $sktemplate->getTitle();
		if (
			$title->getNamespace() !== NS_FILE ||
			!$sktemplate->getUser()->isAllowed( 'embedvideo-refreshmetadata' )
		) {
			return;
		}

		$file = $this->repoGroup->getLocalRepo()->newFile( $title );
		if ( !$this->isRefreshableLocalFile( $file ) ) {
			return;
		}

		$label = $sktemplate->msg( 'embedvideo-refreshmetadata-tab' )->text();
		$links['actions']['embedvideo-refreshmetadata'] = [
			'text' => $label,
			'title' => $label,
			'href' => SpecialPage::getTitleFor(
				'RefreshEmbedVideoMetadata',
				$title->getDBkey()
			)->getLocalURL(),
		];
	}

	/**
	 * Check whether a file can be refreshed via the metadata refresh workflow.
	 *
	 * @param mixed $file Candidate file object.
	 * @return bool
	 */
	private function isRefreshableLocalFile( mixed $file ): bool {
		return $file instanceof LocalFile
			&& $file->exists()
			&& $file->isLocal()
			&& $file->getRedirected() === null
			&& $file->getHandler() instanceof AudioHandler;
	}
}
