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
     * Hooks Initialized
     *
     * @var		boolean
     */
	static private $initialized = false;

    /**
     * Video Services
     *
     * @var		array
     */
	static private $services = array();

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
	 * @param	string	[Optional] Identifier of the chosen service
	 * @param	string	[Optional] Width of video
	 * @param	string	[Optional] Description to show
	 * @param	string	[Optional] Alignment of the video
	 * @return	string	Encoded representation of input params (to be processed later)
	 */
	static public function parseEV($parser, $service = null, $id = null, $width = null, $alignment = null, $description = null) {
		//Initialize things once
		if (!self::$initialized) {
			self::verifyWidthMinAndMax();
			self::$initialized = true;
		}

		$service = trim($service);
		$id      = trim($id);

		/************************************/
		/* Error Checking                   */
		/************************************/
		if (!$service || !$id) {
			return self::error('missingparams', $service, $id);
		}

		$serviceEntry = self::getServiceEntry($service);
		if (!$serviceEntry) {
			return self::error('service', $service);
		}

		if (!self::sanitizeWidth($serviceEntry, $width)) {
			return self::error('width', $width);
		}
		$height = self::getHeight($serviceEntry, $width);

		if ($alignment !== null && !self::validateAlignment($alignment)) {
			return self::error('alignment', $alignment);
		}

		if ($description) {
			$description = $parser->recursiveTagParse($description);
		}

		//If the service has an ID pattern specified, verify the id number.
		if (!self::verifyID($serviceEntry, $id)) {
			return self::error('id', $service, $id);
		}

		//Special Yandex Handler
		$url = null;
		// If service is Yandex -> use own parser
		if ($service == 'yandex' || $service == 'yandexvideo') {
			$url = self::getYandex($id);
			$url = htmlspecialchars_decode($url);
		}

		/************************************/
		/* HMTL Generation                  */
		/************************************/
		if (array_key_exists('embed', $serviceEntry)) {
			//Handled by a premade HTML block.
			if ($service == 'screen9') {
				$html = self::parseScreen9Id($id, $width, $height);
				if ($html == null) {
					return self::error('screen9id');
				}
			} else {
				$html = wfMsgReplaceArgs(
					$serviceEntry['embed'],
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
				$serviceEntry['url'],
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
	 * Get the entry for the specified service by service name.
	 *
	 * @access	private
	 * @param	string	Service string.
	 * @return	array	Array of service definitions.
	 */
	static private function getServiceEntry($service) {
		return self::$services[$service];
	}

	/**
	 * Set the list of services.
	 *
	 * @access	public
	 * @param	array	Array of services.
	 * @return	void
	 */
	static public function setServices($services) {
		self::$services = $services;
	}

	/**
	 * Get the width. If there is no width specified, try to find a default
	 * width value for the service. If that isn't set, default to 425.
	 * If a width value is provided, verify that it is numerical and that it
	 * falls between the specified min and max size values. Return true if
	 * the width is suitable, false otherwise.
	 *
	 * @param	string $service
	 *
	 * @return mixed
	 */
	static private function sanitizeWidth($serviceEntry, &$width) {
		global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;

		if ($width === null || $width == '*' || $width == '') {
			if (array_key_exists('default_width', $serviceEntry)) {
				$width = $serviceEntry['default_width'];
			} else {
				$width = 425;
			}
			return true;
		}
		if (!is_numeric($width)) {
			return false;
		}
		return $width >= $wgEmbedVideoMinWidth && $width <= $wgEmbedVideoMaxWidth;
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
	 * Calculate the height from the given width. The default ratio is 450/350,
	 * but that may be overridden for some sites.
	 *
	 * @param	integer $entry
	 * @param	integer $width
	 *
	 * @return int
	 */
	static private function getHeight($entry, $width) {
		$ratio = 16 / 9;
		if (isset($entry['default_ratio'])) {
			$ratio = $entry['default_ratio'];
		}
		return round($width / $ratio);
	}

	/**
	 * Verify the id number of the video, if a pattern is provided.
	 *
	 * @param	string $entry
	 * @param	string $id
	 *
	 * @return bool
	 */
	static private function verifyID($entry, $id) {
		$idhtml = htmlspecialchars($id);
		//$idpattern = (isset($entry['id_pattern']) ? $entry['id_pattern'] : '%[^A-Za-z0-9_\\-]%');
		//if ($idhtml == null || preg_match($idpattern, $idhtml)) {
		return ($idhtml != null);
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

		switch (strtolower($type)) {
			case 'alignment':
				$message = wfMessage('error_embedvideo_alignment', $arguments[0])->escaped();
				break;
			case 'service':
				$message = wfMessage('error_embedvideo_service', $arguments[0])->escaped();
				break;
			case 'id':
				$message = wfMessage('error_embedvideo_id', $arguments[0], $arguments[1])->escaped();
				break;
			case 'missingparams':
				$message = wfMessage('error_embedvideo_missingparams', $arguments[0], $arguments[1])->escaped();
				break;
			case 'width':
				$message = wfMessage('error_embedvideo_width', $arguments[0])->escaped();
				break;
			case 'height':
				$message = wfMessage('error_embedvideo_height', $arguments[0])->escaped();
				break;
			case 'screen9id':
				$message = wfMessage('error_embedvideo_screen9id', $arguments[0])->escaped();
				break;
			default:
			case 'unknown':
				$message = wfMessage('error_embedvideo_unknown')->escaped();
				break;
		}

		return "<div class='errorbox'>{$message}</div>";
	}

	/**
	 * Verify that the min and max values for width are sane.
	 *
	 * @return void
	 */
	static private function verifyWidthMinAndMax() {
		global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
		if (!is_numeric($wgEmbedVideoMinWidth) || $wgEmbedVideoMinWidth < 100) {
			$wgEmbedVideoMinWidth = 100;
		}
		if (!is_numeric($wgEmbedVideoMaxWidth) || $wgEmbedVideoMaxWidth > 1024) {
			$wgEmbedVideoMaxWidth = 1024;
		}
	}

	/**
	 * Get Yandex information
	 *
	 * @access	public
	 * @param	integer
	 * @return	string
	 */
	static private function getYandex($id) {
		$id = intval($id);
		$return = self::curlGet("http://video.yandex.ru/oembed.xml?url=http://video.yandex.ru/users/{$id}");
		if ($return === false) {
			return false;
		}
		$start = strpos($return, '<html>') + 6;
		$end   = strpos($return, '</html>');
		$url   = substr($return, $start, $end - $start);
		return $url;
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