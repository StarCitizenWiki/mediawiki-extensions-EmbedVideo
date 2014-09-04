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
     * Sets up this extension's parser functions.
     *
     * @access	public
     * @param	object	Parser object passed as a reference.
     * @return	boolean	true
     */
    static public function onParserFirstCallInit(Parser &$parser) {
		global $wgVersion;

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
		wfDeprecated(__METHOD__, '1.23');

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
		$service = trim($service);
		$id      = trim($id);

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

		self::$service->setDescription($description, $parser);

		//If the service has an ID pattern specified, verify the id number.
		$id = self::$service->setVideoID($id);
		if (!$id) {
			return self::error('id', $service, $id);
		}

		if ($alignment !== null && !self::validateAlignment($alignment)) {
			return self::error('alignment', $alignment);
		}

		/************************************/
		/* HMTL Generation                  */
		/************************************/
		if (array_key_exists('embed', self::$service)) {
			//Handled by a premade HTML block.
			if ($service == 'screen9') {
				$html = self::parseScreen9Id($id, $width, $height);
				if ($html == null) {
					return self::error('screen9id');
				}
			} else {
				$html = wfMsgReplaceArgs(
					self::$service['embed'],
					array(
						$id,
						$width,
						$height,
						$url
					)
				);
			}
		} else {
			//Build URL and output embedded flash object.
			$url = wfMsgReplaceArgs(
				self::$service['url'],
				array(
					$id,
					$width,
					$height
				)
			);
			$html = self::generateEmbedHTML($url, $width, $height);
		}

		if (self::getAlignmentClass($alignment) !== false || $hasDescription) {
			$html = self::generateWrapperHTML($html, $alignment, $description);
		}

		return array(
			$html,
			'noparse' => true,
			'isHTML' => true
		);
	}
	
	/**
	 * Generate HTML to embed a video from a standard embed block.
	 *
	 * @access	private
	 * @param	string	URL
	 * @param	integer	Width
	 * @param	integer	Height
	 * @return	string
	 */
	static private function generateEmbedHTML($url, $width, $height) {
		$html = "<object width='{$width}' height='{$height}'><param name='movie' value='{$url}'></param><param name='wmode' value='transparent'></param><embed src='{$url}' type='application/x-shockwave-flash' wmode='transparent' width='{$width}' height='{$height}'></embed></object>";
		return $html;
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

	/**
	 * Parse Screen9 Identification code.
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	static private function parseScreen9Id($id, $width, $height) {
		$parser = new Screen9IdParser();
		
		if (!$parser->parse($id)) {
			return null;
		}
		
		$parser->setWidth($width);
		
		$parser->setHeight($height);
		
		return $parser->toString();
	}

	/**
	 * Perform a Curl GET request.
	 *
	 * @access	private
	 * @param	string URL
	 * @return	mixed
	 */
	static private function curlGet($location) {
		global $wgServer;

		$ch = curl_init();

		$timeout = 10;
		$useragent = "EmbedVideo/1.0/".$wgServer;
		$dateTime = gmdate("D, d M Y H:i:s", time())." GMT";
		$headers = ['Date: '.$dateTime];

		$curlOptions = [
			CURLOPT_TIMEOUT			=> $timeout,
			CURLOPT_USERAGENT		=> $useragent,
			CURLOPT_URL				=> $location,
			CURLOPT_CONNECTTIMEOUT	=> $timeout,
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_MAXREDIRS		=> 10,
			CURLOPT_COOKIEFILE		=> sys_get_temp_dir().DIRECTORY_SEPARATOR.'curlget',
			CURLOPT_COOKIEJAR		=> sys_get_temp_dir().DIRECTORY_SEPARATOR.'curlget',
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HTTPHEADER		=> $headers
		];

		curl_setopt_array($ch, $curlOptions);

		$page = curl_exec($ch);

		$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($responseCode == 503 || $responseCode == 404) {
			return false;
		}

		return $page;
	}
}
?>