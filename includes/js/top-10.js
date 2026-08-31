(function () {
    var tracker = ajax_tptn_tracker;
    var tracked = false;

    function send(id, context) {
        var params = new URLSearchParams({
            action: 'tptn_tracker',
            top_ten_id: id,
            top_ten_blog_id: tracker.top_ten_blog_id,
            activate_counter: tracker.activate_counter,
            top_ten_debug: tracker.top_ten_debug
        });

        if (context) {
            params.append('top_ten_sitewide_context', context);
        }

        var url = tracker.ajax_url;
        if ('query_based' === tracker.tracker_type) {
            url += (url.includes('?') ? '&' : '?') + params;
        }

        if ('1' === tracker.top_ten_debug || !navigator.sendBeacon || !navigator.sendBeacon(url, params)) {
            fetch(url, { method: 'POST', body: params, keepalive: true }).catch(function () {});
        }
    }

    function track() {
        if (tracked) {
            return;
        }
        if (document.prerendering) {
            document.addEventListener('prerenderingchange', track, { once: true });
            return;
        }
        if ('hidden' === document.visibilityState) {
            document.addEventListener('visibilitychange', track, { once: true });
            return;
        }

        tracked = true;
        if (tracker.top_ten_id > 0) {
            send(tracker.top_ten_id, '');
        }
        if (tracker.top_ten_sitewide_context) {
            send(0, tracker.top_ten_sitewide_context);
        }
    }

    document.addEventListener('DOMContentLoaded', track);
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            tracked = false;
            track();
        }
    });
})();
