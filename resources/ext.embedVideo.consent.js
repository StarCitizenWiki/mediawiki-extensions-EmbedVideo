const { makeIframe } = require('./iframe.js');

(function () {
	mw.hook( 'wikipage.content' ).add( () => {
		document.querySelectorAll('.embedvideo').forEach(function (ev) {
			if (ev?.dataset?.service === 'videolink') {
				return;
			}

			makeIframe(ev);
		});
	} );
})();
