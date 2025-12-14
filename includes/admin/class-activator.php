<?php
/**
 * Activator class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Activator {

	/**
	 * Name of the main table.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public static $table_name = 'top_ten';

	/**
	 * Name of the daily table.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public static $table_name_daily = 'top_ten_daily';

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'wpmu_drop_tables', array( $this, 'on_delete_blog' ) );
		add_action( 'plugins_loaded', array( $this, 'update_db_check' ) );
		add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );
		add_action( 'admin_init', array( $this, 'handle_recreate_tables_action' ) );
	}

	/**
	 * Fired when the plugin is Network Activated.
	 *
	 * @since 1.9.10.1
	 *
	 * @param    boolean $network_wide    True if WPMU superadmin uses
	 *                                    "Network Activate" action, false if
	 *                                    WPMU is disabled or plugin is
	 *                                    activated on an individual blog.
	 */
	public static function activation_hook( $network_wide ) {

		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				self::single_activate();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 2.0.0
	 */
	public static function single_activate() {
		global $wpdb, $tptn_db_version;

		$table_name       = $wpdb->base_prefix . self::$table_name;
		$table_name_daily = $wpdb->base_prefix . self::$table_name_daily;

		// Create tables if not exists.
		self::maybe_create_table( $table_name, self::create_full_table_sql() );
		self::maybe_create_table( $table_name_daily, self::create_daily_table_sql() );

		update_site_option( 'tptn_db_version', $tptn_db_version );

		// Upgrade table code.
		$installed_ver = get_site_option( 'tptn_db_version' );

		if ( $installed_ver != $tptn_db_version ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			// Recreate tables with the new structure.
			$result_overall = self::recreate_overall_table( false );
			$result_daily   = self::recreate_daily_table( false );

			// Check for errors.
			if ( is_wp_error( $result_overall ) ) {
				// Log the error.
				error_log( $result_overall->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			if ( is_wp_error( $result_daily ) ) {
				// Log the error.
				error_log( $result_daily->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			// Update the database version.
			update_site_option( 'tptn_db_version', $tptn_db_version );
		}

		/**
		 * Fires after the plugin has been activated.
		 *
		 * @since 4.1.0
		 */
		do_action( 'tptn_activate' );
	}

	/**
	 * Check if the Top Ten table is installed.
	 *
	 * @since 4.1.0
	 *
	 * @param string $table_name Table name.
	 * @return bool True if the table exists, false otherwise.
	 */
	public static function is_table_installed( $table_name ) {
		return Database::is_table_installed( $table_name );
	}

	/**
	 * Create table if not exists.
	 *
	 * @since 4.1.0
	 *
	 * @param string $table_name Table name.
	 * @param string $sql        SQL to create the table.
	 */
	public static function maybe_create_table( $table_name, $sql ) {
		if ( ! self::is_table_installed( $table_name ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			global $wpdb;
			$wpdb->hide_errors();
			dbDelta( $sql );
			$wpdb->show_errors();
		}
	}

	/**
	 * Create tables.
	 *
	 * @since 4.1.0
	 */
	public static function create_tables() {
		global $wpdb;

		$table_name       = $wpdb->base_prefix . self::$table_name;
		$table_name_daily = $wpdb->base_prefix . self::$table_name_daily;

		self::maybe_create_table( $table_name, self::create_full_table_sql() );
		self::maybe_create_table( $table_name_daily, self::create_daily_table_sql() );
	}

	/**
	 * Create full table sql.
	 *
	 * @since 4.1.0
	 *
	 * @return string SQL to create the full table.
	 */
	public static function create_full_table_sql() {
		return Database::create_full_table_sql();
	}

	/**
	 * Create full daily table sql.
	 *
	 * @since 4.1.0
	 *
	 * @return string SQL to create the daily table.
	 */
	public static function create_daily_table_sql() {
		return Database::create_daily_table_sql();
	}

	/**
	 * Recreate a table.
	 *
	 * This method recreates a table by creating a backup, dropping the original table,
	 * and then creating a new table with the original name and inserting the data from the backup.
	 *
	 * @since 4.1.0
	 *
	 * @param string $table_name        The name of the table to recreate.
	 * @param string $create_table_sql  The SQL statement to create the new table.
	 * @param bool   $backup            Whether to backup the table or not.
	 * @param array  $fields            The fields to include in the temporary table and on duplicate key code.
	 * @param array  $group_by_fields   The fields to group by in the temporary table.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_table(
		$table_name,
		$create_table_sql,
		$backup = true,
		$fields = array( 'postnumber', 'cntaccess', 'blog_id' ),
		$group_by_fields = array( 'postnumber', 'blog_id' )
	) {
		return Database::recreate_table( $table_name, $create_table_sql, $backup, $fields, $group_by_fields );
	}

	/**
	 * Recreate overall table.
	 *
	 * @since 4.1.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_overall_table( $backup = true ) {
		return Database::recreate_overall_table( $backup );
	}

	/**
	 * Recreate daily table.
	 *
	 * @since 4.1.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_daily_table( $backup = true ) {
		return Database::recreate_daily_table( $backup );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since 2.0.0
	 *
	 * @param  int|\WP_Site $blog WordPress 5.1 passes a WP_Site object.
	 */
	public static function activate_new_site( $blog ) {

		if ( ! is_plugin_active_for_network( plugin_basename( TOP_TEN_PLUGIN_FILE ) ) ) {
			return;
		}

		if ( ! is_int( $blog ) ) {
			$blog = $blog->id;
		}

		switch_to_blog( $blog );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Fired when a site is deleted in a WPMU environment.
	 *
	 * @since 2.0.0
	 *
	 * @param    array $tables    Tables in the blog.
	 */
	public static function on_delete_blog( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . self::$table_name;
		$tables[] = $wpdb->prefix . self::$table_name_daily;

		return $tables;
	}

	/**
	 * Function to call install function if needed.
	 *
	 * @since   1.9
	 */
	public static function update_db_check() {
		global $tptn_db_version, $network_wide;

		if ( get_site_option( 'tptn_db_version' ) != $tptn_db_version ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			self::activation_hook( $network_wide );
		}

		// Check if tables exist and create them if they don't.
		self::check_table_status();
	}

	/**
	 * Check if tables exist and create them if they don't.
	 *
	 * @since 4.1.0
	 *
	 * @return array Array of table statuses indicating whether they are installed.
	 */
	public static function check_table_status() {
		global $wpdb;

		$table_name       = $wpdb->base_prefix . self::$table_name;
		$table_name_daily = $wpdb->base_prefix . self::$table_name_daily;

		$statuses = array();

		// Check main table.
		$statuses['top_ten'] = self::is_table_installed( $table_name )
			? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
			: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

		// Check daily table.
		$statuses['top_ten_daily'] = self::is_table_installed( $table_name_daily )
			? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
			: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

		// Create tables if they don't exist.
		if ( ! self::is_all_tables_installed() ) {
			self::create_tables();

			// Refresh statuses after creating tables.
			$statuses['top_ten'] = self::is_table_installed( $table_name )
				? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
				: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

			$statuses['top_ten_daily'] = self::is_table_installed( $table_name_daily )
				? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
				: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';
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
	 * Check if all required tables are installed.
	 *
	 * @since 4.1.0
	 *
	 * @return bool True if all tables are installed, false if any are missing.
	 */
	public static function is_all_tables_installed() {
		return Database::are_tables_installed();
	}

	/**
	 * Get database status report.
	 *
	 * @since 4.1.0
	 *
	 * @return string Formatted HTML report of database status.
	 */
	public static function get_db_status_report() {
		global $tptn_db_version;

		// Get table statuses.
		$statuses = self::check_table_status();

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

			<tr>
				<th scope="row"><?php printf( /* translators: %s: Table name */ esc_html__( '%s table', 'top-10' ), esc_html( self::$table_name ) ); ?></th>
				<td><?php echo $statuses['top_ten']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
			</tr>

			<tr>
				<th scope="row"><?php printf( /* translators: %s: Table name */ esc_html__( '%s table', 'top-10' ), esc_html( self::$table_name_daily ) ); ?></th>
				<td><?php echo $statuses['top_ten_daily']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
			</tr>

			<?php if ( ! self::is_all_tables_installed() ) : ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Repair database', 'top-10' ); ?></th>
				<td>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=tptn_dashboard&action=recreate_tables' ), 'tptn-recreate-tables' ) ); ?>" class="button">
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
	 * @since 4.1.0
	 */
	public function handle_recreate_tables_action() {
		if ( ! isset( $_GET['action'] ) || 'recreate_tables' !== $_GET['action'] || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'tptn-recreate-tables' ) ) {
			wp_die( esc_html__( 'Security check failed', 'top-10' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'top-10' ) );
		}

		// Recreate tables.
		$result_overall = self::recreate_overall_table( false );
		$result_daily   = self::recreate_daily_table( false );

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

		// If no errors, add success message.
		if ( ! is_wp_error( $result_overall ) && ! is_wp_error( $result_daily ) ) {
			add_settings_error(
				'tptn-notices',
				'tptn-recreate-success',
				__( 'Tables have been recreated successfully.', 'top-10' ),
				'success'
			);
		}

		// Redirect back to the settings page.
		wp_safe_redirect( admin_url( 'admin.php?page=tptn_dashboard&settings-updated=true' ) );
		exit;
	}
}
