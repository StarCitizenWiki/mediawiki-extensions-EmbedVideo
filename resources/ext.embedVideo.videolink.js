const {makeIframe, fetchThumb} = require('./iframe.js');

(function () {
    mw.hook( 'wikipage.content' ).add( () => {
        document.querySelectorAll('.embedvideo-evl').forEach(function (evl) {
            evl.addEventListener('click', e => {
                e.preventDefault();

                const player = evl?.dataset?.player ?? 'default';
                const iframeConfig = JSON.parse(evl.dataset.iframeconfig);

                const iframe = document.querySelector(`.embedvideo.evlplayer-${player} iframe`);
                // Iframe exists, no consent required or already given
                if (iframe !== null) {
                    for (const [key, value] of Object.entries(iframeConfig)) {
                        iframe.setAttribute(key, value);
                    }

                    return;
                }

                // No iframe exists, only when explicit consent is required
                const div = document.querySelector(`.embedvideo.evlplayer-${player}`);

                if (div === null || evl.dataset?.iframeconfig === null) {
                    console.warn(`No player with id '${player}' found!.`);
                    return;
                }

                const wrapper = div.querySelector('.embedvideo-wrapper');
                const consentDiv = wrapper.querySelector('.embedvideo-consent');

                const origService = div.dataset?.service;

                div.dataset.iframeconfig = evl.dataset.iframeconfig;
                div.dataset.service = evl.dataset.service;

                const serviceMessage = mw.message('embedvideo-service-' + (evl.dataset?.service ?? 'youtube')).escaped();
                const privacyMessage = mw.message('embedvideo-consent-privacy-notice-text', serviceMessage).escaped();

                div.querySelector('.embedvideo-loader__service').innerText = serviceMessage;
                div.querySelector('.embedvideo-privacyNotice__content').innerText = privacyMessage;

                if (evl.dataset?.privacyUrl !== null) {
                    const link = document.createElement('a');
                    link.href = evl.dataset.privacyUrl;
                    link.rel = 'nofollow,noopener';
                    link.target = '_blank';
                    link.classList.add('embedvideo-privacyNotice__link');
                    link.innerText = mw.message('embedvideo-consent-privacy-policy').escaped();

                    div.querySelector('.embedvideo-privacyNotice__content').appendChild(link);
                }

                if (origService === 'videolink') {
                    makeIframe(div);
                } else {
                    fetchThumb(iframeConfig.src, consentDiv, wrapper.parentElement);
                }
            });
        });
    } );
})();
