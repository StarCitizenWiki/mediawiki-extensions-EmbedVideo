<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use ConfigException;
use MediaWiki\Extension\EmbedVideo\Media\AudioHandler;
use MediaWiki\Extension\EmbedVideo\Media\VideoHandler;
use MediaWiki\MediaWikiServices;
use Message;
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

class EmbedVideoHooks {
	/**
	 * Temporary storage for the current service object.
	 *
	 * @var object
	 */
	private static $service;

	/**
	 * Description Parameter
	 *
	 * @var string
	 */
	private static $description = false;

	/**
	 * Alignment Parameter
	 *
	 * @var string
	 */
	private static $alignment = false;

	/**
	 * Alignment Parameter
	 *
	 * @var string
	 */
	private static $vAlignment = false;

	/**
	 * Container Parameter
	 *
	 * @var string
	 */
	private static $container = false;

	/**
	 * Hook to setup defaults.
	 *
	 * @access public
	 * @return void
	 */
	public static function onExtension(): void {
		global $wgEmbedVideoDefaultWidth, $wgMediaHandlers, $wgFileExtensions;

		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !isset( $wgEmbedVideoDefaultWidth ) && ( isset( $_SERVER['HTTP_X_MOBILE'] ) && $_SERVER['HTTP_X_MOBILE'] === 'true' ) && $_COOKIE['stopMobileRedirect'] != 1 ) {
			// Set a smaller default width when in mobile view.
			$wgEmbedVideoDefaultWidth = 320;
		}

		$audioHandler = AudioHandler::class;
		$videoHandler = VideoHandler::class;

		if ( $config->get( 'EmbedVideoEnableAudioHandler' ) ) {
			$wgMediaHandlers['application/ogg']		= $audioHandler;
			$wgMediaHandlers['audio/flac']			= $audioHandler;
			$wgMediaHandlers['audio/ogg']			= $audioHandler;
			$wgMediaHandlers['audio/mpeg']			= $audioHandler;
			$wgMediaHandlers['audio/mp4']			= $audioHandler;
			$wgMediaHandlers['audio/wav']			= $audioHandler;
			$wgMediaHandlers['audio/webm']			= $audioHandler;
			$wgMediaHandlers['audio/x-flac']		= $audioHandler;
		}

		if ( $config->get( 'EmbedVideoEnableVideoHandler' ) ) {
			$wgMediaHandlers['video/mp4']			= $videoHandler;
			$wgMediaHandlers['video/ogg']			= $videoHandler;
			$wgMediaHandlers['video/quicktime']		= $videoHandler;
			$wgMediaHandlers['video/webm']			= $videoHandler;
			$wgMediaHandlers['video/x-matroska']	= $videoHandler;
		}

		if ( $config->get( 'EmbedVideoAddFileExtensions' ) ) {
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
	 * @access public
	 * @param Parser $parser Parser object passed as a reference.
	 * @return bool true
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ): bool {
		$parser->setFunctionHook( 'ev', 'MediaWiki\\Extension\\EmbedVideo\\EmbedVideoHooks::parseEV' );

		return true;
	}

	/**
	 * Embeds a video of the chosen service.
	 *
	 * @access public
	 * @param Parser $parser Parser
	 * @param ?string $service [Optional] Which online service has the video.
	 * @param ?string $id [Optional] Identifier Code or URL for the video on the service.
	 * @param ?string $dimensions [Optional] Dimensions of video
	 * @param ?string $alignment [Optional] Horizontal Alignment of the embed container.
	 * @param ?string $description [Optional] Description to show
	 * @param ?string $container [Optional] Container to use.(Frame is currently the only option.)
	 * @param ?string $urlArgs [Optional] Extra URL Arguments
	 * @param ?string $autoResize [Optional] Automatically Resize video that will break its parent container.
	 * @param ?string $vAlignment [Optional] Vertical Alignment of the embed container.
	 * @return array Encoded representation of input params (to be processed later)
	 */
	public static function parseEV( $parser, $service = null, $id = null, $dimensions = null, $alignment = null, $description = null, $container = null, $urlArgs = null, $autoResize = null, $vAlignment = null ) {
		self::resetParameters();

		$service		= trim( $service ?? '' );
		$id				= trim( $id ?? '' );
		$alignment		= trim( $alignment ?? '' );
		$description	= trim( $description ?? '' );
		$dimensions		= trim( $dimensions ?? '' );
		$urlArgs		= trim( $urlArgs ?? '' );
		$width			= null;
		$height			= null;
		$autoResize		= !( isset( $autoResize ) && strtolower( trim( $autoResize ) ) === "false" );
		$vAlignment		= trim( $vAlignment ?? '' );
		$config = MediaWikiServices::getInstance()->getMainConfig();

		try {
			$enabledServices = $config->get( 'EmbedVideoEnabledServices' ) ?? [];
			if ( !empty( $enabledServices ) && !in_array( $service, $enabledServices, true ) ) {
				return self::error( 'service', sprintf( '%s (as it is disabled)', $service ) );
			}
		} catch ( ConfigException $e ) {
			// Pass through
		}

		// I am not using $parser->parseWidthParam() since it can not handle height only.  Example: x100
		if ( stripos( $dimensions, 'x' ) !== false ) {
			$dimensions = strtolower( $dimensions );
			[ $width, $height ] = explode( 'x', $dimensions );
		} elseif ( is_numeric( $dimensions ) ) {
			$width = $dimensions;
		}

		/************************************/
		/* Twitch Fixes                     */
		/************************************/
		// Add parent attribute for Twitch embeds
		if ( $service === 'twitch' || $service === 'twitchclip' || $service === 'twitchvod' ) {
			$serverName = $config->get( 'ServerName' );
			if ( !isset( $urlArgs ) || empty( $urlArgs ) ) {
				// Set the url args to the parent domain
				$urlArgs = "parent=$serverName";
			} else {
				// Break down the url args and inject the parent
				$urlargsArr = [];
				parse_str( $urlArgs, $urlargsArr );
				$urlargsArr['parent'] = $serverName;
				$urlArgs = http_build_query( $urlargsArr );
			}
		}

		/************************************/
		/* Error Checking                   */
		/************************************/
		if ( !$service || !$id ) {
			return self::error( 'missingparams', $service, $id );
		}

		self::$service = VideoService::newFromName( $service );

		if ( self::$service === false ) {
			return self::error( 'service', $service );
		}

		// Let the service automatically handle bad dimensional values.
		self::$service->setWidth( $width );

		self::$service->setHeight( $height );

		// If the service has an ID pattern specified, verify the id number.
		if ( !self::$service->setVideoID( $id ) ) {
			return self::error( 'id', $service, $id );
		}

		if ( !self::$service->setUrlArgs( $urlArgs ) ) {
			return self::error( 'urlargs', $service, $urlArgs );
		}

		if ( $parser !== null ) {
			self::setDescription( $description, $parser );
		} else {
			self::setDescriptionNoParse( $description );
		}

		if ( !self::setContainer( $container ) ) {
			return self::error( 'container', $container );
		}

		if ( !self::setAlignment( $alignment ) ) {
			return self::error( 'alignment', $alignment );
		}

		if ( !self::setVerticalAlignment( $vAlignment ) ) {
			return self::error( 'valignment', $vAlignment );
		}

		/************************************/
		/* HTML Generation                  */
		/************************************/
		$html = self::$service->getHtml();
		if ( !$html ) {
			return self::error( 'unknown', $service );
		}

		$html = self::generateWrapperHTML( $html, $autoResize ? 'autoResize' : null, $service );

		if ( $parser ) {
			// dont call this if parser is null (such as in API usage).
			$out = $parser->getOutput();
			$out->addModules( 'ext.embedVideo' );
			$out->addModuleStyles( 'ext.embedVideo.styles' );

			if ( MediaWikiServices::getInstance()->getMainConfig()->get( 'EmbedVideoRequireConsent' ) === true ) {
				$parser->getOutput()->addModules( 'ext.embedVideo.consent' );
			}
		}

		return [
			$html,
			'noparse' => true,
			'isHTML' => true
		];
	}

	/**
	 * Generate the HTML necessary to embed the video with the given alignment
	 * and text description
	 *
	 * @private
	 * @param  string	[Optional] Horizontal Alignment
	 * @param  string	[Optional] Description
	 * @param  string  [Optional] Additional Classes to add to the wrapper
	 * @return string
	 */
	private static function generateWrapperHTML( $html, $addClass = null, string $service = '' ): string {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$classString = 'embedvideo';
		$styleString = '';
		$innerClassString = implode( ' ', array_filter( [
			'embedvideowrap',
			$service,
			// This should probably be added as a RL variable
			$config->get( 'EmbedVideoFetchExternalThumbnails' ) ?: 'no-fetch'
		] ) );
		$outerClassString = 'embedvideo ';

		if ( self::getContainer() === 'frame' ) {
			$classString .= ' thumbinner';
		}

		if ( self::getAlignment() !== false ) {
			$outerClassString .= sprintf( ' ev_%s ', self::getAlignment() );
			$styleString .= sprintf( ' width: %dpx;', ( self::$service->getWidth() + 6 ) );
		}

		if ( self::getVerticalAlignment() !== false ) {
			$outerClassString .= sprintf( ' ev_%s ', self::getVerticalAlignment() );
		}

		if ( $addClass ) {
			$classString .= ' ' . $addClass;
			$outerClassString .= $addClass;
		}

		$consentClickContainer = '';
		if ( $config->get( 'EmbedVideoRequireConsent' ) ) {
			$consentClickContainer = sprintf(
				'<div class="embedvideo-consent"><div class="embedvideo-consent__overlay"><div class="embedvideo-consent__message">%s</div></div></div>',
				( new Message( 'embedvideo_consent_text' ) )->text()
			);
		}

		return "<div class='thumb $outerClassString' style='width: " . ( self::$service->getWidth() + 8 ) . "px;'>" .
				"<div class='" . $classString . "' style='" . $styleString . "'>" .
					"<div class='" . $innerClassString . "' style='width: " . self::$service->getWidth() . "px;'>{$consentClickContainer}{$html}</div>" .
					( self::getDescription() !== false ? "<div class='thumbcaption'>" . self::getDescription() . "</div>" : null ) .
				"</div>" .
			"</div>";
	}

	/**
	 * Return the alignment parameter.
	 *
	 * @access public
	 * @return bool|string Alignment or false for not set.
	 */
	private static function getAlignment() {
		return self::$alignment;
	}

	/**
	 * Set the align parameter.
	 *
	 * @private
	 * @param  string	Alignment Parameter
	 * @return bool Valid
	 */
	private static function setAlignment( $alignment ): bool {
		if ( !empty( $alignment ) && ( $alignment === 'left' || $alignment === 'right' || $alignment === 'center' || $alignment === 'inline' ) ) {
			self::$alignment = $alignment;
		} elseif ( !empty( $alignment ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return the valignment parameter.
	 *
	 * @access public
	 * @return bool|string Vertical Alignment or false for not set.
	 */
	private static function getVerticalAlignment() {
		return self::$vAlignment;
	}

	/**
	 * Set the align parameter.
	 *
	 * @private
	 * @param  string	Alignment Parameter
	 * @return bool Valid
	 */
	private static function setVerticalAlignment( $vAlignment ): bool {
		if ( !empty( $vAlignment ) && ( $vAlignment === 'top' || $vAlignment === 'middle' || $vAlignment === 'bottom' || $vAlignment === 'baseline' ) ) {
			if ( $vAlignment !== 'baseline' ) {
				self::$alignment = 'inline';
			}
			self::$vAlignment = $vAlignment;
		} elseif ( !empty( $vAlignment ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return description text.
	 *
	 * @private
	 * @return bool|string String description or false for not set.
	 */
	private static function getDescription() {
		return self::$description;
	}

	/**
	 * Set the description.
	 *
	 * @private
	 * @param string $description Description
	 * @param Parser $parser Mediawiki Parser object
	 * @return void
	 */
	private static function setDescription( string $description, Parser $parser ): void {
		self::$description = ( !$description ? false : $parser->recursiveTagParse( $description ) );
	}

	/**
	 * Set the description without using the parser
	 *
	 * @param string	Description
	 */
	private static function setDescriptionNoParse( $description ): void {
		self::$description = ( !$description ? false : $description );
	}

	/**
	 * Return container type.
	 *
	 * @private
	 * @return bool|string String container type or false for not set.
	 */
	private static function getContainer() {
		return self::$container;
	}

	/**
	 * Set the container type.
	 *
	 * @private
	 * @param  string	Container
	 * @return bool Success
	 */
	private static function setContainer( $container ): bool {
		if ( !empty( $container ) && ( $container === 'frame' ) ) {
			self::$container = $container;
		} elseif ( !empty( $container ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Reset parameters between parses.
	 *
	 * @private
	 * @return void
	 */
	private static function resetParameters(): void {
		self::$description	= false;
		self::$alignment	= false;
		self::$container	= false;
	}

	/**
	 * Error Handler
	 *
	 * @private
	 * @param  string	[Optional] Error Type
	 * @param  mixed	[...] Multiple arguments to be retrieved with func_get_args().
	 * @return array Printable Error Message
	 */
	private static function error( $type = 'unknown' ): array {
		$arguments = func_get_args();
		array_shift( $arguments );

		$message = wfMessage( 'error_embedvideo_' . $type, $arguments )->escaped();

		return [
			"<div class='errorbox'>{$message}</div>",
			'noparse' => true,
			'isHTML' => true
		];
	}
}
