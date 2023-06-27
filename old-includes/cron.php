<?php
/**
 * Cron functions
 *
 * @package Top_Ten
 */

/**
 * Function to truncate daily run.
 *
 * @since   1.9.9.1
 */
function tptn_cron() {
	global $wpdb;

	$table_name_daily = get_tptn_table( true );

	$delete_from = TOP_TEN_STORE_DATA;

	/**
	 * Override maintenance day range.
	 *
	 * @since 2.5.0
	 *
	 * @param int $delete_from Number of days before which post data is deleted from daily tables.
	 */
	$delete_from = apply_filters( 'tptn_maintenance_days', $delete_from );

	$current_time = strtotime( current_time( 'mysql' ) );
	$from_date    = strtotime( "-{$delete_from} DAY", $current_time );
	$from_date    = gmdate( 'Y-m-d H', $from_date );

	$resultscount = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DELETE FROM {$table_name_daily} WHERE dp_date <= %s ", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$from_date
		)
	);
}
add_action( 'tptn_cron_hook', 'tptn_cron' );


/**
 * Function to enable run or actions.
 *
 * @since   1.9
 * @param   int $hour       Hour.
 * @param   int $min        Minute.
 * @param   int $recurrence Frequency.
 */
function tptn_enable_run( $hour, $min, $recurrence ) {
	// Invoke WordPress internal cron.
	if ( ! wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'tptn_cron_hook' );
	} else {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'tptn_cron_hook' );
	}
}


/**
 * Function to disable daily run or actions.
 *
 * @since   1.9
 */
function tptn_disable_run() {
	if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
	}
}
