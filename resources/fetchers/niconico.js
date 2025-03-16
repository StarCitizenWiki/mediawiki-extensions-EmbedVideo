const fetcher = function (url) {
	const baseUrl = 'https://ext.nicovideo.jp/api/getthumbinfo/';
	const defaultData = {
		title: null,
		thumbnail: null,
		duration: null,
	};

	return fetch(`${baseUrl}${url}`, {
		credentials: "omit",
		cache: "force-cache",
	})
		.then(result => {
			return result.text();
		})
		.then(text => {
			if (typeof text === 'undefined') {
				return defaultData;
			}

			const titleMatcher = new RegExp(/<title>(.*)<\/title>/);
			const thumbMatcher = new RegExp(/<thumbnail_url>(.*)<\/thumbnail_url>/);
			const durationMatcher = new RegExp(/<length>(.*)<\/length>/);

			return {
				title: titleMatcher.match(text) ? titleMatcher.match(text)[1] : null,
				thumbnail: thumbMatcher.match(text) ? thumbMatcher.match(text)[1] : null,
				duration: durationMatcher.match(text) ? durationMatcher.match(text)[1] : null,
			};
		})
		.catch(error => {
			return defaultData;
		});
};

module.exports = { fetcher };