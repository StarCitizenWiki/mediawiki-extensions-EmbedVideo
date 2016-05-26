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

if ( !defined( 'MEDIAWIKI' ) ) {
	exit;
}

/******************************************/
/* Credits                                */
/******************************************/
define( 'EV_VERSION', '2.2.9' );

$wgExtensionCredits['parserhook'][] = [
	'path' => __FILE__,
	'name' => 'EmbedVideo',
	'author' => [
		'Jim R. Wilson',
		'Andrew Whitworth',
		'Alexia E. Smith',
		'...'
		],
	'url' => 'https://www.mediawiki.org/wiki/Extension:EmbedVideo',
	'version' => EV_VERSION,
	'descriptionmsg' => 'embedvideo_description',
	'license-name' => 'MIT'
];

/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$wgExtensionMessagesFiles['EmbedVideo'] = __DIR__ . '/EmbedVideo.i18n.php';
$wgExtensionMessagesFiles['EmbedVideoMagic'] = __DIR__ . '/EmbedVideo.i18n.magic.php';
$wgMessagesDirs['EmbedVideo'] = __DIR__ . '/i18n';

$wgAutoloadClasses['EmbedVideoHooks'] = __DIR__ . '/EmbedVideo.hooks.php';
$wgAutoloadClasses['EmbedVideo\VideoService'] = __DIR__ . '/classes/VideoService.php';
$wgAutoloadClasses['EmbedVideo\OEmbed'] = __DIR__ . '/classes/OEmbed.php';

$wgHooks['ParserFirstCallInit'][] = 'EmbedVideoHooks::onParserFirstCallInit';

$wgResourceModules['ext.embedVideo'] = [
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'EmbedVideo',
	'styles' => ['css/embedvideo.css'],
	'position' => 'top'
];

if ( !isset( $wgEmbedVideoDefaultWidth ) && ( isset( $_SERVER['HTTP_X_MOBILE'] ) && $_SERVER['HTTP_X_MOBILE'] == 'true' ) && $_COOKIE['stopMobileRedirect'] != 1 ) {
	// Set a smaller default width when in mobile view.
	$wgEmbedVideoDefaultWidth = 320;
}
