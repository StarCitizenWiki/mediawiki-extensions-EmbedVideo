const {makeIframe, fetchThumb} = require('./iframe.js');

(function () {
    mw.hook( 'wikipage.content' ).add( () => {
        document.querySelectorAll('.embedvideo-evl').forEach(function (evl) {
            evl.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();

                const player = evl?.dataset?.player ?? 'default';
                const iframeConfig = JSON.parse(evl.dataset.iframeconfig);

                const iframe = document.querySelector(`.embedvideo.evlplayer-${player} iframe`);
                if (iframe !== null) {
                    for (const [key, value] of Object.entries(iframeConfig)) {
                        iframe.setAttribute(key, value);
                    }
                } else {
                    const div = document.querySelector(`.embedvideo.evlplayer-${player}`);

                    if (div === null) {
                        console.warn(`No player with id '${player}' found!.`);
                        return;
                    }

                    const wrapper = div.querySelector('.embedvideo-wrapper');
                    const consentDiv = wrapper.querySelector('.embedvideo-consent');

                    const origService = div?.dataset?.service;

                    div.dataset.iframeconfig = evl.dataset.iframeconfig;
                    div.dataset.service = evl.dataset.service;

                    const message = 'embedvideo-service-' + evl.dataset.service;
                    const service = mw.message(message).escaped();

                    const link = document.createElement('a');
                    link.href = evl?.dataset?.privacyUrl ?? '#';
                    link.rel = 'nofollow,noopener';
                    link.target = '_blank';
                    link.classList.add('embedvideo-privacyNotice__link');
                    link.innerText = mw.message('embedvideo-consent-privacy-policy').escaped();

                    div.querySelector('.embedvideo-loader__service').innerText = service;
                    div.querySelector('.embedvideo-privacyNotice__content').innerText = mw.message('embedvideo-consent-privacy-notice-text', service).escaped();

                    if (link.href !== '#') {
                        div.querySelector('.embedvideo-privacyNotice__content').appendChild(link);
                    }

                    if (origService === 'videolink') {
                        makeIframe(div);
                    } else {
                        fetchThumb(iframeConfig.src, consentDiv, wrapper.parentElement);
                    }
                }
            });
        });
    } );
})();
