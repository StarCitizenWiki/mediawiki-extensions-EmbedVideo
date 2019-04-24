(function(mw, $, window) {
	$(function() {
		api = new mw.Api();
		$('a.embedvideo-evl').click(function(e){
			e.preventDefault();

			var data = $(this).data('video-json');
			data['action'] = 'embedvideo';
			if (typeof data['player'] === 'undefined') {
				data['player'] = 'default';
			}
			var player = data['player'];
			var container = $('#vplayerbox-'+player);
			if (!container.length) {
				mw.log.error('No matching vplayer tag found for this link!');
				return;
			}
			// modify dimensions call to match container size if exists.
			if (container.data('size')) {
				data['dimensions'] = container.data('size');
			}
			api.get( data )
			.done( function( res ) {
				if (typeof res.embedvideo.html !== 'undefined') {
					content = res.embedvideo.html;
				} else {
					content = "There was an error while loading this video";
				}
				if (data.notice) {
					content = content + "<small>" + data.notice + "</small>";
				}
				container.html(content);
				window.autoResizer();
			} );
		});
    });
}(mediaWiki, jQuery, window));
