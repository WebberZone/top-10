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
	 * Whether the aggregation cron was missing and had to be rescheduled.
	 *
	 * @var bool
	 */
	private bool $aggregation_cron_was_missing = false;

	/**
	 * Whether the aggregation cron interval changed and had to be rescheduled.
	 *
	 * @var bool
	 */
	private bool $aggregation_cron_interval_changed = false;

	/**
	 * Transient key prefix used to record core cron reschedule failures for our hooks.
	 *
	 * @since 4.4.0
	 */
	private const RESCHEDULE_ERROR_TRANSIENT_PREFIX = 'tptn_cron_reschedule_error_';

	/**
	 * Hooks that this class schedules and should track reschedule errors for.
	 *
	 * @since 4.4.0
	 */
	private const TRACKED_HOOKS = array( 'tptn_cron_hook', 'tptn_aggregation_cron_hook' );

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		Hook_Registry::add_action( 'tptn_cron_hook', array( $this, 'run_cron' ) );
		Hook_Registry::add_action( 'tptn_aggregation_cron_hook', array( $this, 'run_aggregation' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'check_aggregation_cron' ) );
		Hook_Registry::add_action( 'admin_notices', array( $this, 'aggregation_cron_missing_notice' ) );
		Hook_Registry::add_action( 'cron_reschedule_event_error', array( $this, 'log_reschedule_error' ), 10, 2 );
		Hook_Registry::add_action( 'cron_unschedule_event_error', array( $this, 'log_unschedule_error' ), 10, 2 );
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

		$log_retention = TOP_TEN_LOG_STORE_DATA;

		/**
		 * Override retention period for the visits log table.
		 *
		 * @since 4.3.0
		 *
		 * @param int $log_retention Number of days to retain raw visit log rows.
		 */
		$log_retention = apply_filters( 'tptn_log_retention_days', $log_retention );

		$current_time  = strtotime( current_time( 'mysql' ) );
		$cutoff        = strtotime( "-{$delete_from} DAY", $current_time );
		$cutoff_log    = strtotime( "-{$log_retention} DAY", $current_time );
		$from_date     = gmdate( 'Y-m-d H', $cutoff );
		$from_date_log = gmdate( 'Y-m-d H:i:s', $cutoff_log );

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
	 * Schedule the aggregation cron to run at the configured interval.
	 *
	 * @since 4.3.0
	 */
	public static function enable_aggregation_run() {
		/**
		 * Override the aggregation cron schedule. Must be a registered WP-Cron recurrence
		 * (e.g. 'one_minute', 'two_minutes', 'three_minutes', 'five_minutes').
		 *
		 * @since 4.3.0
		 *
		 * @param string $interval Schedule name. Default 'two_minutes'.
		 */
		$interval = (string) apply_filters( 'tptn_aggregation_cron_interval', 'two_minutes' );

		if ( ! wp_next_scheduled( 'tptn_aggregation_cron_hook' ) ) {
			wp_schedule_event( time(), $interval, 'tptn_aggregation_cron_hook' );
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

	/**
	 * Check that the aggregation cron is scheduled at the correct interval; reschedule if missing or changed.
	 *
	 * @since 4.3.0
	 */
	public function check_aggregation_cron() {
		/** This filter is documented in includes/admin/class-cron.php */
		$interval = (string) apply_filters( 'tptn_aggregation_cron_interval', 'two_minutes' );

		$timestamp = wp_next_scheduled( 'tptn_aggregation_cron_hook' );

		if ( ! $timestamp ) {
			self::enable_aggregation_run();
			$this->aggregation_cron_was_missing = true;
			return;
		}

		$crons    = _get_cron_array();
		$args_key = md5( serialize( array() ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$current  = isset( $crons[ $timestamp ]['tptn_aggregation_cron_hook'][ $args_key ]['schedule'] )
			? $crons[ $timestamp ]['tptn_aggregation_cron_hook'][ $args_key ]['schedule']
			: '';

		if ( $current !== $interval ) {
			wp_clear_scheduled_hook( 'tptn_aggregation_cron_hook' );
			self::enable_aggregation_run();
			$this->aggregation_cron_interval_changed = true;
		}
	}

	/**
	 * Show an admin notice when the aggregation cron was missing or rescheduled due to an interval change.
	 *
	 * @since 4.3.0
	 */
	public function aggregation_cron_missing_notice() {
		if ( $this->aggregation_cron_interval_changed ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php esc_html_e( 'Top 10: The visit aggregation cron job (tptn_aggregation_cron_hook) has been rescheduled to match the updated interval.', 'top-10' ); ?>
				</p>
			</div>
			<?php
		} elseif ( $this->aggregation_cron_was_missing ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<?php esc_html_e( 'Top 10: The visit aggregation cron job (tptn_aggregation_cron_hook) was missing and has been rescheduled automatically.', 'top-10' ); ?>
				</p>
			</div>
			<?php
		}

		foreach ( self::TRACKED_HOOKS as $hook ) {
			$error = self::get_reschedule_error( $hook );
			if ( ! $error ) {
				continue;
			}
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: Hook name, 2: Error message, 3: Human-readable time difference. */
						esc_html__( 'Top 10: WP-Cron reported an error rescheduling %1$s: %2$s (%3$s ago). Use the "Fix Cron Schedules" tool on the Top 10 Tools page if the job stops running.', 'top-10' ),
						'<code>' . esc_html( $hook ) . '</code>',
						esc_html( $error['message'] ),
						esc_html( human_time_diff( $error['time'] ) )
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Record a core WP-Cron reschedule failure for one of our hooks.
	 *
	 * Fired from wp-cron.php when the real cron runner fails to persist the
	 * next occurrence of a recurring event (`_set_cron_array()` failure).
	 *
	 * @since 4.4.0
	 *
	 * @param mixed  $result Expected to be a WP_Error when core reports a failure.
	 * @param string $hook   Hook name the error occurred for.
	 */
	public function log_reschedule_error( $result, $hook ) {
		if ( ! in_array( $hook, self::TRACKED_HOOKS, true ) || ! is_wp_error( $result ) ) {
			return;
		}

		self::set_reschedule_error( $hook, $result );
	}

	/**
	 * Record a core WP-Cron unschedule failure for one of our hooks.
	 *
	 * @since 4.4.0
	 *
	 * @param mixed  $result Expected to be a WP_Error when core reports a failure.
	 * @param string $hook   Hook name the error occurred for.
	 */
	public function log_unschedule_error( $result, $hook ) {
		if ( ! in_array( $hook, self::TRACKED_HOOKS, true ) || ! is_wp_error( $result ) ) {
			return;
		}

		self::set_reschedule_error( $hook, $result );
	}

	/**
	 * Store the last cron scheduling error for a hook.
	 *
	 * @since 4.4.0
	 *
	 * @param string    $hook   Hook name.
	 * @param \WP_Error $result Error returned by WordPress core.
	 */
	private static function set_reschedule_error( $hook, $result ) {
		set_transient(
			self::RESCHEDULE_ERROR_TRANSIENT_PREFIX . $hook,
			array(
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message(),
				'time'    => time(),
			),
			WEEK_IN_SECONDS
		);
	}

	/**
	 * Retrieve the last recorded cron scheduling error for a hook, if any.
	 *
	 * @since 4.4.0
	 *
	 * @param string $hook Hook name.
	 * @return array{code: string, message: string, time: int}|false Error data, or false if none recorded.
	 */
	public static function get_reschedule_error( $hook ) {
		$error = get_transient( self::RESCHEDULE_ERROR_TRANSIENT_PREFIX . $hook );

		return is_array( $error ) ? $error : false;
	}

	/**
	 * Clear the recorded cron scheduling errors for all tracked hooks.
	 *
	 * @since 4.4.0
	 */
	public static function clear_reschedule_errors() {
		foreach ( self::TRACKED_HOOKS as $hook ) {
			delete_transient( self::RESCHEDULE_ERROR_TRANSIENT_PREFIX . $hook );
		}
	}
}
