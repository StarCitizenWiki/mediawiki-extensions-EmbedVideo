(function () {
	const fetchThumb = async (url, parent, outerDiv) => {
		let callUrl;

		switch( outerDiv.getAttribute('data-service') ) {
			case 'spotifyalbum':
				callUrl = 'https://open.spotify.com/oembed?url=https://open.spotify.com/album/';
				break;
			case 'spotifyartist':
				callUrl = 'https://open.spotify.com/oembed?url=https://open.spotify.com/artist/';
				break;
			case 'spotifytrack':
				callUrl = 'https://open.spotify.com/oembed?url=https://open.spotify.com/track/';
				break;
			case 'vimeo':
				callUrl = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/';
				break;
			case 'youtube':
			case 'youtubevideolist':
			case 'youtubeplaylist':
				callUrl = 'https://www.youtube-nocookie.com/oembed?url=https://www.youtube.com/watch?v=';
				break;
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
				if (typeof json.thumbnail_url === 'undefined' || parent.querySelectorAll('.embedvideo-consent__thumbnail').length > 0) {
					return;
				}

				const 
					overlay = parent.querySelector('.embedvideo-consent__overlay'),
					picture = document.createElement('picture'),
					image = document.createElement('img');

				picture.classList.add('embedvideo-consent__thumbnail');
				image.src = json.thumbnail_url;
				image.setAttribute('loading', 'lazy');
				image.classList.add('embedvideo-consent__thumbnail__image');
				picture.append(image);
				parent.prepend(picture);

				if (typeof json.title !== 'undefined' && json.title.length > 0) {
					const title = document.createElement('div');
					title.classList.add('embedvideo-consent__title');
					title.innerText = json.title;
					overlay.classList.add('embedvideo-consent__overlay--hastitle');
					overlay.prepend(title);
				}
			})
			.catch(error => {
				//
			})
	}

	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll('.embedvideowrap').forEach(function (div) {
			const clickListener = function (event) {
				if (iframe !== null) {
					iframe.src = iframe.dataset.src ?? '';
				}

				event.target.removeEventListener('click', clickListener);
				div.removeChild(consentDiv);
			};

			const consentDiv = div.querySelector('.embedvideo-consent');
			const iframe = div.querySelector('iframe');

			if (consentDiv === null || iframe === null) {
				return;
			}

			consentDiv.addEventListener('click', clickListener);

			if (!div.classList.contains('no-fetch')) {
				fetchThumb(iframe.dataset.src, consentDiv, div);
			}
		})
	} );
})();
