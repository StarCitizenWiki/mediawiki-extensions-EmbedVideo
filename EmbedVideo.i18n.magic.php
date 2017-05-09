<?php
/**
 * EmbedVideo
 * EmbedVideo Magic Words
 *
 * @license		MIT
 * @package		EmbedVideo
 * @link		https://www.mediawiki.org/wiki/Extension:EmbedVideo
 *
 **/

$magicWords = [];

$magicWords['en']  = [
	'ev'		=> [0, 'ev'],
	'evp'		=> [0, 'evp'],
	'evt'		=> [0, 'evt'],
	'evl'		=> [0, 'evl'],
	'vlink'		=> [0, 'vlink'],
	'evu'		=> [0, 'evu'],
	'ev_start'	=> [0, 'start=$1'],
	'ev_end'	=> [0, 'end=$1'],
];

foreach( \EmbedVideo\VideoService::getAvailableServices() as $service ) {
	if ( !isset($magicWords['en'][$service]) ) {
		$magicWords['en'][$service] = [0, $service];
	}
}