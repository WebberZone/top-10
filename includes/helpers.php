<?php
/**
 * Helper functions
 *
 * @package Top_Ten
 */

/**
 * Function to delete all rows in the posts table.
 *
 * @since   1.3
 * @param   bool $daily  Daily flag.
 */
function tptn_trunc_count( $daily = true ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
}

