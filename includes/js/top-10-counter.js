document.addEventListener('DOMContentLoaded', function () {
    var counters = document.querySelectorAll('.tptn_counter[data-tptn-url]');

    counters.forEach(function (el) {
        fetch(el.getAttribute('data-tptn-url'))
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Counter request failed');
                }
                return response.json();
            })
            .then(function (data) {
                if (data && data.count) {
                    el.innerHTML = data.count;
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
            });
    });
});
