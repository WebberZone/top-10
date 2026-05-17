<?php
/**
 * Cron class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Cron
 */
class Cron {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'tptn_cron_hook', array( $this, 'run_cron' ) );
		Hook_Registry::add_action( 'tptn_aggregation_cron_hook', array( $this, 'run_aggregation' ) );
	}

	/**
	 * Function to truncate daily run.
	 *
	 * @since 3.0.0
	 */
	public function run_cron() {
		global $wpdb;

		$delete_from = TOP_TEN_STORE_DATA;

		/**
		 * Override maintenance day range.
		 *
		 * @since 2.5.0
		 *
		 * @param int $delete_from Number of days before which post data is deleted from daily tables.
		 */
		$delete_from = apply_filters( 'tptn_maintenance_days', $delete_from );

		$current_time  = strtotime( current_time( 'mysql' ) );
		$cutoff        = strtotime( "-{$delete_from} DAY", $current_time );
		$from_date     = gmdate( 'Y-m-d H', $cutoff );
		$from_date_log = gmdate( 'Y-m-d H:i:s', $cutoff );

		// Delete old daily entries.
		$args = array(
			'daily'   => true,
			'to_date' => $from_date,
			'limit'   => 1000,
		);

		$deadline = microtime( true ) + 20;
		do {
			$deleted = Database::delete_counts( $args );
		} while ( $deleted > 0 && microtime( true ) < $deadline );

		// Prune visits log rows older than the retention period.
		$log_table    = Database::get_log_table();
		$deadline_log = microtime( true ) + 20;
		do {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$deleted_log = $wpdb->query( $wpdb->prepare( "DELETE FROM {$log_table} WHERE visited_at < %s LIMIT 1000", $from_date_log ) );
		} while ( $deleted_log > 0 && microtime( true ) < $deadline_log );
	}

	/**
	 * Function to enable run or actions.
	 *
	 * @since 3.0.0
	 * @param   int    $hour       Hour.
	 * @param   int    $min        Minute.
	 * @param   string $recurrence Frequency.
	 */
	public static function enable_run( $hour, $min, $recurrence ) {
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
	 * @since 3.0.0
	 */
	public static function disable_run() {
		if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
			wp_clear_scheduled_hook( 'tptn_cron_hook' );
		}
	}

	/**
	 * Drain the visits log into the count tables and prune orphaned log rows.
	 *
	 * @since 4.3.0
	 */
	public function run_aggregation() {
		Database::aggregate_visit_log();
	}

	/**
	 * Schedule the aggregation cron to run every 5 minutes.
	 *
	 * @since 4.3.0
	 */
	public static function enable_aggregation_run() {
		if ( ! wp_next_scheduled( 'tptn_aggregation_cron_hook' ) ) {
			wp_schedule_event( time(), 'five_minutes', 'tptn_aggregation_cron_hook' );
		}
	}

	/**
	 * Remove the aggregation cron.
	 *
	 * @since 4.3.0
	 */
	public static function disable_aggregation_run() {
		wp_clear_scheduled_hook( 'tptn_aggregation_cron_hook' );
	}
}
