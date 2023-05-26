<?php
/**
 * Functions run on activation / deactivation.
 *
 * @package Top_Ten
 */

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
function tptn_activation_hook( $network_wide ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		// Get all blogs in the network and activate plugin on each one.
		$blog_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			"
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		"
		);
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			tptn_single_activate();
		}

		// Switch back to the current blog.
		restore_current_blog();

	} else {
		tptn_single_activate();
	}
}
register_activation_hook( TOP_TEN_PLUGIN_FILE, 'tptn_activation_hook' );


/**
 * Fired for each blog when the plugin is activated.
 *
 * @since 2.0.0
 */
function tptn_single_activate() {
	global $wpdb, $tptn_db_version;

	$charset_collate = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name       = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		$sql = 'CREATE TABLE ' . $table_name . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			" (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, blog_id)
			) $charset_collate;";

		dbDelta( $sql );

	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) != $table_name_daily ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		$sql = 'CREATE TABLE ' . $table_name_daily . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
			" (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			dp_date DATETIME NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, dp_date, blog_id)
			) $charset_collate;";

		dbDelta( $sql );

	}

	add_site_option( 'tptn_db_version', $tptn_db_version );

	// Upgrade table code.
	$installed_ver = get_site_option( 'tptn_db_version' );

	if ( $installed_ver != $tptn_db_version ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual

		$sql = 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY ';
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$sql = 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY ';
		$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		$sql = 'CREATE TABLE ' . $table_name . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, blog_id)
			) $charset_collate;";

		$sql .= 'CREATE TABLE ' . $table_name_daily . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			dp_date DATETIME NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, dp_date, blog_id)
			) $charset_collate;";

		dbDelta( $sql );

		update_site_option( 'tptn_db_version', $tptn_db_version );
	}
}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param  int|WP_Site $blog WordPress 5.1 passes a WP_Site object.
 */
function tptn_activate_new_site( $blog ) {

	if ( ! is_plugin_active_for_network( plugin_basename( TOP_TEN_PLUGIN_FILE ) ) ) {
		return;
	}

	if ( ! is_int( $blog ) ) {
		$blog = $blog->id;
	}

	switch_to_blog( $blog );
	tptn_single_activate();
	restore_current_blog();
}
if ( version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) ) {
	add_action( 'wp_initialize_site', 'tptn_activate_new_site' );
} else {
	add_action( 'wpmu_new_blog', 'tptn_activate_new_site' );
}


/**
 * Fired when a site is deleted in a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    array $tables    Tables in the blog.
 */
function tptn_on_delete_blog( $tables ) {
	global $wpdb;

	$tables[] = $wpdb->prefix . 'top_ten';
	$tables[] = $wpdb->prefix . 'top_ten_daily';

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'tptn_on_delete_blog' );


/**
 * Function to call install function if needed.
 *
 * @since   1.9
 */
function tptn_update_db_check() {
	global $tptn_db_version, $network_wide;

	if ( get_site_option( 'tptn_db_version' ) != $tptn_db_version ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
		tptn_activation_hook( $network_wide );
	}
}
add_action( 'plugins_loaded', 'tptn_update_db_check' );

