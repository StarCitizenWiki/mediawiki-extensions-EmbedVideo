<?php
/**
 * EmbedVideo
 * EmbedVideo Services List
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/
namespace EmbedVideo;

class VideoService {
	/**
	 * Available services.
	 *
	 * @var		array
	 */
	static private $services = array(
		'archiveorg' => array(
			'embed'			=> '<iframe src="//archive.org/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>'
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#archive\.org/(?:details|embed)/([^/\?#]+)#is'
			),
			'id_regex'		=> array(
				'#^([^/\?#]+)$#is'
			)
		),
		'bambuser' => array(
			'embed'			=> '<iframe src="//embed.bambuser.com/broadcast/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#bambuser\.com/(?:v|broadcast)/([\d\w\-\+]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w\-\+]+)$#is'
			)
		),
		'bambuser_channel' => array(
			'embed' 		=> '<iframe src="//embed.bambuser.com/channel/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#bambuser\.com/channel/([\d\w\-\+]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w\-\+]+)$#is'
			)
		),
		'bing' => array(
			'embed'			=> '<iframe src="//hub.video.msn.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" scrolling="no" noscroll allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#video\.[a-zA-Z]{1,4}\.msn.com/watch/video/.+?/([a-zA-Z0-9]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([a-zA-Z0-9]+)$#is'
			)
		),
		'dailymotion' => array(
			'embed'			=> '<iframe src="//www.dailymotion.com/embed/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#dailymotion\.com/(?:video|embed/video)/([a-zA-Z0-9]+)(?:_\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([a-zA-Z0-9]+)$#is'
			)
		),
		'divshare' => array(
			'embed'			=> '<iframe src="//www.divshare.com/flash/video2?myId=%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
		),
		'funnyordie' => array(
			'embed'			=> '<iframe src="http://www.funnyordie.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64102564102564, //(640 / 390)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#funnyordie\.com/(?:videos|embed)/([a-zA-Z0-9]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([a-zA-Z0-9]+)$#is'
			)
		),
		'kickstarter' => array(
			'embed'			=> '<iframe src="//www.kickstarter.com/projects/%1$s/widget/video.html" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#kickstarter\.com/projects/([\d\w-]+/[\d\w-]+)(?:/widget/video.html)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w-]+/[\d\w-]+)$#is'
			)
		),
		'metacafe' => array(
			'embed'			=> '<iframe src="http://www.metacafe.com/embed/%1$s/" width="%2$d" height="%3$d" frameborder="0" allowFullScreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#metacafe\.com/(?:watch|embed)/([\d]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d]+)$#is'
			)
		),
		'msn' => array(
			'embed'			=> '<iframe src="//hub.video.msn.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" scrolling="no" noscroll></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#video\.[a-zA-Z]{1,4}\.msn.com/watch/video/.+?/([a-zA-Z0-9]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([a-zA-Z0-9]+)$#is'
			)
		),
		'nico' => array(
			'embed'			=> '<script type="text/javascript" src="http://ext.nicovideo.jp/thumb_watch/%1$s?w=%2$d&h=%3$d"></script>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.59609120521173, //(490 / 307)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#nicovideo\.jp/watch/(sm[\d]+)#is'
			),
			'id_regex'		=> array(
				'#^(sm[\d]+)$#is'
			)
		),
		'rutube' => array(
			'embed'			=> '<iframe src="//rutube.ru/play/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#rutube\.ru/video/([a-zA-Z0-9]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([a-zA-Z0-9]+)$#is'
			)
		),
		'teachertube' => array(
			'embed'			=> '<iframe src="http://www.teachertube.com/embed/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.72972972972973, //(640 / 370)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#teachertube\.com/video/(?:.+?-)?([\d]+)$#is',
			),
			'id_regex'		=> array(
				'#^([\d]+)$#is'
			)
		),
		'yahoo' => array(
			'embed'			=> '<iframe src="//screen.yahoo.com/%1$s.html?format=embed" width="%2$d" height="%3$d" scrolling="no" frameborder="0" allowfullscreen="true" allowtransparency="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#screen\.yahoo\.com/(.+?-\d+).html#is'
			),
			'id_regex'		=> array(
				'#^(.+?-\d+)$#is'
			)
		),
		'youtube' => array(
			'embed'			=> '<iframe src="//www.youtube.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#v=([\d\w-]+)(?:&\S+?)?#is',
				'#youtu\.be/([\d\w-]+)#is'
			),
			'id_regex'		=> array(
				'#^([\d\w-]+)$#is'
			)
		),
		'youtubeplaylist' => array(
			'embed'			=> '<iframe src="//www.youtube.com/embed/videoseries?list=%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#list=([\d\w-]+)(?:&\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w-]+)$#is'
			)
		),
		'videomaten' => array(
			'embed'			=> '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="%2$d" height="%3$d" id="videomat" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="http://89.160.51.62/recordMe/play.swf?id=%1$s" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="http://89.160.51.62/recordMe/play.swf?id=%1$s" loop="false" quality="high" bgcolor="#ffffff" width="%2$d" height="%3$d" name="videomat" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>',
			'default_ratio'	=> 1.5, //(300 / 200)
			'https_enabled'	=> false
		),
		'twitch' => array(
			'embed'			=> '<iframe src="http://www.twitch.tv/embed?channel=%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, //(620 / 378)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#twitch\.tv/([\d\w-]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w-]+)$#is'
			)
		),
		'twitchvod' => array(
			'embed'			=> '<object id="clip_embed_player_flash" type="application/x-shockwave-flash" width="%2$d" height="%3$d" data="http://www.twitch.tv/widgets/archive_embed_player.swf" bgcolor="#000000">
	<param name="movie" value="http://www.twitch.tv/widgets/archive_embed_player.swf" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowNetworking" value="all" />
	<param name="allowFullScreen" value="true" />
	<param name="flashvars" value="channel=%1$s&amp;auto_play=false&amp;start_volume=100&amp;chapter_id=%4$d" />
</object>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, //(620 / 378)
			'https_enabled'	=> false,
			'url_regex'		=> array(
				'#twitch\.tv/([\d\w-]+)/c/([\d]+)(?:/\S+?)?#is'
			),
			'id_regex'		=> array(
				'#^([\d\w-]+)/c/([\d]+)$#is'
			)
		),
		'vimeo' => array(
			'embed'			=> '<iframe src="//player.vimeo.com/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#vimeo\.com/([\d]+)#is',
				'#vimeo\.com/channels/[\d\w-]+/([\d]+)#is'
			),
			'id_regex'		=> array(
				'#^([\d]+)$#is'
			)
		)
	);

	/**
	 * This object instance's service information.
	 *
	 * @var		array
	 */
	private $service = array();

	/**
	 * Video ID
	 *
	 * @var		array
	 */
	private $id = false;

	/**
	 * Player Width
	 *
	 * @var		integer
	 */
	private $width = false;

	/**
	 * Player Height
	 *
	 * @var		integer
	 */
	private $height = false;

	/**
	 * Description Text
	 *
	 * @var		string
	 */
	private $description = false;

	/**
	 * Extra IDs that some services require.
	 *
	 * @var		array
	 */
	private $extraIDs = false;

	/**
	 * Main Constructor
	 *
	 * @access	private
	 * @return	void
	 */
	private function __construct($service) {
		$this->service = self::$services[$service];
	}

	/**
	 * Create a new object from a service name.
	 *
	 * @access	public
	 * @return	void
	 */
	static public function newFromName($service) {
		if (array_key_exists($service, self::$services)) {
			return new \EmbedVideo\VideoService($service);
		} else {
			return false;
		}
	}

	/**
	 * Return built HTML.
	 *
	 * @access	public
	 * @return	mixed	String HTML to output or false on error.
	 */
	public function getHtml() {
		if ($this->getVideoID() === false || $this->getWidth() === false || $this->getHeight() === false) {
			return false;
		}

		$data = array(
			$this->service['embed'],
			$this->getVideoID(),
			$this->getWidth(),
			$this->getHeight()
		);

		if ($this->getExtraIds() !== false) {
			$data = array_merge($data, $this->getExtraIds());
		}

		$html = call_user_func_array('sprintf', $data);

		return $html;
	}

	/**
	 * Return Video ID
	 *
	 * @access	public
	 * @return	mixed	Parsed Video ID or false for one that is not set.
	 */
	public function getVideoID() {
		return $this->id;
	}

	/**
	 * Function Documentation
	 *
	 * @access	public
	 * @param	string	Video ID/URL
	 * @return	boolean	Success
	 */
	public function setVideoID($id) {
		$id = $this->parseVideoID($id);
		if ($id !== false) {
			$this->id = $id;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Parse the video ID/URL provided.
	 *
	 * @access	private
	 * @param	string	Video ID/URL
	 * @return	mixed	Parsed Video ID or false on failure.
	 */
	private function parseVideoID($id) {
		$id = trim($id);
		//URL regexes are put into the array first to prevent cases where the ID regexes might accidentally match an incorrect portion of the URL.
		$regexes = array_merge((array) $this->service['url_regex'], (array) $this->service['id_regex']);
		if (is_array($regexes) && count($regexes)) {
			foreach ($regexes as $regex) {
				if (preg_match($regex, $id, $matches)) {
					//Get rid of the full text match.
					array_shift($matches);

					$id = array_shift($matches);

					if (count($matches)) {
						$this->extraIDs = $matches;
					}

					return $id;
				}
			}
			//If nothing matches and matches are specified then return false for an invalid ID/URL.
			return false;
		} else {
			//Service definition has not specified a sanitization/validation regex.
			return $id;
		}
	}

	/**
	 * Return extra IDs.
	 *
	 * @access	public
	 * @return	boolean	Array of extra information or false if not set.
	 */
	public function getExtraIDs() {
		return $this->extraIDs;
	}

	/**
	 * Return the width.
	 *
	 * @access	public
	 * @return	mixed	Integer value or false for not set.
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * Return the height.
	 *
	 * @access	public
	 * @return	mixed	Integer value or false for not set.
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * Set the width of the player.  This also will set the height automatically.
	 * Width will be automatically constrained to the minimum and maximum widths.
	 *
	 * @access	public
	 * @param	integer	Width
	 * @return	void
	 */
	public function setWidth($width = null) {
		global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth, $wgEmbedVideoDefaultWidth;

		if (!is_numeric($width)) {
			if ($width === null && $this->getDefaultWidth() !== false) {
				$width = $this->getDefaultWidth();
			} else {
				$width = ($wgEmbedVideoDefaultWidth > 0 ? $wgEmbedVideoDefaultWidth : 640);
			}
		} else {
			$width = intval($width);
		}

		if ($wgEmbedVideoMaxWidth > 0 && $width > $wgEmbedVideoMaxWidth) {
			$width = $wgEmbedVideoMaxWidth;
		}

		if ($wgEmbedVideoMinWidth > 0 && $width < $wgEmbedVideoMinWidth) {
			$width = $wgEmbedVideoMinWidth;
		}
		$this->width = $width;

		if ($this->getHeight() === false) {
			$this->setHeight();
		}
	}

	/**
	 * Set the height automatically by a ratio of the width or use the provided value.
	 *
	 * @access	public
	 * @param	mixed	[Optional] Height Value
	 * @return	void
	 */
	public function setHeight($height = null) {
		if ($height !== null && $height > 0) {
			$this->height = intval($height);
			return;
		}

		$ratio = 16 / 9;
		if ($this->getDefaultRatio() !== false) {
			$ratio = $this->getDefaultRatio();
		}
		$this->height = round($this->getWidth() / $ratio);
	}

	/**
	 * Is HTTPS enabled?
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isHttpsEnabled() {
		return (bool) $this->service['https_enabled'];
	}

	/**
	 * Return default width if set.
	 *
	 * @access	public
	 * @return	mixed	Integer width or false if not set.
	 */
	public function getDefaultWidth() {
		return ($this->service['default_width'] > 0 ? $this->service['default_width'] : false);
	}

	/**
	 * Return default ratio if set.
	 *
	 * @access	public
	 * @return	mixed	Integer ratio or false if not set.
	 */
	public function getDefaultRatio() {
		return ($this->service['default_ratio'] > 0 ? $this->service['default_ratio'] : false);
	}
}
?>