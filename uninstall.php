<?php
/**
 * Update counts to database.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2020 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

$tptn_settings = get_option( 'tptn_settings' );

if ( $tptn_settings['uninstall_clean_tables'] ) {

	$table_name       = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	$wpdb->query( "DROP TABLE $table_name" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->query( "DROP TABLE $table_name_daily" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
	delete_option( 'tptn_db_version' );

}

if ( $tptn_settings['uninstall_clean_options'] ) {

	if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
	}
	delete_option( 'ald_tptn_settings' );
	delete_option( 'tptn_settings' );

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		"
		DELETE FROM {$wpdb->options}
		WHERE option_name LIKE '%tptn%'
		"
	);
}

