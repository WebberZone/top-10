jQuery(document).ready(function ($) {

	$('button[name="tptn_cache_clear"]').on('click', function () {
		if (confirm(top_ten_admin_data.strings.confirm_message)) {
			var $button = $(this);
			var originalText = $button.text();
			var clearingText = top_ten_admin_data.strings.clearing_text ? top_ten_admin_data.strings.clearing_text : 'Clearing...';
			$button.prop('disabled', true).text(clearingText).append(' <span class="spinner is-active"></span>');
			clearCache($button, originalText);
		}
	});

	// Function to clear the cache.
	function clearCache($button, originalText) {
		$.post(ajaxurl, {
			action: 'tptn_clear_cache',
			security: top_ten_admin_data.security
		}, function (response) {
			if (response.success) {
				alert(response.data.message);
			} else {
				alert(top_ten_admin_data.strings.fail_message);
			}
		}).fail(function (jqXHR, textStatus) {
			alert(top_ten_admin_data.strings.request_fail_message + textStatus);
		}).always(function () {
			if ($button && $button.length) {
				$button.prop('disabled', false).text(originalText).find('.spinner').remove();
			}
		});
	}

	/**
	 * Handle style changes and enforce valid thumbnail location settings.
	 */
	function tptnHandleThumbnailStyleChange() {
		var $styleSelect = $('select[name="tptn_settings[tptn_styles]"]');
		if (!$styleSelect.length) {
			return;
		}

		var $postThumbOptions = $('input[name="tptn_settings[post_thumb_op]"]');
		if (!$postThumbOptions.length) {
			return;
		}

		var strings = top_ten_admin_data.strings || {};
		var styleRules = {
			'left_thumbs': {
				allowed: ['inline', 'thumbs_only'],
				fallback: 'inline',
				message: strings.left_thumbs_message
			},
			'grid_thumbs': {
				allowed: ['inline', 'thumbs_only'],
				fallback: 'inline',
				message: strings.grid_thumbs_message || strings.left_thumbs_message
			},
			'text_only': {
				force: 'text_only',
				disableAll: true,
				message: strings.text_only_message
			}
		};

		function removeMessage() {
			var $container = $postThumbOptions.first().closest('td');
			if (!$container.length) {
				$container = $postThumbOptions.first().parent();
			}
			$container.find('.tptn-js-message').remove();
		}

		function addMessage(message) {
			if (!message) {
				return;
			}

			var $container = $postThumbOptions.first().closest('td');
			if (!$container.length) {
				$container = $postThumbOptions.first().parent();
			}

			var $existingMessage = $container.find('.tptn-js-message');
			if ($existingMessage.length) {
				$existingMessage.text(message);
				return;
			}

			$container.append(
				$('<p />', {
					'class': 'description tptn-js-message',
					'css': { 'color': '#9B0800' },
					'text': message
				})
			);
		}

		function updateFieldStates() {
			var selectedStyle = $styleSelect.val();
			var $checked = $postThumbOptions.filter(':checked');
			var rule = styleRules[selectedStyle] || null;

			removeMessage();
			$postThumbOptions.prop('disabled', false);

			if (!rule) {
				return;
			}

			if (rule.disableAll) {
				$postThumbOptions.prop('disabled', true);
			}

			if (rule.allowed && rule.allowed.length) {
				$postThumbOptions.each(function () {
					var $option = $(this);
					var value = $option.val();
					if (-1 === rule.allowed.indexOf(value)) {
						$option.prop('disabled', true);
					}
				});

				if (!$checked.length || -1 === rule.allowed.indexOf($checked.val())) {
					var fallback = rule.fallback || rule.allowed[0];
					$postThumbOptions.filter('[value="' + fallback + '"]').prop('checked', true);
				}
			}

			if (rule.force) {
				$postThumbOptions.filter('[value="' + rule.force + '"]').prop('checked', true);
			}

			if (rule.message) {
				addMessage(rule.message);
			}
		}

		updateFieldStates();
		$styleSelect.on('change', updateFieldStates);
	}

	tptnHandleThumbnailStyleChange();

	function updateFastConfigUI($button, data) {
		if (!data) {
			return;
		}

		if (data.status_html) {
			var $status = $button.closest('td').find('.tptn-fast-config-status').first();
			if (!$status.length) {
				$status = $('.tptn-fast-config-status').first();
			}
			if ($status.length) {
				$status.replaceWith($(data.status_html));
			}
		}

		if (data.generate_nonce) {
			$('.tptn-generate-fast-config').data('nonce', data.generate_nonce);
		}

		if (data.delete_nonce) {
			$('.tptn-delete-fast-config').data('nonce', data.delete_nonce);
		}

		if (typeof data.has_config !== 'undefined') {
			var $deleteBtn = $('.tptn-delete-fast-config');
			if ($deleteBtn.length) {
				$deleteBtn.prop('disabled', !data.has_config);
			}
		}

		if (data.message) {
			alert(data.message);
		}
	}

	// Function to generate fast config.
	function generateFastConfig(button) {
		var $button = $(button);
		var originalText = $button.text();
		$button.prop('disabled', true).text('Generating...');

		$.post(ajaxurl, {
			action: 'tptn_generate_fast_config',
			security: $button.data('nonce')
		}, function (response) {
			if (response && response.data) {
				if (response.data.nonce) {
					$button.data('nonce', response.data.nonce);
				}
				updateFastConfigUI($button, response.data);
			}
		}).fail(function (jqXHR, textStatus) {
			if (window.console && window.console.error) {
				window.console.error(top_ten_admin_data.strings.request_fail_message + textStatus);
			}
		}).always(function () {
			$button.prop('disabled', false).text(originalText);
		});
	}

	// Function to delete fast config.
	function deleteFastConfig(button) {
		var $button = $(button);
		var originalText = $button.text();
		$button.prop('disabled', true).text('Deleting...');

		$.post(ajaxurl, {
			action: 'tptn_delete_fast_config',
			security: $button.data('nonce')
		}, function (response) {
			if (response && response.data) {
				if (response.data.nonce) {
					$button.data('nonce', response.data.nonce);
				}
				updateFastConfigUI($button, response.data);
			}
		}).fail(function (jqXHR, textStatus) {
			if (window.console && window.console.error) {
				window.console.error(top_ten_admin_data.strings.request_fail_message + textStatus);
			}
		}).always(function () {
			$button.text(originalText);
			if ($button.data('nonce')) {
				$button.prop('disabled', false);
			}
		});
	}

	// Handle fast config generation button click.
	$('body').on('click', '.tptn-generate-fast-config', function () {
		var confirmMessage = $(this).data('confirm');
		if (confirm(confirmMessage)) {
			generateFastConfig(this);
		}
	});

	// Handle fast config deletion button click.
	$('body').on('click', '.tptn-delete-fast-config', function () {
		var confirmMessage = $(this).data('confirm');
		if (confirm(confirmMessage)) {
			deleteFastConfig(this);
		}
	});

	$(function () {
		var $tabsContainer = $("#dashboard-historical-visits");
		$tabsContainer.tabs({
			create: function (event, ui) {
				$(ui.tab.find("a")).addClass("nav-tab-active");
			},
			activate: function (event, ui) {
				$(ui.oldTab.find("a")).removeClass("nav-tab-active");
				$(ui.newTab.find("a")).addClass("nav-tab-active");
			}
		});

		// Wait for the tabs to be fully initialized
		setTimeout(function () {
			// Select Today tab (second tab) by default and add the nav-tab-active class
			var $tabs = $tabsContainer.tabs();

			// First find the Today tab index (usually 1)
			var tabIndex = 0;
			$tabsContainer.find(".nav-tab-wrapper li a").each(function (index) {
				if ($(this).text().indexOf('Today') >= 0) {
					tabIndex = index;
					return false; // Break the loop
				}
			});

			// Set the active tab
			$tabs.tabs("option", "active", tabIndex);

			// Manually add the active class to the Today tab
			$tabsContainer.find(".nav-tab-wrapper li a").removeClass("nav-tab-active");
			$tabsContainer.find(".nav-tab-wrapper li").eq(tabIndex).find("a").addClass("nav-tab-active");
		}, 10);
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
