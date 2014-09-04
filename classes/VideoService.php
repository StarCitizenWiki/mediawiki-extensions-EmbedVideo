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
		'bambuser' => array(
			'embed'			=> '<iframe src="//embed.bambuser.com/broadcast/%1$s" width="%2$d" height="%3$d" frameborder="0"></iframe>',
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true
		),
		'bambuser_channel' => array(
			'embed' 		=> '<iframe src="//embed.bambuser.com/channel/%1$s" width="%2$d" height="%3$d" frameborder="0"></iframe>',
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true
		),
		'bing' => array(
			'embed'			=> '<iframe src="//hub.video.msn.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" scrolling="no" noscroll></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
		),
		'dailymotion' => array(
			'embed'			=> '<iframe src="//www.dailymotion.com/embed/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
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
			'https_enabled'	=> false
		),
		'kickstarter' => array(
			'embed'			=> '<iframe src="%1$s/widget/video.html" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
		),
		'metacafe' => array(
			'embed'			=> '<iframe src="http://www.metacafe.com/embed/%1$s/" width="%2$d" height="%3$d" frameborder="0" allowFullScreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> false
		),
		'msn' => array(
			'embed'			=> '<iframe src="//hub.video.msn.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" scrolling="no" noscroll></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
		),
		'rutube' => array(
			'embed'			=> '<iframe src="//rutube.ru/play/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true
		),
		'teachertube' => array(
			'embed'			=> '<iframe src="http://www.teachertube.com/embed/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.72972972972973, //(640 / 370)
			'https_enabled'	=> false
		),
		'yahoo' => array(
			'embed'			=> '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=%1$s" width="%2$d" height="%3$d" frameborder="0"></iframe>'
		),
		'youtube' => array(
			'embed'			=> '<iframe src="//www.youtube.com/embed/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.77777777777778, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#v=([\d\w-]+)(?:&\S+?)?#is',
				'#youtu.be/([\d\w-]+)#is'
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
		'twitchchannel' => array(
			'embed'			=> '<object id="live_embed_player_flash" type="application/x-shockwave-flash" width="%2$d" height="%3$d" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=%1$s">
	<param name="allowFullScreen" value="true" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowNetworking" value="all" />
	<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
	<param name="flashvars" value="hostname=www.twitch.tv&channel=%1$s&auto_play=false&start_volume=100" />
</object>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, //(620 / 378)
			'https_enabled'	=> false
		),
		'twitchvod' => array(
			'embed'			=> '<object id="clip_embed_player_flash" type="application/x-shockwave-flash" width="%2$d" height="%3$d" data="http://www.twitch.tv/widgets/archive_embed_player.swf" bgcolor="#000000">
	<param name="movie" value="http://www.twitch.tv/widgets/archive_embed_player.swf" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowNetworking" value="all" />
	<param name="allowFullScreen" value="true" />
	<param name="flashvars" value="channel=%1$s&amp;auto_play=false&amp;start_volume=100&amp;chapter_id=$4" />
</object>',
			'default_width'	=> 640,
			'default_ratio'	=> 1.64021164021164, //(620 / 378)
			'https_enabled'	=> false
		),
		'vimeo' => array(
			'embed'			=> '<iframe src="//player.vimeo.com/video/%1$s" width="%2$d" height="%3$d" frameborder="0" allowfullscreen="true"></iframe>',
			'default_width'	=> 640,
			'default_ratio' => 1.2994923857868, //(16 / 9)
			'https_enabled'	=> true,
			'url_regex'		=> array(
				'#vimeo.com/([\d]+)#is',
				'#vimeo.com/channels/[\d\w-]+/([\d]+)#is'
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

		$html = sprintf(
			$this->service['embed'],
			$this->getVideoID(),
			$this->getWidth(),
			$this->getHeight()
		);
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
					$id = $matches[1];
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
	 * Return description text.
	 *
	 * @access	public
	 * @return	mixed	String description or false for not set.
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set the description.
	 *
	 * @access	public
	 * @param	string	Description
	 * @param	object	Mediawiki Parser object
	 * @return	void
	 */
	public function setDescription($description, \Parser $parser) {
		$this->description = (!$description ? false : $parser->recursiveTagParse($description));
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

		$width = ($wgEmbedVideoDefaultWidth > 0 ? $wgEmbedVideoDefaultWidth : 640);
		if (!is_numeric($width)) {
			if ($width === null && $this->getDefaultWidth() !== false) {
				$width = $this->getDefaultWidth();
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