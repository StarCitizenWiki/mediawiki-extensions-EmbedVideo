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

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'EmbedVideo' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['EmbedVideo'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['EmbedVideoMagic']	= __DIR__ . '/EmbedVideo.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for EmbedVideo extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the EmbedVideo extension requires MediaWiki 1.25+' );
}