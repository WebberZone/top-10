/**
 * Top 10 - WordPress Popular Posts Importer
 *
 * JavaScript for handling the WordPress Popular Posts import process.
 *
 * @package Top_Ten
 * @subpackage Admin/JS
 */

/* global topTenWPPImporter */
jQuery(document).ready(function ($) {
    'use strict';

    // Track if the import has been cancelled
    var importCancelled = false;

    /**
     * Escape HTML to prevent XSS attacks
     * 
     * @param {string} unsafeText The unsafe text
     * @return {string} Escaped text
     */
    function escapeHTML(unsafeText) {
        return $('<div>').text(unsafeText).html();
    }

    // Handle form submission.
    $('#top-ten-wpp-import-form').on('submit', function (e) {
        e.preventDefault();

        // Confirm before proceeding.
        if (!confirm(topTenWPPImporter.strings.confirm)) {
            return;
        }

        var $form = $(this);
        var $submit = $('#top-ten-wpp-import-submit');
        var $progress = $('#top-ten-wpp-import-progress');
        var $results = $('#top-ten-wpp-import-results');

        // Create cancel button if it doesn't exist
        var $cancelBtn = $('#top-ten-wpp-import-cancel');
        if ($cancelBtn.length === 0) {
            $cancelBtn = $('<button>', {
                id: 'top-ten-wpp-import-cancel',
                class: 'button',
                text: topTenWPPImporter.strings.cancel_button,
                css: { 'margin-left': '10px' }
            }).insertAfter($submit);

            // Add click handler to cancel button
            $cancelBtn.on('click', function () {
                if (confirm(topTenWPPImporter.strings.cancel_confirm)) {
                    importCancelled = true;
                    $progress
                        .removeClass('notice-info notice-success')
                        .addClass('notice-error')
                        .html('<strong>' + topTenWPPImporter.strings.import_cancelled + '</strong>');
                    $submit.prop('disabled', false);
                    $(this).hide();
                }
                return false;
            });
        }

        // Reset cancelled flag
        importCancelled = false;

        // Disable submit button and show cancel button
        $submit.prop('disabled', true);
        $cancelBtn.show();

        // Show progress
        $progress
            .removeClass('hidden notice-error notice-success')
            .addClass('notice-info')
            .html(topTenWPPImporter.strings.importing +
                ' <span class="import-status">' + topTenWPPImporter.strings.starting + '</span><br>' +
                '<div class="import-progress-bar" style="background-color: #f5f5f5; border: 1px solid #ddd; height: 20px; margin: 10px 0;">' +
                '<div style="width: 0%; height: 100%; background-color: #0073aa;"></div></div>' +
                '<span class="import-percentage">0%</span>');

        $results.addClass('hidden');

        // Get selected sites for multisite.
        var selectedSites = [];
        var isNetworkAdmin = $('input[name="sites[]"]').length > 0;

        // Only collect checked sites if we're in network admin
        if (isNetworkAdmin) {
            $('input[name="sites[]"]:checked').each(function () {
                selectedSites.push($(this).val());
            });

            // Validate that at least one site is selected in network admin
            if (selectedSites.length === 0) {
                $progress
                    .removeClass('hidden notice-info notice-success')
                    .addClass('notice-error')
                    .html('<strong>' + topTenWPPImporter.strings.error + '</strong> ' + topTenWPPImporter.strings.no_sites_selected);
                $submit.prop('disabled', false);
                return false;
            }
        }

        // Validate minimum views input
        var minViews = $('input[name="min_views"]').val();
        if (minViews !== '' && (!$.isNumeric(minViews) || parseInt(minViews) < 0)) {
            $progress
                .removeClass('hidden notice-info notice-success')
                .addClass('notice-error')
                .html('<strong>' + topTenWPPImporter.strings.error + '</strong> ' + topTenWPPImporter.strings.invalid_min_views);
            $submit.prop('disabled', false);
            return false;
        }

        // Store the form parameters for batch processing
        var importParams = {
            action: 'top_ten_import_wpp',
            nonce: topTenWPPImporter.nonce,
            import_mode: $('input[name="import_mode"]:checked').val(),
            import_data: $('input[name="import_data"]:checked').val(),
            min_views: $('input[name="min_views"]').val(),
            dry_run: $('input[name="dry_run"]').is(':checked') ? 1 : 0,
            is_network_admin: isNetworkAdmin ? 1 : 0,
            batch: true,
            sites: selectedSites,
            currentSiteIndex: 0,
            batch_number: 1,
            total_batches: 0,
            results: {}
        };

        // Start the batch process
        processBatch(importParams, $progress, $results, $submit);
    });

    /**
     * Update the progress bar with current percentage
     * 
     * @param {jQuery} $progress The progress element
     * @param {number} current   Current step or batch
     * @param {number} total     Total steps or batches
     */
    function updateProgressBar($progress, current, total) {
        // Calculate percentage, with a minimum of 5% to show activity
        var percentage = total > 0 ? Math.min(Math.round((current / total) * 100), 100) : 5;

        // Update the progress bar
        $progress.find('.import-progress-bar div').css('width', percentage + '%');
        $progress.find('.import-percentage').text(percentage + '%');
    }

    /**
     * Process a single batch of the import
     *
     * @param {Object} params    The import parameters
     * @param {jQuery} $progress The progress element
     * @param {jQuery} $results  The results element
     * @param {jQuery} $submit   The submit button
     */
    function processBatch(params, $progress, $results, $submit) {
        // Check if import has been cancelled
        if (importCancelled) {
            $('#top-ten-wpp-import-cancel').hide();
            $submit.prop('disabled', false);
            return;
        }

        // If we've processed all sites, we're done
        if (params.currentSiteIndex >= params.sites.length && params.sites.length > 0) {
            // Show final results
            showFinalResults(params, $progress, $results, $submit);
            return;
        }

        // Current site being processed
        var currentSite = params.sites.length ? params.sites[params.currentSiteIndex] : 0;

        // Update the progress indicator
        $progress.find('.import-status').html(
            (params.sites.length > 1 ? topTenWPPImporter.strings.processing_site + ' ' + currentSite + ' - ' : '') +
            topTenWPPImporter.strings.batch + ' ' + params.batch_number +
            (params.total_batches > 0 ? ' ' + topTenWPPImporter.strings.of + ' ' + params.total_batches : '')
        );

        // Calculate overall progress based on sites and batches
        var currentSiteIndex = params.currentSiteIndex;
        var totalSites = params.sites.length;
        var currentBatch = params.batch_number;
        var totalBatches = params.total_batches;

        // If we know the total batches, use more precise calculation
        if (totalBatches > 0) {
            var overallProgress = ((currentSiteIndex * totalBatches) + currentBatch) / (totalSites * totalBatches);
            updateProgressBar($progress, overallProgress * 100, 100);
        } else {
            // Simple calculation based on sites processed
            updateProgressBar($progress, currentSiteIndex + 1, totalSites);
        }

        // Debug info to help diagnose multisite issues
        /* console.log('Processing batch with params:', {
            currentSite: currentSite,
            isNetworkAdmin: params.is_network_admin,
            selectedSites: params.sites,
            batchNumber: params.batch_number
        }); */

        // Send AJAX request for this batch
        $.ajax({
            url: topTenWPPImporter.ajaxurl,
            type: 'POST',
            timeout: 60000, // 60 seconds timeout
            data: {
                action: 'top_ten_import_wpp',
                nonce: topTenWPPImporter.nonce,
                import_mode: params.import_mode,
                import_data: params.import_data,
                min_views: params.min_views,
                dry_run: params.dry_run,
                batch: true,
                blog_id: currentSite,
                batch_number: params.batch_number,
                is_network_admin: params.is_network_admin,
                sites: params.sites // Add sites array to fix multisite selection
            },
            success: function (response) {
                if (response.success) {
                    // Handle batch response
                    if (response.data.batch_status) {
                        // Store batch results for the current site
                        if (!params.results[currentSite]) {
                            params.results[currentSite] = {
                                posts_processed: 0,
                                total_counts: 0,
                                total_views_imported: 0,
                                daily_counts: 0,
                                daily_views_imported: 0,
                                errors: []
                            };
                        }

                        // Update the batch status
                        params.total_batches = response.data.total_batches || params.total_batches;

                        // Update the progress percentage
                        var currentSiteIndex = params.currentSiteIndex;
                        var totalSites = params.sites.length;
                        var currentBatch = params.batch_number;
                        var totalBatches = params.total_batches;

                        if (totalBatches > 0) {
                            var overallProgress = ((currentSiteIndex * totalBatches) + currentBatch) / (totalSites * totalBatches);
                            updateProgressBar($progress, overallProgress * 100, 100);
                        }

                        // Increment stats from this batch
                        if (response.data.batch_results) {
                            var batchResults = response.data.batch_results;
                            params.results[currentSite].posts_processed += parseInt(batchResults.posts_processed || 0);
                            params.results[currentSite].total_counts += parseInt(batchResults.total_counts || 0);
                            params.results[currentSite].total_views_imported += parseInt(batchResults.total_views_imported || 0);
                            params.results[currentSite].daily_counts += parseInt(batchResults.daily_counts || 0);
                            params.results[currentSite].daily_views_imported += parseInt(batchResults.daily_views_imported || 0);

                            // Add any errors
                            if (batchResults.errors && batchResults.errors.length) {
                                params.results[currentSite].errors =
                                    params.results[currentSite].errors.concat(batchResults.errors);
                            }
                        }

                        // If we have more batches for this site
                        if (response.data.has_more_batches) {
                            params.batch_number++;
                            // Process the next batch for this site
                            processBatch(params, $progress, $results, $submit);
                            return;
                        } else {
                            // Move to the next site
                            params.currentSiteIndex++;
                            params.batch_number = 1;
                            // Process the first batch of the next site
                            processBatch(params, $progress, $results, $submit);
                            return;
                        }
                    } else {
                        // This is the final success response - for compatibility with non-batch responses
                        // Process the response.data.results if available
                        if (response.data.results) {
                            var results = response.data.results;

                            // Add these results to our params.results
                            $.each(results, function (blogId, result) {
                                params.results[blogId] = result;
                            });
                        }

                        // Show final results
                        showFinalResults(params, $progress, $results, $submit);
                    }
                } else {
                    // Properly handle server-side errors
                    var errorMessage = response.data && response.data.message
                        ? response.data.message
                        : topTenWPPImporter.strings.unknown_error;
                    handleError(errorMessage);
                }
            },
            error: function (xhr, status, error) {
                if (status === 'timeout') {
                    handleError(topTenWPPImporter.strings.timeout_error);
                } else {
                    var errorMessage = topTenWPPImporter.strings.server_error + ' ' + (xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : error);
                    handleError(errorMessage);
                }
            }
        });
    }

    /**
     * Handle error display.
     *
     * @param {string} error Error message to display.
     */
    function handleError(error) {
        var $progress = $('#top-ten-wpp-import-progress');
        var $results = $('#top-ten-wpp-import-results');
        var $submit = $('#top-ten-wpp-import-submit');
        var $cancelBtn = $('#top-ten-wpp-import-cancel');

        $progress
            .removeClass('hidden notice-info notice-success')
            .addClass('notice-error')
            .html('<strong>' + topTenWPPImporter.strings.import_error + '</strong>');

        $results
            .removeClass('hidden notice-success')
            .addClass('notice-error')
            .html(topTenWPPImporter.strings.import_error + '<br><br>' + escapeHTML(error));

        // Re-enable the submit button and hide cancel button
        $submit.prop('disabled', false);
        $cancelBtn.hide();
    }

    /**
     * Display the final results after all batches are complete
     *
     * @param {Object} params    The import parameters
     * @param {jQuery} $progress The progress element
     * @param {jQuery} $results  The results element
     * @param {jQuery} $submit   The submit button
     */
    function showFinalResults(params, $progress, $results, $submit) {
        // Enable the submit button and hide cancel button
        $submit.prop('disabled', false);
        $('#top-ten-wpp-import-cancel').hide();

        // Update progress status
        $progress
            .removeClass('notice-info')
            .addClass('notice-success')
            .removeClass('hidden')
            .html('<strong>' + topTenWPPImporter.strings.import_complete + '</strong>');

        // Prepare to show results
        $results
            .removeClass('hidden notice-error')
            .addClass('notice-success');

        var message = '<br />';
        var isDryRun = params.dry_run ? true : false;
        var runType = isDryRun ? topTenWPPImporter.strings.dry_run + ' ' : '';
        // Calculate the number of sites processed - use the number of keys in the results object
        var sitesProcessed = Object.keys(params.results).length;

        // If we have a multisite setup with multiple sites
        if (Object.keys(params.results).length > 1) {
            // Multi-site results
            message += '<strong>' + runType + topTenWPPImporter.strings.sites_processed + ' ' + sitesProcessed + '</strong><br><br>';

            $.each(params.results, function (blogId, result) {
                message += '<strong>' + topTenWPPImporter.strings.blog_id + ' ' + blogId + ':</strong><br>';
                message += topTenWPPImporter.strings.posts_processed + ' ' + (result.posts_processed || 0) + '<br>';
                message += topTenWPPImporter.strings.total_records + ' ' + (result.total_counts || 0) + '<br>';
                message += topTenWPPImporter.strings.total_views_found + ' ' + (result.total_views_imported || 0) + '<br>';
                message += topTenWPPImporter.strings.daily_records + ' ' + (result.daily_counts || 0) + '<br>';
                message += topTenWPPImporter.strings.daily_views_found + ' ' + (result.daily_views_imported || 0) + '<br>';

                if (result.errors && result.errors.length) {
                    message += '<strong>' + topTenWPPImporter.strings.errors + '</strong><br>';
                    $.each(result.errors, function (i, error) {
                        message += '- ' + escapeHTML(error) + '<br>';
                    });
                }
                message += '<br>';
            });
        } else {
            // Single site result
            var siteId = Object.keys(params.results)[0] || 0;
            var results = params.results[siteId] || {
                posts_processed: 0,
                total_counts: 0,
                total_views_imported: 0,
                daily_counts: 0,
                daily_views_imported: 0,
                errors: []
            };

            message += '<strong>' + runType + topTenWPPImporter.strings.results + '</strong><br>';
            message += topTenWPPImporter.strings.posts_processed + ' ' + (results.posts_processed || 0) + '<br>';
            message += topTenWPPImporter.strings.total_records + ' ' + (results.total_counts || 0) + '<br>';
            message += topTenWPPImporter.strings.total_views_found + ' ' + (results.total_views_imported || 0) + '<br>';
            message += topTenWPPImporter.strings.daily_records + ' ' + (results.daily_counts || 0) + '<br>';
            message += topTenWPPImporter.strings.daily_views_found + ' ' + (results.daily_views_imported || 0) + '<br>';

            if (results.errors && results.errors.length) {
                message += '<strong>' + topTenWPPImporter.strings.errors + '</strong><br>';
                $.each(results.errors, function (i, error) {
                    message += '- ' + escapeHTML(error) + '<br>';
                });
            }
        }

        // Display the results
        $results.html(message);
    }
});
