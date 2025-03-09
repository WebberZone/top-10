<?php
/**
 * Functions to import data from WordPress Popular Posts to Top 10.
 *
 * @link  https://webberzone.com
 * @since 4.1.0
 *
 * @package    Top 10
 */

declare(strict_types=1);

namespace WebberZone\Top_Ten\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class WPP_Importer
 *
 * Handles importing WordPress Popular Posts data into Top 10 tables.
 *
 * @since 4.1.0
 */
class WPP_Importer {

	/**
	 * Initialize hooks.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'tptn_admin_import_export_tab_content', array( $this, 'render_page' ) );
		add_action( 'wp_ajax_top_ten_import_wpp', array( $this, 'process_ajax_import' ) );
		add_action( 'admin_post_top_ten_import_wpp', array( $this, 'handle_import_request' ) );
	}

	/**
	 * Renders the importer form.
	 *
	 * This is meant to be called within tools-page.php.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function render_page() {
		// Enqueue required scripts.
		$this->enqueue_scripts();
		?>
		<div class="wrap">
			<hr />
			<h2 style="padding-left:0px;font-size: 1.5em;"><?php esc_html_e( 'Migrate WordPress Popular Posts Data to Top 10', 'top-10' ); ?></h2>
			
			<?php
			$wpp_active   = self::is_wpp_active();
			$tables_exist = self::wpp_tables_exist();
			?>
			
			<?php if ( ! $wpp_active && ! $tables_exist ) : ?>
				<p class="notice notice-error"><?php esc_html_e( 'WordPress Popular Posts plugin is not active and no WPP tables were found in the database. Import is not possible.', 'top-10' ); ?></p>
			<?php elseif ( ! $tables_exist ) : ?>
				<p class="notice notice-error"><?php esc_html_e( 'WordPress Popular Posts tables were not found in the database. Import is not possible.', 'top-10' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'This tool allows you to import post view counts from WordPress Popular Posts into Top 10.', 'top-10' ); ?></p>
				<div id="top-ten-import-notice" class="notice notice-warning">
					<p>
						<strong><?php esc_html_e( 'Important:', 'top-10' ); ?></strong>
						<?php esc_html_e( 'Please backup your database before proceeding with the import.', 'top-10' ); ?>
					</p>
				</div>
				
				<div class="postbox">
					<h3 class="hndle"><?php esc_html_e( 'Important Notes', 'top-10' ); ?></h3>
					<div class="inside">
						<ul style="list-style: disc; padding-left: 20px;">
							<li><?php esc_html_e( 'WordPress Popular Posts tracks each individual view, while Top 10 tracks cumulative views and daily views by hour.', 'top-10' ); ?></li>
							<li><?php esc_html_e( 'For daily data, the importer will consolidate WPP data by hour to match Top 10\'s format.', 'top-10' ); ?></li>
							<li><?php esc_html_e( 'It\'s recommended to backup your database before running this import.', 'top-10' ); ?></li>
							<li><?php esc_html_e( 'Large sites may experience timeouts during import. If this happens, try importing with a higher minimum view count.', 'top-10' ); ?></li>
						</ul>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if ( $wpp_active && $tables_exist ) : ?>
			<form id="top-ten-wpp-import-form" method="post">
				<?php wp_nonce_field( 'top_ten_import_wpp_nonce', 'top_ten_import_wpp_nonce_field' ); ?>
				<input type="hidden" name="action" value="top_ten_import_wpp">
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Import Mode', 'top-ten' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Import Mode', 'top-ten' ); ?></legend>
								<label>
									<input type="radio" name="import_mode" value="merge">
									<span><?php esc_html_e( 'Merge data (add WPP counts to existing Top 10 counts)', 'top-ten' ); ?></span>
								</label>
								<br>
								<label>
									<input type="radio" name="import_mode" value="replace" checked="checked">
									<span><?php esc_html_e( 'Replace data (replace Top 10 counts with WPP counts)', 'top-ten' ); ?></span>
								</label>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Merge mode will add WPP counts to existing Top 10 counts, while replace mode will replace Top 10 counts with WPP counts if they exists for the same post.', 'top-ten' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Data to Import', 'top-ten' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><?php esc_html_e( 'Data to Import', 'top-ten' ); ?></legend>
								<label>
									<input type="radio" name="import_data" value="total" checked="checked">
									<span><?php esc_html_e( 'Total counts only', 'top-ten' ); ?></span>
								</label>
								<br>
								<label>
									<input type="radio" name="import_data" value="daily">
									<span><?php esc_html_e( 'Daily counts only', 'top-ten' ); ?></span>
								</label>
								<br>
								<label>
									<input type="radio" name="import_data" value="both">
									<span><?php esc_html_e( 'Both total and daily counts', 'top-10' ); ?></span>
								</label>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Total counts only will import only the total view counts from WPP, while daily counts only will import only the daily view counts from WPP. Both total and daily counts will import both total and daily view counts from WPP. If you have a very large dataset, it might be better to import total and daily counts separately.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Minimum View Count', 'top-ten' ); ?></th>
						<td>
							<input type="number" name="min_views" value="1" min="1" step="1">
							<p class="description"><?php esc_html_e( 'Only import posts with at least this many views. Use this setting to filter out posts with very few views.', 'top-ten' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Dry Run', 'top-ten' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="dry_run" value="1" checked="checked">
								<?php esc_html_e( 'Enable Dry Run (simulate import)', 'top-ten' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'A dry run will simulate the import process without actually updating the database. This is useful for testing the import parameters.', 'top-ten' ); ?></p>
						</td>
					</tr>
					<?php if ( is_multisite() && is_network_admin() ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Select Sites', 'top-ten' ); ?></th>
							<td>
								<?php
								$sites = get_sites();
								if ( ! empty( $sites ) ) {
									foreach ( $sites as $site ) {
										$blog_details = get_blog_details( $site->blog_id );
										printf(
											'<label><input type="checkbox" name="sites[]" value="%1$d"> %2$s (ID: %1$d)</label><br>',
											absint( $site->blog_id ),
											esc_html( $blog_details->blogname )
										);
									}
								}
								?>
							</td>
						</tr>
					<?php endif; ?>
				</table>
				<?php submit_button( esc_html__( 'Run Import', 'top-ten' ), 'primary', 'submit', true, array( 'id' => 'top-ten-wpp-import-submit' ) ); ?>

				<p id="top-ten-wpp-import-progress" class="hidden notice notice-info">
				</p>
				<p id="top-ten-wpp-import-results" class="hidden notice notice-info">
				</p>
			</form>
			<?php endif; ?>
		</div>
		<?php
	}
	/**
	 * Process import parameters from a request array.
	 *
	 * @since 4.1.0
	 * @param array $request Request data array.
	 * @return array Processed import parameters
	 */
	private function process_import_parameters( array $request ): array {
		$parameters = array(
			'import_mode' => isset( $request['import_mode'] ) && 'merge' === sanitize_text_field( wp_unslash( $request['import_mode'] ) ) ? 'merge' : 'replace',
			'import_data' => isset( $request['import_data'] ) ? sanitize_text_field( wp_unslash( $request['import_data'] ) ) : 'total',
			'min_views'   => isset( $request['min_views'] ) ? absint( $request['min_views'] ) : 1,
			'blog_id'     => ! empty( $request['blog_id'] ) ? absint( $request['blog_id'] ) : get_current_blog_id(),
			'dry_run'     => isset( $request['dry_run'] ) && ( 1 === absint( $request['dry_run'] ) || true === $request['dry_run'] ),
		);

		// Validate import_data is one of the allowed values.
		if ( ! in_array( $parameters['import_data'], array( 'total', 'daily', 'both' ), true ) ) {
			$parameters['import_data'] = 'total';
		}

		return $parameters;
	}

	/**
	 * Process AJAX import request.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function process_ajax_import(): void {
		check_ajax_referer( 'top_ten_import_wpp_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have sufficient permissions to perform this action.', 'top-10' ),
				)
			);
		}

		// Validate WPP plugin and tables.
		if ( ! self::is_wpp_active() || ! self::wpp_tables_exist() ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'WordPress Popular Posts plugin is not active or its tables are missing.', 'top-10' ),
				)
			);
		}

		// Get import options from AJAX request.
		$params = $this->process_import_parameters( $_POST );

		// Determine sites to process.
		$sites            = array();
		$is_network_admin = is_multisite() && isset( $_POST['is_network_admin'] ) && '1' === $_POST['is_network_admin'];

		if ( isset( $_POST['sites'] ) && is_array( $_POST['sites'] ) ) {
			$sites = array_map( 'absint', $_POST['sites'] );
		} elseif ( ! $is_network_admin ) {
			$sites[] = $params['blog_id'];
		}

		// No sites to process.
		if ( empty( $sites ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'No sites selected for import.', 'top-10' ),
				)
			);
		}

		$import_results = array();

		// Process each selected site.
		foreach ( $sites as $blog_id ) {
			$result = self::do_import_for_site(
				$blog_id,
				$params['import_mode'],
				$params['dry_run'],
				$params['import_data'],
				$params['min_views']
			);

			$import_results[ $blog_id ] = $result;
		}

		// Send success response with dry_run flag and all results.
		wp_send_json_success(
			array(
				'message'         => $params['dry_run'] ? esc_html__( 'Dry run completed successfully.', 'top-10' ) : esc_html__( 'Import completed successfully.', 'top-10' ),
				'results'         => $import_results,
				'dry_run'         => $params['dry_run'],
				'sites_processed' => count( $sites ),
			)
		);
	}

	/**
	 * Check if WordPress Popular Posts plugin is active
	 *
	 * @since 4.1.0
	 * @return bool Whether WPP is active.
	 */
	private function is_wpp_active(): bool {
		return defined( 'WPP_VERSION' );
	}

	/**
	 * Check if WPP tables exist in the database
	 *
	 * @since 4.1.0
	 * @return bool Whether WPP tables exist.
	 */
	private function wpp_tables_exist(): bool {
		global $wpdb;

		$tables_exist = true;

		$data_table    = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}popularpostsdata'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$summary_table = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}popularpostssummary'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! $data_table || ! $summary_table ) {
			$tables_exist = false;
		}

		return $tables_exist;
	}

	/**
	 * Enqueues required scripts and styles.
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script( 'top-ten-wpp-importer-js' );
		wp_localize_script(
			'top-ten-wpp-importer-js',
			'topTenWPPImporter',
			array(
				'nonce'   => wp_create_nonce( 'top_ten_import_wpp_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'strings' => array(
					'confirm'           => esc_html__( 'Are you sure you want to proceed with the import? This operation cannot be undone.', 'top-10' ),
					'importing'         => esc_html__( 'Importing data...', 'top-10' ),
					'import_complete'   => esc_html__( 'Import completed successfully!', 'top-10' ),
					'import_error'      => esc_html__( 'An error occurred during import:', 'top-10' ),
					'cancel_confirm'    => esc_html__( 'Are you sure you want to cancel the import? All progress will be lost.', 'top-10' ),
					'cancel_button'     => esc_html__( 'Cancel Import', 'top-10' ),
					'import_cancelled'  => esc_html__( 'Import cancelled by user.', 'top-10' ),
					'no_sites_selected' => esc_html__( 'Please select at least one site to import data from.', 'top-10' ),
					'invalid_min_views' => esc_html__( 'Minimum views must be a non-negative number.', 'top-10' ),
					'timeout_error'     => esc_html__( 'Request timed out. The server might be processing a large amount of data. Try again with a smaller batch size or reduce the number of sites processed at once.', 'top-10' ),
					'processing_site'   => esc_html__( 'Processing Site', 'top-10' ),
					'batch'             => esc_html__( 'Batch', 'top-10' ),
					'of'                => esc_html__( 'of', 'top-10' ),
					'dry_run'           => esc_html__( 'Dry Run -', 'top-10' ),
					'sites_processed'   => esc_html__( 'Sites Processed:', 'top-10' ),
					'blog_id'           => esc_html__( 'Blog ID', 'top-10' ),
					'posts_processed'   => esc_html__( 'Posts Processed:', 'top-10' ),
					'total_records'     => esc_html__( 'Total Records:', 'top-10' ),
					'total_views_found' => esc_html__( 'Total Views Found:', 'top-10' ),
					'daily_records'     => esc_html__( 'Daily Records:', 'top-10' ),
					'daily_views_found' => esc_html__( 'Daily Views Found:', 'top-10' ),
					'errors'            => esc_html__( 'Errors:', 'top-10' ),
					'results'           => esc_html__( 'Results:', 'top-10' ),
					'error'             => esc_html__( 'Error:', 'top-10' ),
					'server_error'      => esc_html__( 'Server error:', 'top-10' ),
					'unknown_error'     => esc_html__( 'Unknown error occurred during import', 'top-10' ),
					'starting'          => esc_html__( 'Starting...', 'top-10' ),
				),
			)
		);
	}

	/**
	 * Handles the import form submission.
	 *
	 * @since 4.1.0
	 *
	 * @return void
	 */
	public function handle_import_request() {
		// Verify nonce.
		if ( ! isset( $_POST['top_ten_import_wpp_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['top_ten_import_wpp_nonce_field'] ) ), 'top_ten_import_wpp_nonce' ) ) {
			wp_die( esc_html__( 'Nonce verification failed', 'top-ten' ) );
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action', 'top-ten' ) );
		}

		// Get import parameters using our helper method.
		$params = $this->process_import_parameters( $_POST );

		// Determine sites.
		$sites = array();
		if ( is_multisite() && is_network_admin() ) {
			if ( isset( $_POST['sites'] ) && is_array( $_POST['sites'] ) ) {
				$sites = array_map( 'absint', $_POST['sites'] );
			}
		} else {
			$sites[] = get_current_blog_id();
		}

		if ( empty( $sites ) ) {
			wp_die( esc_html__( 'No sites selected for import.', 'top-ten' ) );
		}

		$import_results = array();

		// Process each selected site.
		foreach ( $sites as $blog_id ) {
			/**
			 * Action fired before processing a site.
			 *
			 * @param int $blog_id The blog ID being processed.
			 */
			do_action( 'top_10_importer_before', $blog_id );

			$result = self::do_import_for_site(
				$blog_id,
				$params['import_mode'],
				$params['dry_run'],
				$params['import_data'],
				$params['min_views']
			);

			$import_results[ $blog_id ] = $result;
			/**
			 * Action fired after processing a site.
			 *
			 * @param int   $blog_id The blog ID that was processed.
			 * @param array $result  The results array.
			 */
			do_action( 'top_10_importer_after', $blog_id, $result );
		}

		// Build summary message.
		$message = '';
		foreach ( $import_results as $blog_id => $result ) {
			$blog_details = get_blog_details( $blog_id );
			/* translators: 1. Blog ID. */
			$blog_name = $blog_details ? $blog_details->blogname : sprintf( __( 'Blog ID %d', 'top-ten' ), $blog_id );
			$message  .= sprintf(
				/* translators: 1: Site name, 2: Blog ID, 3: Total records processed, 4: Daily records processed, 5: Dry run notice */
				__( 'Site %1$s (ID: %2$d): Total Counts Processed: %3$d, Daily Records Processed: %4$d%5$s', 'top-ten' ),
				esc_html( $blog_name ),
				$blog_id,
				isset( $result['total_counts'] ) ? $result['total_counts'] : 0,
				isset( $result['daily_counts'] ) ? $result['daily_counts'] : 0,
				$params['dry_run'] ? ' (Dry Run)' : ''
			) . '<br>';
		}

		$redirect_url = add_query_arg( 'top_ten_import_message', rawurlencode( $message ), wp_get_referer() );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Performs the import for a single site.
	 *
	 * @since 4.1.0
	 *
	 * @param int    $blog_id     Blog ID to import from.
	 * @param string $import_mode Either 'merge' or 'replace'.
	 * @param bool   $dry_run     Whether to simulate the import.
	 * @param string $import_data Type of data to import: 'total', 'daily', or 'both'.
	 * @param int    $min_views   Minimum number of views to import.
	 * @return array Result counts and status.
	 * @throws \Exception If there's an error during the import process.
	 */
	private function do_import_for_site( int $blog_id, string $import_mode = 'replace', bool $dry_run = false, string $import_data = 'both', int $min_views = 1 ): array {
		global $wpdb;

		$results = array(
			'total_counts'         => 0,
			'daily_counts'         => 0,
			'posts_processed'      => 0,
			'total_views_imported' => 0,
			'daily_views_imported' => 0,
			'errors'               => array(),
			'status'               => 'success',
		);

		// If multisite and blog is not current, switch context.
		$switched = false;
		if ( is_multisite() && get_current_blog_id() !== $blog_id ) {
			switch_to_blog( $blog_id );
			$switched = true;
		}

		try {
			// Verify WPP tables exist.
			if ( ! self::wpp_tables_exist() ) {
				throw new \Exception( sprintf( 'WPP tables not found for blog ID: %d', $blog_id ) );
			}

			// Process total counts if needed.
			if ( 'total' === $import_data || 'both' === $import_data ) {
				// Import total view counts using the efficient bulk method.
				if ( ! $dry_run ) {
					$total_import_result = self::import_total_counts( $import_mode, $min_views, $blog_id );

					// Merge any errors from the total import.
					if ( ! empty( $total_import_result['errors'] ) ) {
						$results['errors'] = array_merge( $results['errors'], $total_import_result['errors'] );
					}

					// Update the results counts.
					$results['total_views_imported'] = $total_import_result['views'];
					$results['total_counts']         = $total_import_result['rows_affected'] ?? 0;

					// Increment posts processed count.
					$results['posts_processed'] += $results['total_counts'];
				} else {
					// For dry run, just count records that would be imported.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$count_results = $wpdb->get_row(
						$wpdb->prepare(
							"SELECT
							COUNT(DISTINCT postid) AS post_count,
							SUM(pageviews) AS total_views
							FROM (
								SELECT
								postid,
								COUNT(*) AS pageviews
								FROM {$wpdb->prefix}popularpostsdata
								GROUP BY postid
								HAVING pageviews >= %d
							) AS subquery",
							$min_views
						)
					);

					$results['total_counts']         = $count_results->post_count ?? 0;
					$results['total_views_imported'] = $count_results->total_views ?? 0;
					$results['posts_processed']     += $results['total_counts'];
				}
			}

			// Process daily views.
			if ( 'daily' === $import_data || 'both' === $import_data ) {
				// Import daily view counts from WPP using the efficient bulk method.
				if ( ! $dry_run ) {
					$daily_import_result = self::import_daily_counts( $import_mode, $min_views, $blog_id );

					// Merge any errors from the daily import.
					if ( ! empty( $daily_import_result['errors'] ) ) {
						$results['errors'] = array_merge( $results['errors'], $daily_import_result['errors'] );
					}

					// Update the results counts.
					$results['daily_views_imported'] = $daily_import_result['views'];
					$results['daily_counts']         = $daily_import_result['rows_affected'] ?? 0;

					// Increment posts processed count (estimate based on rows affected).
					$results['posts_processed'] += $results['daily_counts'];
				} else {
					// For dry run, just count records that would be imported.
					$count_results = $wpdb->get_row(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$wpdb->prepare(
							"SELECT
							COUNT(*) AS total_entries,
							COUNT(DISTINCT postid) AS post_count,
							SUM(hourly_views) AS total_views
							FROM (
								SELECT
								postid,
								DATE_FORMAT(MIN(view_datetime), '%%Y-%%m-%%d %%H:00:00') AS dp_date,
								COUNT(*) AS hourly_views
								FROM {$wpdb->prefix}popularpostssummary
								GROUP BY postid, DATE(view_datetime), HOUR(view_datetime)
								HAVING hourly_views >= %d
							) AS subquery",
							$min_views
						)
					);

					$results['daily_counts']         = $count_results->post_count ?? 0;
					$results['daily_views_imported'] = $count_results->total_views ?? 0;
					$results['posts_processed']     += $results['daily_counts'];
				}
			}
		} catch ( \Exception $e ) {
			$results['errors'][] = $e->getMessage();
			$results['status']   = 'error';
		} finally {
			// Restore original blog if switched.
			if ( $switched ) {
				restore_current_blog();
			}
		}

		return $results;
	}

	/**
	 * Efficiently import total counts using a bulk operation approach.
	 *
	 * @since 4.1.0
	 * @param string $import_mode Either 'merge' or 'replace'.
	 * @param int    $min_views   Minimum number of views to import.
	 * @param int    $blog_id     Blog ID for multisite.
	 * @return array Result with views count and any errors.
	 */
	private function import_total_counts( string $import_mode, int $min_views, int $blog_id ): array {
		global $wpdb;

		$result = array(
			'views'         => 0,
			'errors'        => array(),
			'rows_affected' => 0,
		);

		try {
			// Prepare a temporary table to hold the aggregated view counts.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'CREATE TEMPORARY TABLE IF NOT EXISTS temp_total_views (
				post_id BIGINT,
				total_views BIGINT
			)'
			);

			// Clear the temporary table if it exists.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				'TRUNCATE TABLE temp_total_views'
			);

			// Aggregate WPP views into total counts.
			$aggregate_query = $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				"INSERT INTO temp_total_views (post_id, total_views)
				SELECT 
					postid, 
					SUM(pageviews) AS total_views
				FROM {$wpdb->prefix}popularpostsdata
				GROUP BY postid
				HAVING total_views >= %d",
				$min_views
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $aggregate_query );

			// Get total views before proceeding.
			$total_views     = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				'SELECT SUM(total_views) FROM temp_total_views'
			) ?? 0;
			$result['views'] = (int) $total_views;

			if ( 'merge' === $import_mode ) {
				// Merge query using temporary table.
				$merge_query = $wpdb->prepare(
					"INSERT INTO {$wpdb->base_prefix}top_ten (postnumber, cntaccess, blog_id)
					SELECT 
						post_id, 
						total_views,
						%d
					FROM temp_total_views
					ON DUPLICATE KEY UPDATE 
					cntaccess = cntaccess + VALUES(cntaccess)",
					$blog_id
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$rows_affected = $wpdb->query( $merge_query );
			} else { // Replace mode
				// First, get a list of the post IDs we're about to import.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$post_ids = $wpdb->get_col( 'SELECT DISTINCT post_id FROM temp_total_views' );

				if ( ! empty( $post_ids ) ) {
					$post_ids_list = implode( ',', array_map( 'intval', $post_ids ) );

					// Clear existing entries for these posts and this blog_id.
					$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->prepare(
							"DELETE FROM {$wpdb->base_prefix}top_ten 
							WHERE blog_id = %d AND postnumber IN ({$post_ids_list})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							$blog_id
						)
					);
				}

				// Then insert new data from temporary table.
				$insert_query = $wpdb->prepare(
					"INSERT INTO {$wpdb->base_prefix}top_ten (postnumber, cntaccess, blog_id)
					SELECT 
						post_id, 
						total_views,
						%d
					FROM temp_total_views",
					$blog_id
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$rows_affected = $wpdb->query( $insert_query );
			}

			// Store the number of rows affected.
			$result['rows_affected'] = false !== $rows_affected ? $rows_affected : 0;

			// Drop the temporary table.
			$wpdb->query( 'DROP TEMPORARY TABLE IF EXISTS temp_total_views' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange

			if ( false === $rows_affected ) {
				$result['errors'][] = __( 'Error importing total counts', 'top-10' );
			}
		} catch ( \Exception $e ) {
			$result['errors'][] = $e->getMessage();

			// Make sure we clean up the temporary table even if there's an error.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'DROP TEMPORARY TABLE IF EXISTS temp_total_views'
			);
		}

		return $result;
	}

	/**
	 * Efficiently import daily counts using a temporary table approach.
	 *
	 * @since 4.1.0
	 * @param string $import_mode Either 'merge' or 'replace'.
	 * @param int    $min_views   Minimum number of views to import.
	 * @param int    $blog_id     Blog ID for multisite.
	 * @return array Result with views count and any errors.
	 */
	private function import_daily_counts( string $import_mode, int $min_views, int $blog_id ): array {
		global $wpdb;

		$result = array(
			'views'         => 0,
			'errors'        => array(),
			'rows_affected' => 0,
		);

		try {
			// Prepare a temporary table to aggregate hourly views.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'CREATE TEMPORARY TABLE IF NOT EXISTS temp_hourly_views (
				post_id BIGINT,
				dp_date DATETIME,
				hourly_views BIGINT
			)'
			);

			// Clear the temporary table if it exists.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'TRUNCATE TABLE temp_hourly_views'
			);

			// Aggregate WPP views into hourly counts.
			$aggregate_query = $wpdb->prepare(
				"INSERT INTO temp_hourly_views (post_id, dp_date, hourly_views)
				SELECT 
					postid, 
					DATE_FORMAT(MIN(view_datetime), '%%Y-%%m-%%d %%H:00:00') AS dp_date,
					COUNT(*) AS hourly_views
				FROM {$wpdb->prefix}popularpostssummary
				GROUP BY postid, DATE(view_datetime), HOUR(view_datetime)
				HAVING hourly_views >= %d",
				$min_views
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $aggregate_query );

			// Get total views before proceeding.
			$total_views = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'SELECT SUM(hourly_views) FROM temp_hourly_views'
			) ?? 0;
			$result['views'] = (int) $total_views;

			if ( 'merge' === $import_mode ) {
				// Merge query using temporary table.
				$merge_query = $wpdb->prepare(
					"INSERT INTO {$wpdb->base_prefix}top_ten_daily (postnumber, cntaccess, dp_date, blog_id)
					SELECT 
						post_id, 
						hourly_views,
						dp_date,
						%d
					FROM temp_hourly_views
					ON DUPLICATE KEY UPDATE 
					cntaccess = cntaccess + VALUES(cntaccess)",
					$blog_id
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$rows_affected = $wpdb->query( $merge_query );
			} else { // Replace mode
				// First, get a list of the post IDs we're about to import.
				$post_ids = $wpdb->get_col( 'SELECT DISTINCT post_id FROM temp_hourly_views' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

				if ( ! empty( $post_ids ) ) {
					$post_ids_list = implode( ',', array_map( 'intval', $post_ids ) );

					// Clear existing entries for these posts and this blog_id.
					$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->prepare(
							"DELETE FROM {$wpdb->base_prefix}top_ten_daily 
							WHERE blog_id = %d AND postnumber IN ({$post_ids_list})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							$blog_id
						)
					);
				}

				// Then insert new data from temporary table.
				$insert_query = $wpdb->prepare(
					"INSERT INTO {$wpdb->base_prefix}top_ten_daily (postnumber, cntaccess, dp_date, blog_id)
					SELECT 
						post_id, 
						hourly_views,
						dp_date,
						%d
					FROM temp_hourly_views",
					$blog_id
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$rows_affected = $wpdb->query( $insert_query );
			}

			// Store the number of rows affected.
			$result['rows_affected'] = false !== $rows_affected ? $rows_affected : 0;

			// Drop the temporary table.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'DROP TEMPORARY TABLE IF EXISTS temp_hourly_views'
			);

			if ( false === $rows_affected ) {
				$result['errors'][] = __( 'Error importing daily counts', 'top-10' );
			}
		} catch ( \Exception $e ) {
			$result['errors'][] = $e->getMessage();

			// Make sure we clean up the temporary table even if there's an error.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'DROP TEMPORARY TABLE IF EXISTS temp_hourly_views'
			);
		}

		return $result;
	}
}
