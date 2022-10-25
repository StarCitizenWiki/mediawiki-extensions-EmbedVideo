const fetcher = function (url) {
	const baseUrl = 'https://api.bilibili.com/x/web-interface/view';
	const defaultData = {
		title: null,
		thumbnail: null,
		duration: null,
	};

	const queryParser = require('./queryKeyParser.js').queryKeyParser;

	url = queryParser(url, ['aid', 'bvid']);
	return fetch(`${baseUrl}${url}`, {
		credentials: "omit",
		cache: "force-cache"
	})
		.then(result => {
			return result.json();
		})
		.then(json => {
			if (typeof json.data === 'undefined') {
				return defaultData;
			}

			return {
				title: json.data.title ?? null,
				thumbnail: json.data.pic ?? null,
				duration: json.data.duration ?? null,
			};
		})
		.catch(error => {
			return defaultData;
		});
};

module.exports = { fetcher };