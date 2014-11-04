<?php
/**
 * EmbedVideo
 * EmbedVideo Services List
 * Adds a parser function embedding video from popular sources.
 * See README for details. For licensing information, see LICENSE. For a
 * complete list of contributors, see CREDITS
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/

if (!defined('MEDIAWIKI')) {
	exit;
}

/******************************************/
/* Credits                                */
/******************************************/
$wgExtensionCredits['parserhook'][] = array(
	'path'				=> __FILE__,
	'name'				=> 'EmbedVideo',
	'author'			=> array('Jim R. Wilson', 'Andrew Whitworth', 'Alexia E. Smith'),
	'url'				=> 'http://www.mediawiki.org/wiki/Extension:EmbedVideo',
	'version'			=> '2.1.5',
	'descriptionmsg'	=> 'embedvideo_description'
);

/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;

$wgExtensionMessagesFiles['EmbedVideo']			= "{$extDir}/EmbedVideo.i18n.php";
$wgExtensionMessagesFiles['EmbedVideoMagic']	= "{$extDir}/EmbedVideo.i18n.magic.php";
$wgMessagesDirs['EmbedVideo']					= "{$extDir}/i18n";

$wgAutoloadClasses['EmbedVideoHooks']			= "{$extDir}/EmbedVideo.hooks.php";
$wgAutoloadClasses['EmbedVideo\VideoService']	= "{$extDir}/classes/VideoService.php";
$wgAutoloadClasses['EmbedVideo\OEmbed']			= "{$extDir}/classes/OEmbed.php";

$wgHooks['ParserFirstCallInit'][]				= 'EmbedVideoHooks::onParserFirstCallInit';

$wgResourceModules['ext.embedVideo'] = array(
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'EmbedVideo',
	'styles'		=> array('css/embedvideo.css'),
	'position'		=> 'top'
);

if (!isset($wgEmbedVideoDefaultWidth) && $_SERVER['HTTP_X_MOBILE'] == 'true' && $_COOKIE['stopMobileRedirect'] != 1) {
	//Set a smaller default width when in mobile view.
	$wgEmbedVideoDefaultWidth = 320;
}
?>
