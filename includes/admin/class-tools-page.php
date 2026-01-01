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
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'handle_recreate_tables_action' ) );

		// Clear table statistics cache when counts are updated.
		add_action( 'tptn_count_updated', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
		add_action( 'tptn_delete_counts', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
		add_action( 'tptn_set_count', array( 'WebberZone\Top_Ten\Database', 'clear_table_statistics_cache' ) );
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
					'ajax_url'             => admin_url( 'admin-ajax.php' ),
					'security'             => wp_create_nonce( 'tptn-admin' ),
					'confirm_message'      => esc_html__( 'Are you sure you want to clear the cache?', 'top-10' ),
					'clearing_text'        => esc_html__( 'Clearing...', 'top-10' ),
					'fail_message'         => esc_html__( 'Failed to clear cache. Please try again.', 'top-10' ),
					'request_fail_message' => esc_html__( 'Request failed: ', 'top-10' ),
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
		$network_wide = false;

		if ( $screen->id === $this->parent_id . '-network' ) {
			$network_wide = true;
		}

		/* Truncate overall posts table */
		if ( isset( $_POST['tptn_recreate_primary_key'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
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
			add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 popular posts reset', 'top-10' ), 'updated' );
		}

		/* Truncate daily posts table */
		if ( isset( $_POST['tptn_reset_daily'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			if ( ! is_multisite() || $network_wide ) {
				Database::truncate_table( Database::get_table( true ) );
			} else {
				Counter::delete_counts( array( 'daily' => true ) );
			}
			add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 daily popular posts reset', 'top-10' ), 'updated' );
		}

		/* Recreate tables */
		if ( isset( $_POST['tptn_recreate_tables'] ) && check_admin_referer( 'tptn-tools-settings' ) ) {
			self::recreate_tables();
			add_settings_error( 'tptn-notices', '', esc_html__( 'Top 10 tables have been recreated', 'top-10' ), 'updated' );
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
					<h2><span><?php esc_html_e( 'Database Status', 'top-10' ); ?></span></h2>
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
									'<button type="button" name="tptn_cache_clear" class="button button-secondary tptn_cache_clear" aria-label="%1$s">%1$s</button>',
									esc_html__( 'Clear cache', 'top-10' )
								);
							?>
						</p>
						<p class="description">
							<?php esc_html_e( 'Clear the Top 10 cache. This will also be cleared automatically when you save the settings page.', 'top-10' ); ?>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Recreate Primary Key', 'top-10' ); ?></span></h2>
					<div class="inside">
						<p>
							<button name="tptn_recreate_primary_key" type="submit" id="tptn_recreate_primary_key" class="button button-secondary"><?php esc_attr_e( 'Recreate Primary Key', 'top-10' ); ?></button>
						</p>
						<p class="description">
							<?php esc_html_e( 'Deletes and reinitializes the primary key in the database tables. If the above function gives an error, then you can run the below code in phpMyAdmin or Adminer. Remember to backup your database first!', 'top-10' ); ?>
						</p>
						<p>
							<code style="display:block;"><?php echo self::recreate_primary_key_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></code>
						</p>
					</div>
				</div>

				<div class="postbox">
					<h2><span><?php esc_html_e( 'Reset database', 'top-10' ); ?></span></h2>
					<div class="inside">
						<p class="description">
							<?php esc_html_e( 'This will reset the Top 10 tables. If this is a multisite install, this will reset the popular posts for the current site. If this is the Network Admin screen, then it will reset the popular posts across all sites. This cannot be reversed. Make sure that your database has been backed up before proceeding', 'top-10' ); ?>
						</p>
						<p>
							<?php
							printf(
								'<button name="tptn_reset_overall" type="submit" id="tptn_reset_overall" class="button button-secondary" style="color:#fff;background-color: #a00;border-color: #900;" onclick="if (!confirm(\'%s\')) return false;">%s</button>',
								esc_attr__( 'Are you sure you want to reset the popular posts?', 'top-10' ),
								esc_attr__( 'Reset Popular Posts', 'top-10' )
							);
							?>
						</p>
						<p>
							<?php
							printf(
								'<button name="tptn_reset_daily" type="submit" id="tptn_reset_daily" class="button button-secondary" style="color:#fff;background-color: #a00;border-color: #900;" onclick="if (!confirm(\'%s\')) return false;">%s</button>',
								esc_attr__( 'Are you sure you want to reset the daily popular posts?', 'top-10' ),
								esc_attr__( 'Reset Daily Popular Posts', 'top-10' )
							);
							?>
						</p>
					</div>
				</div>

				<?php if ( ! is_multisite() || is_network_admin() ) : ?>
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

		$wpdb->hide_errors();

		if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name} WHERE Key_name = %s", 'PRIMARY' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		}
		if ( $wpdb->query( $wpdb->prepare( "SHOW INDEXES FROM {$table_name_daily} WHERE Key_name = %s", 'PRIMARY' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		}

		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY(postnumber, blog_id) ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' ADD PRIMARY KEY(postnumber, dp_date, blog_id) ' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange

		$wpdb->show_errors();
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
		$sql .= 'ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY(postnumber, blog_id); ';
		$sql .= '<br />';
		$sql .= 'ALTER TABLE ' . $table_name_daily . ' ADD PRIMARY KEY(postnumber, dp_date, blog_id); ';

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
		global $wpdb;

		$table_name            = Database::get_table( false );
		$table_name_daily      = Database::get_table( true );
		$table_name_temp       = $table_name . '_temp';
		$table_name_daily_temp = $table_name_daily . '_temp';

		$wpdb->hide_errors();

		// 1. create temporary tables with the data.
		$wpdb->query( "CREATE TEMPORARY TABLE {$table_name_temp} SELECT * FROM $table_name;" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "CREATE TEMPORARY TABLE {$table_name_daily_temp} SELECT * FROM $table_name_daily;" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// 2. Drop the tables.
		$wpdb->query( "DROP TABLE $table_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( "DROP TABLE $table_name_daily" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		// 3. Run the activation function which will recreate the tables.
		Activator::single_activate();

		// 4. Reinsert the data from the temporary table.
		$sql = "
		INSERT INTO `$table_name` (postnumber, cntaccess, blog_id) (
			SELECT
				postnumber,
				cntaccess,
				blog_id
			FROM `$table_name_temp`
		);
		";

		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		$sql = "
		INSERT INTO `$table_name_daily` (postnumber, cntaccess, dp_date, blog_id) (
			SELECT
				postnumber,
				cntaccess,
				dp_date,
				blog_id
			FROM `$table_name_daily_temp`
		);
		";

		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		// 5. Drop the temporary tables.
		$wpdb->query( "DROP TABLE $table_name_temp" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( "DROP TABLE $table_name_daily_temp" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		$wpdb->show_errors();
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
		global $wpdb;

		$table_name       = $wpdb->base_prefix . 'top_ten';
		$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

		$statuses = array();

		// Check main table.
		$statuses['top_ten'] = Database::is_table_installed( $table_name )
			? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
			: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

		// Check daily table.
		$statuses['top_ten_daily'] = Database::is_table_installed( $table_name_daily )
			? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
			: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

		// Create tables if they don't exist.
		if ( ! Database::are_tables_installed() ) {
			// Use Activator to create tables.
			Activator::create_tables();
			Database::clear_table_statistics_cache();

			// Refresh statuses after creating tables.
			$statuses['top_ten'] = Database::is_table_installed( $table_name )
				? '<span style="color: #006400;">' . __( 'Installed', 'top-10' ) . '</span>'
				: '<span style="color: #8B0000;">' . __( 'Not Installed', 'top-10' ) . '</span>';

			$statuses['top_ten_daily'] = Database::is_table_installed( $table_name_daily )
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

			<tr>
				<th scope="row"><?php printf( /* translators: %s: Table name */ esc_html__( '%s table', 'top-10' ), esc_html( 'top_ten' ) ); ?></th>
				<td>
					<?php
					echo $statuses['top_ten']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 

					if ( isset( $table_stats['top_ten'] ) ) {
						echo '<br><span class="description">';
						if ( is_multisite() && ! is_network_admin() ) {
							printf(
								/* translators: 1: Number of entries, 2: Estimated table size */
								esc_html__( 'Entries: %1$s | Est. Size: %2$s', 'top-10' ),
								'<strong>' . esc_html( number_format_i18n( $table_stats['top_ten']['entries'] ) ) . '</strong>',
								'<strong>' . esc_html( size_format( $table_stats['top_ten']['size'] ) ) . '</strong>'
							);
						} else {
							printf(
								/* translators: 1: Number of entries, 2: Table size */
								esc_html__( 'Entries: %1$s | Size: %2$s', 'top-10' ),
								'<strong>' . esc_html( number_format_i18n( $table_stats['top_ten']['entries'] ) ) . '</strong>',
								'<strong>' . esc_html( size_format( $table_stats['top_ten']['size'] ) ) . '</strong>'
							);
						}
						echo '</span>';
					}
					?>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php printf( /* translators: %s: Table name */ esc_html__( '%s table', 'top-10' ), esc_html( 'top_ten_daily' ) ); ?></th>
				<td>
					<?php
					echo $statuses['top_ten_daily']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 

					if ( isset( $table_stats['top_ten_daily'] ) ) {
						echo '<br><span class="description">';
						if ( is_multisite() && ! is_network_admin() ) {
							printf(
								/* translators: 1: Number of entries, 2: Estimated table size */
								esc_html__( 'Entries: %1$s | Est. Size: %2$s', 'top-10' ),
								'<strong>' . esc_html( number_format_i18n( $table_stats['top_ten_daily']['entries'] ) ) . '</strong>',
								'<strong>' . esc_html( size_format( $table_stats['top_ten_daily']['size'] ) ) . '</strong>'
							);
						} else {
							printf(
								/* translators: 1: Number of entries, 2: Table size */
								esc_html__( 'Entries: %1$s | Size: %2$s', 'top-10' ),
								'<strong>' . esc_html( number_format_i18n( $table_stats['top_ten_daily']['entries'] ) ) . '</strong>',
								'<strong>' . esc_html( size_format( $table_stats['top_ten_daily']['size'] ) ) . '</strong>'
							);
						}
						echo '</span>';
					}
					?>
				</td>
			</tr>

			<?php if ( ! Database::are_tables_installed() ) : ?>
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
	 * @since 4.2.0
	 */
	public static function handle_recreate_tables_action() {
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
		$result_overall = Database::recreate_overall_table( false );
		$result_daily   = Database::recreate_daily_table( false );

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

			// Clear table statistics cache since tables were recreated.
			Database::clear_table_statistics_cache();
		}

		// Redirect back to the tools page.
		$page = is_network_admin() ? 'tptn_network_tools_page' : 'tptn_tools_page';
		wp_safe_redirect( admin_url( 'admin.php?page=' . $page . '&settings-updated=true' ) );
		exit;
	}
}
