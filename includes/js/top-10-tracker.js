document.addEventListener('DOMContentLoaded', function () {
    var params = {
        action: 'tptn_tracker',
        top_ten_id: ajax_tptn_tracker.top_ten_id,
        top_ten_blog_id: ajax_tptn_tracker.top_ten_blog_id,
        activate_counter: ajax_tptn_tracker.activate_counter,
        top_ten_debug: ajax_tptn_tracker.top_ten_debug
    };

    fetch(ajax_tptn_tracker.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Cache-Control': 'no-cache'
        },
        body: new URLSearchParams(params).toString()
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            // handle the response data
        })
        .catch(function (error) {
            console.error('Error:', error);
        });
});
