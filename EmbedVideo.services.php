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

//Build services list - Make modifications within this file.
$wgEmbedVideoServiceList = array(
	'bambuser' => array(
		'embed'			=> '<iframe src="//embed.bambuser.com/broadcast/$1" width="$2" height="$3" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394,
		'https_enabled'	=> true
	),
	'bambuser_channel' => array(
		'embed' 		=> '<iframe src="//embed.bambuser.com/channel/$1" width="$2" height="$3" frameborder="0"></iframe>',
		'default_ratio' => 512 / 394,
		'https_enabled'	=> true
	),
	'bing' => array(
		'embed'			=> '<iframe src="//hub.video.msn.com/embed/$1" width="$2" height="$3" frameborder="0" scrolling="no" noscroll></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'dailymotion' => array(
		'embed'			=> '<iframe src="//www.dailymotion.com/embed/video/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'divshare' => array(
		'embed'			=> '<iframe src="//www.divshare.com/flash/video2?myId=$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'funnyordie' => array(
		'embed'			=> '<iframe src="http://www.funnyordie.com/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 640 / 390,
		'https_enabled'	=> false
	),
	'kickstarter' => array(
		'embed'			=> '<iframe src="$1/widget/video.html" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'metacafe' => array(
		'embed'			=> '<iframe src="http://www.metacafe.com/embed/$1/" width="$2" height="$3" frameborder="0" allowFullScreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> false
	),
	'msn' => array(
		'embed'			=> '<iframe src="//hub.video.msn.com/embed/$1" width="$2" height="$3" frameborder="0" scrolling="no" noscroll></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'rutube' => array(
		'embed'			=> '<iframe src="//rutube.ru/play/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'screen9' => array(
		'embed' 		=> 't',
		'default_ratio' => 579 / 358
	),
	'teachertube' => array(
		'embed'			=> '<iframe src="http://www.teachertube.com/embed/video/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 640 / 370,
		'https_enabled'	=> false
	),
	'yahoo' => array(
		'embed'			=> '<iframe src="http://d.yimg.com/nl/vyc/site/player.html#vid=$1" width="$2" height="$3" frameborder="0"></iframe>'
	),
	'yandex' => array(
		'embed'			=> '$5'
	),
	'yandexvideo' => array(
		'embed'			=> '$5'
	),
	'youtube' => array(
		'embed'			=> '<iframe src="//www.youtube.com/embed/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'youtubeplaylist' => array(
		'embed'			=> '<iframe src="//www.youtube.com/embed/videoseries?list=$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	),
	'videomaten' => array(
		'embed'			=> '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="$2" height="$3" id="videomat" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="http://89.160.51.62/recordMe/play.swf?id=$1" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="http://89.160.51.62/recordMe/play.swf?id=$1" loop="false" quality="high" bgcolor="#ffffff" width="$2" height="$3" name="videomat" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>',
		'default_ratio'	=> 300 / 200,
		'https_enabled'	=> false
	),
	'twitchchannel' => array(
		'embed'			=> '<object id="live_embed_player_flash" type="application/x-shockwave-flash" width="$2" height="$3" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=$1">
	<param name="allowFullScreen" value="true" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowNetworking" value="all" />
	<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
	<param name="flashvars" value="hostname=www.twitch.tv&channel=$1&auto_play=false&start_volume=100" />
</object>',
		'default_width'	=> 640,
		'default_ratio'	=> 620 / 378,
		'https_enabled'	=> false
	),
	'twitchvod' => array(
		'embed'			=> '<object id="clip_embed_player_flash" type="application/x-shockwave-flash" width="$2" height="$3" data="http://www.twitch.tv/widgets/archive_embed_player.swf" bgcolor="#000000">
	<param name="movie" value="http://www.twitch.tv/widgets/archive_embed_player.swf" />
	<param name="allowScriptAccess" value="always" />
	<param name="allowNetworking" value="all" />
	<param name="allowFullScreen" value="true" />
	<param name="flashvars" value="channel=$1&amp;auto_play=false&amp;start_volume=100&amp;chapter_id=$4" />
</object>',
		'default_width'	=> 640,
		'default_ratio'	=> 620 / 378,
		'https_enabled'	=> false
	),
	'vimeo' => array(
		'embed'			=> '<iframe src="//player.vimeo.com/video/$1" width="$2" height="$3" frameborder="0" allowfullscreen="true"></iframe>',
		'default_width'	=> 640,
		'default_ratio'	=> 16 / 9,
		'https_enabled'	=> true
	)
);
?>