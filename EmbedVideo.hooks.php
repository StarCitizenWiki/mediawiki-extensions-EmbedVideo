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
	 * Description for the current video being processed.
	 *
	 * @var		object
	 */
	static private $description = false;

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
	 * @param	string	[Optional] Which online service has the video.
	 * @param	string	[Optional] Identifier of the chosen service
	 * @param	string	[Optional] Description to show
	 * @param	string	[Optional] Alignment of the video
	 * @param	string	[Optional] Width of video
	 * @return	string	Output from self::parseEV
	 */
	static public function parseEVP($parser, $service = null, $id = null, $description = null, $alignment = null, $width = null) {
		return self::parseEV($parser, $service, $id, $width, $alignment, $description);
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
	 * @return	string	Encoded representation of input params (to be processed later)
	 */
	static public function parseEV($parser, $service = null, $id = null, $width = null, $alignment = null, $description = null) {
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

		if (!empty($alignment) && !self::validateAlignment($alignment)) {
			return self::error('alignment', $alignment);
		}

		/************************************/
		/* HMTL Generation                  */
		/************************************/
		$html = self::$service->getHtml();
		if (!$html) {
			return self::error('unknown', $service);
		}

		if (self::getAlignmentClass($alignment) !== false || self::getDescription() !== false) {
			$html = self::generateWrapperHTML($html, $alignment, self::getDescription());
		}

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
	static private function generateWrapperHTML($html, $alignment = null, $description = null) {
		$alignClass = self::getAlignmentClass($alignment);

		$html = "<div class='thumb".($alignClass ? " ".$alignClass : null)."'><div class='thumbinner' style='width: {$width}px;'>{$html}".($description ? "<div class='thumbcaption'>{$description}</div>" : null)."</div></div>";
		return $html;
	}

	/**
	 * Validate the align parameter.
	 *
	 * @access	private
	 * @param	string	Alignment Parameter
	 * @return	boolean	Valid
	 */
	static private function validateAlignment($alignment) {
		return ($alignment == 'left' || $alignment == 'right' || $alignment == 'none');
	}

	/**
	 * Return the standard Mediawiki alignment class for the provided alignment parameter.
	 *
	 * @access	public
	 * @return	mixed
	 */
	static private function getAlignmentClass($alignment) {
		if ($alignment == 'left' || $alignment == 'right') {
			return 't'.$alignment;
		}

		return false;
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