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

// Build services list (may be augmented in LocalSettings.php)
$wgEmbedVideoServiceList = array(
	'bambuser' => array(
		'embed' => '<iframe src="http://embed.bambuser.com/broadcast/$1" width="$2" height="$3" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394
	),
	'bambuser_channel' => array(
		'embed' => '<iframe src="http://embed.bambuser.com/channel/$1" width="$2" height="$3" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394
	),
	'bing' => array(
		'embed' => '<iframe src="http://hub.video.msn.com/embed/$1" width="$2" height="$3" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'dailymotion' => array(
		'embed'		=> '<iframe src="//www.dailymotion.com/embed/video/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'divshare' => array(
		'embed'	=> '<iframe src="http://www.divshare.com/flash/video2?myId=$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'funnyordie' => array(
		'embed'		=> '<iframe src="http://www.funnyordie.com/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 640 / 390
	),
	'metacafe' => array(
		'embed'		=> '<iframe src="http://www.metacafe.com/embed/$1/" width="$2" height="$3" frameborder="0" allowFullScreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'msn' => array(
		'embed'			=> '<iframe src="http://hub.video.msn.com/embed/$1" width="$2" height="$3" frameborder="0" scrolling="no" noscroll></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'rutube' => array(
		'embed'	=> '<iframe src="//rutube.ru/play/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'screen9' => array(
		'embed' => 't',
		'default_ratio' => 579 / 358
	),
	'teachertube' => array(
		'embed'			=> '<iframe src="http://www.teachertube.com/embed/video/345423" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 640 / 370
	),
	'yahoo' => array(
		'embed' => '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=$1" width="$2" height="$3" frameborder="0"></iframe>'
	),
	'yandex' => array(
		'embed' => '$5'
	),
	'yandexvideo' => array(
		'embed' => '$5'
	),
	'youtube' => array(
		'embed' => '<iframe src="//www.youtube.com/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'youtubeplaylist' => array(
		'embed'			=> '<iframe src="http://www.youtube.com/embed/videoseries?list=$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'videomaten' => array(
		'embed'			=> '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="$2" height="$3" id="videomat" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="http://89.160.51.62/recordMe/play.swf?id=$1" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="http://89.160.51.62/recordMe/play.swf?id=$1" loop="false" quality="high" bgcolor="#ffffff" width="$2" height="$3" name="videomat" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>',
		'default_ratio'	=> 300 / 200
	),
	'vimeo' => array(
		'embed'			=> '<iframe src="//player.vimeo.com/video/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	)
);
?>