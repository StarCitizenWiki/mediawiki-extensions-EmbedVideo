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
	'version'			=> '2.0',
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
$wgAutoloadClasses['EmbedVideoHooks']			= "{$extDir}/EmbedVideo.services.php";
$wgAutoloadClasses['Screen9IdParser']			= "{$extDir}/Screen9IdParser.php";

$wgHooks['ParserFirstCallInit'][]				= 'EmbedVideoHooks::onParserFirstCallInit';

//The services file is separate due to its large size.
require_once($extDir."/EmbedVideo.services.php");
?>
