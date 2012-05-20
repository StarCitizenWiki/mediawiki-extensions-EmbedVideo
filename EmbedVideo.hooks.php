<?php
/**
 * Wrapper class for encapsulating EmbedVideo related parser methods
 */
abstract class EmbedVideo {

	protected static $initialized = false;

	/**
	 * Sets up parser functions.
	 */
	public static function setup() {
		// Setup parser hooks. ev is the primary hook, evp is supported for
		// legacy purposes
		global $wgVersion;
		$prefix = version_compare($wgVersion, '1.7', '<') ? '#' : '';
		self::addMagicWord($prefix, "ev", "EmbedVideo::parserFunction_ev");
		self::addMagicWord($prefix, "evp", "EmbedVideo::parserFunction_evp");
		return true;
	}

	private static function addMagicWord($prefix, $word, $function) {
		global $wgParser;
		$wgParser->setFunctionHook($prefix . $word, $function);
	}

	/**
	 * Adds magic words for parser functions.
	 * @param array  $magicWords
	 * @param string $langCode
	 *
	 * @return bool Always true
	 */
	public static function parserFunctionMagic(&$magicWords, $langCode='en') {
		$magicWords['evp'] = array(0, 'evp');
		$magicWords['ev']  = array(0, 'ev');
		return true;
	}

	/**
	 * Embeds video of the chosen service, legacy support for 'evp' version of
	 * the tag
	 * @param Parser $parser Instance of running Parser.
	 * @param String $service Which online service has the video.
	 * @param String $id Identifier of the chosen service
	 * @param String $width Width of video (optional)
	 * @return String Encoded representation of input params (to be processed later)
	 */
	public static function parserFunction_evp($parser, $service = null, $id = null, $desc = null, $align = null, $width = null) {
		return self::parserFunction_ev($parser, $service, $id, $width, $align, $desc);
	}

	/**
	 * Embeds video of the chosen service
	 * @param Parser $parser Instance of running Parser.
	 * @param String $service Which online service has the video.
	 * @param String $id Identifier of the chosen service
	 * @param String $width Width of video (optional)
	 * @param String $desc description to show (optional, unused)
	 * @param String $align alignment of the video (optional, unused)
	 * @return String Encoded representation of input params (to be processed later)
	 */
	public static function parserFunction_ev($parser, $service = null, $id = null, $width = null, $align = null, $desc = null) {
		global $wgScriptPath;

		// Initialize things once
		if (!self::$initialized) {
			self::VerifyWidthMinAndMax();
			// Add system messages
			wfLoadExtensionMessages('embedvideo');
			$parser->disableCache();
			self::$initialized = true;
		}

		// Get the name of the host
		if ($service === null || $id === null) {
			return self::errMissingParams($service, $id);
		}

		$service = trim($service);
		$id = trim($id);
		$desc = $parser->recursiveTagParse($desc);

		$entry = self::getServiceEntry($service);
		if (!$entry) {
			return self::errBadService($service);
		}

		if (!self::sanitizeWidth($entry, $width)) {
			return self::errBadWidth($width);
		}
		$height = self::getHeight($entry, $width);
		$hasalign = ($align !== null);
		if ($hasalign) {
			$desc = self::getDescriptionMarkup($desc);
		}

		// If the service has an ID pattern specified, verify the id number
		if (!self::verifyID($entry, $id)) {
			return self::errBadID($service, $id);
		}
		$url = null;
		// If service is Yandex -> use own parser
		if ($service == 'yandex' || $service == 'yandexvideo') {
		$url = self::getYandex($id);
		$url = htmlspecialchars_decode($url);
		}
		// if the service has it's own custom extern declaration, use that instead
		if (array_key_exists ('extern', $entry) && ($clause = $entry['extern']) != NULL) {
			$clause = wfMsgReplaceArgs($clause, array($wgScriptPath, $id, $width, $height, $url));
			if ($hasalign) {
				$clause = self::generateAlignExternClause($clause, $align, $desc, $width, $height);
			}
			return array($clause, 'noparse' => true, 'isHTML' => true);
		}

		// Build URL and output embedded flash object
		$url = wfMsgReplaceArgs($entry['url'], array($id, $width, $height));
		$clause = "";
		// If service is RuTube -> use own parser
		if ($service == 'rutube'){
		$url = self::getRuTube($id);
		}
		if ($hasalign) {
			$clause = self::generateAlignClause($url, $width, $height, $align, $desc);
		}
		else {
			$clause = self::generateNormalClause($url, $width, $height);
		}
		return array($clause, 'noparse' => true, 'isHTML' => true);
	}

	/**
	 * Return the HTML necessary to embed the video normally.
	 *
	 * @param string $url
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return string
	 */
	private static function generateNormalClause($url, $width, $height) {
		$clause = "<object width=\"{$width}\" height=\"{$height}\">" .
			"<param name=\"movie\" value=\"{$url}\"></param>" .
			"<param name=\"wmode\" value=\"transparent\"></param>" .
			"<embed src=\"{$url}\" type=\"application/x-shockwave-flash\"" .
			" wmode=\"transparent\" width=\"{$width}\" height=\"{$height}\">" .
			"</embed></object>";
		return $clause;
	}

	/**
	 * The HTML necessary to embed the video with a custom embedding clause,
	 * specified align and description text
	 *
	 * @param string $clause
	 * @param string $align
	 * @param string $desc
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return string
	 */
	private static function generateAlignExternClause($clause, $align, $desc, $width, $height)
	{
		$clause = "<div class=\"thumb t{$align}\">" .
			"<div class=\"thumbinner\" style=\"width: {$width}px;\">" .
			$clause .
			"<div class=\"thumbcaption\">" .
			$desc .
			"</div></div></div>";
		return $clause;
	}

	/**
	 * Generate the HTML necessary to embed the video with the given alignment
	 * and text description
	 *
	 * @param string $url
	 * @param int    $width
	 * @param int    $height
	 * @param string $align
	 * @param string $desc
	 *
	 * @return string
	 */
	private static function generateAlignClause($url, $width, $height, $align, $desc) {
		$clause = "<div class=\"thumb t{$align}\">" .
			"<div class=\"thumbinner\" style=\"width: {$width}px;\">" .
			"<object width=\"{$width}\" height=\"{$height}\">" .
			"<param name=\"movie\" value=\"{$url}\"></param>" .
			"<param name=\"wmode\" value=\"transparent\"></param>" .
			"<embed src=\"{$url}\" type=\"application/x-shockwave-flash\"" .
			" wmode=\"transparent\" width=\"{$width}\" height=\"{$height}\"></embed>" .
			"</object>" .
			"<div class=\"thumbcaption\">" .
			$desc .
			"</div></div></div>";
		return $clause;
	}

	/**
	 * Get the entry for the specified service, by name
	 *
	 * @param string $service
	 *
	 * @return $string
	 */
	private static function getServiceEntry($service) {
		// Get the entry in the list of services
		global $wgEmbedVideoServiceList;
		return $wgEmbedVideoServiceList[$service];
	}

	/**
	 * Get the width. If there is no width specified, try to find a default
	 * width value for the service. If that isn't set, default to 425.
	 * If a width value is provided, verify that it is numerical and that it
	 * falls between the specified min and max size values. Return true if
	 * the width is suitable, false otherwise.
	 *
	 * @param string $service
	 *
	 * @return mixed
	 */
	private static function sanitizeWidth($entry, &$width) {
		global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
		if ($width === null || $width == '*' || $width == '') {
			if (isset($entry['default_width'])) {
				$width = $entry['default_width'];
			}
			else {
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
	 * Calculate the height from the given width. The default ratio is 450/350,
	 * but that may be overridden for some sites.
	 *
	 * @param int $entry
	 * @param int $width
	 *
	 * @return int
	 */
	private static function getHeight($entry, $width) {
		$ratio = 4 / 3;
		if (isset($entry['default_ratio'])) {
			$ratio = $entry['default_ratio'];
		}
		return round($width / $ratio);
	}

	/**
	 * If we have a textual description, get the markup necessary to display
	 * it on the page.
	 *
	 * @param string $desc
	 *
	 * @return string
	 */
	private static function getDescriptionMarkup($desc) {
		if ($desc !== null) {
			return "<div class=\"thumbcaption\">$desc</div>";
		}
		return "";
	}

	/**
	 * Verify the id number of the video, if a pattern is provided.
	 *
	 * @param string $entry
	 * @param string $id
	 *
	 * @return bool
	 */
	private static function verifyID($entry, $id) {
		$idhtml = htmlspecialchars($id);
		//$idpattern = (isset($entry['id_pattern']) ? $entry['id_pattern'] : '%[^A-Za-z0-9_\\-]%');
		//if ($idhtml == null || preg_match($idpattern, $idhtml)) {
		return ($idhtml != null);
	}

	/**
	 * Get an error message for the case where the ID value is bad
	 *
	 * @param string $service
	 * @param string $id
	 *
	 * @return string
	 */
	private static function errBadID($service, $id) {
		$idhtml = htmlspecialchars($id);
		$msg = wfMsgForContent('embedvideo-bad-id', $idhtml, @htmlspecialchars($service));
		return '<div class="errorbox">' . $msg . '</div>';
	}

	/**
	 * Get an error message if the width is bad
	 *
	 * @param int $width
	 *
	 * @return string
	 */
	private static function errBadWidth($width) {
		$msg = wfMsgForContent('embedvideo-illegal-width', @htmlspecialchars($width));
		return '<div class="errorbox">' . $msg . '</div>';
	}

	/**
	 * Get an error message if there are missing parameters
	 *
	 * @param string $service
	 * @param string $id
	 *
	 * @return string
	 */
	private static function errMissingParams($service, $id) {
		return '<div class="errorbox">' . wfMsg('embedvideo-missing-params') . '</div>';
	}

	/**
	 * Get an error message if the service name is bad
	 *
	 * @param string $service
	 *
	 * @return string
	 */
	private static function errBadService($service) {
		$msg = wfMsg('embedvideo-unrecognized-service', @htmlspecialchars($service));
		return '<div class="errorbox">' . $msg . '</div>';
	}

	/**
	 * Verify that the min and max values for width are sane.
	 *
	 * @return void
	 */
	private static function VerifyWidthMinAndMax() {
		global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
		if (!is_numeric($wgEmbedVideoMinWidth) || $wgEmbedVideoMinWidth < 100) {
			$wgEmbedVideoMinWidth = 100;
		}
		if (!is_numeric($wgEmbedVideoMaxWidth) || $wgEmbedVideoMaxWidth > 1024) {
			$wgEmbedVideoMaxWidth = 1024;
		}
	}
	private static function getRuTube($id)
	{
	/**
	 * Custom parser for RuTube
	 */
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://rutube.ru/oembed/track?&amp;url=http://rutube.ru/tracks/{$id}.html&amp;format=json");
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, false);
	$json = curl_exec($ch);
	curl_close($ch);
	$start=strpos($json, 'value=\"')+8;
	$end=strpos($json, '\"></');
	$url=substr($json, $start, $end-$start);
	return $url;
	}
	private static function getYandex($id)
	{
	/**
	 * Custom parser for Yandex
	 */
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, "http://video.yandex.ru/oembed.xml?url=http://video.yandex.ru/users/{$id}");
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, false);
	$json = curl_exec($ch);
	curl_close($ch);
	$start=strpos($json, '<html>')+6;
	$end=strpos($json, '</html>');
	$url=substr($json, $start, $end-$start);
	return $url;
	}
}
