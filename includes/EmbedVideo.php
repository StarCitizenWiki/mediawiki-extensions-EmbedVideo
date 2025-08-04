<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\EmbedVideo;

use InvalidArgumentException;
use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigException;
use MediaWiki\Extension\EmbedVideo\EmbedService\AbstractEmbedService;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedHtmlFormatter;
use MediaWiki\Extension\EmbedVideo\EmbedService\EmbedServiceFactory;
use MediaWiki\Extension\EmbedVideo\EmbedService\OEmbedServiceInterface;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use RuntimeException;

class EmbedVideo {
	/**
	 * @var Parser|null
	 */
	private $parser;

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * Temporary storage for the current service object.
	 *
	 * @var AbstractEmbedService
	 */
	private $service;

	/**
	 * Description Parameter
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Alignment Parameter
	 *
	 * @var string
	 */
	private $alignment = false;

	/**
	 * Alignment Parameter
	 *
	 * @var string
	 */
	private $vAlignment = false;

	/**
	 * Container Parameter
	 *
	 * @var string
	 */
	private $container = false;

	/**
	 * Creates a new EmbedVideo instance
	 *
	 * @param Parser|null $parser
	 * @param array $args
	 * @param bool $fromTag
	 */
	public function __construct( ?Parser $parser, array $args, bool $fromTag = false ) {
		$this->parser = $parser;
		$this->args = $this->parseArgs( $args, $fromTag );
		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EmbedVideo' );
	}

	/**
	 * {{#evu}} parser function that tries to extract the service from the host name
	 *
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param array $args
	 * @param bool $fromTag
	 * @return array
	 */
	public static function parseEVU( Parser $parser, PPFrame $frame, array $args, bool $fromTag = false ): array {
		$host = parse_url( $args[0] ?? '', PHP_URL_HOST ) ?? '';

		if ( is_string( $host ) ) {
			$host = explode( '.', trim( strtolower( $host ), 'w.' ) );
			array_pop( $host );
			$host = implode( '.', $host ?? '' );
		}

		array_unshift( $args, $host );

		return self::parseEV( $parser, $frame, $args, $fromTag );
	}

	/**
	 * Parse the values input from the {{#ev}} parser function
	 *
	 * @param Parser $parser The active Parser instance
	 * @param PPFrame $frame Frame
	 * @param array $args Arguments
	 * @param bool $fromTag Toggle whether this was called from a tag like <youtube> or the parser fn {{#ev
	 *
	 * @return array Parser options and the HTML comments of cached attributes
	 */
	public static function parseEV( Parser $parser, PPFrame $frame, array $args, bool $fromTag = false ): array {
		$expandedArgs = [];

		foreach ( $args as $key => $arg ) {
			$value = trim( $frame->expand( $arg ) );
			if ( $fromTag === true ) {
				$expandedArgs[$key] = $parser->recursiveTagParse( $value, $frame );
			} else {
				$expandedArgs[] = $value;
			}
		}

		return ( new EmbedVideo( $parser, $expandedArgs, $fromTag ) )->output();
	}

	/**
	 * Parse the values input from the {{#evl}} or {{#vlink}} parser function
	 *
	 * @param Parser $parser The active Parser instance
	 * @param PPFrame $frame Frame
	 * @param array $args Arguments
	 *
	 * @return array Parser options and the HTML comments of cached attributes
	 */
	public static function parseEVL( Parser $parser, PPFrame $frame, array $args ): array {
		$expandedArgs = [
			'id' => null,
			'text' => null,
			'player' => null,
			'service' => null,
			'urlArgs' => null,
		];

		$keys = array_keys( $expandedArgs );

		foreach ( $args as $key => $arg ) {
			$value = trim( $frame->expand( $arg ) );

			if ( str_contains( $value, '=' ) ) {
				$parts = array_map( 'trim', explode( '=', $value, 2 ) );

				$expandedArgs[$parts[0]] = $parts[1] ?? null;
			} else {
				$expandedArgs[$keys[$key]] = $value;
			}
		}

		if ( empty( $expandedArgs['service'] ) ) {
			$expandedArgs['service'] = 'youtube';
		}

		$ev = new EmbedVideo( $parser, $expandedArgs, true );

		try {
			$ev->init();
			$ev->addModules();

			if ( empty( $ev->args['text'] ) ) {
				throw new InvalidArgumentException( $ev->error( 'missingparams', $ev->args['service'] )[0] );
			}
		} catch ( InvalidArgumentException $e ) {
			return [
				$e->getMessage(),
				'noparse' => true,
				'isHTML' => true
			];
		}

		$linkConfig = [
			'data-iframeconfig' => $ev->service->getIframeConfig( $ev->args['width'], $ev->args['height'] ),
			'data-service' => $ev->args['service'],
			'data-player' => $ev->args['player'] ?? 'default',
			'class' => 'embedvideo-evl vplink',
			'href' => '#',
		];

		if ( MediaWikiServices::getInstance()->getMainConfig()->get( 'EmbedVideoRequireConsent' ) === true ) {
			$linkConfig['data-privacy-url'] = $ev->service->getPrivacyPolicyUrl();
		}

		return [
			Html::element( 'a', $linkConfig, $ev->args['text'] ),
			'noparse' => true,
			'isHTML' => true
		];
	}

	/**
	 * Parse a service tag like <youtube>
	 *
	 * @param string $input The content of the tag i.e. the video id
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return array
	 */
	public static function parseEVTag( $input, array $args, Parser $parser, PPFrame $frame ): array {
		if ( !isset( $args['id'] ) && !empty( $input ) ) {
			$args['id'] = $input;
		}

		return self::parseEV( $parser, $frame, $args, true );
	}

	/**
	 * Parse a service tag like <evlplayer> or <vplayer>
	 *
	 * @param string $input The content of the tag i.e. the video id
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return array
	 */
	public static function parseEVLTag( $input, array $args, Parser $parser, PPFrame $frame ): array {
		$args['player'] = $args['id'] ?? 'default';
		$args['id'] = $args['defaultid'] ?? null;
		$args['service'] = $args['service'] ?? 'youtube';

		if ( empty( $args['id'] ) ) {
			$args['id'] = '-1';
			$args['service'] = 'videolink';
		}

		if ( !isset( $args['title'] ) ) {
			$args['title'] = $input;
		}

		if ( !isset( $args['service'] ) ) {
			$args['service'] = 'youtube';
		}

		$args['width'] = $args['w'] ?? null;
		$args['height'] = $args['h'] ?? null;
		$args['class'] = ( $args['class'] ?? '' ) . ' evlplayer evlplayer-' . $args['player'];

		$args = array_filter( $args );

		$parser->getOutput()->addModules( [
			'ext.embedVideo.videolink',
			'ext.embedVideo.messages',
		] );

		return self::parseEV( $parser, $frame, $args, true );
	}

	/**
	 * Wrapper for service specific tag calls
	 *
	 * @param string $name Method name
	 * @param array $arguments Method arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( !str_starts_with( $name, 'parseTag' ) ) {
			return '';
		}

		[
			0 => $input,
			1 => $args,
			2 => $parser,
			3 => $frame,
		] = $arguments;

		$args['service'] = strtolower( substr( $name, 8 ) );

		return self::parseEVTag( $input, $args, $parser, $frame );
	}

	/**
	 * Outputs the iframe or error message
	 *
	 * @return array
	 */
	public function output(): array {
		$service = $this->args['service'] ?? null;

		try {
			$enabledServices = $this->config->get( 'EmbedVideoEnabledServices' ) ?? [];
			if ( !empty( $enabledServices ) && !in_array( $service, $enabledServices, true ) ) {
				return $this->error( 'service', sprintf( '%s (as it is disabled)', $service ) );
			}
		} catch ( ConfigException $e ) {
			// Pass through
		}

		try {
			$this->init();
		} catch ( InvalidArgumentException $e ) {
			return [
				$e->getMessage(),
				'noparse' => true,
				'isHTML' => true
			];
		}

		$this->addModules();

		return [
			// This does the whole HTML generation
			EmbedHtmlFormatter::toHtml(
				$this->service,
				$this->makeHtmlFormatConfig( $this->service ),
				$this->args
			),
			'noparse' => false,
			'isHTML' => true
		];
	}

	/**
	 * Parses the arguments given to the parser function
	 *
	 * @param array $args
	 * @param bool $fromTag
	 * @return array
	 */
	private function parseArgs( array $args, bool $fromTag ): array {
		$supportedArgs = [
			'id' => '',
			'dimensions' => '',
			'alignment' => '',
			'description' => '',
			'container' => '',
			'urlArgs' => '',
			'autoresize' => false,
			'vAlignment' => '',
			'width' => null,
			'height' => null,
			'cover' => null,
			'poster' => null,
			'title' => null,
		];

		if ( $fromTag === true ) {
			$supportedArgs['service'] = $args['service'] ?? null;

			return array_merge( $supportedArgs, $args );
		}

		$keys = array_keys( $supportedArgs );

		$serviceName = str_replace( 'service=', '', array_shift( $args ) ?? '' );

		$counter = 0;

		/**
		 * This takes each 'raw' argument and tries to parse it into named and unnamed arguments
		 * If no 'name' is detected, the value is set in order of $results (see above)
		 */
		foreach ( $args as $arg ) {
			$pair = [ $arg ];
			// Only split arg if it is not an url and not urlArgs
			// phpcs:ignore Generic.Files.LineLength.TooLong
			if ( ( $keys[$counter] !== 'urlArgs' || str_contains( $arg, 'urlArgs' ) ) && preg_match( '/https?:/', $arg ) !== 1 ) {
				$pair = explode( '=', $arg, 2 );
			}
			$pair = array_map( 'strip_tags', array_map( 'trim', $pair ) );

			// We are handling a named argument
			if ( count( $pair ) === 2 ) {
				[ $name, $value ] = $pair;
				if ( array_key_exists( $name, $supportedArgs ) ) {
					$supportedArgs[$name] = $value;
				}
			// An unnamed argument we have to match by position
			} elseif ( count( $pair ) === 1 && !empty( $pair[0] ) ) {
				$pair = $pair[0];

				$supportedArgs[$keys[$counter]] = $pair;
			}

			++$counter;
		}

		$supportedArgs['service'] = empty( $serviceName ) ? false : $serviceName;

		// An intentional weak check
		if ( $supportedArgs['autoresize'] == true ) {
			$supportedArgs['autoresize'] = true;
		}

		return $supportedArgs;
	}

	/**
	 * Error Handler
	 *
	 * @param string $type [Optional] Error Type; Multiple arguments to be retrieved with func_get_args().
	 * @return array Printable Error Message
	 */
	private function error( $type = 'unknown' ): array {
		$arguments = func_get_args();
		array_shift( $arguments );

		$message = wfMessage( 'embedvideo-error-' . $type, $arguments )->escaped();

		return [
			"<div class='errorbox'>{$message}</div>",
			'noparse' => true,
			'isHTML' => true
		];
	}

	/**
	 * Initializes the service and checks for errors
	 * @throws InvalidArgumentException
	 */
	private function init(): void {
		[
			'service' => $service,
			'id' => $id,
			'dimensions' => $dimensions,
			'alignment' => $alignment,
			'description' => $description,
			'container' => $container,
			'urlArgs' => $urlArgs,
			'width' => $width,
			'height' => $height,
			'vAlignment' => $vAlignment,
			'cover' => $cover,
			'poster' => $poster,
			'title' => $title,
		] = $this->args;

		$rpl = fn( $input ) => preg_replace( '/[a-z]/i', '', (string)$input );

		// Height only
		if ( !empty( $dimensions ) && strtolower( $dimensions )[0] === 'x' ) {
			$height = $dimensions;
		// Width and height
		} elseif ( preg_match( '/[0-9]+(?:px)?x[0-9]+(?:px)?/i', $dimensions ?? '' ) ) {
			[ $width, $height ] = array_map( fn( $dim ) => $rpl( $dim ), array_filter( explode( 'x', $dimensions ) ) );
		} elseif ( is_numeric( $rpl( $dimensions ) ) ) {
			$width = $dimensions;
		}

		if ( !$service || !$id ) {
			throw new InvalidArgumentException( $this->error( 'missingparams', $service, $id )[0] );
		}

		$this->service = EmbedServiceFactory::newFromName( $service, $id );

		// Let the service automatically handle bad dimensional values.
		$this->service->setWidth( $rpl( (string)$width ) );
		$this->service->setHeight( $rpl( (string)$height ) );

		if ( $this->config->get( 'EmbedVideoRequireConsent' ) === true ) {
			$this->service->setUrlArgs( $this->service->getAutoplayParameter() );
		}

		if ( !$this->service->setUrlArgs( $urlArgs ) ) {
			throw new InvalidArgumentException( $this->error( 'urlargs', $service, $urlArgs )[0] );
		}

		if ( $this->parser !== null ) {
			$this->setDescription( $description, $this->parser );
		} else {
			$this->setDescriptionNoParse( $description );
		}

		if ( !$this->setContainer( $container ) ) {
			throw new InvalidArgumentException( $this->error( 'container', $container )[0] );
		}

		if ( !$this->setAlignment( $alignment ) ) {
			throw new InvalidArgumentException( $this->error( 'alignment', $alignment )[0] );
		}

		if ( !$this->setVerticalAlignment( $vAlignment ) ) {
			throw new InvalidArgumentException( $this->error( 'valignment', $vAlignment )[0] );
		}

		if ( !empty( $cover ?? $poster ?? '' ) ) {
			try {
				$this->service->setLocalThumb( $cover ?? $poster );
			} catch ( InvalidArgumentException | RuntimeException $e ) {
				wfLogWarning( $e->getMessage() );
			}
		}

		$this->service->setTitle( $title );
	}

	/**
	 * Set the description.
	 *
	 * @private
	 * @param string $description Description
	 * @param Parser $parser Mediawiki Parser object
	 * @return void
	 */
	private function setDescription( string $description, Parser $parser ): void {
		$this->description = ( !$description ? false : $parser->recursiveTagParse( $description ) );
	}

	/**
	 * Set the description without using the parser
	 *
	 * @param string $description Description
	 */
	private function setDescriptionNoParse( $description ): void {
		$this->description = ( !$description ? false : $description );
	}

	/**
	 * Set the container type.
	 *
	 * @private
	 * @param string $container Container
	 * @return bool Success
	 */
	private function setContainer( $container ): bool {
		if ( !empty( $container ) && ( $container === 'frame' ) ) {
			$this->container = $container;
		} elseif ( !empty( $container ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Set the align parameter.
	 *
	 * @private
	 * @param string $alignment Alignment Parameter
	 * @return bool Valid
	 */
	private function setAlignment( $alignment ): bool {
		if ( !empty( $alignment ) && in_array( $alignment, [ 'left', 'right', 'center', 'none' ], true ) ) {
			$this->alignment = $alignment;
		} elseif ( !empty( $alignment ) && $alignment !== 'inline' ) {
			// 'inline' is removed since v3.2.3, but kept for backwards compatibility
			// TODO: Remove check after some releases
			return false;
		}

		return true;
	}

	/**
	 * Set the align parameter.
	 *
	 * @private
	 * @param string $vAlignment Alignment Parameter
	 * @return bool Valid
	 */
	private function setVerticalAlignment( $vAlignment ): bool {
		if (
			!empty( $vAlignment )
			&& in_array( $vAlignment, [
				'middle', 'baseline', 'sub', 'super', 'top', 'text-top', 'bottom', 'text-bottom'
			], true )
		) {
			$this->vAlignment = $vAlignment;
		} elseif ( !empty( $vAlignment ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Makes the config used by the HTML Formatter
	 *
	 * @see EmbedHtmlFormatter::toHtml()
	 *
	 * @param AbstractEmbedService $embedService The service in question
	 * @return array
	 */
	private function makeHtmlFormatConfig( $embedService ): array {
		$classString = implode( ' ', array_filter( [
			'embedvideo',
			// This should probably be added as a RL variable
			$this->config->get( 'EmbedVideoFetchExternalThumbnails' ) ? '' : 'no-fetch',
			strip_tags( $this->args['class'] ?? '' ),
		] ) );
		$serviceString = $embedService::getServiceName();
		$styleString = '';

		if ( $this->container === 'frame' ) {
			$classString .= ' frame';
		}

		if ( $this->alignment !== false ) {
			$classString .= sprintf( ' mw-halign-%s', $this->alignment );
		}

		if ( $this->vAlignment !== false ) {
			$classString .= sprintf( ' mw-valign-%s', $this->vAlignment );
		}

		return [
			'class' => $classString,
			'style' => $styleString,
			'service' => $serviceString,
			'autoresize' => $this->args['autoresize'] === true,
			// phpcs:ignore Generic.Files.LineLength.TooLong
			'withConsent' => !( $this->service instanceof OEmbedServiceInterface ) && $this->config->get( 'EmbedVideoRequireConsent' ),
			'description' => $this->description,
		];
	}

	/**
	 * Adds all relevant modules if the parser is present
	 */
	private function addModules(): void {
		if ( $this->parser === null || $this->service instanceof OEmbedServiceInterface ) {
			return;
		}

		$out = $this->parser->getOutput();

		// Add CSP if needed
		$defaultSrcArr = $this->service->getCSPUrls();
		if ( !empty( $defaultSrcArr ) ) {
			foreach ( $defaultSrcArr as $defaultSrc ) {
				$out->addExtraCSPDefaultSrc( $defaultSrc );
			}
		}

		$out->addModuleStyles( [ 'ext.embedVideo.styles' ] );

		if ( MediaWikiServices::getInstance()->getMainConfig()->get( 'EmbedVideoRequireConsent' ) === true ) {
			$out->addModules( [
				'ext.embedVideo.consent',
			] );

			$serviceAttributes = $this->service->getIframeAttributes();
			$serviceAttributes['width'] = $this->service->getDefaultWidth();
			$serviceAttributes['height'] = $this->service->getDefaultHeight();

			$this->parser->getOutput()->setJsConfigVar(
				sprintf( 'ev-%s-config', $this->service::getServiceName() ),
				$serviceAttributes
			);
		}
	}
}
