document.addEventListener('DOMContentLoaded', function () {
    function sendTracker(id, context) {
        var params = {
            action: 'tptn_tracker',
            top_ten_id: id,
            top_ten_blog_id: ajax_tptn_tracker.top_ten_blog_id,
            activate_counter: ajax_tptn_tracker.activate_counter,
            top_ten_debug: ajax_tptn_tracker.top_ten_debug
        };

        if (context) {
            params.top_ten_sitewide_context = context;
        }

        var requestUrl = ajax_tptn_tracker.ajax_url;
        if ('query_based' === ajax_tptn_tracker.tracker_type) {
            requestUrl += (requestUrl.indexOf('?') >= 0 ? '&' : '?') + new URLSearchParams(params).toString();
        }

        fetch(requestUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            body: new URLSearchParams(params).toString()
        })
            .then(function (response) {
                if (!response.ok && 204 !== response.status) {
                    throw new Error('Tracker request failed');
                }

                return response.text();
            })
            .then(function (data) {
                // handle the response data
            })
            .catch(function (error) {
                console.error('Error:', error);
            });
    }

    if (ajax_tptn_tracker.top_ten_id > 0) {
        sendTracker(ajax_tptn_tracker.top_ten_id, '');
    }

    if (ajax_tptn_tracker.top_ten_sitewide_context) {
        sendTracker(0, ajax_tptn_tracker.top_ten_sitewide_context);
    }
});
