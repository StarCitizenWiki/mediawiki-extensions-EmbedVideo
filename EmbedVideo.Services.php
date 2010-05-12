<?php

# Build services list (may be augmented in LocalSettings.php)
$wgEmbedVideoServiceList = array(
    'dailymotion' => array(
        'url' => 'http://www.dailymotion.com/swf/$1'
    ),
    'funnyordie' => array(
        'url' =>
            'http://www.funnyordie.com/v1/flvideo/fodplayer.swf?file='.
            'http://funnyordie.vo.llnwd.net/o16/$1.flv&autoStart=false'
    ),
    'teachertube' => array(
        'extern' => '<iframe width="490" height="295" src="$1/TeacherTube.html?id=$2"></iframe>'
    ),
    'googlevideo' => array(
        'id_pattern'=>'%[^0-9\\-]%',
        'url' => 'http://video.google.com/googleplayer.swf?docId=$1'
    ),
    'sevenload' => array(
        'url' => 'http://page.sevenload.com/swf/en_GB/player.swf?id=$1'
    ),
    'revver' => array(
        'url' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$1'
    ),
    'youtube' => array(
        'url'=>'http://www.youtube.com/v/$1'
    )
);
