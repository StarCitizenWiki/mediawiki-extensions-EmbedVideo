<?php

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
		'extern' => '<iframe style="overflow: hidden;" src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'bingvideo' => array(
		'extern' => '<iframe style="overflow: hidden;" src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'dailymotion' => array(
		'url' => 'http://www.dailymotion.com/swf/$1'
	),
	'divshare' => array(
		'url' => 'http://www.divshare.com/flash/video2?myId=$1',
	),
	'edutopia' => array(
		'extern' =>
			'<object id="flashObj" width="$3" height="$4">' .
				'<param name="movie" value="http://c.brightcove.com/services/viewer/federated_f9?isVid=1&isUI=1" />' .
				'<param name="flashVars" value="videoId=$2&playerID=85476225001&domain=embed&dynamicStreaming=true" />' .
				'<param name="base" value="http://admin.brightcove.com" />' .
				'<param name="allowScriptAccess" value="always" />' .
				'<embed src="http://c.brightcove.com/services/viewer/federated_f9?isVid=1&isUI=1" ' .
					'flashVars="videoId=$2&playerID=85476225001&domain=embed&dynamicStreaming=true" '.
					'base="http://admin.brightcove.com" name="flashObj" width="$3" height="$4" '.
					'seamlesstabbing="false" type="application/x-shockwave-flash" allowFullScreen="true" ' .
					'allowScriptAccess="always" swLiveConnect="true" ' .
					'pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash">' .
				'</embed>' .
			'</object>',
		'default_width' => 326,
		'default_ratio' => 326/399,
	),
	'funnyordie' => array(
		'url' =>
			'http://www.funnyordie.com/v1/flvideo/fodplayer.swf?file='.
			'http://funnyordie.vo.llnwd.net/o16/$1.flv&autoStart=false'
	),
    'google' => array(
	'id_pattern'=>'%[^0-9\\-]%',
	'url' => 'http://video.google.com/googleplayer.swf?docId=$1'
    ),
	'googlevideo' => array(
		'id_pattern'=>'%[^0-9\\-]%',
		'url' => 'http://video.google.com/googleplayer.swf?docId=$1'
	),
	'interiavideo' => array(
		'url' => 'http://video.interia.pl/i/players/iVideoPlayer.05.swf?vid=$1',
	),
	'interia' => array(
		'url' => 'http://video.interia.pl/i/players/iVideoPlayer.05.swf?vid=$1',
	),
	'metacafe' => array(
		'url' => 'http://www.metacafe.com/fplayer/$1.swf'
	),
	'msn' => array(
		'extern' => '<iframe style="overflow: hidden;" src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'msnvideo' => array(
		'extern' => '<iframe style="overflow: hidden;" src="http://hub.video.msn.com/embed/$2" width="$3" height="$4" frameborder="0" scrolling="no" noscroll></iframe>'
	),
	'revver' => array(
		'url' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$1'
	),
	'rutube' => array(
		'url' => ''
	),
	'sevenload' => array(
		'url' => 'http://page.sevenload.com/swf/en_GB/player.swf?id=$1'
	),
	'teachertube' => array(
		'extern' =>
			'<embed src="http://www.teachertube.com/embed/player.swf" ' .
				'width="$3" ' .
				'height="$4" ' .
				'bgcolor="undefined" ' .
				'allowscriptaccess="always" ' .
				'allowfullscreen="true" ' .
				'flashvars="file=http://www.teachertube.com/embedFLV.php?pg=video_$2' .
					'&menu=false' .
					'&frontcolor=ffffff&lightcolor=FF0000' .
					'&logo=http://www.teachertube.com/www3/images/greylogo.swf' .
					'&skin=http://www.teachertube.com/embed/overlay.swf volume=80' .
					'&controlbar=over&displayclick=link' .
					'&viral.link=http://www.teachertube.com/viewVideo.php?video_id=$2' .
					'&stretching=exactfit&plugins=viral-2' .
					'&viral.callout=none&viral.onpause=false' .
				'"' .
			'/>',
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
		'extern' =>
			'<iframe src="http://www.youtube.com/embed/$2?showsearch=0&amp;modestbranding=1" ' .
				'width="$3" height="$4" ' .
				'frameborder="0" allowfullscreen="true"></iframe>',
	),
	'youtubehd' => array(
		'extern' =>
			'<iframe src="http://www.youtube.com/embed/$2?showsearch=0&amp;modestbranding=1&amp;hd=1" ' .
				'width="$3" height="$4" ' .
				'frameborder="0" allowfullscreen="true"></iframe>',
		'default_ratio' => 16 / 9
	),
	'youtubeplaylist' => array(
		'extern' =>
			'<iframe src="http://www.youtube.com/embed/videoseries?showsearch=0&amp;modestbranding=1&amp;list=$2" ' .
				'width="$3" height="$4" ' .
				'frameborder="0" allowfullscreen="true"></iframe>',
		'default_ratio' => 16 / 9
	),
	'videomaten' => array(
		'extern' => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="300" height="200" id="videomat" align="middle"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="http://89.160.51.62/recordMe/play.swf?id=$2" /><param name="loop" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="http://89.160.51.62/recordMe/play.swf?id=$2" loop="false" quality="high" bgcolor="#ffffff" width="$3" height="$4" name="videomat" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>',
		'default_ratio' => 300 / 200
	),
	'vimeo' => array(
		'url'=>'http://vimeo.com/moogaloop.swf?clip_id=$1&;server=vimeo.com&fullscreen=0&show_title=1&show_byline=1&show_portrait=0'
	)
);
