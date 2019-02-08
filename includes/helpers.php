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


/**
 * Retrieve the from date for the query
 *
 * @since 2.6.0
 *
 * @param string $time A date/time string.
 * @return string From date
 */
function tptn_get_from_date( $time = null ) {

	$current_time = isset( $time ) ? strtotime( $time ) : current_time( 'timestamp', 0 );

	if ( tptn_get_option( 'daily_midnight' ) ) {
		$from_date = $current_time - ( max( 0, ( tptn_get_option( 'daily_range' ) - 1 ) ) * DAY_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d 0', $from_date );
	} else {
		$from_date = $current_time - ( tptn_get_option( 'daily_range' ) * DAY_IN_SECONDS + tptn_get_option( 'hour_range' ) * HOUR_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d H', $from_date );
	}

	/**
	 * Retrieve the from date for the query
	 *
	 * @since 2.6.0
	 *
	 * @param string $time From date.
	 */
	return apply_filters( 'tptn_get_from_date', $from_date );
}

