(function () {
	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll('.local-embed .embedvideowrap').forEach(function (div) {
			const clickListener = function () {
				consentDiv.classList.add('hidden');
				video.controls = true;
				video.play();
			};

			const consentDiv = div.querySelector('.embedvideo-consent');
			const video = div.querySelector('video');
			const fakeButton = div.querySelector('.embedvideo-loader__fakeButton');
			fakeButton.innerHTML = mw.message('embedvideo-play').escaped();

			if (consentDiv === null || video === null) {
				return;
			}

			video.controls = false;

			video.addEventListener('click', function () {
				if (video.paused) {
					return;
				}

				// Remove thumb after the user clicked play
				const thumb = consentDiv.querySelector('.embedvideo-consent__thumbnail');
				if (thumb !== null) {
					consentDiv.removeChild(thumb);
				}

				video.pause();
				video.controls = false;
				consentDiv.classList.remove('hidden');
			});

			consentDiv.addEventListener('click', clickListener);
		})
	} );
})();
