<?php
/**
 * Wrapper class for encapsulating EmbedVideo related parser methods
 */
class EmbedVideo
{
    protected $initialized = false;

    /**
     * Sets up parser functions.
     */
    function setup()
    {
        # Setup parser hooks. ev is the primary hook, evp is supported for
        # legacy purposes
        global $wgParser, $wgVersion;
        $hookEV =  version_compare($wgVersion, '1.7', '<') ? '#ev' : 'ev';
        $hookEVP = version_compare($wgVersion, '1.7', '<') ? '#evp' : 'evp';
        $wgParser->setFunctionHook($hookEV, array($this, 'parserFunction_ev'));
        $wgParser->setFunctionHook($hookEVP, array($this, 'parserFunction_evp'));
    }

    /**
     * Adds magic words for parser functions.
     * @param Array $magicWords
     * @param $langCode
     * @return Boolean Always true
     */
    function parserFunctionMagic(&$magicWords, $langCode='en')
    {
        $magicWords['ev'] = array(0, 'ev');
        $magicWords['evp'] = array(0, 'evp');
        return true;
    }

    /**
     * Embeds video of the chosen service, legacy support for 'evp' version of
     * the tag
     * @param Parser $parser Instance of running Parser.
     * @param String $service Which online service has the video.
     * @param String $id Identifier of the chosen service
     * @param String $width Width of video (optional)
     * @return String Encoded representation of input params (to be processed later)
     */
    function parserFunction($parser, $service = null, $id = null, $desc = null,
        $align = null, $width = null)
    {
        # TODO: Support the other options
        return $this->parserFunction_ev($parser, $service, $id, $width, $desc, $align);
    }

    /**
     * Embeds video of the chosen service
     * @param Parser $parser Instance of running Parser.
     * @param String $service Which online service has the video.
     * @param String $id Identifier of the chosen service
     * @param String $width Width of video (optional)
     * @param String $desc description to show (optional, unused)
     * @param String $align alignment of the video (optional, unused)
     * @return String Encoded representation of input params (to be processed later)
     */
    function parserFunction_ev($parser, $service = null, $id = null, $width = null,
        $desc = null, $align = null)
    {
        global $wgScriptPath;

        # Initialize things once
        if (!$this->initialized) {
            $this->VerifyWidthMinAndMax();
            # Add system messages
            wfLoadExtensionMessages('embedvideo');
            $this->initialized = true;
        }

        # Sanitize and prepare all parameters
        if ($service === null || $id === null)
            return '<div class="errorbox">' . wfMsg('embedvideo-missing-params') . '</div>';
        $service = trim($service);
        $id = trim($id);
        if ($width === null)
            $width = 425;
        else if(!$this->WidthIsOk($width)) {
            $msg = wfMsgForContent('embedvideo-illegal-width', @htmlspecialchars($params['width']));
            return '<div class="errorbox">' . $msg . '</div>';
        }
        $ratio = 425 / 350;
        $height = round($width / $ratio);
        if ($desc !== null)
            $desc = "<div class=\"thumbcaption\">$desc</div>";
        else
            $desc = "";
        if ($align !== null)
            $align = "float: " . trim($align) . ";";
        else
            $align = "";

        # Get the entry in the list of services
        global $wgEmbedVideoServiceList;
        $entry = $wgEmbedVideoServiceList[$service];
        if (!$entry) {
            $msg = wfMsg('embedvideo-unrecognized-service', @htmlspecialchars($params['service']));
            return '<div class="errorbox">' . $msg . '</div>';
        }

        # If the service has an ID pattern specified, verify the id number
        $idhtml = htmlspecialchars($id);
        $idpattern = (isset($entry['id_pattern']) ? $entry['id_pattern'] : '%[^A-Za-z0-9_\\-]%');
        if ($idhtml == null || preg_match($idpattern, $idhtml)) {
            $msg = wfMsgForContent('embedvideo-bad-id', $idhtml, @htmlspecialchars($service));
            return '<div class="errorbox">' . $msg . '</div>';
        }

        # if the service has it's own custom extern declaration, use that instead
        $clause = $entry['extern'];
        if (isset($clause)) {
            $parser->disableCache();
            $path = $wgScriptPath . "/extensions/EmbedVideo";
            $clause = wfMsgReplaceArgs($clause, array($path, $id));
            $clause = <<<EOT
<div style="{$align}">
{$clause}
{$desc}
</div>
EOT;
            return array($clause, 'noparse' => true, 'isHTML' => true);
        }

        # Build URL and output embedded flash object
        $url = wfMsgReplaceArgs($entry['url'], array($id, $width, $height));
        $clause = <<<EOC
<div style="{$align}">
    <object width="{$width}" height="{$height}">
        <param name="movie" value="{$url}"></param>
        <param name="wmode" value="transparent"></param>
        <embed src="{$url}" type="application/x-shockwave-flash"
            wmode="transparent" width="{$width}" height="{$height}">
        </embed>
    </object>
    {$desc}
</div>
EOC;
        return $parser->insertStripItem(
            $clause,
            $parser->mStripState
        );
    }

    function WidthIsOk($width)
    {
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
