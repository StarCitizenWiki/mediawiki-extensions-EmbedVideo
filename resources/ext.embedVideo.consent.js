(function () {
	const fetchThumb = async (url, parent, outerDiv) => {
		let callUrl;
		if (outerDiv.classList.contains('youtube') || outerDiv.classList.contains('youtubevideolist') || outerDiv.classList.contains('youtubeplaylist')) {
			callUrl = 'https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=';
		} else if(outerDiv.classList.contains('vimeo')) {
			callUrl = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/'
		} else if(outerDiv.classList.contains('spotifytrack')) {
			// does not work callUrl = 'https://open.spotify.com/oembed?url=https://open.spotify.com/track/'
			return;
		} else {
			return;
		}

		// Some url manipulation foo which tries to get the id of the requested video
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

		// Do the actual fetch
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

				if (typeof json.title !== 'undefined' && json.title.length > 0) {
					const title = document.createElement('small');
					title.innerText = json.title;
					parent.querySelector('span').appendChild(title);
				}
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

		if (!div.classList.contains('no-fetch')) {
			fetchThumb(div.querySelector('iframe').dataset.src, consentDiv, div);
		}
	})
})();
