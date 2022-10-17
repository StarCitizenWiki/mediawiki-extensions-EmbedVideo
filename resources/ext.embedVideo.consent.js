(function () {
	const fetchThumb = async (url, parent, outerDiv) => {
		let callUrl;
		/**
		 * An optional configuration dict for accessing data from the 'info' endpoint for titles and thumbnails
		 * @type {{dataKey: null, titleKey: string, thumbnailKey: string}}
		 */
		const dataConfig = {
			'dataKey': null,
			'titleKey': 'title',
			'thumbnailKey': 'thumbnail_url',
		}

		switch( outerDiv.getAttribute('data-service') ) {
			case 'bilibili':
				// Not Oembed
				callUrl = 'https://api.bilibili.com/x/web-interface/view?bvid=';
				dataConfig['dataKey'] = 'data';
				dataConfig['thumbnailKey'] = 'pic';
				break;
			case 'niconico':
				// Not Oembed
				// The official API is in XML sadly
				//callUrl = 'https://ext.nicovideo.jp/api/getthumbinfo/';
				break;
			case 'soundcloud':
				callUrl = 'https://soundcloud.com/oembed?format=json&url=';
				break;
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
		if (url.substring(0, 1) === '/') {
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

		if (id.substring(-1) === '?') {
			id = id.substring(0, id.length - 1)
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
				if (dataConfig.dataKey !== null) {
					json = json[dataConfig.dataKey];
				}

				if (typeof json[dataConfig.thumbnailKey] === 'undefined' || parent.querySelectorAll('.embedvideo-consent__thumbnail').length > 0) {
					return;
				}

				const
					overlay = parent.querySelector('.embedvideo-consent__overlay'),
					picture = document.createElement('picture'),
					image = document.createElement('img');

				picture.classList.add('embedvideo-consent__thumbnail');
				image.src = json[dataConfig.thumbnailKey];
				image.setAttribute('loading', 'lazy');
				image.classList.add('embedvideo-consent__thumbnail__image');
				picture.append(image);
				parent.prepend(picture);

				if (typeof json[dataConfig.titleKey] !== 'undefined' && json[dataConfig.titleKey].length > 0) {
					const title = document.createElement('div');
					title.classList.add('embedvideo-consent__title');
					title.innerText = json[dataConfig.titleKey];
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
