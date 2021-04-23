(function () {
	const fetchThumb = async (url, parent, outerDiv) => {
		let callUrl;
		if (outerDiv.classList.contains('youtube')) {
			callUrl = 'https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=';
		} else if(outerDiv.classList.contains('vimeo')) {
			callUrl = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/'
		} else {
			return;
		}

		if (url.substr(0, 1) === '/') {
			url = 'http:' + url;
		}

		let id;
		try {
			url = (new URL(url.split('?').shift())).pathname;
		} catch (e) {

		}

		id = url.split('/').pop();
		if (id === '') {
			return;
		}

		if (id.substr(-1) === '?') {
			id = id.substr(0, id.length - 1)
		}

		await fetch(callUrl + id, {
			credentials: "omit",
			cache: "force-cache"
		})
			.then(result => {
				return result.json();
			})
			.then(json => {
				if (typeof json.thumbnail_url === 'undefined') {
					return;
				}

				const image = document.createElement('img');
				image.src = json.thumbnail_url;
				image.setAttribute('loading', 'lazy');
				image.classList.add('embedvideo-consent__thumbnail');
				parent.appendChild(image);
			})
			.catch(error => {
				//
			})
	}

	document.querySelectorAll('.embedvideowrap').forEach(function (div) {
		const clickListener = function (event) {
			const iframe = div.querySelector('iframe');

			if (iframe !== null) {
				iframe.src = iframe.dataset.src ?? '';
			}

			event.target.removeEventListener('click', clickListener);
			div.removeChild(consentDiv);
		};

		const consentDiv = div.querySelector('.embedvideo-consent__overlay');

		if (consentDiv === null) {
			return;
		}

		consentDiv.addEventListener('click', clickListener);

		fetchThumb(div.querySelector('iframe').dataset.src, consentDiv, div);
	})
})();
