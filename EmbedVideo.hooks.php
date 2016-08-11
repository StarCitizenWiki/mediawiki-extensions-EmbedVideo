<?php
/**
 * EmbedVideo
 * EmbedVideo Hooks
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/

class EmbedVideoHooks {
	/**
	 * Temporary storage for the current service object.
	 *
	 * @var		object
	 */
	static private $service;

	/**
	 * Description Parameter
	 *
	 * @var		string
	 */
	static private $description = false;

	/**
	 * Alignment Parameter
	 *
	 * @var		string
	 */
	static private $alignment = false;

	/**
	 * Container Parameter
	 *
	 * @var		string
	 */
	static private $container = false;

	/**
	 * Valid Arguments for the parseEV function hook.
	 *
	 * @var		string
	 */
	static private $validArguments = [
		'service'		=> null,
		'id'			=> null,
		'dimensions'	=> null,
		'alignment'		=> null,
		'description'	=> null,
		'container'		=> null,
		'urlargs'		=> null,
		'autoresize'	=> null
	];

	/**
	 * Hook to setup defaults.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function onExtension() {
		global $wgEmbedVideoDefaultWidth, $wgMediaHandlers, $wgFileExtensions;

		if ( !isset($wgEmbedVideoDefaultWidth) && (isset($_SERVER['HTTP_X_MOBILE']) && $_SERVER['HTTP_X_MOBILE'] == 'true') && $_COOKIE['stopMobileRedirect'] != 1 ) {
			//Set a smaller default width when in mobile view.
			$wgEmbedVideoDefaultWidth = 320;
		}

		$wgMediaHandlers['application/ogg']		= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/flac']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/ogg']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/mpeg']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/mp4']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/wav']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/webm']			= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['audio/x-flac']		= 'EmbedVideo\AudioHandler';
		$wgMediaHandlers['video/mp4']			= 'EmbedVideo\VideoHandler';
		$wgMediaHandlers['video/ogg']			= 'EmbedVideo\VideoHandler';
		$wgMediaHandlers['video/quicktime']		= 'EmbedVideo\VideoHandler';
		$wgMediaHandlers['video/webm']			= 'EmbedVideo\VideoHandler';
		$wgMediaHandlers['video/x-matroska']	= 'EmbedVideo\VideoHandler';

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

	/**
	 * Sets up this extension's parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( "ev", "EmbedVideoHooks::parseEV" );
		$parser->setFunctionHook( "evt", "EmbedVideoHooks::parseEVT" );
		$parser->setFunctionHook( "evp", "EmbedVideoHooks::parseEVP" );

		$parser->setHook( "embedvideo", "EmbedVideoHooks::parseEVTag" );

		return true;
	}

	/**
	 * Adapter to call the new style tag.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @return	string	Error Message
	 */
	static public function parseEVP( $parser ) {
		wfDeprecated( __METHOD__, '2.0', 'EmbedVideo' );
		return self::error( 'evp_deprecated' );
	}

	/**
	 * Adapter to call the EV parser tag with template like calls.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @return	string	Error Message
	 */
	static public function parseEVT( $parser ) {
		$arguments = func_get_args();
		array_shift( $arguments );

		foreach ( $arguments as $argumentPair ) {
			$argumentPair = trim( $argumentPair );
			if ( !strpos( $argumentPair, '=' ) ) {
				continue;
			}

			list( $key, $value ) = explode( '=', $argumentPair, 2 );

			if (!array_key_exists($key, self::$validArguments)) {
				continue;
			}
			$args[$key] = $value;
		}

		$args = array_merge( self::$validArguments, $args );

		return self::parseEV(
			$parser,
			$args['service'],
			$args['id'],
			$args['dimensions'],
			$args['alignment'],
			$args['description'],
			$args['container'],
			$args['urlargs'],
			$args['autoresize']
		);
	}

	/**
	 * Adapter to call the parser hook.
	 *
	 * @access	public
	 * @param	string	Raw User Input
	 * @param	array	Arguments on the tag.
	 * @param	object	Parser object.
	 * @param	object	PPFrame object.
	 * @return	string	Error Message
	 */
	static public function parseEVTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		$args = array_merge( self::$validArguments, $args );

		return self::parseEV(
			$parser,
			$args['service'],
			$input,
			$args['dimensions'],
			$args['alignment'],
			$args['description'],
			$args['container'],
			$args['urlargs'],
			$args['autoresize']
		);
	}

	/**
	 * Embeds a video of the chosen service.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @param	string	[Optional] Which online service has the video.
	 * @param	string	[Optional] Identifier Code or URL for the video on the service.
	 * @param	string	[Optional] Dimensions of video
	 * @param	string	[Optional] Description to show
	 * @param	string	[Optional] Alignment of the video
	 * @param	string	[Optional] Container to use.(Frame is currently the only option.)
	 * @param	string	[Optional] Extra URL Arguments
	 * @param 	string	[Optional] Automatically Resize video that will break its parent container.
	 * @return	string	Encoded representation of input params (to be processed later)
	 */
	static public function parseEV( $parser, $service = null, $id = null, $dimensions = null, $alignment = null, $description = null, $container = null, $urlArgs = null, $autoResize = null ) {
		self::resetParameters();

		$service		= trim( $service );
		$id				= trim( $id );
		$alignment		= trim( $alignment );
		$description	= trim( $description );
		$dimensions		= trim( $dimensions );
		$urlArgs		= trim( $urlArgs );
		$width			= null;
		$height			= null;
		$autoResize		= ( isset( $autoResize ) && strtolower( trim( $autoResize ) ) == "false" ) ? false : true;

		// I am not using $parser->parseWidthParam() since it can not handle height only.  Example: x100
		if ( stristr( $dimensions, 'x' ) ) {
			$dimensions = strtolower( $dimensions );
			list( $width, $height ) = explode( 'x', $dimensions );
		} elseif ( is_numeric( $dimensions ) ) {
			$width = $dimensions;
		}

		/************************************/
		/* Error Checking                   */
		/************************************/
		if ( !$service || !$id ) {
			return self::error( 'missingparams', $service, $id );
		}

		self::$service = \EmbedVideo\VideoService::newFromName( $service );
		if ( !self::$service ) {
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

		self::setDescription( $description, $parser );

		if ( !self::setContainer( $container ) ) {
			return self::error( 'container', $container );
		}

		if ( !self::setAlignment( $alignment ) ) {
			return self::error( 'alignment', $alignment );
		}

		/************************************/
		/* HMTL Generation                  */
		/************************************/
		$html = self::$service->getHtml();
		if ( !$html ) {
			return self::error( 'unknown', $service );
		}

		if ($autoResize) {
			$html = self::generateWrapperHTML( $html, null, "autoResize" );
		} else {
			$html = self::generateWrapperHTML( $html );
		}

		$parser->getOutput()->addModules( ['ext.embedVideo'] );

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
	 * @access	private
	 * @param	string	[Optional] Horizontal Alignment
	 * @param	string	[Optional] Description
	 * @param	string  [Optional] Additional Classes to add to the wrapper
	 * @return string
	 */
	static private function generateWrapperHTML( $html, $description = null, $addClass = null ) {
		$classString = "embedvideo";
		$styleString = "";
		$innerClassString = "embedvideowrap";

		if ( self::getContainer() == 'frame' ) {
			$classString .= " thumb";
			$innerClassString .= " thumbinner";
		}

		if (self::getAlignment() !== false) {
			$classString .= " ev_" . self::getAlignment();
			$styleString .= " width: " . ( self::$service->getWidth() + 6 ) . "px;'";
		}

		if ($addClass) {
			$classString .= " " . $addClass;
		}

		$html = "<div class='" . $classString . "' style='" . $styleString . "'><div class='" . $innerClassString . "' style='width: " . self::$service->getWidth() . "px;'>{$html}" . ( self::getDescription() !== false ? "<div class='thumbcaption'>" . self::getDescription() . "</div>" : null ) . "</div></div>";

		return $html;
	}

	/**
	 * Return the alignment parameter.
	 *
	 * @access	public
	 * @return	mixed	Alignment or false for not set.
	 */
	static private function getAlignment() {
		return self::$alignment;
	}

	/**
	 * Set the align parameter.
	 *
	 * @access	private
	 * @param	string	Alignment Parameter
	 * @return	boolean	Valid
	 */
	static private function setAlignment( $alignment ) {
		if ( !empty( $alignment ) && ( $alignment == 'left' || $alignment == 'right' || $alignment == 'center' || $alignment == 'inline' ) ) {
			self::$alignment = $alignment;
		} elseif ( !empty( $alignment ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Return description text.
	 *
	 * @access	private
	 * @return	mixed	String description or false for not set.
	 */
	static private function getDescription() {
		return self::$description;
	}

	/**
	 * Set the description.
	 *
	 * @access	private
	 * @param	string	Description
	 * @param	object	Mediawiki Parser object
	 * @return	void
	 */
	static private function setDescription( $description, \Parser $parser ) {
		self::$description = ( !$description ? false : $parser->recursiveTagParse( $description ) );
	}

	/**
	 * Return container type.
	 *
	 * @access	private
	 * @return	mixed	String container type or false for not set.
	 */
	static private function getContainer() {
		return self::$container;
	}

	/**
	 * Set the container type.
	 *
	 * @access	private
	 * @param	string	Container
	 * @return	boolean	Success
	 */
	static private function setContainer( $container ) {
		if ( !empty( $container ) && ( $container == 'frame' ) ) {
			self::$container = $container;
		} elseif ( !empty( $container ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Reset parameters between parses.
	 *
	 * @access	private
	 * @return	void
	 */
	static private function resetParameters() {
		self::$description	= false;
		self::$alignment	= false;
		self::$container	= false;
	}

	/**
	 * Error Handler
	 *
	 * @access	private
	 * @param	string	[Optional] Error Type
	 * @param	mixed	[...] Multiple arguments to be retrieved with func_get_args().
	 * @return	string	Printable Error Message
	 */
	static private function error( $type = 'unknown' ) {
		$arguments = func_get_args();
		array_shift( $arguments );

		$message = wfMessage( 'error_embedvideo_' . $type, $arguments )->escaped();

		return "<div class='errorbox'>{$message}</div>";
	}
}