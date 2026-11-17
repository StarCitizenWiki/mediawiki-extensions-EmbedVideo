/**
 * Related-videos overlay for YouTube embeds ($wgEmbedVideoYouTubeHideRelated).
 *
 * When a YouTube embed is paused or ends, YouTube shows its own "related videos" screen. This module
 * listens to the player through the IFrame Player API and marks the embed's wrapper `paused` or
 * `ended`; a button in the wrapper then covers the player (above the control bar while paused) and
 * resumes, restarts the video or restarts the playlist. The embed URLs carry enablejsapi/rel/origin
 * (added server-side; origin is corrected here if the page is served from another address).
 * Iframes inserted later (consent mode) are picked up with a MutationObserver.
 */
const API_URL = 'https://www.youtube.com/iframe_api';
const WRAPPERS = 'figure.embedvideo[data-service^="youtube"] .embedvideo-wrapper';
// endedDelay: YouTube reports ENDED between playlist tracks too; wait before showing the overlay.
// pollInterval: readiness check, in case another script replaces onYouTubeIframeAPIReady.
const DEFAULTS = { endedDelay: 400, pollInterval: 250, pollFor: 60000 };

let options = Object.assign( {}, DEFAULTS );
let queue = [];
let apiRequested = false;
let poll = null;

function apiReady() {
	return typeof window.YT !== 'undefined' && typeof window.YT.Player === 'function';
}

function drain() {
	if ( !apiReady() ) {
		return;
	}
	if ( poll ) {
		clearInterval( poll );
		poll = null;
	}
	const pending = queue;
	queue = [];
	pending.forEach( ( fn ) => fn() );
}

/**
 * Load the IFrame Player API once; players queued meanwhile start when it is ready.
 *
 * @param {Function} start called once the API is ready
 */
function whenApiReady( start ) {
	if ( apiReady() ) {
		start();
		return;
	}
	queue.push( start );
	if ( apiRequested ) {
		return;
	}
	apiRequested = true;
	const previous = window.onYouTubeIframeAPIReady;
	window.onYouTubeIframeAPIReady = function () {
		if ( typeof previous === 'function' ) {
			previous();
		}
		drain();
	};
	const started = Date.now();
	poll = setInterval( () => {
		if ( Date.now() - started > options.pollFor ) {
			clearInterval( poll );
			poll = null;
			return;
		}
		drain();
	}, options.pollInterval );
	const script = document.createElement( 'script' );
	script.src = API_URL;
	document.head.appendChild( script );
}

/**
 * The video an embed URL shows (path + playlist), ignoring the parameters YouTube itself adds.
 *
 * @param {string} src embed URL
 * @return {string}
 */
function videoOf( src ) {
	try {
		const url = new URL( src, location.href );
		return url.pathname + '|' + ( url.searchParams.get( 'list' ) || '' ) + '|' +
			( url.searchParams.get( 'playlist' ) || '' );
	} catch ( e ) {
		return src;
	}
}

/**
 * The IFrame API ignores a page whose address is not the embed's origin: make them match.
 *
 * @param {HTMLIFrameElement} iframe
 */
function fixOrigin( iframe ) {
	try {
		const url = new URL( iframe.getAttribute( 'src' ) || '', location.href );
		if ( url.searchParams.has( 'enablejsapi' ) && url.searchParams.get( 'origin' ) !== location.origin ) {
			url.searchParams.set( 'origin', location.origin );
			iframe.setAttribute( 'src', url.toString() );
		}
	} catch ( e ) {}
}

function overlayButton( wrapper ) {
	let button = wrapper.querySelector( ':scope > button.embedvideo-youtube-overlay' );
	if ( !button ) {
		button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'embedvideo-youtube-overlay';
		button.setAttribute( 'aria-label', mw.msg( 'embedvideo-youtube-overlay-play' ) );
		wrapper.appendChild( button );
	}
	return button;
}

function attach( wrapper, iframe ) {
	if ( iframe.dataset.evYoutubeOverlay ) {
		return;
	}
	iframe.dataset.evYoutubeOverlay = '1';
	fixOrigin( iframe );
	const button = overlayButton( wrapper );
	let onClick = null;
	let endedTimer = null;

	const onStateChange = ( state ) => {
		const states = window.YT.PlayerState;
		clearTimeout( endedTimer );
		if ( state === states.ENDED ) {
			endedTimer = setTimeout( () => {
				wrapper.classList.remove( 'paused' );
				wrapper.classList.add( 'ended' );
				button.setAttribute( 'aria-label', mw.msg( 'embedvideo-youtube-overlay-replay' ) );
			}, options.endedDelay );
		} else if ( state === states.PAUSED ) {
			wrapper.classList.remove( 'ended' );
			wrapper.classList.add( 'paused' );
			button.setAttribute( 'aria-label', mw.msg( 'embedvideo-youtube-overlay-play' ) );
		} else {
			wrapper.classList.remove( 'paused', 'ended' );
		}
	};

	whenApiReady( () => {
		const player = new window.YT.Player( iframe, {
			events: { onStateChange: ( event ) => onStateChange( event.data ) }
		} );
		onClick = () => {
			if ( wrapper.classList.contains( 'ended' ) ) {
				const playlist = typeof player.getPlaylist === 'function' ? player.getPlaylist() : null;
				if ( playlist && playlist.length ) {
					player.playVideoAt( 0 );
				} else {
					player.seekTo( 0 );
					player.playVideo();
				}
			} else if ( wrapper.classList.contains( 'paused' ) ) {
				player.playVideo();
			}
		};
		button.addEventListener( 'click', onClick );
	} );

	// A video link ({{#evl}}) loads another video into the same iframe: the old player no longer
	// reports states, so drop its overlay state and listener and attach to the new document.
	const video = videoOf( iframe.getAttribute( 'src' ) || '' );
	const observer = new MutationObserver( () => {
		// the IFrame API itself rewrites src (e.g. widgetid=1): only another video counts
		if ( videoOf( iframe.getAttribute( 'src' ) || '' ) === video ) {
			return;
		}
		observer.disconnect();
		clearTimeout( endedTimer );
		wrapper.classList.remove( 'paused', 'ended' );
		if ( onClick ) {
			button.removeEventListener( 'click', onClick );
		}
		delete iframe.dataset.evYoutubeOverlay;
		attach( wrapper, iframe );
	} );
	observer.observe( iframe, { attributes: true, attributeFilter: [ 'src' ] } );
}

/**
 * Attach every YouTube embed under `root`; watch (once) wrappers whose iframe is not there yet.
 *
 * @param {HTMLElement|Document} root
 */
function scan( root ) {
	root.querySelectorAll( WRAPPERS ).forEach( ( wrapper ) => {
		const iframe = wrapper.querySelector( 'iframe' );
		if ( iframe ) {
			attach( wrapper, iframe );
			return;
		}
		if ( wrapper.dataset.evYoutubeOverlayWatch ) {
			return;
		}
		wrapper.dataset.evYoutubeOverlayWatch = '1';
		const observer = new MutationObserver( () => {
			const late = wrapper.querySelector( 'iframe' );
			if ( late ) {
				observer.disconnect();
				attach( wrapper, late );
			}
		} );
		observer.observe( wrapper, { childList: true, subtree: true } );
	} );
}

/**
 * For tests: forget the pending API request and queue, and optionally change the timings.
 *
 * @param {Object} [testOptions] overrides of the default timings
 */
function reset( testOptions ) {
	if ( poll ) {
		clearInterval( poll );
		poll = null;
	}
	queue = [];
	apiRequested = false;
	options = Object.assign( {}, DEFAULTS, testOptions || {} );
}

mw.hook( 'wikipage.content' ).add( ( $content ) => scan( $content[ 0 ] ) );

module.exports = { scan, reset };
