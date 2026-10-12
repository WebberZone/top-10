<?php
/**
 * Feed view counts: schema migration, switch-over and backfill.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Manages the feedcnt column, the feed share of cntaccess in both count tables.
 *
 * @since 4.6.0
 */
class Feed_Counts {


	/**
	 * Network option holding the feed counts state.
	 *
	 * @var string
	 */
	const OPTION = 'tptn_feed_counts';

	/**
	 * Cron hook that runs the backfill in the background.
	 *
	 * @var string
	 */
	const BACKFILL_HOOK = 'tptn_feed_backfill_hook';

	/**
	 * Request cache of the state option.
	 *
	 * @var array|null
	 */
	private static $state = null;

	/**
	 * Request cache of column presence keyed by table name.
	 *
	 * @var array<string,bool>
	 */
	private static $column_cache = array();

	/**
	 * Get the stored state.
	 *
	 * @since 4.6.0
	 *
	 * @return array {
	 * @type   bool   $enabled    Whether aggregation writes feedcnt.
	 * @type   bool   $pending    Whether the column still needs adding by an admin.
	 * @type   int    $cutoff     Last visits log ID covered by the backfill.
	 * @type   int    $cursor     Last visits log ID already backfilled.
	 * @type   string $since      Earliest feed visit counted.
	 * @type   string $enabled_at When live feed counting started.
	 * }
	 */
	public static function get_state() {
		if ( null === self::$state ) {
			// Network options are never autoloaded on single sites, so use a regular autoloaded option there.
			$state       = is_multisite() ? get_site_option( self::OPTION, array() ) : get_option( self::OPTION, array() );
			self::$state = is_array( $state ) ? $state : array();
		}
		return self::$state;
	}

	/**
	 * Save the state.
	 *
	 * @since 4.6.0
	 *
	 * @param array $state State to save.
	 */
	private static function save_state( array $state ) {
		self::$state = $state;
		if ( is_multisite() ) {
			update_site_option( self::OPTION, $state );
		} else {
			update_option( self::OPTION, $state, true );
		}
	}

	/**
	 * Clear the request caches.
	 *
	 * @since 4.6.0
	 */
	public static function reset_cache() {
		self::$state        = null;
		self::$column_cache = array();
	}

	/**
	 * Whether feed counts are written to the count tables.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$state = self::get_state();
		return ! empty( $state['enabled'] );
	}

	/**
	 * Whether the column could not be added automatically and awaits an admin action.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function is_pending() {
		$state = self::get_state();
		return ! self::is_enabled() && ! empty( $state['pending'] );
	}

	/**
	 * Whether historical feed views are still being backfilled.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function is_backfilling() {
		$state = self::get_state();
		return self::is_enabled() && (int) ( $state['cursor'] ?? 0 ) < (int) ( $state['cutoff'] ?? 0 );
	}

	/**
	 * Earliest date from which feed views are counted.
	 *
	 * @since 4.6.0
	 *
	 * @return string MySQL datetime or empty string.
	 */
	public static function get_since() {
		$state = self::get_state();
		return (string) ( $state['since'] ?? '' );
	}

	/**
	 * Whether a table has the feedcnt column.
	 *
	 * @since 4.6.0
	 *
	 * @param  string $table Table name.
	 * @param  bool   $force Whether to bypass the request cache.
	 * @return bool False also when the table is missing.
	 */
	public static function table_has_column( $table, $force = false ) {
		global $wpdb;

		// A column that exists cannot disappear mid-request, so a cached true is always reused.
		if ( isset( self::$column_cache[ $table ] ) && ( ! $force || self::$column_cache[ $table ] ) ) {
			return self::$column_cache[ $table ];
		}

		$suppress = $wpdb->suppress_errors();
		$result   = $wpdb->query( "SELECT feedcnt FROM `{$table}` LIMIT 0" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->suppress_errors( $suppress );

		self::$column_cache[ $table ] = ( false !== $result );
		return self::$column_cache[ $table ];
	}

	/**
	 * Whether CREATE TABLE SQL for a count table should include feedcnt.
	 *
	 * True for a table that does not exist yet (creating an empty table is free) or that
	 * already has the column. False otherwise, so dbDelta never issues a table-rebuilding ALTER.
	 *
	 * @since 4.6.0
	 *
	 * @param  string $table Table name.
	 * @return bool
	 */
	public static function schema_includes_column( $table ) {
		global $wpdb;

		return self::table_has_column( $table, true ) || ! self::table_exists( $table );
	}

	/**
	 * Whether a table exists.
	 *
	 * @since 4.6.0
	 *
	 * @param  string $table Table name.
	 * @return bool
	 */
	private static function table_exists( $table ) {
		global $wpdb;

     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	}

	/**
	 * Count tables that carry feedcnt.
	 *
	 * @since 4.6.0
	 *
	 * @return string[]
	 */
	private static function get_count_tables() {
		return array( Database::get_table( false ), Database::get_table( true ) );
	}

	/**
	 * Add the column where it can be done without a long table rebuild, then enable feed counts.
	 *
	 * Runs on activation and DB upgrade. Uses ALGORITHM=INSTANT (MySQL 8.0.12+, MariaDB 10.3+);
	 * small tables are altered directly. Otherwise the column is left for an admin to add.
	 *
	 * @since 4.6.0
	 */
	public static function maybe_upgrade() {
		self::reset_cache();

		$missing = array();
		foreach ( self::get_count_tables() as $table ) {
			if ( self::table_has_column( $table, true ) ) {
				continue;
			}
			if ( ! self::table_exists( $table ) ) {
				return;
			}
			$missing[] = $table;
		}

		if ( self::is_enabled() ) {
			if ( empty( $missing ) ) {
				return;
			}
			// A table lost the column (e.g. recreated by an older version): stop writing feedcnt before aggregation fails.
			$state            = self::get_state();
			$state['enabled'] = false;
			self::save_state( $state );

			// The backfill rebuilds both tables from the log, so clear the one that kept its column.
			global $wpdb;
			foreach ( array_diff( self::get_count_tables(), $missing ) as $table ) {
				$wpdb->query( "UPDATE `{$table}` SET feedcnt = 0 WHERE feedcnt > 0" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}

		if ( ! empty( $missing ) && true !== self::add_columns( $missing, false ) ) {
			$state            = self::get_state();
			$state['pending'] = true;
			self::save_state( $state );
			return;
		}

		self::enable();
	}

	/**
	 * Add the feedcnt column to the given tables.
	 *
	 * @since 4.6.0
	 *
	 * @param  string[] $tables Tables missing the column.
	 * @param  bool     $online Whether to allow an online table rebuild (admin-triggered only).
	 * @return true|\WP_Error
	 */
	public static function add_columns( array $tables, $online = false ) {
		global $wpdb;

		$is_sqlite = Database::is_sqlite();
		$small     = self::get_inline_alter_limit();
		$stats     = $is_sqlite ? array() : self::get_row_estimates( $tables );

		foreach ( $tables as $table ) {
			$base = "ALTER TABLE `{$table}` ADD COLUMN feedcnt bigint(20) NOT NULL DEFAULT '0'";

			if ( $is_sqlite || ( isset( $stats[ $table ] ) && $stats[ $table ] <= $small ) ) {
				$attempts = array( $base );
			} elseif ( $online ) {
				$attempts = array( $base . ', ALGORITHM=INSTANT', $base . ', ALGORITHM=INPLACE, LOCK=NONE', $base );
			} else {
				$attempts = array( $base . ', ALGORITHM=INSTANT' );
			}

			$added    = false;
			$suppress = $wpdb->suppress_errors();
			foreach ( $attempts as $sql ) {
				if ( false !== $wpdb->query( $sql ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared
					$added = true;
					break;
				}
			}
			$error = $wpdb->last_error;
			$wpdb->suppress_errors( $suppress );

			if ( ! $added && ! self::table_has_column( $table, true ) ) {
				return new \WP_Error( 'tptn_feed_column_failed', $error ? $error : __( 'Could not add the feed views column.', 'top-10' ) );
			}
			self::$column_cache[ $table ] = true;
		}

		Database::clear_table_installation_cache();

		return true;
	}

	/**
	 * Add any missing columns from an admin action (Tools page or WP-CLI) and enable feed counts.
	 *
	 * @since 4.6.0
	 *
	 * @return true|\WP_Error
	 */
	public static function add_columns_now() {
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		}

		$missing = array();
		foreach ( self::get_count_tables() as $table ) {
			if ( ! self::table_has_column( $table, true ) ) {
				$missing[] = $table;
			}
		}

		if ( ! empty( $missing ) ) {
			$result = self::add_columns( $missing, true );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		if ( ! self::is_enabled() && ! self::enable() ) {
			return new \WP_Error( 'tptn_feed_enable_failed', __( 'Another aggregation run is in progress. Please try again in a moment.', 'top-10' ) );
		}

		return true;
	}

	/**
	 * Tables at or below this many rows are altered directly.
	 *
	 * @since 4.6.0
	 *
	 * @return int
	 */
	private static function get_inline_alter_limit() {
		/**
		 * Filters the row count up to which the feed views column is added with a plain ALTER TABLE.
		 *
		 * @since 4.6.0
		 *
		 * @param int $rows Row limit. Default 100000.
		 */
		return absint( apply_filters( 'tptn_feed_counts_inline_alter_rows', 100000 ) );
	}

	/**
	 * Estimated row counts from information_schema.
	 *
	 * @since 4.6.0
	 *
	 * @param  string[] $tables Table names.
	 * @return array<string,int>
	 */
	private static function get_row_estimates( array $tables ) {
		global $wpdb;

		$placeholders = implode( ', ', array_fill( 0, count( $tables ), '%s' ) );
		$rows         = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
       // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})",
				...$tables
			),
			ARRAY_A
		);

		$estimates = array();
		foreach ( (array) $rows as $row ) {
			$estimates[ $row['TABLE_NAME'] ] = absint( $row['TABLE_ROWS'] );
		}
		return $estimates;
	}

	/**
	 * Switch aggregation to writing feedcnt and record the backfill cutoff.
	 *
	 * Holds the aggregation lock so no run straddles the switch: log rows up to the cutoff
	 * are backfilled, later rows are counted live.
	 *
	 * @since 4.6.0
	 *
	 * @return bool False when the lock could not be acquired; a retry is scheduled.
	 */
	public static function enable() {
		global $wpdb;

		$is_sqlite = Database::is_sqlite();
		$log_table = Database::get_log_table();

     // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $is_sqlite && '1' !== (string) $wpdb->get_var( "SELECT GET_LOCK('tptn_aggregation', 10)" ) ) {
			self::schedule_backfill( MINUTE_IN_SECONDS );
			return false;
		}

		try {
			$now   = current_time( 'mysql' );
			$range = $is_sqlite ? null : $wpdb->get_row( "SELECT MIN(id) AS min_id, MAX(id) AS max_id FROM {$log_table}" );

			$cutoff = $range ? (int) $range->max_id : 0;
			$cursor = $range && $range->min_id ? (int) $range->min_id - 1 : $cutoff;

			// Feed views can only exist if feed tracking was ever on; multisite sites decide individually.
			if ( ! is_multisite() && ! \tptn_get_option( 'track_feed_views' ) ) {
				$cursor = $cutoff;
			}

			self::save_state(
				array(
					'enabled'    => true,
					'pending'    => false,
					'cutoff'     => $cutoff,
					'cursor'     => $cursor,
					'since'      => $cursor >= $cutoff ? $now : '',
					'enabled_at' => $now,
				)
			);
		} finally {
			if ( ! $is_sqlite ) {
				$wpdb->query( "SELECT RELEASE_LOCK('tptn_aggregation')" );
			}
		}
     // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( self::is_backfilling() ) {
			self::schedule_backfill( 30 );
		}

		return true;
	}

	/**
	 * Schedule the next backfill batch.
	 *
	 * @since 4.6.0
	 *
	 * @param int $delay Seconds from now.
	 */
	private static function schedule_backfill( $delay ) {
		if ( ! wp_next_scheduled( self::BACKFILL_HOOK ) ) {
			wp_schedule_single_event( time() + (int) $delay, self::BACKFILL_HOOK );
		}
	}

	/**
	 * Backfill feedcnt from the visits log, one time-boxed batch per cron run.
	 *
	 * Only updates rows that already exist, clamped to cntaccess, so it can never create rows
	 * or push the feed count above the total.
	 *
	 * @since 4.6.0
	 */
	public static function run_backfill() {
		global $wpdb;

		self::reset_cache();

		if ( ! self::is_enabled() ) {
			if ( ! self::is_pending() && self::table_has_column( Database::get_table( false ), true ) && self::table_has_column( Database::get_table( true ), true ) ) {
				self::enable();
			}
			return;
		}

		if ( ! self::is_backfilling() ) {
			return;
		}

		$state = self::get_state();

		if ( Database::is_sqlite() ) {
			$state['cursor'] = $state['cutoff'];
			$state['since']  = '' !== $state['since'] ? $state['since'] : $state['enabled_at'];
			self::save_state( $state );
			return;
		}

     // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( '1' !== (string) $wpdb->get_var( "SELECT GET_LOCK('tptn_feed_backfill', 0)" ) ) {
			return;
		}

		$log_table   = Database::get_log_table();
		$full_table  = Database::get_table( false );
		$daily_table = Database::get_table( true );

		/**
		 * Filters the number of visits log IDs processed per backfill query.
		 *
		 * @since 4.6.0
		 *
		 * @param int $chunk Chunk size. Default 20000.
		 */
		$chunk = max( 1000, absint( apply_filters( 'tptn_feed_backfill_chunk', 20000 ) ) );

		/**
		 * Filters the seconds a single backfill run may spend before yielding.
		 *
		 * @since 4.6.0
		 *
		 * @param float $budget Time budget in seconds. Default 1.
		 */
		$budget = (float) apply_filters( 'tptn_feed_backfill_time_budget', 1.0 );
		$start  = microtime( true );

		try {
			while ( $state['cursor'] < $state['cutoff'] && ( microtime( true ) - $start ) < $budget ) {
				$from = (int) $state['cursor'];
				$to   = min( $from + $chunk, (int) $state['cutoff'] );

				// Log IDs are not strictly chronological, so keep the earliest date across all chunks.
				$since = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(visited_at) FROM {$log_table} WHERE id > %d AND id <= %d AND source = 1", $from, $to ) );
				if ( $since && ( '' === $state['since'] || $since < $state['since'] ) ) {
					$state['since'] = $since;
				}

				$overall = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$full_table} t
						 INNER JOIN (
							SELECT postnumber, blog_id, COUNT(*) AS feed
							FROM   {$log_table}
							WHERE  id > %d AND id <= %d AND source = 1
							GROUP  BY postnumber, blog_id
						 ) l ON t.postnumber = l.postnumber AND t.blog_id = l.blog_id
						 SET t.feedcnt = LEAST(t.cntaccess, t.feedcnt + l.feed)",
						$from,
						$to
					)
				);

				$daily = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$daily_table} d
						 INNER JOIN (
							SELECT postnumber, blog_id, DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00') AS dp_date, COUNT(*) AS feed
							FROM   {$log_table}
							WHERE  id > %d AND id <= %d AND source = 1
							GROUP  BY postnumber, blog_id, DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00')
						 ) l ON d.postnumber = l.postnumber AND d.dp_date = l.dp_date AND d.blog_id = l.blog_id
						 SET d.feedcnt = LEAST(d.cntaccess, d.feedcnt + l.feed)",
						$from,
						$to
					)
				);

				if ( false === $overall || false === $daily ) {
					break;
				}

				$state['cursor'] = $to;
				self::save_state( $state );
			}
		} finally {
			$wpdb->query( "SELECT RELEASE_LOCK('tptn_feed_backfill')" );
		}
     // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( $state['cursor'] < $state['cutoff'] ) {
			self::schedule_backfill( MINUTE_IN_SECONDS );
			return;
		}

		if ( '' === $state['since'] ) {
			$state['since'] = $state['enabled_at'];
			self::save_state( $state );
		}

		/** This action is documented in includes/class-database.php */
		do_action( 'tptn_count_updated', 0, 0, false, array() );
	}

	/**
	 * Delete the state and any scheduled backfill.
	 *
	 * @since 4.6.0
	 */
	public static function delete_state() {
		wp_clear_scheduled_hook( self::BACKFILL_HOOK );
		delete_site_option( self::OPTION );
		delete_option( self::OPTION );
		self::reset_cache();
	}
}
