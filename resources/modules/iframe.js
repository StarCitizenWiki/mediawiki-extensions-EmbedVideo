const session = mw.storage.session;

const fetchThumb = async ( url, parent, container ) => {
	const fetcherFactory = require( '../fetchers/fetchFactory.js' ).fetchFactory;

	const removeElements = () => {
		// Thumbnail
		parent.querySelectorAll( '.embedvideo-thumbnail' ).forEach( ( thumb ) => {
			parent.removeChild( thumb );
		} );

		// Title
		const overlay = parent.querySelector( '.embedvideo-loader' );
		overlay.querySelectorAll( '.embedvideo-loader__title:not(.embedvideo-loader__title--manual)' ).forEach( ( title ) => {
			overlay.removeChild( title );
		} );

		// Duration
		const footer = parent.querySelector( '.embedvideo-loader__footer' );
		footer.querySelectorAll( '.embedvideo-loader__duration' ).forEach( ( duration ) => {
			overlay.removeChild( duration );
		} );
	};

	const {
		fetcher,
		urlManipulation
	} = fetcherFactory( container.getAttribute( 'data-service' ) );

	if ( fetcher === null ) {
		return;
	}

	let id = url;

	// Some url manipulation foo which tries to get the id of the requested video
	if ( urlManipulation ) {
		if ( url.slice( 0, 1 ) === '/' ) {
			url = 'http:' + url;
		}

		try {
			url = ( new URL( url.split( '?' ).shift() ) ).pathname;
		} catch ( e ) {

		}

		id = url.split( '/' ).pop();
		if ( id === '' ) {
			return;
		}

		if ( id.slice( 0 ) === '?' ) {
			id = id.slice( 0, Math.max( 0, id.length - 1 ) );
		}
	}

	// Do the actual fetch
	await fetcher( id )
		.then( ( json ) => {
			if ( json.thumbnail === null ) {
				return;
			}

			removeElements();

			const
				picture = document.createElement( 'picture' ),
				image = document.createElement( 'img' );

			picture.classList.add( 'embedvideo-thumbnail' );
			image.src = json.thumbnail;
			image.setAttribute( 'loading', 'lazy' );
			image.classList.add( 'embedvideo-thumbnail__image' );
			picture.append( image );
			parent.prepend( picture );

			if ( typeof json.title !== 'undefined' && json.title.length > 0 && parent.querySelector( '.embedvideo-loader__title--manual' ) === null ) {
				const
					overlay = parent.querySelector( '.embedvideo-loader' ),
					title = document.createElement( 'div' ),
					link = document.createElement( 'a' );

				title.classList.add( 'embedvideo-loader__title' );

				link.classList.add( 'embedvideo-loader__link' );
				const iframeConfig = ( container && container.dataset && container.dataset.mwIframeconfig ) || '{"src": "#"}';
				link.href = JSON.parse( iframeConfig ).src;
				link.target = '_blank';
				link.rel = 'noopener noreferrer nofollow';
				link.innerText = json.title;

				title.append( link );
				overlay.prepend( title );
			}

			if ( typeof json.duration === 'number' ) {
				const formatTime = ( seconds ) => {
					const
						h = Math.floor( seconds / 3600 ),
						m = Math.floor( ( seconds % 3600 ) / 60 ),
						s = Math.round( seconds % 60 );

					return [
						h,
						m > 9 ? m : ( h ? '0' + m : m || '0' ),
						s > 9 ? s : '0' + s
					].filter( Boolean ).join( ':' );
				};

				const
					footer = parent.querySelector( '.embedvideo-loader__footer' ),
					duration = document.createElement( 'div' );

				duration.classList.add( 'embedvideo-loader__duration' );
				duration.innerText = formatTime( json.duration );
				footer.append( duration );
			}
		} )
		.catch( () => {} );
};

/**
 * @param {HTMLElement} ev
 */
const makeIframe = function ( ev ) {
	const wrapper = ev.querySelector( '.embedvideo-wrapper' );
	/** @type {HTMLDivElement|null} */
	const consentDiv = wrapper.querySelector( '.embedvideo-consent' );
	let iframeConfig = ev.dataset.mwIframeconfig;

	if ( consentDiv === null || iframeConfig === null ) {
		return;
	}

	const loader = consentDiv.querySelector( '.embedvideo-loader' );
	const privacyNotice = consentDiv.querySelector( '.embedvideo-privacyNotice' );

	const getSessionStorageKey = function () {
		return `ev-${ ev.dataset.service }-consent-given`;
	};

	const getIframeConfig = function () {
		let config = ev.dataset.mwIframeconfig;

		config = Object.assign( {},
			mw.config.get( `ev-${ ev.dataset.service }-config` ) || [],
			JSON.parse( config )
		);

		return config;
	};

	const createIframeHandler = function ( event ) {
		if ( ( ev.dataset && ev.dataset.service === 'externalvideo' ) || ( ev.dataset && ev.dataset.service === 'local-embed' ) ) {
			event.target.removeEventListener( 'click', createIframeHandler );
			wrapper.removeChild( consentDiv );
			return;
		}
		const iframe = document.createElement( 'iframe' );

		for ( const [ key, value ] of Object.entries( getIframeConfig() ) ) {
			iframe.setAttribute( key, value );
		}

		event.target.removeEventListener( 'click', createIframeHandler );

		consentDiv.parentElement.removeChild( consentDiv );
		wrapper.appendChild( iframe );
	};

	const togglePrivacyClickListener = function ( event ) {
		event.stopPropagation();

		if ( session.get( getSessionStorageKey() ) === '1' ) {
			createIframeHandler( event );
			return;
		}

		if ( event.target.classList.contains( 'embedvideo-loader__link' ) ) {
			return;
		}

		loader.classList.toggle( 'hidden' );
		privacyNotice.classList.toggle( 'hidden' );
	};

	iframeConfig = getIframeConfig();

	if ( consentDiv.dataset.showPrivacyNotice === '1' ) {
		const continueBtn = consentDiv.querySelector( '.embedvideo-privacyNotice__continue' );
		const dismissBtn = consentDiv.querySelector( '.embedvideo-privacyNotice__dismiss' );

		consentDiv.addEventListener( 'click', togglePrivacyClickListener );
		continueBtn.addEventListener( 'click', ( event ) => {
			session.set( getSessionStorageKey(), '1' );
			createIframeHandler( event );
		} );
		dismissBtn.addEventListener( 'click', togglePrivacyClickListener );
	} else {
		consentDiv.addEventListener( 'click', createIframeHandler );
	}

	if ( !wrapper.parentElement.classList.contains( 'no-fetch' ) ) {
		fetchThumb( iframeConfig.src, consentDiv, wrapper.parentElement );
	}
};

module.exports = { makeIframe, fetchThumb };
