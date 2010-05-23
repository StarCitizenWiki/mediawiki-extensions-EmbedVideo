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
    function parserFunction_evp($parser, $service = null, $id = null, $desc = null,
        $align = null, $width = null)
    {
        return $this->parserFunction_ev($parser, $service, $id, $width, $align, $desc);
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
        $align = null, $desc = null)
    {
        global $wgScriptPath;

        # Initialize things once
        if (!$this->initialized) {
            $this->VerifyWidthMinAndMax();
            # Add system messages
            wfLoadExtensionMessages('embedvideo');
            $this->initialized = true;
        }

        # Get the name of the host
        if ($service === null || $id === null)
            return $this->errMissingParams($service, $id);

        $service = trim($service);
        $id = trim($id);

        $entry = $this->getServiceEntry($service);
        if (!$entry)
            return $this->errBadService($service);

        if (!$this->sanitizeWidth($entry, $width))
            return $this->errBadWidth($width);
        $height = $this->getHeight($entry, $width);

        $hasalign = ($align !== null);
        if ($hasalign)
            $desc = $this->getDescriptionMarkup($desc);

        # If the service has an ID pattern specified, verify the id number
        if (!$this->verifyID($entry, $id))
            return $this->errBadID($service, $id);

        # if the service has it's own custom extern declaration, use that instead
        $clause = $entry['extern'];
        if (isset($clause)) {
            $parser->disableCache();
            $clause = wfMsgReplaceArgs($clause, array($wgScriptPath, $id, $width, $height));
            if ($hasalign)
                $clause = $this->generateAlignExternClause($clause, $align, $desc, $width, $height);
            return array($clause, 'noparse' => true, 'isHTML' => true);
        }

        # Build URL and output embedded flash object
        $url = wfMsgReplaceArgs($entry['url'], array($id, $width, $height));
        $clause = "";
        if ($hasalign)
            $clause = $this->generateAlignClause($url, $width, $height, $align, $desc);
        else
            $clause = $this->generateNormalClause($url, $width, $height);
        return array($clause, 'noparse' => true, 'isHTML' => true);
    }

    # Return the HTML necessary to embed the video normally.
    function generateNormalClause($url, $width, $height)
    {
        $clause = "<object width=\"{$width}\" height=\"{$height}\">" .
            "<param name=\"movie\" value=\"{$url}\"></param>" .
            "<param name=\"wmode\" value=\"transparent\"></param>" .
            "<embed src=\"{$url}\" type=\"application/x-shockwave-flash\"" .
            " wmode=\"transparent\" width=\"{$width}\" height=\"{$height}\">" .
            "</embed></object>";
        return $clause;
    }

    # The HTML necessary to embed the video with a custom embedding clause,
    # specified align and description text
    function generateAlignExternClause($clause, $align, $desc, $width, $height)
    {
        $clause = "<div class=\"thumb t{$align}\">" .
            "<div class=\"thumbinner\" style=\"width: {$width}px;\">" .
            $clause .
            "<div class=\"thumbcaption\">" .
            $desc .
            "</div></div></div>";
        return $clause;
    }

    # Generate the HTML necessary to embed the video with the given alignment
    # and text description
    function generateAlignClause($url, $width, $height, $align, $desc)
    {
        $clause = "<div class=\"thumb t{$align}\">" .
            "<div class=\"thumbinner\" style=\"width: {$width}px;\">" .
            "<object width=\"{$width}\" height=\"{$height}\">" .
            "<param name=\"movie\" value=\"{$url}\"></param>" .
            "<param name=\"wmode\" value=\"transparent\"></param>" .
            "<embed src=\"{$url}\" type=\"application/x-shockwave-flash\"" .
            " wmode=\"transparent\" width=\"{$width}\" height=\"{$height}\"></embed>" .
            "</object>" .
            "<div class=\"thumbcaption\">" .
            $desc .
            "</div></div></div>";
        return $clause;
    }

    # Get the entry for the specified service, by name
    function getServiceEntry($service)
    {
        # Get the entry in the list of services
        global $wgEmbedVideoServiceList;
        return $wgEmbedVideoServiceList[$service];
    }

    # Get the width. If there is no width specified, try to find a default
    # width value for the service. If that isn't set, default to 425.
    # If a width value is provided, verify that it is numerical and that it
    # falls between the specified min and max size values. Return true if
    # the width is suitable, false otherwise.
    function sanitizeWidth($entry, &$width)
    {
        global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
        if ($width === null) {
            if (isset($entry['default_width']))
                $width = $entry['default_width'];
            else
                $width = 425;
            return true;
        }
        if (!is_numeric($width))
            return false;
        return $width >= $wgEmbedVideoMinWidth && $width <= $wgEmbedVideoMaxWidth;
    }

    # Calculate the height from the given width. The default ratio is 450/350,
    # but that may be overridden for some sites.
    function getHeight($entry, $width)
    {
        $ratio = 425 / 350;
        if (isset($entry['default_ratio']))
            $ratio = $entry['default_ratio'];
        return round($width / $ratio);
    }

    # If we have a textual description, get the markup necessary to display
    # it on the page.
    function getDescriptionMarkup($desc)
    {
        if ($desc !== null)
            return "<div class=\"thumbcaption\">$desc</div>";
        return "";
    }

    # Verify the id number of the video, if a pattern is provided.
    function verifyID($entry, $id)
    {
        $idhtml = htmlspecialchars($id);
        //$idpattern = (isset($entry['id_pattern']) ? $entry['id_pattern'] : '%[^A-Za-z0-9_\\-]%');
        //if ($idhtml == null || preg_match($idpattern, $idhtml)) {
        return ($idhtml != null);
    }

    # Get an error message for the case where the ID value is bad
    function errBadID($service, $id)
    {
        $idhtml = htmlspecialchars($id);
        $msg = wfMsgForContent('embedvideo-bad-id', $idhtml, @htmlspecialchars($service));
        return '<div class="errorbox">' . $msg . '</div>';
    }

    # Get an error message if the width is bad
    function errBadWidth($width)
    {
        $msg = wfMsgForContent('embedvideo-illegal-width', @htmlspecialchars($width));
        return '<div class="errorbox">' . $msg . '</div>';
    }

    # Get an error message if there are missing parameters
    function errMissingParams($service, $id)
    {
        return '<div class="errorbox">' . wfMsg('embedvideo-missing-params') . '</div>';
    }

    # Get an error message if the service name is bad
    function errBadService($service)
    {
        $msg = wfMsg('embedvideo-unrecognized-service', @htmlspecialchars($service));
        return '<div class="errorbox">' . $msg . '</div>';
    }

    # Verify that the min and max values for width are sane.
    function VerifyWidthMinAndMax()
    {
        global $wgEmbedVideoMinWidth, $wgEmbedVideoMaxWidth;
        if (!is_numeric($wgEmbedVideoMinWidth) || $wgEmbedVideoMinWidth < 100)
            $wgEmbedVideoMinWidth = 100;
        if (!is_numeric($wgEmbedVideoMaxWidth) || $wgEmbedVideoMaxWidth > 1024)
            $wgEmbedVideoMaxWidth = 1024;
    }
}
