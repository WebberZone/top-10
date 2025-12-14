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

	// Function to generate fast config.
	function generateFastConfig(button) {
		var $button = $(button);
		var originalText = $button.text();
		$button.prop('disabled', true).text('Generating...');

		$.post(top_ten_admin_data.ajax_url, {
			action: 'tptn_generate_fast_config',
			security: $button.data('nonce')
		}, function (response) {
			if (response.success) {
				alert(response.data.message);
				$button.data('nonce', response.data.nonce);
			} else {
				alert(response.data.message);
				$button.data('nonce', response.data.nonce);
			}
		}).fail(function (jqXHR, textStatus) {
			alert(top_ten_admin_data.request_fail_message + textStatus);
		}).always(function () {
			$button.prop('disabled', false).text(originalText);
		});
	}

	// Handle fast config generation button click.
	$('body').on('click', '.tptn-generate-fast-config', function () {
		var confirmMessage = $(this).data('confirm');
		if (confirm(confirmMessage)) {
			generateFastConfig(this);
		}
	});


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
		
		// Wait for the tabs to be fully initialized
		setTimeout(function() {
			// Select Today tab (second tab) by default and add the nav-tab-active class
			var $tabs = $("#post-body-content").tabs();
			
			// First find the Today tab index (usually 1)
			var tabIndex = 0;
			$(".nav-tab-wrapper li a").each(function(index) {
				if ($(this).text().indexOf('Today') >= 0) {
					tabIndex = index;
					return false; // Break the loop
				}
			});
			
			// Set the active tab
			$tabs.tabs("option", "active", tabIndex);
			
			// Manually add the active class to the Today tab
			$(".nav-tab-wrapper li a").removeClass("nav-tab-active");
			$(".nav-tab-wrapper li").eq(tabIndex).find("a").addClass("nav-tab-active");
		}, 200);
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
		var blog_id = $element.attr('data-wp-blog-id');
		var count = $element.attr('data-wp-count');
		var value = $element.text();

		$element.removeClass("live_edit_mode");

		var ajaxData = {
			action: 'tptn_edit_count_ajax',
			post_id: post_id,
			total_count: value,
			total_count_original: count,
			top_ten_admin_nonce: top_ten_admin.nonce
		};

		// Include blog_id if available (network mode).
		if (blog_id) {
			ajaxData.blog_id = blog_id;
		}

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: ajaxData,
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
