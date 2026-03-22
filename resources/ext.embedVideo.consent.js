const { makeIframe } = require( './modules/iframe.js' );

( function () {
	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll( '.embedvideo' ).forEach( ( ev ) => {
			if ( ev.dataset && ev.dataset.service === 'videolink' ) {
				return;
			}

			makeIframe( ev );
		} );
	} );
}() );
