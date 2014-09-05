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
     * Sets up this extension's parser functions.
     *
     * @access	public
     * @param	object	Parser object passed as a reference.
     * @return	boolean	true
     */
    static public function onParserFirstCallInit(Parser &$parser) {
		$parser->setFunctionHook("ev", "EmbedVideoHooks::parseEV");
		$parser->setFunctionHook("evp", "EmbedVideoHooks::parseEVP");

		return true;
	}
	
	/**
	 * Adapter to call the new style tag.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @return	string	Error Message
	 */
	static public function parseEVP($parser) {
		wfDeprecated(__METHOD__, '2.0', 'EmbedVideo');
		return self::error('evp_deprecated');
	}
	
	/**
	 * Embeds video of the chosen service
	 *
	 * @access	public
	 * @param	object	Parser
	 * @param	string	[Optional] Which online service has the video.
	 * @param	string	[Optional] Identifier Code or URL for the video on the service.
	 * @param	string	[Optional] Width of video
	 * @param	string	[Optional] Description to show
	 * @param	string	[Optional] Alignment of the video
	 * @param	string	[Optional] Container to use, frame or thumb.
	 * @return	string	Encoded representation of input params (to be processed later)
	 */
	static public function parseEV($parser, $service = null, $id = null, $width = null, $alignment = null, $description = null, $container = null) {
		$service		= trim($service);
		$id				= trim($id);
		$alignment		= trim($alignment);
		$description	= trim($description);

		/************************************/
		/* Error Checking                   */
		/************************************/
		if (!$service || !$id) {
			return self::error('missingparams', $service, $id);
		}

		self::$service = \EmbedVideo\VideoService::newFromName($service);
		if (!self::$service) {
			return self::error('service', $service);
		}

		//Let the service automatically handle bad width values.
		self::$service->setWidth($width);

		//The parser tag currently does not support specifying the height, but the coding functionality is available.
		//self::$service->setHeight($height);

		//If the service has an ID pattern specified, verify the id number.
		if (!self::$service->setVideoID($id)) {
			return self::error('id', $service, $id);
		}

		self::setDescription($description, $parser);

		if (!self::setContainer($container)) {
			return self::error('container', $container);
		}

		if (!self::setAlignment($alignment)) {
			return self::error('alignment', $alignment);
		}

		/************************************/
		/* HMTL Generation                  */
		/************************************/
		$html = self::$service->getHtml();
		if (!$html) {
			return self::error('unknown', $service);
		}

		$html = self::generateWrapperHTML($html);

		return array(
			$html,
			'noparse' => true,
			'isHTML' => true
		);
	}
	
	/**
	 * Generate the HTML necessary to embed the video with the given alignment
	 * and text description
	 *
	 * @access	private
	 * @param	string	[Optional] Horizontal Alignment
	 * @param	string	[Optional] Description
	 * @return string
	 */
	static private function generateWrapperHTML($html, $description = null) {
		if (self::getContainer() == 'frame') {
			$html = "<div class='thumb".(self::getAlignment() !== false ? " t".self::getAlignment() : null)."'><div class='thumbinner' style='width: {$width}px;'>{$html}".(self::getDescription() !== false ? "<div class='thumbcaption'>".self::getDescription()."</div>" : null)."</div></div>";
		} elseif (self::getContainer() == 'thumb') {
			$html = "<div class='thumb".(self::getAlignment() !== false ? " t".self::getAlignment() : null)."'><div class='thumbinner' style='width: {$width}px;'>{$html}".(self::getDescription() !== false ? "<div class='thumbcaption'>".self::getDescription()."</div>" : null)."</div></div>";
		} else {
			$html = "<div class='embedvideo ".(self::getAlignment() !== false ? " ev_".self::getAlignment() : null)."'>{$html}".(self::getDescription() !== false ? "<div class='thumbcaption'>".self::getDescription()."</div>" : null)."</div>";
		}
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
	static private function setAlignment($alignment) {
		if (!empty($alignment) && ($alignment == 'left' || $alignment == 'right')) {
			self::$alignment = $alignment;
		} elseif (!empty($alignment)) {
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
	static private function setDescription($description, \Parser $parser) {
		self::$description = (!$description ? false : $parser->recursiveTagParse($description));
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
	static private function setContainer($container) {
		if (!empty($container) && ($container == 'thumb' || $container == 'frame')) {
			self::$container = $container;
		} elseif (!empty($container)) {
			return false;
		}
		return true;
	}

	/**
	 * Error Handler
	 *
	 * @access	private
	 * @param	string	[Optional] Error Type
	 * @param	mixed	[...] Multiple arguments to be retrieved with func_get_args().
	 * @return	string	Printable Error Message
	 */
	static private function error($type = 'unknown') {
		$arguments = func_get_args();
		array_shift($arguments);

		$message = wfMessage('error_embedvideo_'.$type, $arguments)->escaped();

		return "<div class='errorbox'>{$message}</div>";
	}
}
?>