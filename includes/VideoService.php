<?php
/**
 * EmbedVideo
 * EmbedVideo VideoService Class
 *
 * @license MIT
 * @package EmbedVideo
 * @link    https://www.mediawiki.org/wiki/Extension:EmbedVideo
 **/

declare(strict_types=1);

namespace MediaWiki\Extension\EmbedVideo;

use MediaWiki\MediaWikiServices;
use MWException;

class VideoService {
	/**
	 * Available services.
	 *
	 * @var array
	 */
	static private $services = [
		'archiveorg' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="//archive.org/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, // (640 / 493)
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#archive\.org/(?:details|embed)/([\d\w\-_][^/\?\#]+)#is'
			],
			'id_regex'		=> [
				'#^([\d\w\-_][^/\?\#]+)$#is'
			]
		],
		'soundcloud' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://w.soundcloud.com/player/?url=%1$s&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true" width="%2$d" height="%3$d" scrolling="no" frameborder="no"></iframe>',
			'default_width'	=> 186,
			'default_ratio'	=> 2.66666,
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#^(https://soundcloud\.com/.+?/.+?)$#is',
			]
		],
		'spotifyalbum' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://open.spotify.com/embed/album/%1$s" width="%2$d" height="%3$d" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>',
			'default_width'	=> 300,
			'default_ratio'	=> 0.7895,
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#open\.spotify\.com/album/([a-zA-Z0-9]+)#is',
			],
			'id_regex'		=> [
				'#^([a-zA-Z0-9]+)$#is'
			]
		],
		'spotifyartist' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://open.spotify.com/embed/artist/%1$s" width="%2$d" height="%3$d" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>',
			'default_width'	=> 300,
			'default_ratio'	=> 0.7895,
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#open\.spotify\.com/artist/([a-zA-Z0-9]+)#is',
			],
			'id_regex'		=> [
				'#^([a-zA-Z0-9]+)$#is'
			]
		],
		'spotifytrack' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://open.spotify.com/embed/track/%1$s" width="%2$d" height="%3$d" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>',
			'default_width'	=> 300,
			'default_ratio'	=> 0.7895,
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#open\.spotify\.com/track/([a-zA-Z0-9]+)#is',
			],
			'id_regex'		=> [
				'#^([a-zA-Z0-9]+)$#is'
			]
		],
		'twitch' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://player.twitch.tv/?channel=%1$s&%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, // (620 / 378)
			'https_enabled'	=> false,
			'url_regex'		=> [
				'#twitch\.tv/([\d\w-]+)(?:/\S+?)?#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			]
		],
		'twitchclip' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://clips.twitch.tv/embed?autoplay=false&clip=%1$s&%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, // (620 / 378)
			'https_enabled'	=> false,
			'url_regex'		=> [
				'#twitch\.tv/(?:[\d\w-]+)/(?:clip/)([\d\w-]+)?#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			]
		],
		'twitchvod' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="https://player.twitch.tv/?autoplay=false&video=%1$s&%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, // (620 / 378)
			'https_enabled'	=> false,
			'url_regex'		=> [
				'#twitch\.tv/videos/([\d\w-]+)(?:/\S+?)?#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			]
		],
		'vimeo' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="//player.vimeo.com/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.77777777777778, // (640 / 360)
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#vimeo\.com/([\d]+)#is',
				'#vimeo\.com/channels/[\d\w-]+/([\d]+)#is'
			],
			'id_regex'		=> [
				'#^([\d]+)$#is'
			],
			'oembed'		=> '%4$s//vimeo.com/api/oembed.json?url=%1$s&width=%2$d&maxwidth=%2$d'
		],
		'youtube' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="//www.youtube-nocookie.com/embed/%1$s?%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, // (16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#v=([\d\w-]+)(?:&\S+?)?#is',
				'#youtu\.be/([\d\w-]+)#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			],
			'oembed'		=> [
				'http'	=> 'http://www.youtube-nocookie.com/oembed?url=%1$s&width=%2$d&maxwidth=%2$d',
				'https'	=> 'http://www.youtube-nocookie.com/oembed?scheme=https&url=%1$s&width=%2$d&maxwidth=%2$d'
			]
		],
		'youtubeplaylist' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="//www.youtube-nocookie.com/embed/videoseries?list=%1$s&%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, // (16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#list=([\d\w-]+)(?:&\S+?)?#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			]
		],
		'youtubevideolist' => [
			'embed'			=> '<iframe loading="lazy" title="%4$s" %6$s="//www.youtube-nocookie.com/embed/%1$s?%5$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, // (16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> [
				'#playlist=([\d\w-]+)(?:&\S+?)?#is'
			],
			'id_regex'		=> [
				'#^([\d\w-]+)$#is'
			]
		],
	];

	/**
	 * Mapping of host names to services
	 *
	 * @var array
	 */
	static private $serviceHostMap = [
		'archive.org'				=> 'archiveorg',
		'mixer.com'					=> 'mixer',
		'soundcloud.com'			=> 'soundcloud',
		'spotify.com'				=> ['spotifyalbum', 'spotifyartist', 'spotifytrack'],
		'twitch.tv'					=> ['twitch', 'twitchclip', 'twitchvod'],
		'youtube-nocookie.com'		=> ['youtube', 'youtubeplaylist', 'youtubevideolist'],
	];

	/**
	 * This object instance's service information.
	 *
	 * @var array
	 */
	private $service;

	/**
	 * Video ID
	 *
	 * @var array
	 */
	private $id = false;

	/**
	 * Player Width
	 *
	 * @var integer
	 */
	private $width = false;

	/**
	 * Player Height
	 *
	 * @var integer
	 */
	private $height = false;

	/**
	 * Extra IDs that some services require.
	 *
	 * @var array
	 */
	private $extraIDs = false;

	/**
	 * Extra URL Arguments that may be utilized by some services.
	 *
	 * @var array
	 */
	private $urlArgs = false;

	/**
	 * Title for iframe.
	 *
	 * @var string
	 */
	private $iframeTitle = "";

	/**
	 * Main Constructor
	 *
	 * @access private
	 * @param  string	Service Name
	 * @return void
	 */
	private function __construct($service) {
		$this->service = self::$services[$service];
	}

	/**
	 * Create a new object from a service name.
	 *
	 * @access public
	 * @param  string	Service Name
	 * @return mixed	New VideoService object or false on initialization error.
	 */
	public static function newFromName($service) {
		if (isset(self::$services[$service])) {
			return new self($service);
		}

		return false;
	}

	/**
	 * return the service host map array
	 *
	 * @return array $serviceHostMap
	 */
	public static function getServiceHostMap(): array {
		return self::$serviceHostMap;
	}

	/**
	 * return an array of defined services
	 *
	 * @return array $services
	 */
	public static function getAvailableServices(): array {
		return array_keys(self::$services);
	}

	/**
	 * Add a service
	 *
	 * @access public
	 * @param  string    Service Name
	 * @param  mixed   args
	 * @throws MWException
	 */
	public static function addService($service, $args): void {
		if (isset(self::$services[$service])) {
			throw new MWException("Service already already exists: $service");
		}
		self::$services[$service] = $args;
	}

	/**
	 * Return built HTML.
	 *
	 * @access public
	 * @return mixed	String HTML to output or false on error.
	 */
	public function getHtml() {
		if ($this->getVideoID() === false || $this->getWidth() === false || $this->getHeight() === false) {
			return false;
		}

		$html = false;

		$srcType = 'src';
		try {
			$consent = MediaWikiServices::getInstance()->getMainConfig()->get('EmbedVideoRequireConsent');
			if ($consent === true) {
				$srcType = 'data-src';
			}
		} catch (\ConfigException $e) {
			//
		}

		if (isset($this->service['embed'])) {
			// Embed can be generated locally instead of calling out to the service to get it.
			$data = [
				$this->service['embed'],
				htmlentities($this->getVideoID(), ENT_QUOTES),
				$this->getWidth(),
				$this->getHeight(),
				$this->getIframeTitle(),
			];

			if ($this->getExtraIds() !== false) {
				foreach ($this->getExtraIds() as $extraId) {
					$data[] = htmlentities($extraId, ENT_QUOTES);
				}
			}

			$urlArgs = $this->getUrlArgs();
			if ($urlArgs !== false) {
				$data[] = $urlArgs;
			} else {
				$data[] = '';
			}

			$data[] = $srcType;

			$html = sprintf(...$data);
		} elseif (isset($this->service['oembed'])) {
			// Call out to the service to get the embed HTML.
			if ($this->service['https_enabled']
				&& stripos($this->getVideoID(), 'https:') !== false
			) {
				$protocol = 'https:';
			} else {
				$protocol = 'http:';
			}
			$url = sprintf(
				$this->service['oembed'],
				$this->getVideoID(),
				$this->getWidth(),
				$this->getHeight(),
				$protocol,
				$srcType
			);
			$oEmbed = OEmbed::newFromRequest($url);
			if ($oEmbed !== false) {
				$html = $oEmbed->getHtml();
			}
		}

		return $html;
	}

	/**
	 * Return Video ID
	 *
	 * @access public
	 * @return mixed	Parsed Video ID or false for one that is not set.
	 */
	public function getVideoID() {
		return $this->id;
	}

	/**
	 * Set the Video ID for this video.
	 *
	 * @access public
	 * @param  string	Video ID/URL
	 * @return boolean	Success
	 */
	public function setVideoID($id) {
		$id = $this->parseVideoID($id);
		if ($id !== false) {
			$this->id = $id;
			return true;
		}

		return false;
	}

	/**
	 * Parse the video ID/URL provided.
	 *
	 * @access public
	 * @param  string	Video ID/URL
	 * @return mixed	Parsed Video ID or false on failure.
	 */
	public function parseVideoID($id) {
		$id = trim($id);
		// URL regexes are put into the array first to prevent cases where the ID regexes might accidentally match an incorrect portion of the URL.
		$regexes = array_merge((array)$this->service['url_regex'], (array)$this->service['id_regex']);
		if (is_array($regexes) && count($regexes)) {
			foreach ($regexes as $regex) {
				if (preg_match($regex, $id, $matches)) {
					// Get rid of the full text match.
					array_shift($matches);

					$id = array_shift($matches);

					if (count($matches)) {
						$this->extraIDs = $matches;
					}

					return $id;
				}
			}
			// If nothing matches and matches are specified then return false for an invalid ID/URL.
			return false;
		}

		// Service definition has not specified a sanitization/validation regex.
		return $id;
	}

	/**
	 * Return extra IDs.
	 *
	 * @access public
	 * @return array|boolean	Array of extra information or false if not set.
	 */
	public function getExtraIDs() {
		return $this->extraIDs;
	}

	/**
	 * Return the width.
	 *
	 * @access public
	 * @return mixed	Integer value or false for not set.
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Return the iframeTitle.
	 *
	 * @access public
	 * @return String, defaulting to message 'ev_default_play_desc'
	 */
	public function getIframeTitle() {
		if ($this->iframeTitle === '') {
			return wfMessage('ev_default_play_desc')->text();
		}

		return $this->iframeTitle;
	}

	/**
	 * Set the width of the player.  This also will set the height automatically.
	 * Width will be automatically constrained to the minimum and maximum widths.
	 *
	 * @access public
	 * @param  integer	Width
	 * @return void
	 */
	public function setWidth($width = null) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$videoMinWidth = $config->get('EmbedVideoMinWidth');
		$videoMaxWidth = $config->get('EmbedVideoMaxWidth');
		$videoDefaultWidth = $config->get('EmbedVideoDefaultWidth');

		if (!is_numeric($width)) {
			if ($width === null && $this->getDefaultWidth() !== false && $videoDefaultWidth < 1) {
				$width = $this->getDefaultWidth();
			} else {
				$width = ($videoDefaultWidth > 0 ? $videoDefaultWidth : 640);
			}
		} else {
			$width = (int)$width;
		}

		if ($videoMaxWidth > 0 && $width > $videoMaxWidth) {
			$width = $videoMaxWidth;
		}

		if ($videoMinWidth > 0 && $width < $videoMinWidth) {
			$width = $videoMinWidth;
		}
		$this->width = $width;

		if ($this->getHeight() === false) {
			$this->setHeight();
		}
	}

	/**
	 * Return the height.
	 *
	 * @access public
	 * @return mixed	Integer value or false for not set.
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Set the height automatically by a ratio of the width or use the provided value.
	 *
	 * @access public
	 * @param  mixed	[Optional] Height Value
	 * @return void
	 */
	public function setHeight($height = null) {
		if ($height !== null && $height > 0) {
			$this->height = (int)$height;
			return;
		}

		$ratio = 16 / 9;
		if ($this->getDefaultRatio() !== false) {
			$ratio = $this->getDefaultRatio();
		}
		$this->height = round($this->getWidth() / $ratio);
	}

	/**
	 * Return the optional URL arguments.
	 *
	 * @access public
	 * @return mixed	Integer value or false for not set.
	 */
	public function getUrlArgs() {
		if ($this->urlArgs !== false) {
			return http_build_query($this->urlArgs);
		}

		return false;
	}

	/**
	 * Set URL Arguments to optionally add to the embed URL.
	 *
	 * @access public
	 * @param  string	Raw Arguments
	 * @return boolean	Success
	 */
	public function setUrlArgs($urlArgs) {
		if (!$urlArgs) {
			return true;
		}

		$urlArgs = urldecode($urlArgs);
		$_args = explode('&', $urlArgs);
		$arguments = [];

		if (is_array($_args)) {
			foreach ($_args as $rawPair) {
				[$key, $value] = explode("=", $rawPair, 2);
				if (empty($key) || ($value === null || $value === '')) {
					return false;
				}
				$arguments[$key] = htmlentities($value, ENT_QUOTES);
			}
		} else {
			return false;
		}
		$this->urlArgs = $arguments;
		return true;
	}

	/**
	 * Is HTTPS enabled?
	 *
	 * @access public
	 * @return boolean
	 */
	public function isHttpsEnabled() {
		return (bool)$this->service['https_enabled'];
	}

	/**
	 * Return default width if set.
	 *
	 * @access public
	 * @return mixed	Integer width or false if not set.
	 */
	public function getDefaultWidth() {
		return ($this->service['default_width'] > 0 ? $this->service['default_width'] : false);
	}

	/**
	 * Return default ratio if set.
	 *
	 * @access public
	 * @return mixed	Integer ratio or false if not set.
	 */
	public function getDefaultRatio() {
		return ($this->service['default_ratio'] > 0 ? $this->service['default_ratio'] : false);
	}
}
