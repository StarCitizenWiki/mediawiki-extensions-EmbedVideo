const youtube = function(url) {
	return oembed('https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=' + url);
};

const vimeo = function(url) {
	return oembed('https://vimeo.com/api/oembed.json?url=https://vimeo.com/' + url);
};

const spotifyalbum = function(url) {
	return oembed('https://open.spotify.com/oembed?url=https://open.spotify.com/album/' + url);
};

const spotifyartist = function(url) {
	return oembed('https://open.spotify.com/oembed?url=https://open.spotify.com/artist/' + url);
};

const spotifytrack = function(url) {
	return oembed('https://open.spotify.com/oembed?url=https://open.spotify.com/track/' + url);
};

const soundcloud = function(url) {
	const queryParser = require('./queryKeyParser.js').queryKeyParser;

	url = queryParser(url, ['url']);
	return oembed('https://soundcloud.com/oembed' + url);
};

const navertv = function(url) {
	return oembed('https://tv.naver.com/oembed?url=' + url);
};

const kakaotv = function(url) {
	return oembed('https://tv.kakao.com/oembed?url=' + url);
};

const loom = function(url) {
	return oembed('https://www.loom.com/v1/oembed?url=https://www.loom.com/share/' + url);
};

const oembed = function(url) {
	return fetch(url, {
		credentials: "omit",
		cache: "force-cache"
	})
		.then(result => {
			return result.json();
		})
		.then(json => {
			return {
				title: json.title ?? null,
				thumbnail: json.thumbnail_url ?? null,
				duration: json.duration ?? null,
			};
		})
		.catch(error => {
			return {
				title: null,
				thumbnail: null,
				duration: null,
			};
		});
};

module.exports = {
	navertv,
	kakaotv,
	loom,
	youtube,
	vimeo,
	spotifyalbum,
	spotifyartist,
	spotifytrack,
	soundcloud,
	oembed,
};