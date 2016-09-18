<?php
/**
 * Cron functions
 *
 * @package Top_Ten
 */

/**
 * Function to truncate daily run.
 *
 * @since	1.9.9.1
 */
function tptn_cron() {
	global $wpdb;

	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	$current_time = current_time( 'timestamp', 0 );
	$from_date = strtotime( '-90 DAY' , $current_time );
	$from_date = gmdate( 'Y-m-d H' , $from_date );

	$resultscount = $wpdb->query( $wpdb->prepare( // WPCS: unprepared SQL OK.
		"DELETE FROM {$table_name_daily} WHERE dp_date <= '%s' ",
		$from_date
	) ); // DB call ok; no-cache ok; WPCS: unprepared SQL OK.

}
add_action( 'tptn_cron_hook', 'tptn_cron' );


/**
 * Function to enable run or actions.
 *
 * @since	1.9
 * @param	int	$hour		Hour.
 * @param	int	$min		Minute.
 * @param	int	$recurrence	Frequency.
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
 * @since	1.9
 */
function tptn_disable_run() {
	if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
	}
}

// Let's declare this conditional function to add more schedules. It will be a generic function across all plugins that I develop.
if ( ! function_exists( 'ald_more_reccurences' ) ) :

	/**
	 * Function to add weekly and fortnightly recurrences. Filters `cron_schedules`.
	 *
	 * @param	array $schedules Array of existing schedules.
	 * @return	array Filtered array with new schedules
	 */
	function ald_more_reccurences( $schedules ) {
		// Add a 'weekly' interval.
		$schedules['weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display' => __( 'Once Weekly', 'top-10' ),
		);
		$schedules['fortnightly'] = array(
			'interval' => 2 * WEEK_IN_SECONDS,
			'display' => __( 'Once Fortnightly', 'top-10' ),
		);
		$schedules['monthly'] = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display' => __( 'Once Monthly', 'top-10' ),
		);
		$schedules['quarterly'] = array(
			'interval' => 90 * DAY_IN_SECONDS,
			'display' => __( 'Once quarterly', 'top-10' ),
		);
		return $schedules;
	}
	add_filter( 'cron_schedules', 'ald_more_reccurences' );

endif;


