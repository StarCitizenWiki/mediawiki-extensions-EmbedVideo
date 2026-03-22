( function () {
	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll( '[data-service="local-embed"] .embedvideo-wrapper' ).forEach( ( div ) => {
			const consentDiv = div.querySelector( '.embedvideo-consent' );
			const video = div.querySelector( 'video' );
			const fakeButton = div.querySelector( '.embedvideo-loader__fakeButton' );

			const clickListener = function () {
				video.controls = true;
				video.play();
				consentDiv.removeEventListener( 'click', clickListener );
				consentDiv.parentElement.removeChild( consentDiv );
			};

			fakeButton.innerHTML = mw.message( 'embedvideo-play' ).escaped();

			if ( consentDiv === null || video === null ) {
				return;
			}

			video.controls = false;

			consentDiv.addEventListener( 'click', clickListener );
		} );
	} );
}() );
