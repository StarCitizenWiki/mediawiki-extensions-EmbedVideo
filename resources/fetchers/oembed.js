const youtube = function(url) {
	return oembed(`https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=${url}`);
}

const vimeo = function(url) {
	return oembed(`https://vimeo.com/api/oembed.json?url=https://vimeo.com/${url}`);
}

const spotifyalbum = function(url) {
	return oembed(`https://open.spotify.com/oembed?url=https://open.spotify.com/album/${url}`);
}

const spotifyartist = function(url) {
	return oembed(`https://open.spotify.com/oembed?url=https://open.spotify.com/artist/${url}`);
}

const spotifytrack = function(url) {
	return oembed(`https://open.spotify.com/oembed?url=https://open.spotify.com/track/${url}`);
}

const soundcloud = function(url) {
	const queryParser = require('./queryKeyParser.js');

	url = queryParser(url, ['url']);
	return oembed(`https://soundcloud.com/oembed${url}`);
}

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
}
