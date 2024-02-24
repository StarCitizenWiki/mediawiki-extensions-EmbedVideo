const fetchFactory = function (service) {
	const oEmbedFetchers = require('./oembed.js');

	let fetcher = null;
	let urlManipulation = true;

	switch( service ) {
		case 'archiveorg':
			break;
		// Bilibili is missing CORS headers
		case 'bilibili':
			//fetcher = require('./bilibili.js').fetcher;
			break;
		case 'ccc':
			fetcher = oEmbedFetchers.ccc;
			break;
		case 'loom':
			fetcher = oEmbedFetchers.loom;
			break;
		// Niconico is missing CORS headers
		case 'niconico':
			//fetcher = require('./niconico.js').fetcher;
			break;
		case 'soundcloud':
			urlManipulation = false;
			fetcher = oEmbedFetchers.soundcloud;
			break;
		case 'spotifyalbum':
			fetcher = oEmbedFetchers.spotifyalbum;
			break;
		case 'spotifyartist':
			fetcher = oEmbedFetchers.spotifyartist;
			break;
		case 'spotify':
		case 'spotifytrack':
			fetcher = oEmbedFetchers.spotifytrack;
			break;
		case 'spotifyshow':
			fetcher = oEmbedFetchers.spotifyshow;
			break;
		case 'spotifyepisode':
			fetcher = oEmbedFetchers.spotifyepisode;
			break;
		case 'vimeo':
			fetcher = oEmbedFetchers.vimeo;
			break;
		case 'wistia':
			fetcher = oEmbedFetchers.wistia;
			break;
		case 'youtube':
		case 'youtubevideolist':
		case 'youtubeplaylist':
			fetcher = oEmbedFetchers.youtube;
			break;

		// Missing CORS
		case 'navertv':
			//urlManipulation=false;
			//fetcher = oEmbedFetchers.navertv;
			break;
		// Missing CORS
		case 'kakaotv':
			//urlManipulation=false;
			//fetcher = oEmbedFetchers.kakaotv;
			break;
	}

	return {
		fetcher,
		urlManipulation
	}
};

module.exports = { fetchFactory };