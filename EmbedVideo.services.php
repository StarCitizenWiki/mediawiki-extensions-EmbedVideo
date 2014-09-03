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
		'extern' => '<iframe src="http://embed.bambuser.com/broadcast/$2" width="$3" height="$4" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394
	),
	'bambuser_channel' => array(
		'extern' => '<iframe src="http://embed.bambuser.com/channel/$2" width="$3" height="$4" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394
	),
	'bing' => array(
		'extern' => '<iframe src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'dailymotion' => array(
		'extern'		=> '<iframe src="//www.dailymotion.com/embed/video/$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'divshare' => array(
		'extern'	=> '<iframe src="http://www.divshare.com/flash/video2?myId=$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'funnyordie' => array(
		'extern'		=> '<iframe src="http://www.funnyordie.com/embed/$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 640 / 390
	),
	'metacafe' => array(
		'extern'		=> '<iframe src="http://www.metacafe.com/embed/$2/" width="$3" height="$4" frameborder="0" allowFullScreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'msn' => array(
		'extern' => '<iframe src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'rutube' => array(
		'url' => ''
	),
	'screen9' => array(
		'extern' => 't',
		'default_ratio' => 579 / 358
	),
	'sevenload' => array(
		'url' => 'http://page.sevenload.com/swf/en_GB/player.swf?id=$1'
	),
	'teachertube' => array(
		'extern' => '<embed src="http://www.teachertube.com/embed/player.swf" width="$3" height="$4" bgcolor="undefined" allowscriptaccess="always" allowfullscreen="true" flashvars="file=http://www.teachertube.com/embedFLV.php?pg=video_$2&menu=false&frontcolor=ffffff&lightcolor=FF0000&logo=http://www.teachertube.com/www3/images/greylogo.swf&skin=http://www.teachertube.com/embed/overlay.swf volume=80&controlbar=over&displayclick=link&viral.link=http://www.teachertube.com/viewVideo.php?video_id=$2&stretching=exactfit&plugins=viral-2&viral.callout=none&viral.onpause=false"/>'
	),
	'yahoo' => array(
		'extern' => '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=$2" width="$3" height="$4" frameborder="0"></iframe>'
	),
	'yahoovideo' => array(
		'extern' => '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=$2" width="$3" height="$4" frameborder="0"></iframe>'
	),
	'yahooscreen' => array(
		'extern' => '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=$2" width="$3" height="$4" frameborder="0"></iframe>'
	),
	'yandex' => array(
		'extern' => '$5'
	),
	'yandexvideo' => array(
		'extern' => '$5'
	),
	'youtube' => array(
		'extern' => '<iframe src="//www.youtube.com/embed/$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9
	),
	'youtubeplaylist' => array(
		'extern' => '<iframe src="http://www.youtube.com/embed/videoseries?showsearch=0&amp;modestbranding=1&amp;list=$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>',
		'default_ratio' => 16 / 9
	),
	'videomaten' => array(
		'extern' => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="$3" height="$4" id="videomat" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="http://89.160.51.62/recordMe/play.swf?id=$2" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="http://89.160.51.62/recordMe/play.swf?id=$2" loop="false" quality="high" bgcolor="#ffffff" width="$3" height="$4" name="videomat" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>',
		'default_ratio' => 300 / 200
	),
	'vimeo' => array(
		'extern' =>	'<iframe src="//player.vimeo.com/video/$2" width="$3" height="$4" frameborder="0" allowfullscreen="true"></iframe>'
	)
);
?>