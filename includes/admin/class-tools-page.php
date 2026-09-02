<?php
/**
 * Tools Page class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Counter;
use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generates the Tools page.
 *
 * @since 3.3.0
 */
class Tools_Page {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.3.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		Hook_Registry::add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
		Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'handle_recreate_tables_action' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'handle_create_missing_tables_action' ) );

		// Clear table statistics cache when counts are updated.
		Hook_Registry::add_action( 'tptn_count_updated', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
		Hook_Registry::add_action( 'tptn_delete_counts', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
		Hook_Registry::add_action( 'tptn_set_count', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function admin_menu() {

		$this->parent_id = add_submenu_page(
			'tptn_dashboard',
			esc_html__( 'Top 10 Tools', 'top-10' ),
			esc_html__( 'Tools', 'top-10' ),
			'manage_options',
			'tptn_tools_page',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.3.0
	 */
	public function network_admin_menu() {

		$this->parent_id = add_submenu_page(
			'tptn_dashboard',
			esc_html__( 'Top 10 Tools', 'top-10' ),
			esc_html__( 'Tools', 'top-10' ),
			'manage_network_options',
			'tptn_network_tools_page',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'top-ten-admin-js' );
			wp_enqueue_style( 'top-ten-admin-css' );
			wp_enqueue_style( 'wp-spinner' );
			wp_localize_script(
				'top-ten-admin-js',
				'top_ten_admin_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'security' => wp_create_nonce( 'tptn-admin' ),
					'strings'  => array(
						'confirm_message'         => esc_html__( 'Are you sure you want to clear the cache?', 'top-10' ),
						'network_confirm_message' => esc_html__( 'Are you sure you want to clear the Top 10 cache across all active sites?', 'top-10' ),
						'clearing_text'           => esc_html__( 'Clearing...', 'top-10' ),
						'fail_message'            => esc_html__( 'Failed to clear cache. Please try again.', 'top-10' ),
						'request_fail_message'    => esc_html__( 'Request failed: ', 'top-10' ),
					),
				)
			);
		}
	}

	/**
	 * Render the tools settings page.
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function render_page() {
		$screen       = get_current_screen();
		$network_wide = is_multisite() && is_network_admin();

		// Keep the screen check as a fallback for admin screen implementations that
		// do not expose is_network_admin() reliably.
		if ( $screen && $screen->id === $this->parent_id . '-network' ) {
			$network_wide = true;
		}

		/* Recreate the shared table indexes. */
		if ( ( ! is_multisite() || $network_wide ) && isset( $_POST['tptn_recreate_primary_key'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			self::recreate_primary_key();
			add_settings_error( 'tptn-notices', '', esc_html__( 'Primary Key has been recreated', 'top-10' ), 'updated' );
		}

		/* Truncate overall posts table */
		if ( isset( $_POST['tptn_reset_overall'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			if ( ! is_multisite() || $network_wide ) {
				Database::truncate_table( Database::get_table( false ) );
			} else {
				Counter::delete_counts( array( 'daily' => false ) );
			}
			Dashboard_Widgets::clear_network_dashboard_cache();
			add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 popular posts reset', 'top-10' ), 'updated' );
		}

		/* Truncate daily posts table */
		if ( isset( $_POST['tptn_reset_daily'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			if ( ! is_multisite() || $network_wide ) {
				Database::truncate_table( Database::get_table( true ) );
			} else {
				Counter::delete_counts( array( 'daily' => true ) );
			}
			Dashboard_Widgets::clear_network_dashboard_cache();
			add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 daily popular posts reset', 'top-10' ), 'updated' );
		}

		/* Recreate the shared tables. */
		if ( ( ! is_multisite() || $network_wide ) && isset( $_POST['tptn_recreate_tables'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			$result = self::recreate_tables();
			if ( is_wp_error( $result ) ) {
				add_settings_error( 'tptn-notices', '', $result->get_error_message(), 'error' );
			} else {
				add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 tables have been recreated', 'top-10' ), 'updated' );
			}
		}

		/* Sync funnel (run aggregation now) */
		if ( isset( $_POST['tptn_sync_funnel'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			$blog_id = ( is_multisite() && ! $network_wide ) ? get_current_blog_id() : null;
			$result  = Database::aggregate_visit_log( 10000, $blog_id );
			if ( true === $result ) {
				$message = $network_wide
					? __( 'The network funnel has been synced. Buffered visits from all sites have been aggregated.', 'top-10' )
					: __( 'The site funnel has been synced. Buffered visits for this site have been aggregated.', 'top-10' );
				add_settings_error( 'tptn-notices', '', esc_html( $message ), 'updated' );
			} elseif ( 0 === $result ) {
				$message = $network_wide
					? __( 'Nothing to sync. The network funnel is empty.', 'top-10' )
					: __( 'Nothing to sync. This site funnel is empty.', 'top-10' );
				add_settings_error( 'tptn-notices', '', esc_html( $message ), 'updated' );
			} elseif ( false === $result ) {
				add_settings_error( 'tptn-notices', '', esc_html__( 'Sync skipped: another aggregation is already in progress. Please try again in a moment.', 'top-10' ), 'updated' );
			} elseif ( is_wp_error( $result ) ) {
				add_settings_error(
					'tptn-notices',
					'',
					sprintf(
						/* translators: %s: error message from the database */
						esc_html__( 'Sync failed: %s', 'top-10' ),
						esc_html( $result->get_error_message() )
					),
					'error'
				);
			}
		}

		/* Fix cron schedules */
		if ( isset( $_POST['tptn_fix_crons'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			$result = self::fix_crons( $network_wide );
			if ( is_wp_error( $result ) ) {
				add_settings_error( 'tptn-notices', '', implode( ' ', array_map( 'esc_html', $result->get_error_messages() ) ), 'error' );
			} else {
				add_settings_error( 'tptn-notices', '', esc_html( $result ), 'updated' );
			}
		}

		ob_start();
		?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Top 10 Tools', 'top-10' ); ?></h1>
		<?php do_action( 'tptn_settings_page_header' ); ?>

		<?php settings_errors(); ?>

		<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">

			<form method="post" >

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Status', 'top-10' ); ?></span></h2>
					<div class="inside">
						<div class="tptn-db-status">
							<?php echo self::get_db_status_report(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Clear cache', 'top-10' ); ?></span></h2>
					<div class="inside">
						<p>
							<?php
								printf(
									'<button type="button" name="tptn_cache_clear" class="button button-secondary tptn_cache_clear" data-network-wide="%1$s" aria-label="%2$s">%2$s</button>',
									$network_wide ? '1' : '0',
									esc_html( $network_wide ? __( 'Clear Cache Across Network', 'top-10' ) : __( 'Clear cache', 'top-10' ) )
								);
							?>
						</p>
						<p class="description">
							<?php
							if ( $network_wide ) {
								esc_html_e( 'Clear the Top 10 cache for all active sites in the network.', 'top-10' );
							} else {
								esc_html_e( 'Clear the Top 10 cache. This will also be cleared automatically when you save the settings page.', 'top-10' );
							}
							?>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php echo esc_html( $network_wide ? __( 'Sync Network Funnel', 'top-10' ) : __( 'Sync Funnel', 'top-10' ) ); ?></span></h2>
					<div class="inside">
						<p>
							<button name="tptn_sync_funnel" type="submit" id="tptn_sync_funnel" class="button button-secondary"><?php echo esc_html( $network_wide ? __( 'Sync Network Funnel Now', 'top-10' ) : __( 'Sync Funnel Now', 'top-10' ) ); ?></button>
						</p>
						<p class="description">
							<?php
							if ( $network_wide ) {
								esc_html_e( 'Drain buffered visits from all sites into the count tables immediately. This is equivalent to running the aggregation cron job.', 'top-10' );
							} else {
								esc_html_e( 'Drain this site\'s buffered visits into the count tables immediately. Other sites\' buffered visits are not affected.', 'top-10' );
							}
							?>
						</p>
					</div>
				</div>

				<?php do_action( 'tptn_tools_page_actions', $network_wide ); ?>

				<div class="postbox">
					<h2><span><?php echo esc_html( $network_wide ? __( 'Fix Network Cron Schedules', 'top-10' ) : __( 'Fix Cron Schedules', 'top-10' ) ); ?></span></h2>
					<div class="inside">
						<p>
							<button name="tptn_fix_crons" type="submit" id="tptn_fix_crons" class="button button-secondary"><?php echo esc_html( $network_wide ? __( 'Fix Network Cron Schedules', 'top-10' ) : __( 'Fix Cron Schedules', 'top-10' ) ); ?></button>
						</p>
						<p class="description">
							<?php
							if ( $network_wide ) {
								esc_html_e( 'Clears and reschedules the Top 10 maintenance and aggregation jobs for all active sites. Use this if a site\'s job is not scheduled or if your error log reports cron reschedule errors.', 'top-10' );
							} else {
								esc_html_e( 'Clears and reschedules the Top 10 cron jobs for this site. Use this if the status above shows a job as not scheduled or if your error log reports cron reschedule errors for the Top 10 hooks.', 'top-10' );
							}
							?>
						</p>
					</div>
				</div>

				<?php if ( ! is_multisite() || $network_wide ) : ?>
					<div class="postbox">
						<h2><span><?php esc_html_e( 'Recreate Primary Key', 'top-10' ); ?></span></h2>
						<div class="inside">
							<p>
								<button name="tptn_recreate_primary_key" type="submit" id="tptn_recreate_primary_key" class="button button-secondary"><?php esc_attr_e( 'Recreate Primary Key', 'top-10' ); ?></button>
							</p>
							<p class="description">
								<?php esc_html_e( 'Deletes and reinitializes the primary key in the shared database tables. Run this from Network Admin on multisite installations. Remember to back up your database first!', 'top-10' ); ?>
							</p>
							<p>
								<code style="display:block;"><?php echo self::recreate_primary_key_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></code>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Reset database', 'top-10' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php
							if ( $network_wide ) {
								esc_html_e( 'This will reset the Top 10 popular posts across all sites in the network. This cannot be reversed. Make sure that your database has been backed up before proceeding.', 'top-10' );
							} elseif ( is_multisite() ) {
								esc_html_e( 'This will reset the Top 10 popular posts for the current site only. Other sites in the network will not be affected. This cannot be reversed. Make sure that your database has been backed up before proceeding.', 'top-10' );
							} else {
								esc_html_e( 'This will reset the Top 10 popular posts for this site. This cannot be reversed. Make sure that your database has been backed up before proceeding.', 'top-10' );
							}
							?>
						</p>
						<p>
							<?php
							printf(
								'<button name="tptn_reset_overall" type="submit" id="tptn_reset_overall" class="button button-secondary" style="color:#fff;background-color: #a00;border-color: #900;" onclick="if (!confirm(\'%s\')) return false;">%s</button>',
								esc_attr__( $network_wide ? 'Are you sure you want to reset popular posts across the network?' : 'Are you sure you want to reset the popular posts for this site?', 'top-10' ),
								esc_attr__( $network_wide ? 'Reset Popular Posts Across Network' : 'Reset Popular Posts', 'top-10' )
							);
							?>
						</p>
						<p>
							<?php
							printf(
								'<button name="tptn_reset_daily" type="submit" id="tptn_reset_daily" class="button button-secondary" style="color:#fff;background-color: #a00;border-color: #900;" onclick="if (!confirm(\'%s\')) return false;">%s</button>',
								esc_attr__( $network_wide ? 'Are you sure you want to reset daily popular posts across the network?' : 'Are you sure you want to reset the daily popular posts for this site?', 'top-10' ),
								esc_attr__( $network_wide ? 'Reset Daily Popular Posts Across Network' : 'Reset Daily Popular Posts', 'top-10' )
							);
							?>
						</p>
					</div>
				</div>

				<?php if ( ! is_multisite() || $network_wide ) : ?>
					<div class="postbox">
						<h2><span><?php esc_html_e( 'Recreate Database Tables', 'top-10' ); ?></span></h2>
						<div class="inside">
							<p class="description">
								<?php esc_html_e( 'Only click the button below after performing a full backup of the database. You can use any of the popular backup plugins or phpMyAdmin to achieve this. The authors of this plugin do not guarantee that everything will go smoothly as it depends on your site environment and volume of data. If you are not comfortable, please do not proceed.', 'top-10' ); ?>
							</p>
							<p>
								<button name="tptn_recreate_tables" type="submit" id="tptn_recreate_tables" style="color:#fff;background-color: #a00;border-color: #900;" onclick="if (!confirm('<?php esc_attr_e( 'Hit Cancel if you have not backed up your database', 'top-10' ); ?>')) return false;" class="button button-secondary"><?php esc_attr_e( 'Recreate Database Tables', 'top-10' ); ?></button>
							</p>
						</div>
					</div>
				<?php endif; ?>

				<?php wp_nonce_field( 'tptn-tools-settings' ); ?>
			</form>

		</div><!-- /#post-body-content -->

		<div id="postbox-container-1" class="postbox-container">

			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				<?php include_once 'sidebar.php'; ?>
			</div><!-- /#side-sortables -->

		</div><!-- /#postbox-container-1 -->
		</div><!-- /#post-body -->
		<br class="clear" />
		</div><!-- /#poststuff -->

	</div><!-- /.wrap -->

		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Function to delete and create the primary keys in the database table.
	 *
	 * @since   2.5.6
	 */
	public static function recreate_primary_key() {
		global $wpdb;

		$table_name       = Database::get_table( false );
		$table_name_daily = Database::get_table( true );

		$show_errors = $wpdb->hide_errors();

		if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name} WHERE Key_name = %s", 'PRIMARY' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		}
		if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name_daily} WHERE Key_name = %s", 'PRIMARY' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		}
		if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name_daily} WHERE Key_name = %s", 'blog_date' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' DROP INDEX blog_date' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		}

		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY(postnumber, blog_id) ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' ADD PRIMARY KEY(postnumber, dp_date, blog_id) ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' ADD INDEX blog_date(blog_id, dp_date, postnumber)' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange

		$wpdb->show_errors( $show_errors );
	}

	/**
	 * Retrieves the SQL code to recreate the PRIMARY KEY.
	 *
	 * @since   2.5.7
	 */
	public static function recreate_primary_key_html() {

		$table_name       = Database::get_table( false );
		$table_name_daily = Database::get_table( true );

		$sql  = 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY; ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY; ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name_daily . ' DROP INDEX IF EXISTS blog_date; ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY(postnumber, blog_id); ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name_daily . ' ADD PRIMARY KEY(postnumber, dp_date, blog_id); ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name_daily . ' ADD INDEX blog_date(blog_id, dp_date, postnumber); ';

		/**
		 * Filters the SQL code to recreate the PRIMARY KEY.
		 *
		 * @since   2.5.7
		 * @param string $sql SQL code to recreate PRIMARY KEY.
		 */
		return apply_filters( 'tptn_recreate_primary_key_html', $sql );
	}

	/**
	 * Retrieves the SQL code to recreate the PRIMARY KEY.
	 *
	 * @since 2.7.0
	 */
	public static function recreate_tables() {
		$errors = new \WP_Error();

		$result = Database::recreate_overall_table( false );
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}

		$result = Database::recreate_daily_table( false );
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}

		$result = Database::recreate_funnel_table( false );
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}

		$result = Database::recreate_log_table( false );
		if ( is_wp_error( $result ) ) {
			$errors->merge_from( $result );
		}

		Database::clear_table_installation_cache();
		Dashboard_Widgets::clear_network_dashboard_cache();

		return $errors->has_errors() ? $errors : true;
	}

	/**
	 * Clear and reschedule the Top 10 cron jobs.
	 *
	 * @since 4.4.0
	 *
	 * @param bool $network_wide Whether to repair cron schedules for all active sites.
	 * @return string|\WP_Error Success message or WP_Error if a job could not be rescheduled.
	 */
	public static function fix_crons( $network_wide = false ) {
		if ( $network_wide && is_multisite() ) {
			$site_ids        = wp_list_pluck(
				get_sites(
					array(
						'archived' => 0,
						'spam'     => 0,
						'deleted'  => 0,
						'number'   => 0,
					)
				),
				'blog_id'
			);
			$errors          = new \WP_Error();
			$sites_processed = 0;
			$current_blog_id = get_current_blog_id();

			foreach ( $site_ids as $site_id ) {
				$site_id  = absint( $site_id );
				$switched = $site_id !== $current_blog_id;
				if ( $switched ) {
					switch_to_blog( $site_id );
				}

				try {
					$result = self::fix_crons_for_site();
					if ( is_wp_error( $result ) ) {
						foreach ( $result->get_error_messages() as $message ) {
							$errors->add(
								'tptn-cron-site-' . $site_id,
								sprintf(
									/* translators: 1: Site ID, 2: Error message. */
									__( 'Site %1$d: %2$s', 'top-10' ),
									$site_id,
									$message
								)
							);
						}
					} else {
						++$sites_processed;
					}
				} finally {
					if ( $switched ) {
						restore_current_blog();
					}
				}
			}

			if ( $errors->has_errors() ) {
				return $errors;
			}

			return sprintf(
				/* translators: %s: Number of sites. */
				__( 'Cron schedules have been repaired for %s active site(s).', 'top-10' ),
				number_format_i18n( $sites_processed )
			);
		}

		return self::fix_crons_for_site();
	}

	/**
	 * Clear and reschedule the Top 10 cron jobs for the current site.
	 *
	 * @since 4.5.0
	 *
	 * @return string|\WP_Error Success message or WP_Error if a job could not be rescheduled.
	 */
	private static function fix_crons_for_site() {
		$errors   = new \WP_Error();
		$messages = array();
		$success  = true;

		if ( self::clear_scheduled_hook_or_error( 'tptn_cron_hook', $errors ) ) {
			if ( tptn_get_option( 'cron_on' ) ) {
				Cron::enable_run(
					(int) tptn_get_option( 'cron_hour' ),
					(int) tptn_get_option( 'cron_min' ),
					tptn_get_option( 'cron_recurrence' )
				);
				if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
					$messages[] = __( 'Maintenance cron has been rescheduled.', 'top-10' );
				} else {
					$errors->add( 'tptn_cron_hook', __( 'The maintenance cron could not be rescheduled as the cron event list could not be saved.', 'top-10' ) );
					$success = false;
				}
			} else {
				$messages[] = __( 'Maintenance cron is disabled in the settings and was left unscheduled.', 'top-10' );
			}
		} else {
			$success = false;
		}

		if ( self::clear_scheduled_hook_or_error( 'tptn_aggregation_cron_hook', $errors ) ) {
			Cron::enable_aggregation_run();
			if ( wp_next_scheduled( 'tptn_aggregation_cron_hook' ) ) {
				$messages[] = __( 'Aggregation cron has been rescheduled.', 'top-10' );
			} else {
				$errors->add( 'tptn_aggregation_cron_hook', __( 'The aggregation cron could not be rescheduled as the cron event list could not be saved. Check for object cache or database issues.', 'top-10' ) );
				$success = false;
			}
		} else {
			$success = false;
		}

		// Only drop the previously recorded WP-Cron errors once both jobs are confirmed rebuilt (or intentionally left off).
		if ( $success ) {
			Cron::clear_reschedule_errors();
		}

		return $errors->has_errors() ? $errors : implode( ' ', $messages );
	}

	/**
	 * Clear a scheduled hook, surfacing any WP_Error from WordPress core instead of silently ignoring it.
	 *
	 * If the existing event cannot be cleared, the caller must not attempt to reschedule it: the stale
	 * event would still satisfy wp_next_scheduled(), making a broken cron write look like a successful repair.
	 *
	 * @since 4.4.0
	 *
	 * @param string    $hook   Hook name to clear.
	 * @param \WP_Error $errors Error collector to append to on failure.
	 * @return bool True if the hook was cleared (or had nothing scheduled), false on failure.
	 */
	private static function clear_scheduled_hook_or_error( $hook, \WP_Error $errors ) {
		$result = wp_clear_scheduled_hook( $hook, array(), true );

		if ( is_wp_error( $result ) ) {
			$errors->add(
				$hook,
				sprintf(
					/* translators: 1: Hook name, 2: Error message from WordPress core. */
					__( 'Could not clear the existing schedule for %1$s: %2$s', 'top-10' ),
					$hook,
					$result->get_error_message()
				)
			);
			return false;
		}

		return true;
	}

	/**
	 * Generates the Tools help page.
	 *
	 * @since 3.3.0
	 */
	public static function help_tabs() {
		$screen = get_current_screen();

		$screen->set_help_sidebar(
		/* translators: 1: Support link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'top-10' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: Forum link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'top-10' ), esc_url( 'https://wordpress.org/support/plugin/top-10' ) ) . '</p>' .
			'<p>' . sprintf(
			/* translators: 1: Github Issues link, 2: Github page. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'top-10' ),
				esc_url( 'https://github.com/WebberZone/top-10/issues' ),
				esc_url( 'https://github.com/WebberZone/top-10' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'tptn-settings-general',
				'title'   => __( 'General', 'top-10' ),
				'content' =>
				'<p>' . __( 'This screen provides some tools that help maintain certain features of Top 10.', 'top-10' ) . '</p>' .
					'<p>' . __( 'Clear the cache, reset the popular posts tables plus some miscellaneous fixes for older versions of Top 10.', 'top-10' ) . '</p>',
			)
		);

		do_action( 'tptn_settings_tools_help', $screen );
	}

	/**
	 * Check if tables exist and create them if they don't.
	 *
	 * @since 4.2.0
	 *
	 * @return array Array of table statuses indicating whether they are installed.
	 */
	public static function check_table_status() {
		$tables = array(
			'top_ten'               => Database::get_table( false ),
			'top_ten_daily'         => Database::get_table( true ),
			'top_ten_visits_funnel' => Database::get_funnel_table(),
			'top_ten_visits_log'    => Database::get_log_table(),
		);

		$statuses = array();

		$installed_label     = '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>';
		$not_installed_label = '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';
		$table_statuses      = Database::get_table_installation_status( true );

		foreach ( $tables as $key => $table_name ) {
			$statuses[ $key ] = ! empty( $table_statuses[ $table_name ] ) ? $installed_label : $not_installed_label;
		}

		// Create tables if they don't exist.
		if ( empty( $table_statuses[ Database::get_table( false ) ] ) || empty( $table_statuses[ Database::get_table( true ) ] ) ) {
			// Use Activator to create tables.
			Activator::create_tables();
			Database::clear_table_statistics_cache();
			$table_statuses = Database::get_table_installation_status( true );

			// Refresh statuses after creating tables.
			foreach ( $tables as $key => $table_name ) {
				$statuses[ $key ] = ! empty( $table_statuses[ $table_name ] ) ? $installed_label : $not_installed_label;
			}
		}

		/**
		 * Filter the table statuses report.
		 *
		 * @since 4.1.0
		 *
		 * @param array $statuses Array of table statuses.
		 */
		return apply_filters( 'tptn_table_statuses', $statuses );
	}

	/**
	 * Get database status report.
	 *
	 * @since 4.2.0
	 *
	 * @return string HTML output for the database status report.
	 */
	public static function get_db_status_report() {
		global $tptn_db_version;

		// Get table statuses.
		$statuses = self::check_table_status();

		// Get table statistics from Database class.
		$table_stats = Database::get_table_statistics();

		ob_start();
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Database version', 'top-10' ); ?></th>
				<td>
					<?php esc_html_e( 'Installed version', 'top-10' ); ?> <?php echo esc_html( get_site_option( 'tptn_db_version', '0' ) ); ?> /
					<?php esc_html_e( 'Current version', 'top-10' ); ?> <?php echo esc_html( $tptn_db_version ); ?>
				</td>
			</tr>

			<?php
			$table_keys = array(
				'top_ten',
				'top_ten_daily',
				'top_ten_visits_funnel',
				'top_ten_visits_log',
			);
			foreach ( $table_keys as $table_key ) :
				if ( ! isset( $statuses[ $table_key ] ) ) {
					continue;
				}
				?>
				<tr>
					<th scope="row"><?php printf( /* translators: %s: Table name */ esc_html__( '%s table', 'top-10' ), esc_html( $table_key ) ); ?></th>
					<td>
						<?php
						echo $statuses[ $table_key ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						if ( isset( $table_stats[ $table_key ] ) ) {
							$format = is_multisite()
								? __( 'Network-wide estimated entries: %1$s | Network-wide estimated size: %2$s', 'top-10' )
								: __( 'Estimated entries: %1$s | Estimated size: %2$s', 'top-10' );

							echo '<br><span class="description">';
							printf(
								esc_html( $format ),
								'<strong>' . esc_html( number_format_i18n( $table_stats[ $table_key ]['entries'] ) ) . '</strong>',
								'<strong>' . esc_html( size_format( $table_stats[ $table_key ]['size'] ) ) . '</strong>'
							);
							echo '</span>';
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php
			$cron_jobs = array(
				'tptn_cron_hook'             => __( 'Maintenance cron', 'top-10' ),
				'tptn_aggregation_cron_hook' => __( 'Aggregation cron', 'top-10' ),
			);
			foreach ( $cron_jobs as $hook => $label ) :
				$next_run         = wp_next_scheduled( $hook );
				$recurrence       = $next_run ? wp_get_schedule( $hook ) : false;
				$schedules        = wp_get_schedules();
				$interval_display = '';
				if ( $recurrence && isset( $schedules[ $recurrence ]['display'] ) ) {
					$interval_display = $schedules[ $recurrence ]['display'];
				}
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $label ); ?></th>
					<td>
						<?php if ( $next_run ) : ?>
							<span style="color: #006400;"><?php esc_html_e( 'Scheduled', 'top-10' ); ?></span>
							<br><span class="description">
								<?php
								if ( $interval_display ) {
									printf(
										/* translators: %s: schedule display name e.g. "Every two minutes" */
										esc_html__( 'Runs: %s', 'top-10' ),
										esc_html( $interval_display )
									);
									echo '<br>';
								}
								printf(
									/* translators: %s: human-readable time difference */
									esc_html__( 'Next run: %s', 'top-10' ),
									/* translators: %s: human-readable time difference */
									esc_html( sprintf( __( 'in %s', 'top-10' ), human_time_diff( time(), $next_run ) ) )
								);
								?>
							</span>
						<?php else : ?>
							<span style="color: #8B0000;"><?php esc_html_e( 'Not scheduled', 'top-10' ); ?></span>
						<?php endif; ?>
						<?php
						$reschedule_error = Cron::get_reschedule_error( $hook );
						if ( $reschedule_error ) :
							?>
							<br><span class="description" style="color: #8B0000;">
								<?php
								printf(
									/* translators: 1: Error message from WP-Cron, 2: Human-readable time difference. */
									esc_html__( 'WP-Cron reported an error rescheduling this job %2$s ago: %1$s', 'top-10' ),
									esc_html( $reschedule_error['message'] ),
									esc_html( human_time_diff( $reschedule_error['time'] ) )
								);
								?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php if ( ( ! is_multisite() || is_network_admin() ) && ! Database::are_tables_installed() ) : ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Repair database', 'top-10' ); ?></th>
				<td>
					<a href="<?php echo esc_url( wp_nonce_url( ( is_multisite() && is_network_admin() ? network_admin_url( 'admin.php?page=tptn_network_tools_page&action=recreate_tables' ) : admin_url( 'admin.php?page=tptn_tools_page&action=recreate_tables' ) ), 'tptn-recreate-tables' ) ); ?>" class="button">
						<?php esc_html_e( 'Recreate tables', 'top-10' ); ?>
					</a>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Handle recreate tables action from admin area.
	 *
	 * @since 4.2.0
	 */
	/**
	 * Handle the create-missing-tables GET action triggered from the admin notice.
	 *
	 * @since 4.3.0
	 */
	public static function handle_create_missing_tables_action() {
		if ( ! isset( $_GET['action'] ) || 'tptn_create_missing_tables' !== $_GET['action'] || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'tptn-create-missing-tables' ) ) {
			wp_die( esc_html__( 'Security check failed', 'top-10' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( esc_html__( 'Database table repair must be run from Network Admin.', 'top-10' ) );
		}

		$capability = is_network_admin() ? 'manage_network_options' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'top-10' ) );
		}

		Activator::create_tables();
		Database::clear_table_statistics_cache();
		Dashboard_Widgets::clear_network_dashboard_cache();
		$table_statuses = Database::get_table_installation_status( true );

		$still_missing = false;
		foreach ( $table_statuses as $installed ) {
			if ( ! $installed ) {
				$still_missing = true;
				break;
			}
		}

		$status   = $still_missing ? 'failed' : 'created';
		$referer  = wp_get_referer();
		$fallback = is_network_admin() ? network_admin_url( 'admin.php?page=tptn_network_tools_page' ) : admin_url( 'admin.php?page=tptn_tools_page' );
		$redirect = add_query_arg( 'tptn_tables_created', $status, $referer ? $referer : $fallback );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle the recreate-tables GET action from the tools page.
	 *
	 * @since 4.1.0
	 */
	public static function handle_recreate_tables_action() {
		if ( ! isset( $_GET['action'] ) || 'recreate_tables' !== $_GET['action'] || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'tptn-recreate-tables' ) ) {
			wp_die( esc_html__( 'Security check failed', 'top-10' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( esc_html__( 'Database table repair must be run from Network Admin.', 'top-10' ) );
		}

		$capability = is_network_admin() ? 'manage_network_options' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'top-10' ) );
		}

		// Recreate tables.
		$result_overall = Database::recreate_overall_table( false );
		$result_daily   = Database::recreate_daily_table( false );
		$result_funnel  = Database::recreate_funnel_table( false );
		$result_log     = Database::recreate_log_table( false );
		Database::clear_table_installation_cache();
		Dashboard_Widgets::clear_network_dashboard_cache();

		// Check for errors.
		if ( is_wp_error( $result_overall ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-overall-error',
				$result_overall->get_error_message(),
				'error'
			);
		}

		if ( is_wp_error( $result_daily ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-daily-error',
				$result_daily->get_error_message(),
				'error'
			);
		}

		if ( is_wp_error( $result_funnel ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-funnel-error',
				$result_funnel->get_error_message(),
				'error'
			);
		}

		if ( is_wp_error( $result_log ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-log-error',
				$result_log->get_error_message(),
				'error'
			);
		}

		// If no errors, add success message.
		if ( ! is_wp_error( $result_overall ) && ! is_wp_error( $result_daily ) && ! is_wp_error( $result_funnel ) && ! is_wp_error( $result_log ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-success',
				__( 'Tables have been recreated successfully.', 'top-10' ),
				'success'
			);

			// Clear table statistics cache since tables were recreated.
			Database::clear_table_statistics_cache();
		}

		// Redirect back to the tools page.
		if ( is_network_admin() ) {
			wp_safe_redirect( network_admin_url( 'admin.php?page=tptn_network_tools_page&settings-updated=true' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=tptn_tools_page&settings-updated=true' ) );
		}
		exit;
	}
}
