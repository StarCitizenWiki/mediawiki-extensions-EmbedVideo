const fetchFactory = function (service) {
	const oEmbedFetchers = require('./oembed.js');
	const bilibili = require('./bilibili.js');

	let fetcher = oEmbedFetchers.oembed;
	let urlManipulation = true;

	switch( service ) {
		case 'bilibili':
			urlManipulation = false;
			fetcher = bilibili.fetcher;
			break;
		case 'niconico':
			// Not Oembed
			// The official API is in XML sadly
			//callUrl = 'https://ext.nicovideo.jp/api/getthumbinfo/';
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