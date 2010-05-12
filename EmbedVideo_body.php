<?php
/**
 * Wrapper class for encapsulating EmbedVideo related parser methods
 */
class EmbedVideo {

    /**
     * Sets up parser functions.
     */
    function setup( ) {

        # Setup parser hook
        global $wgParser, $wgVersion;
        $hook = (version_compare($wgVersion, '1.7', '<')?'#ev':'ev');
        $wgParser->setFunctionHook( $hook, array($this, 'parserFunction'));

        # Add system messages
        global $wgMessageCache;
        $wgMessageCache->addMessage('embedvideo-missing-params', 'EmbedVideo is missing a required parameter.');
        $wgMessageCache->addMessage('embedvideo-bad-params', 'EmbedVideo received a bad parameter.');
        $wgMessageCache->addMessage('embedvideo-unparsable-param-string', 'EmbedVideo received the unparsable parameter string "<tt>$1</tt>".');
        $wgMessageCache->addMessage('embedvideo-unrecognized-service', 'EmbedVideo does not recognize the video service "<tt>$1</tt>".');
        $wgMessageCache->addMessage('embedvideo-bad-id', 'EmbedVideo received the bad id "$1" for the service "$2".');
        $wgMessageCache->addMessage('embedvideo-illegal-width', 'EmbedVideo received the illegal width parameter "$1".');
        $wgMessageCache->addMessage('embedvideo-embed-clause',
            '<object width="$2" height="$3">'.
            '<param name="movie" value="$1"></param>'.
            '<param name="wmode" value="transparent"></param>'.
            '<embed src="$1" type="application/x-shockwave-flash" '.
            'wmode="transparent" width="$2" height="$3">'.
            '</embed></object>'
        );
    }

    /**
     * Adds magic words for parser functions.
     * @param Array $magicWords
     * @param $langCode
     * @return Boolean Always true
     */
    function parserFunctionMagic( &$magicWords, $langCode='en' ) {
        $magicWords['ev'] = array( 0, 'ev' );
        return true;
    }

    /**
     * Embeds video of the chosen service
     * @param Parser $parser Instance of running Parser.
     * @param String $service Which online service has the video.
     * @param String $id Identifier of the chosen service
     * @param String $width Width of video (optional)
     * @return String Encoded representation of input params (to be processed later)
     */
    function parserFunction( $parser, $service=null, $id=null, $width=null ) {
        global $wgScriptPath;

        if ($service === null || $id === null)
            return '<div class="errorbox">' . wfMsg('embedvideo-missing-params') . '</div>';

        $params = array(
            'service' => trim($service),
            'id' => trim($id),
            'width' => ($width===null?null:trim($width)),
        );

        $this->VerifyWidthMinAndMax();

        global $wgEmbedVideoServiceList;
        $service = $wgEmbedVideoServiceList[$params['service']];
        if (!$service) {
            $msg = wfMsg('embedvideo-unrecognized-service', @htmlspecialchars($params['service']));
            return '<div class="errorbox">' . $msg . '</div>';
        }

        $id = htmlspecialchars($params['id']);
        $idpattern = ( isset($service['id_pattern']) ? $service['id_pattern'] : '%[^A-Za-z0-9_\\-]%' );
        if ($id == null || preg_match($idpattern, $id)) {
            $msg = wfMsgForContent('embedvideo-bad-id', $id, @htmlspecialchars($params['service']));
            return '<div class="errorbox">' . $msg . '</div>';
        }

        $clause = $service['extern'];
        if (isset($clause)) {
            $parser->disableCache();
            $path = $wgScriptPath . "/extensions/EmbedVideo";
            return array(wfMsgReplaceArgs($clause, array($path, $id)), 'noparse' => true, 'isHTML' => true);
        }

        # Build URL and output embedded flash object
        $ratio = 425 / 350;
        $width = 425;

        if ($params['width'] !== null) {
            if (!$this->WidthIsOk($params['width'])) {
                $msg = wfMsgForContent('embedvideo-illegal-width', @htmlspecialchars($params['width']));
                return '<div class="errorbox">' . $msg . '</div>';
            }
            $width = $params['width'];
        }
        $height = round($width / $ratio);
        $url = wfMsgReplaceArgs($service['url'], array($id, $width, $height));

        return $parser->insertStripItem(
            wfMsgForContent('embedvideo-embed-clause', $url, $width, $height),
            $parser->mStripState
        );
    }

    function WidthIsOk($width) {
        global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
        if (!is_numeric($width))
            return false;
        return $width >= $wgEmbedVideoMinWidth && $width <= $wgEmbedVideoMaxWidth;
    }

    function VerifyWidthMinAndMax()
    {
        global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
        if (!is_numeric($wgEmbedVideoMinWidth) || $wgEmbedVideoMinWidth<100)
            $wgEmbedVideoMinWidth = 100;
        if (!is_numeric($wgEmbedVideoMaxWidth) || $wgEmbedVideoMaxWidth>1024)
            $wgEmbedVideoMaxWidth = 1024;
    }
}
