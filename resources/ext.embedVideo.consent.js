(function () {
	const fetchThumb = async (url, parent, outerDiv) => {
		let callUrl;
		/**
		 * An optional configuration dict for accessing data from the 'info' endpoint for titles and thumbnails
		 * @type {{queryKeys: null, dataKey: null, titleKey: string, thumbnailKey: string, durationKey: string}}
		 */
		const dataConfig = {
			'queryKeys': null,
			'dataKey': null,
			'titleKey': 'title',
			'thumbnailKey': 'thumbnail_url',
			'durationKey': 'duration'
		}

		switch( outerDiv.getAttribute('data-service') ) {
			case 'bilibili':
				// Not Oembed
				// This currently only work for bvid links,
				callUrl = 'https://api.bilibili.com/x/web-interface/view';
				dataConfig['queryKeys'] = ['aid', 'bvid'];
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

		let id;

		// If queryKeys are defined, look for it directly
		if (dataConfig.queryKeys !== null && typeof dataConfig.queryKeys === 'object') {
			console.log( 'start loop' );
			dataConfig.queryKeys.every(queryKey => {
				const 
					regex = new RegExp( `[?&]${queryKey}=(\\S[^?&]+)` ),
					match = url.match(regex) ? url.match(regex)[1] : null;

				if (match !== null) {
					id = `?${queryKey}=${match}`;
					return false;
				}
				return true;
			});
		} else {
			// Some url manipulation foo which tries to get the id of the requested video
			if (url.substring(0, 1) === '/') {
				url = 'http:' + url;
			}

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

				if (typeof json[dataConfig.thumbnailKey] === 'undefined' || parent.querySelectorAll('.embedvideo-thumbnail').length > 0) {
					return;
				}

				const
					picture = document.createElement('picture'),
					image = document.createElement('img');

				picture.classList.add('embedvideo-thumbnail');
				image.src = json[dataConfig.thumbnailKey];
				image.setAttribute('loading', 'lazy');
				image.classList.add('embedvideo-thumbnail__image');
				picture.append(image);
				parent.prepend(picture);

				if (typeof json[dataConfig.titleKey] !== 'undefined' && json[dataConfig.titleKey].length > 0) {
					const
						overlay = parent.querySelector('.embedvideo-loader'),
						title = document.createElement('div');

					title.classList.add('embedvideo-loader__title');
					title.innerText = json[dataConfig.titleKey];
					overlay.prepend(title);
				}

				if (typeof json[dataConfig.durationKey] === 'number' ) {
					const formatTime = seconds => {
						const 
							h = Math.floor(seconds / 3600),
							m = Math.floor((seconds % 3600) / 60),
							s = Math.round(seconds % 60);
	
						return [
						  h,
						  m > 9 ? m : (h ? '0' + m : m || '0'),
						  s > 9 ? s : '0' + s
						].filter(Boolean).join(':');
					};

					const
						footer = parent.querySelector('.embedvideo-loader__footer'),
						duration = document.createElement('div');

					duration.classList.add('embedvideo-loader__duration');
					duration.innerText = formatTime(json[dataConfig.durationKey]);
					footer.append(duration);
				}
			})
			.catch(error => {
				//
			})
	}

	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll('.embedvideo-wrapper').forEach(function (div) {
			const clickListener = function (event) {
				if (iframe !== null) {
					iframe.src = iframe.dataset.src ?? '';
				}

				event.target.removeEventListener('click', clickListener);
				div.removeChild(consentDiv);
			};

			const togglePrivacyClickListener = function (event) {
				event.stopPropagation();

				loader.classList.toggle('hidden');
				privacyNotice.classList.toggle('hidden');
			};

			/** @type HTMLDivElement|null */
			const consentDiv = div.querySelector('.embedvideo-consent');
			const iframe = div.querySelector('iframe');

			if (consentDiv === null || iframe === null) {
				return;
			}

			const loader = consentDiv.querySelector('.embedvideo-loader');
			const privacyNotice = consentDiv.querySelector('.embedvideo-privacyNotice');

			if (consentDiv.dataset.showPrivacyNotice === '1') {
				consentDiv.addEventListener('click', togglePrivacyClickListener);
				consentDiv.querySelector('.embedvideo-privacyNotice__continue').addEventListener('click', clickListener);
				consentDiv.querySelector('.embedvideo-privacyNotice__dismiss').addEventListener('click', togglePrivacyClickListener);

			} else {
				consentDiv.addEventListener('click', clickListener);
			}

			if (!div.classList.contains('no-fetch')) {
				fetchThumb(iframe.dataset.src, consentDiv, div);
			}
		})
	} );
})();
