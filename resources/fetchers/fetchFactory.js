const fetchFactory = function (service) {
	const oEmbedFetchers = require('./oembed.js');

	let fetcher = null;
	let urlManipulation = true;

	switch( service ) {
		case 'archiveorg':
			break;
		// Bilibili is missing CORS headers
		case 'bilibili':
			// urlManipulation = false;
			// fetcher = bilibili.fetcher;
			break;
		// Bilibili is missing CORS headers
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
		case 'spotifytrack':
			fetcher = oEmbedFetchers.spotifytrack;
			break;
		case 'vimeo':
			fetcher = oEmbedFetchers.vimeo;
			break;
		case 'youtube':
		case 'youtubevideolist':
		case 'youtubeplaylist':
			fetcher = oEmbedFetchers.youtube;
			break;
	}

	return {
		fetcher,
		urlManipulation
	}
};

module.exports = { fetchFactory };