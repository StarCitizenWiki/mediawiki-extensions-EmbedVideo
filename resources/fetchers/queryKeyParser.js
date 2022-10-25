const queryKeyParser = function (url, keys) {
	if (typeof keys !== 'object') {
		return url;
	}

	let id;
	keys.every(queryKey => {
		const
			regex = new RegExp( `[?&]${queryKey}=(\\S[^?&]+)` ),
			match = url.match(regex) ? url.match(regex)[1] : null;

		if (match !== null) {
			id = `?${queryKey}=${match}&format=json`;
			return false;
		}
		return true;
	});

	if (typeof id === 'undefined'){
		return url;
	}

	return id;
};

module.exports = { queryKeyParser };