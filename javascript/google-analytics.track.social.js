var EA = window.EA || { GA: {} };

if(EA.GA.hasOwnProperty('trackers')) {
    EA.GA.social = (function (social, ea, mwm) {
        social.fb = function (event, action) {
            try {
                if (window.FB && window.FB.Event && window.FB.Event.subscribe) {
                    FB.Event.subscribe(event, function (targetUrl) {
                        for (var key in ea.GA.trackers) {
                            if (ea.GA.trackers.hasOwnProperty(key)) {
                                ea.GA.trackers[key].sendEvent({
                                    hitType:       'social',
                                    socialNetwork: 'facebook',
                                    socialAction:  action,
                                    socialTarget:  targetUrl
                                });
                            }
                        }
                    });
                }
            } catch (e) {
            }
        };

        social.twitter = function (eventName, action) {
            try {
                if (window.twttr && window.twttr.events) {
                    twttr.ready(function (twttr) {
                        twttr.events.bind(eventName, function (event) {
                            if (event) {
                                var targetUrl;

                                if (event.target && event.target.nodeName == 'IFRAME')
                                    targetUrl = ga_track.extractParamFromURI(event.target.src, 'url');

                                if (event.data && event.data.user_id)
                                    action = action + ' (@' + event.data.user_id + ')';

                                for (var key in ea.GA.trackers) {
                                    if (ea.GA.trackers.hasOwnProperty(key)) {
                                        ea.GA.trackers[key].sendEvent({
                                            hitType:       'social',
                                            socialNetwork: 'twitter',
                                            socialAction:  action,
                                            socialTarget:  targetUrl
                                        });
                                    }
                                }
                            }
                        });
                    });
                }
            } catch (e) {
            }
        };

        social.extractParamFromUri = function (uri, paramName) {
            if (!uri) return '';

            var regex = new RegExp('[\\?&#]' + paramName + '=([^&#]*)');
            var params = regex.exec(uri);
            if (params !== null) return decodeURI(params[1]);

            return '';
        };

        social.init = function () {
            social.fb('edge.create', 'like');
            social.fb('edge.remove', 'unlike');
            social.fb('message.send', 'share');
            social.fb('comment.create', 'comment');
            social.fb('comment.remove', 'deleted comment');

            social.fb('tweet', 'tweet');
            social.fb('follow', 'follow');
            social.fb('favorite', 'favourite');
        };

        social.init();

        mwm.utilities.attachToEvent(window, "mwm::loaded:js", social.init);

        return social;
    }(EA.GA.social || {}, EA || {}, mwm || {}));
}