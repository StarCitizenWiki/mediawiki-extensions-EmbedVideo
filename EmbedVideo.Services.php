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
        #'extern' =>
        #'<embed src="http://www.teachertube.com/embed/player.swf" ' .
        #'   width="470" ' .
        #'   height="275" ' .
        #'   bgcolor="undefined" ' .
        #'   allowscriptaccess="always" ' .
        #'   allowfullscreen="true" ' .
        #'   flashvars="file=http://www.teachertube.com/embedFLV.php?pg=video_$2' .
        #' menu=false frontcolor=ffffff lightcolor=FF0000' .
        #' logo=http://www.teachertube.com/www3/images/greylogo.swf' .
        #' skin=http://www.teachertube.com/embed/overlay.swf volume=80' .
        #' controlbar=over displayclick=link' .
        #' viral.link=http://www.teachertube.com/viewVideo.php?video_id=$2' .
        #' stretching=exactfit plugins=viral-2 viral.callout=none viral.onpause=false"' .
        #'/>'
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
