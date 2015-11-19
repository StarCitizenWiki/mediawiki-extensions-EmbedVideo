<?php
/**
 * EmbedVideo
 * EmbedVideo OEmbed Class
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/
namespace EmbedVideo;

class OEmbed {
	/**
	 * Data from oEmbed service.
	 *
	 * @var		array
	 */
	private $data = [];

	/**
	 * Main Constructor
	 *
	 * @access	private
	 * @param	array	Data return from oEmbed service.
	 * @return	void
	 */
	private function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 * Create a new object from an oEmbed URL.
	 *
	 * @access	public
	 * @param	string	Full oEmbed URL to process.
	 * @return	mixed	New OEmbed object or false on initialization failure.
	 */
	static public function newFromRequest( $url ) {
		$data = self::curlGet( $url );
		if ( $data !== false ) {
			// Error suppression is required as json_decode() tosses E_WARNING in contradiction to its documentation.
			$data = @json_decode( $data, true );
		}
		if ( !$data || !is_array( $data ) ) {
			return false;
		}
		return new self( $data );
	}

	/**
	 * Return the HTML from the data, typically an iframe.
	 *
	 * @access	public
	 * @return	mixed	String HTML or false on error.
	 */
	public function getHtml() {
		if ( isset( $this->data['html'] ) ) {
			// Remove any extra HTML besides the iframe.
			$iframeStart = strpos( $this->data['html'], '<iframe' );
			$iframeEnd = strpos( $this->data['html'], '</iframe>' );
			if ( $iframeStart !== false ) {
				// Only strip if an iframe was found.
				$this->data['html'] = substr( $this->data['html'], $iframeStart, $iframeEnd + 9 );
			}

			return $this->data['html'];
		} else {
			return false;
		}
	}

	/**
	 * Return the title from the data.
	 *
	 * @access	public
	 * @return	mixed	String or false on error.
	 */
	public function getTitle() {
		if ( isset( $this->data['title'] ) ) {
			return $this->data['title'];
		} else {
			return false;
		}
	}

	/**
	 * Return the author name from the data.
	 *
	 * @access	public
	 * @return	mixed	String or false on error.
	 */
	public function getAuthorName() {
		if ( isset( $this->data['author_name'] ) ) {
			return $this->data['author_name'];
		} else {
			return false;
		}
	}

	/**
	 * Return the author URL from the data.
	 *
	 * @access	public
	 * @return	mixed	String or false on error.
	 */
	public function getAuthorUrl() {
		if ( isset( $this->data['author_url'] ) ) {
			return $this->data['author_url'];
		} else {
			return false;
		}
	}

	/**
	 * Return the provider name from the data.
	 *
	 * @access	public
	 * @return	mixed	String or false on error.
	 */
	public function getProviderName() {
		if ( isset( $this->data['provider_name'] ) ) {
			return $this->data['provider_name'];
		} else {
			return false;
		}
	}

	/**
	 * Return the provider URL from the data.
	 *
	 * @access	public
	 * @return	mixed	String or false on error.
	 */
	public function getProviderUrl() {
		if ( isset( $this->data['provider_url'] ) ) {
			return $this->data['provider_url'];
		} else {
			return false;
		}
	}

	/**
	 * Return the width from the data.
	 *
	 * @access	public
	 * @return	mixed	Integer or false on error.
	 */
	public function getWidth() {
		if ( isset( $this->data['width'] ) ) {
			return intval( $this->data['width'] );
		} else {
			return false;
		}
	}

	/**
	 * Return the height from the data.
	 *
	 * @access	public
	 * @return	mixed	Integer or false on error.
	 */
	public function getHeight() {
		if ( isset( $this->data['height'] ) ) {
			return intval( $this->data['height'] );
		} else {
			return false;
		}
	}

	/**
	 * Return the thumbnail width from the data.
	 *
	 * @access	public
	 * @return	mixed	Integer or false on error.
	 */
	public function getThumbnailWidth() {
		if ( isset( $this->data['thumbnail_width'] ) ) {
			return intval( $this->data['thumbnail_width'] );
		} else {
			return false;
		}
	}

	/**
	 * Return the thumbnail height from the data.
	 *
	 * @access	public
	 * @return	mixed	Integer or false on error.
	 */
	public function getThumbnailHeight() {
		if ( isset( $this->data['thumbnail_height'] ) ) {
			return intval( $this->data['thumbnail_height'] );
		} else {
			return false;
		}
	}

	/**
	 * Perform a Curl GET request.
	 *
	 * @access	private
	 * @param	string	URL
	 * @return	mixed
	 */
	static private function curlGet( $location ) {
	   global $wgServer;

	   $ch = curl_init();

	   $timeout = 10;
	   $useragent = "EmbedVideo/1.0/" . $wgServer;
	   $dateTime = gmdate( "D, d M Y H:i:s", time() ) . " GMT";
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

	   curl_setopt_array( $ch, $curlOptions );

	   $page = curl_exec( $ch );

	   $response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	   if ( $responseCode == 503 || $responseCode == 404 || $responseCode == 501 || $responseCode == 401 ) {
		   return false;
	   }

	   return $page;
   }
}
