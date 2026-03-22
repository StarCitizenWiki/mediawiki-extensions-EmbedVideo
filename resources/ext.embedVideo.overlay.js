( function () {
	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll( '[data-service="local-embed"] .embedvideo-wrapper' ).forEach( ( div ) => {
			const consentDiv = div.querySelector( '.embedvideo-consent' );
			const video = div.querySelector( 'video' );
			const fakeButton = div.querySelector( '.embedvideo-loader__fakeButton' );
			const localEmbedStyle = div.querySelector( '.embedvideo-localEmbedStyle' );

			if ( localEmbedStyle !== null && video !== null ) {
				video.addEventListener( 'play', () => {
					localEmbedStyle.classList.add( 'embedvideo-localEmbedStyle--hidden' );
				} );

				video.addEventListener( 'ended', () => {
					localEmbedStyle.classList.remove( 'embedvideo-localEmbedStyle--hidden' );
				} );
			}

			if ( consentDiv === null || video === null || fakeButton === null ) {
				return;
			}

			const clickListener = function () {
				video.controls = true;
				video.play();
				consentDiv.removeEventListener( 'click', clickListener );
				consentDiv.parentElement.removeChild( consentDiv );
			};

			fakeButton.innerHTML = mw.message( 'embedvideo-play' ).escaped();

			video.controls = false;

			consentDiv.addEventListener( 'click', clickListener );
		} );
	} );
}() );
