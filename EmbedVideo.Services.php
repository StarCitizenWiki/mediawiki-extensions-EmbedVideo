<?php

# Build services list (may be augmented in LocalSettings.php)
$wgEmbedVideoServiceList = array(
    'dailymotion' => array(
        'url' => 'http://www.dailymotion.com/swf/$1'
    ),
    'divshare' => array(
        'url' => 'http://www.divshare.com/flash/video2?myId=$1',
    ),
    'funnyordie' => array(
        'url' =>
            'http://www.funnyordie.com/v1/flvideo/fodplayer.swf?file='.
            'http://funnyordie.vo.llnwd.net/o16/$1.flv&autoStart=false'
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
    'revver' => array(
        'url' => 'http://flash.revver.com/player/1.0/player.swf?mediaId=$1'
    ),
    'sevenload' => array(
        'url' => 'http://page.sevenload.com/swf/en_GB/player.swf?id=$1'
    ),
    'teachertube' => array(
        'extern' =>
            '<embed src="http://www.teachertube.com/embed/player.swf" ' .
                'width="470" ' .
                'height="275" ' .
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
    'youtube' => array(
        'url'=>'http://www.youtube.com/v/$1'
    ),
    'youtubehd' => array(
        'url'=>'http://www.youtube.com/v/$1&ap=%2526fmt%3D22',
        'default_width'=>720,
        'default_ratio'=>16/9
    )

/* Edutopia:
<object width="400" height="292"> <param value="flvPath=http://www.edutopia.org/media//.flv&pPath=http://www.edutopia.org/media//.jpg" name="FlashVars"/> <param value="best" name="quality"/> <param value="false" name="play"/> <param value="http://www.edutopia.org/media/videofalse.swf" name="movie"/> <embed id="video_embed" width="400" height="292" type="application/x-shockwave-flash" src="http://www.edutopia.org/media/videofalse.swf" play="false" pluginspage="http://www.macromedia.com/go/getflashplayer" name="video" quality="best" flashvars="flvPath=http://www.edutopia.org/media//.flv&pPath=http://www.edutopia.org/media//.jpg"/> </object>
*/
);
