<?php
/**
 * Update counts to database.
 *
 * @package   Top_Ten
 */

defined( 'ABSPATH' ) || exit;

if ( ! ( defined( 'WP_UNINSTALL_PLUGIN' ) || defined( 'WP_FS__UNINSTALL_MODE' ) ) ) {
	exit;
}

global $wpdb;

$tptn_settings = get_option( 'tptn_settings' );

wp_clear_scheduled_hook( 'tptn_aggregation_cron_hook' );

if ( ! empty( $tptn_settings['uninstall_clean_tables'] ) ) {

	$table_names = array(
		$wpdb->base_prefix . 'top_ten',
		$wpdb->base_prefix . 'top_ten_daily',
		$wpdb->base_prefix . 'top_ten_visits_log',
		$wpdb->base_prefix . 'top_ten_visits_funnel',
	);

	foreach ( $table_names as $table_name ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( "DROP TABLE $table_name" );
	}
	delete_site_option( 'tptn_db_version' );
}

// Delete data across multisite or single site.
if ( is_multisite() ) {

	$sites = get_sites(
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
		tptn_delete_data();
		restore_current_blog();
	}
} else {
	tptn_delete_data();
}

/**
 * Function to delete data when the plugin is uninstalled.
 *
 * @since 4.0.0
 */
function tptn_delete_data() {
	global $wpdb;

	$settings = get_option( 'tptn_settings' );

	if ( $settings['uninstall_clean_options'] ) {

		if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
			wp_clear_scheduled_hook( 'tptn_cron_hook' );
		}
		delete_option( 'ald_tptn_settings' );
		delete_option( 'tptn_settings' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query(
			"
			DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '%tptn%'
			"
		);
	}
}
