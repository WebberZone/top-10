jQuery(document).ready(function ($) {

	$('button[name="tptn_cache_clear"]').on('click', function () {
		if (confirm(top_ten_admin_data.confirm_message)) {
			clearCache();
		}
	});

	// Function to clear the cache.
	function clearCache() {
		$.post(top_ten_admin_data.ajax_url, {
			action: 'tptn_clear_cache',
			security: top_ten_admin_data.security
		}, function (response) {
			if (response.success) {
				alert(response.data.message);
			} else {
				alert(top_ten_admin_data.fail_message);
			}
		}).fail(function (jqXHR, textStatus) {
			alert(top_ten_admin_data.request_fail_message + textStatus);
		});
	}


	// Prompt the user when they leave the page without saving the form.
	var formmodified = 0;

	function confirmFormChange() {
		formmodified = 1;
	}

	function confirmExit() {
		if (formmodified == 1) {
			return true;
		}
	}

	function formNotModified() {
		formmodified = 0;
	}

	$('form').on('change', 'input, textarea, select', confirmFormChange);

	window.onbeforeunload = confirmExit;

	$('input[name="submit"], input#search-submit, input#doaction, input#doaction2, input[name="filter_action"]').on('click', formNotModified);

	$(function () {
		$("#post-body-content").tabs({
			create: function (event, ui) {
				$(ui.tab.find("a")).addClass("nav-tab-active");
			},
			activate: function (event, ui) {
				$(ui.oldTab.find("a")).removeClass("nav-tab-active");
				$(ui.newTab.find("a")).addClass("nav-tab-active");
			}
		});
	});

	// Datepicker.
	$(function () {
		var dateFormat = 'dd M yy',
			from = $("#datepicker-from")
				.datepicker({
					changeMonth: true,
					maxDate: 0,
					dateFormat: dateFormat
				})
				.on("change", function () {
					to.datepicker("option", "minDate", getDate(this));
				}),
			to = $("#datepicker-to")
				.datepicker({
					changeMonth: true,
					maxDate: 0,
					dateFormat: dateFormat
				})
				.on("change", function () {
					from.datepicker("option", "maxDate", getDate(this));
				});

		function getDate(element) {
			var date;
			try {
				date = $.datepicker.parseDate(dateFormat, element.value);
			} catch (error) {
				date = null;
			}

			return date;
		}
	});

	// Editable table code.
	$('.live_edit').on('click', function () {
		$(this)
			.addClass('live_edit_mode')
			.removeClass('live_edit_mode_success live_edit_mode_error');
	});

	$(".live_edit").on('focusout keypress', function (e) {
		if (e.type !== "focusout" && e.which !== 13) {
			return;
		}
		if (e.which == 13) {
			e.preventDefault();
		}
		var $element = $(this);
		var post_id = $element.attr('data-wp-post-id');
		var count = $element.attr('data-wp-count');
		var value = $element.text();

		$element.removeClass("live_edit_mode");

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: {
				action: 'tptn_edit_count_ajax',
				post_id: post_id,
				total_count: value,
				total_count_original: count,
				top_ten_admin_nonce: top_ten_admin.nonce
			},
			success: function (response) {
				if (response === false) {
					$element.addClass("live_edit_mode_error");
					$element.html(count);
				} else if (response > 0) {
					$element.addClass("live_edit_mode_success");
				}
			},
		});
	});
});
