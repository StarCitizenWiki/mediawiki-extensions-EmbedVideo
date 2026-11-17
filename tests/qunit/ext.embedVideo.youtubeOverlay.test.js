/* eslint-env qunit */
( function () {
	const overlay = require( 'ext.embedVideo.youtubeOverlay' );
	const API_URL = 'https://www.youtube.com/iframe_api';
	const STATE = { UNSTARTED: -1, ENDED: 0, PLAYING: 1, PAUSED: 2, BUFFERING: 3 };

	// A stand-in for the YouTube IFrame Player API: records players and lets tests fire
	// state changes.
	function stubYT( playlist ) {
		const players = [];
		window.YT = {
			PlayerState: STATE,
			loaded: 1,
			Player: function ( iframe, options ) {
				this.iframe = iframe;
				this.calls = [];
				this.fire = ( state ) => options.events.onStateChange(
					{ data: state, target: this }
				);
				this.seekTo = ( t ) => this.calls.push( 'seekTo:' + t );
				this.playVideo = () => this.calls.push( 'playVideo' );
				this.playVideoAt = ( i ) => this.calls.push( 'playVideoAt:' + i );
				this.getPlaylist = () => playlist || null;
				players.push( this );
			}
		};
		return players;
	}

	function embed( service, src ) {
		const figure = document.createElement( 'figure' );
		figure.className = 'embedvideo';
		figure.setAttribute( 'data-service', service );
		const wrapper = document.createElement( 'div' );
		wrapper.className = 'embedvideo-wrapper';
		if ( src !== null ) {
			const iframe = document.createElement( 'iframe' );
			iframe.setAttribute( 'src', src || 'https://www.youtube-nocookie.com/embed/abc?enablejsapi=1&origin=' +
				encodeURIComponent( location.origin ) );
			wrapper.appendChild( iframe );
		}
		figure.appendChild( wrapper );
		document.getElementById( 'qunit-fixture' ).appendChild( figure );
		return wrapper;
	}

	const fixture = () => document.getElementById( 'qunit-fixture' );
	const button = ( wrapper ) => wrapper.querySelector( 'button.embedvideo-youtube-overlay' );
	const later = ( ms ) => new Promise( ( resolve ) => {
		setTimeout( resolve, ms || 0 );
	} );

	// capture <script> insertions so no test loads the real API from youtube.com
	let requested, appendChild;

	QUnit.module( 'ext.embedVideo.youtubeOverlay', {
		beforeEach: function () {
			overlay.reset( { endedDelay: 0, pollInterval: 10 } );
			requested = [];
			appendChild = document.head.appendChild;
			document.head.appendChild = function ( node ) {
				if ( node.tagName === 'SCRIPT' ) {
					requested.push( node.src );
					return node;
				}
				return appendChild.call( this, node );
			};
		},
		afterEach: function () {
			document.head.appendChild = appendChild;
			overlay.reset();
			delete window.YT;
			delete window.onYouTubeIframeAPIReady;
		}
	} );

	QUnit.test( 'player state sets and clears the wrapper classes', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube' );
		overlay.scan( fixture() );
		assert.strictEqual( players.length, 1, 'one player for the embed' );

		players[ 0 ].fire( STATE.PAUSED );
		assert.true( wrapper.classList.contains( 'paused' ), 'paused' );
		players[ 0 ].fire( STATE.PLAYING );
		assert.false( wrapper.classList.contains( 'paused' ), 'playing clears paused' );
		players[ 0 ].fire( STATE.ENDED );
		await later();
		assert.true( wrapper.classList.contains( 'ended' ), 'ended' );
		players[ 0 ].fire( STATE.PAUSED );
		assert.false( wrapper.classList.contains( 'ended' ), 'paused clears ended' );
	} );

	QUnit.test( 'the overlay is a focusable button with a label', ( assert ) => {
		stubYT();
		const wrapper = embed( 'youtube' );
		overlay.scan( fixture() );
		const b = button( wrapper );
		assert.true( !!b, 'a button in the wrapper' );
		assert.strictEqual( b.type, 'button' );
		assert.true( b.getAttribute( 'aria-label' ).length > 0, 'it has an accessible name' );
	} );

	QUnit.test( 'the button restarts an ended video and resumes a paused one', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube' );
		overlay.scan( fixture() );

		players[ 0 ].fire( STATE.ENDED );
		await later();
		button( wrapper ).click();
		assert.deepEqual( players[ 0 ].calls, [ 'seekTo:0', 'playVideo' ], 'ended: restart' );

		players[ 0 ].calls.length = 0;
		players[ 0 ].fire( STATE.PAUSED );
		button( wrapper ).click();
		assert.deepEqual( players[ 0 ].calls, [ 'playVideo' ], 'paused: resume' );
	} );

	QUnit.test( 'the end of a playlist restarts the playlist', async ( assert ) => {
		const players = stubYT( [ 'a', 'b' ] );
		const wrapper = embed( 'youtubeplaylist' );
		overlay.scan( fixture() );
		players[ 0 ].fire( STATE.ENDED );
		await later();
		button( wrapper ).click();
		assert.deepEqual( players[ 0 ].calls, [ 'playVideoAt:0' ] );
	} );

	QUnit.test( 'between playlist tracks the ended overlay does not flash', async ( assert ) => {
		overlay.reset( { endedDelay: 30, pollInterval: 10 } );
		const players = stubYT( [ 'a', 'b' ] );
		const wrapper = embed( 'youtubeplaylist' );
		overlay.scan( fixture() );
		players[ 0 ].fire( STATE.ENDED );
		players[ 0 ].fire( STATE.UNSTARTED );
		await later( 50 );
		assert.false( wrapper.classList.contains( 'ended' ), 'next track started: no overlay' );

		players[ 0 ].fire( STATE.ENDED );
		await later( 50 );
		assert.true( wrapper.classList.contains( 'ended' ), 'last track: overlay' );
	} );

	QUnit.test( 'an iframe inserted later (consent) is attached, with one observer per wrapper', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube', null );
		overlay.scan( fixture() );
		overlay.scan( fixture() );
		assert.strictEqual( players.length, 0, 'nothing to attach yet' );
		assert.strictEqual( wrapper.dataset.evYoutubeOverlayWatch, '1', 'one watcher flag' );

		const iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'src', 'https://www.youtube-nocookie.com/embed/abc?enablejsapi=1&origin=' +
			encodeURIComponent( location.origin ) );
		wrapper.appendChild( iframe );
		await later();
		assert.strictEqual( players.length, 1, 'attached once after insertion' );
	} );

	QUnit.test( 'the API script is requested once, and queued players start when it is ready', ( assert ) => {
		embed( 'youtube' );
		embed( 'youtubeplaylist' );
		overlay.scan( fixture() );
		assert.deepEqual( requested, [ API_URL ], 'one request for the API script' );

		const players = stubYT();
		window.onYouTubeIframeAPIReady();
		assert.strictEqual( players.length, 2, 'both players created when the API is ready' );
	} );

	QUnit.test( 'players still start if another script replaces onYouTubeIframeAPIReady', async ( assert ) => {
		embed( 'youtube' );
		overlay.scan( fixture() );
		window.onYouTubeIframeAPIReady = function () {}; // a later script takes the global callback
		const players = stubYT();
		window.onYouTubeIframeAPIReady();
		await later( 50 );
		assert.strictEqual( players.length, 1, 'started by the readiness check' );
	} );

	QUnit.test( 'an origin that is not this page is corrected before attaching', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube', 'https://www.youtube-nocookie.com/embed/abc?enablejsapi=1&origin=' +
			encodeURIComponent( 'https://other.example.org' ) );
		overlay.scan( fixture() );
		await later();
		const src = new URL( wrapper.querySelector( 'iframe' ).getAttribute( 'src' ) );
		assert.strictEqual( src.searchParams.get( 'origin' ), location.origin );
		assert.strictEqual( src.searchParams.get( 'enablejsapi' ), '1', 'other arguments kept' );
		assert.strictEqual( players.length, 1 );
	} );

	QUnit.test( 'a new src in the same iframe (video link) clears the overlay and re-attaches', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube' );
		overlay.scan( fixture() );
		players[ 0 ].fire( STATE.ENDED );
		await later();
		assert.true( wrapper.classList.contains( 'ended' ) );

		wrapper.querySelector( 'iframe' ).setAttribute( 'src', 'https://www.youtube-nocookie.com/embed/other?enablejsapi=1&origin=' +
			encodeURIComponent( location.origin ) );
		await later();
		assert.false( wrapper.classList.contains( 'ended' ), 'stale state cleared' );
		assert.strictEqual( players.length, 2, 'a new player for the new document' );
		players[ 1 ].fire( STATE.PAUSED );
		button( wrapper ).click();
		assert.deepEqual( players[ 0 ].calls, [], 'the old player gets no more clicks' );
		assert.deepEqual( players[ 1 ].calls, [ 'playVideo' ], 'the new player does' );
	} );

	QUnit.test( 'YouTube rewriting the src of the same video (widgetid) keeps the player', async ( assert ) => {
		const players = stubYT();
		const wrapper = embed( 'youtube' );
		overlay.scan( fixture() );
		const iframe = wrapper.querySelector( 'iframe' );
		iframe.setAttribute( 'src', iframe.getAttribute( 'src' ) + '&widgetid=1' );
		await later();
		assert.strictEqual( players.length, 1, 'no new player' );
		players[ 0 ].fire( STATE.PAUSED );
		assert.true( wrapper.classList.contains( 'paused' ), 'still driven by the first player' );
	} );

	QUnit.test( 'non-YouTube embeds are ignored, and the same iframe is not attached twice', ( assert ) => {
		const players = stubYT();
		embed( 'vimeo', 'https://player.vimeo.com/video/1' );
		embed( 'youtube' );
		overlay.scan( fixture() );
		overlay.scan( fixture() );
		assert.strictEqual( players.length, 1 );
	} );
}() );
