<?php
/**
 * EmbedVideo.php - Adds a parser function embedding video from popular sources.
 * See README for details. For licensing information, see LICENSE. For a
 * complete list of contributors, see CREDITS
 */

# Confirm MW environment
if (!defined('MEDIAWIKI')) {
       echo <<<EOT
To install EmbedVideo, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/EmbedVideo/EmbedVideo.php" );
EOT;
    exit( 1 );
}

# Credits
$wgExtensionCredits['parserhook'][] = array(
	'path'        => __FILE__,
	'name'        => 'EmbedVideo',
	'author'      => array('Jim R. Wilson', 'Andrew Whitworth',),
	'url'         => 'http://www.mediawiki.org/wiki/Extension:EmbedVideo',
	'version'     => '1.0',
	'descriptionmsg' => 'embedvideo-desc'
);
$dir = dirname(__FILE__) . '/';
$wgExtensionMessagesFiles['embedvideo'] = $dir . 'EmbedVideo.i18n.php';
require_once($dir . "EmbedVideo.hooks.php");
require_once($dir . "EmbedVideo.Services.php");

$wgAutoloadClasses['Screen9IdParser'] = dirname( __FILE__ ) . '/Screen9IdParser.php';

$wgHooks['ParserFirstCallInit'][] = "EmbedVideo::setup";
if (version_compare($wgVersion, '1.7', '<')) {
	// Hack solution to resolve 1.6 array parameter nullification for hook args
	function wfEmbedVideoLanguageGetMagic( &$magicWords ) {
		EmbedVideo::parserFunctionMagic( $magicWords );
		return true;
	}
	$wgHooks['LanguageGetMagic'][] = 'wfEmbedVideoLanguageGetMagic';
} else {
	$wgHooks['LanguageGetMagic'][] = 'EmbedVideo::parserFunctionMagic';
}
