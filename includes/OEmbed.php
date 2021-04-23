<?php
/**
 * EmbedVideo
 * EmbedVideo OEmbed Class
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 **/

declare(strict_types=1);

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\MediaWikiServices;

class OEmbed {
	/**
	 * Data from oEmbed service.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Main Constructor
	 *
	 * @access private
	 * @param  array	Data return from oEmbed service.
	 * @return void
	 */
	private function __construct($data) {
		$this->data = $data;
	}

	/**
	 * Create a new object from an oEmbed URL.
	 *
	 * @access public
	 * @param  string	Full oEmbed URL to process.
	 * @return mixed	New OEmbed object or false on initialization failure.
	 */
	public static function newFromRequest($url) {
		$data = self::curlGet($url);
		if ($data !== false) {
			// Error suppression is required as json_decode() tosses E_WARNING in contradiction to its documentation.
			$data = @json_decode($data, true);
		}
		if (!$data || !is_array($data)) {
			return false;
		}
		return new self($data);
	}

	/**
	 * Return the HTML from the data, typically an iframe.
	 *
	 * @access public
	 * @return mixed	String HTML or false on error.
	 */
	public function getHtml() {
		if (isset($this->data['html'])) {
			// Remove any extra HTML besides the iframe.
			$iframeStart = strpos($this->data['html'], '<iframe');
			$iframeEnd = strpos($this->data['html'], '</iframe>');
			if ($iframeStart !== false) {
				// Only strip if an iframe was found.
				$this->data['html'] = substr($this->data['html'], $iframeStart, $iframeEnd + 9);
			}

			return $this->data['html'];
		}

		return false;
	}

	/**
	 * Return the title from the data.
	 *
	 * @access public
	 * @return mixed	String or false on error.
	 */
	public function getTitle() {
		return $this->data['title'] ?? false;
	}

	/**
	 * Return the author name from the data.
	 *
	 * @access public
	 * @return mixed	String or false on error.
	 */
	public function getAuthorName() {
		return $this->data['author_name'] ?? false;
	}

	/**
	 * Return the author URL from the data.
	 *
	 * @access public
	 * @return mixed	String or false on error.
	 */
	public function getAuthorUrl() {
		return $this->data['author_url'] ?? false;
	}

	/**
	 * Return the provider name from the data.
	 *
	 * @access public
	 * @return mixed	String or false on error.
	 */
	public function getProviderName() {
		return $this->data['provider_name'] ?? false;
	}

	/**
	 * Return the provider URL from the data.
	 *
	 * @access public
	 * @return mixed	String or false on error.
	 */
	public function getProviderUrl() {
		return $this->data['provider_url'] ?? false;
	}

	/**
	 * Return the width from the data.
	 *
	 * @access public
	 * @return mixed	Integer or false on error.
	 */
	public function getWidth() {
		if (isset($this->data['width'])) {
			return (int)$this->data['width'];
		}

		return false;
	}

	/**
	 * Return the height from the data.
	 *
	 * @access public
	 * @return mixed	Integer or false on error.
	 */
	public function getHeight() {
		if (isset($this->data['height'])) {
			return (int)$this->data['height'];
		}

		return false;
	}

	/**
	 * Return the thumbnail width from the data.
	 *
	 * @access public
	 * @return mixed	Integer or false on error.
	 */
	public function getThumbnailWidth() {
		if (isset($this->data['thumbnail_width'])) {
			return (int)$this->data['thumbnail_width'];
		}

		return false;
	}

	/**
	 * Return the thumbnail height from the data.
	 *
	 * @access public
	 * @return mixed	Integer or false on error.
	 */
	public function getThumbnailHeight() {
		if (isset($this->data['thumbnail_height'])) {
			return (int)$this->data['thumbnail_height'];
		}

		return false;
	}

	/**
	 * Perform a Curl GET request.
	 *
	 * @access private
	 * @param  string	URL
	 * @return mixed
	 */
	private static function curlGet($location) {
		$ch = curl_init();

		$timeout = 10;
		$useragent = "EmbedVideo/1.0/" . MediaWikiServices::getInstance()->getMainConfig()->get('Server');
		$dateTime = gmdate("D, d M Y H:i:s", time()) . " GMT";
		$headers = ['Date: ' . $dateTime];

		$curlOptions = [
			CURLOPT_TIMEOUT		   => $timeout,
			CURLOPT_USERAGENT	   => $useragent,
			CURLOPT_URL			   => $location,
			CURLOPT_CONNECTTIMEOUT  => $timeout,
			CURLOPT_FOLLOWLOCATION  => true,
			CURLOPT_MAXREDIRS	   => 10,
			CURLOPT_COOKIEFILE	   => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'curlget',
			CURLOPT_COOKIEJAR	   => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'curlget',
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_HTTPHEADER	   => $headers
		];

		curl_setopt_array($ch, $curlOptions);

		$page = curl_exec($ch);

		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($responseCode == 503 || $responseCode == 404 || $responseCode == 501 || $responseCode == 401) {
			return false;
		}

		return $page;
	}
}
