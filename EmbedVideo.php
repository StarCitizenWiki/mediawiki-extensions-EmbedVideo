<?php
/*
 * EmbedVideo.php - Adds a parser function embedding video from popular sources.
 * See README for details. For licensing information, see LICENSE. For a
 * complete list of contributors, see CREDITS
 */

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
       echo <<<EOT
To install EmbedVideo, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/EmbedVideo/EmbedVido.php" );
EOT;
    exit( 1 );
}

# Credits
$wgExtensionCredits['parserhook'][] = array(
    'name' => 'EmbedVideo',
    'author' => 'Jim R. Wilson and Andrew Whitworth',
    'url' => 'http://www.mediawiki.org/wiki/Extension:EmbedVideo',
    'description' => 'Adds a parser function embedding video from popular sources.',
    'version' => '0.1.2'
);

$dir = dirname(__FILE__) . '/';
require_once($dir . "EmbedVideo_body.php");
require_once($dir . "EmbedVideo.Services.php");


/**
 * Wrapper function for language magic call (hack for 1.6
 */

# Create global instance and wire it up!
$wgEmbedVideo = new EmbedVideo();
$wgExtensionFunctions[] = array($wgEmbedVideo, 'setup');
if (version_compare($wgVersion, '1.7', '<')) {
    # Hack solution to resolve 1.6 array parameter nullification for hook args
    function wfEmbedVideoLanguageGetMagic( &$magicWords ) {
        global $wgEmbedVideo;
        $wgEmbedVideo->parserFunctionMagic( $magicWords );
        return true;
    }
    $wgHooks['LanguageGetMagic'][] = 'wfEmbedVideoLanguageGetMagic';
} else {
    $wgHooks['LanguageGetMagic'][] = array($wgEmbedVideo, 'parserFunctionMagic');
}
