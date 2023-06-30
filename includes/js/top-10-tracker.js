document.addEventListener('DOMContentLoaded', function() {
    fetch(ajax_tptn_tracker.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'tptn_tracker',
            top_ten_id: ajax_tptn_tracker.top_ten_id,
            top_ten_blog_id: ajax_tptn_tracker.top_ten_blog_id,
            activate_counter: ajax_tptn_tracker.activate_counter,
            top_ten_debug: ajax_tptn_tracker.top_ten_debug
        }).toString()
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        // handle the response data
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
});
