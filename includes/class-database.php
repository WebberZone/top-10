<?php
/**
 * Database class.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Util\Helpers;

/**
 * Database operations class.
 *
 * @since 4.2.0
 */
class Database {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// No initialization needed for static methods.
	}

	/**
	 * Get the table name for overall or daily counts.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $daily Whether to get the daily table.
	 * @return string Table name.
	 */
	public static function get_table( $daily = false ) {
		global $wpdb;

		$table_name = $wpdb->base_prefix . 'top_ten';
		if ( $daily ) {
			$table_name .= '_daily';
		}
		return $table_name;
	}

	/**
	 * Get count for a specific post.
	 *
	 * @since 4.2.0
	 *
	 * @param int   $post_id    Post ID.
	 * @param int   $blog_id    Blog ID (optional, defaults to current blog).
	 * @param bool  $daily      Whether to get daily count.
	 * @param array $date_range Date range array for daily counts ['from_date', 'to_date'].
	 * @return int Post count.
	 */
	public static function get_count( $post_id, $blog_id = null, $daily = false, $date_range = array() ) {
		global $wpdb;

		$blog_id = $blog_id ?? get_current_blog_id();
		$table   = self::get_table( $daily );

		if ( $daily && ! empty( $date_range ) ) {
			$where = $wpdb->prepare( 'WHERE postnumber = %d AND blog_id = %d', $post_id, $blog_id );

			if ( ! empty( $date_range['from_date'] ) ) {
				$where .= $wpdb->prepare( ' AND dp_date >= %s', $date_range['from_date'] );
			}
			if ( ! empty( $date_range['to_date'] ) ) {
				$where .= $wpdb->prepare( ' AND dp_date <= %s', $date_range['to_date'] );
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "SELECT SUM(cntaccess) FROM {$table} {$where}";
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT cntaccess FROM {$table} WHERE postnumber = %d AND blog_id = %d", $post_id, $blog_id );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Update count for a post.
	 *
	 * @since 4.2.0
	 * @deprecated 4.3.0 Use {@see Database::append_to_funnel()} instead.
	 *
	 * @param int  $post_id Post ID.
	 * @param int  $blog_id Blog ID (optional, defaults to current blog).
	 * @param bool $daily   Whether to update daily count.
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function update_count( $post_id, $blog_id = null, $daily = false ) {
		_deprecated_function( __METHOD__, '4.3.0', 'Database::append_to_funnel()' );

		$blog_id          = $blog_id ?? get_current_blog_id();
		$activate_counter = $daily ? 10 : 1;

		return self::append_to_funnel( $post_id, $blog_id, $activate_counter );
	}

	/**
	 * Set count for a post to a specific value.
	 *
	 * @since 4.2.0
	 *
	 * @param int  $post_id Post ID.
	 * @param int  $count   Count value to set.
	 * @param int  $blog_id Blog ID (optional, defaults to current blog).
	 * @param bool $daily   Whether to update daily count.
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function set_count( $post_id, $count, $blog_id = null, $daily = false ) {
		global $wpdb;

		$blog_id = $blog_id ?? get_current_blog_id();
		$table   = self::get_table( $daily );

		if ( $daily ) {
			$dp_date = current_time( 'Y-m-d H' );
			$sql     = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (postnumber, cntaccess, dp_date, blog_id) VALUES (%d, %d, %s, %d) ON DUPLICATE KEY UPDATE cntaccess = %d",
				$post_id,
				$count,
				$dp_date,
				$blog_id,
				$count
			);
		} else {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (postnumber, cntaccess, blog_id) VALUES (%d, %d, %d) ON DUPLICATE KEY UPDATE cntaccess = %d",
				$post_id,
				$count,
				$blog_id,
				$count
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $sql );

		// Trigger action to clear cache.
		if ( false !== $result ) {
			do_action( 'tptn_set_count', $post_id, $count, $blog_id, $daily );
		}

		return $result;
	}

	/**
	 * Delete counts based on criteria.
	 *
	 * @since 4.2.0
	 *
	 * @param array $args {
	 *     Optional. Array of arguments.
	 *
	 *     @type array  $post_ids   Array of post IDs to delete.
	 *     @type int    $blog_id    Blog ID to delete from.
	 *     @type string $from_date  Delete entries from this date (daily table only).
	 *     @type string $to_date    Delete entries until this date (daily table only).
	 *     @type bool   $daily      Whether to delete from daily table.
	 *     @type int    $limit      Maximum number of rows to delete per call (0 = no limit).
	 * }
	 * @return int|false Number of rows deleted or false on error.
	 */
	public static function delete_counts( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'post_ids'  => array(),
			'blog_id'   => null,
			'from_date' => '',
			'to_date'   => '',
			'daily'     => false,
			'limit'     => 0,
		);
		$args     = wp_parse_args( $args, $defaults );

		$table = self::get_table( $args['daily'] );
		$where = array();

		if ( ! empty( $args['post_ids'] ) ) {
			$post_ids = array_map( 'intval', $args['post_ids'] );
			$where[]  = 'postnumber IN (' . implode( ',', $post_ids ) . ')';
		}

		if ( null !== $args['blog_id'] ) {
			$where[] = $wpdb->prepare( 'blog_id = %d', $args['blog_id'] );
		}

		if ( $args['daily'] ) {
			if ( ! empty( $args['from_date'] ) ) {
				$where[] = $wpdb->prepare( 'dp_date >= %s', $args['from_date'] );
			}
			if ( ! empty( $args['to_date'] ) ) {
				$where[] = $wpdb->prepare( 'dp_date <= %s', $args['to_date'] );
			}
		}

		$sql = "DELETE FROM {$table}";
		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		if ( ! empty( $args['limit'] ) && $args['limit'] > 0 && ! empty( $where ) ) {
			$sql .= $wpdb->prepare( ' LIMIT %d', $args['limit'] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $sql );

		// Trigger action to clear cache.
		if ( false !== $result ) {
			do_action( 'tptn_delete_counts', $args );
		}

		return $result;
	}

	/**
	 * Get table statistics including entry count and size.
	 *
	 * @since 4.2.0
	 *
	 * @return array Array of table statistics with entry count and size.
	 */
	public static function get_table_statistics() {
		$cache_key = 'tptn_table_statistics';
		$stats     = wp_cache_get( $cache_key, 'top-10' );

		if ( false === $stats ) {
			$stats = array();

			$tables = array(
				'top_ten'               => self::get_table( false ),
				'top_ten_daily'         => self::get_table( true ),
				'top_ten_visits_funnel' => self::get_funnel_table(),
				'top_ten_visits_log'    => self::get_log_table(),
			);

			foreach ( $tables as $key => $table_name ) {
				if ( self::is_table_installed( $table_name ) ) {
					$stats[ $key ] = self::get_single_table_statistics( $table_name );
				}
			}

			// Cache for 5 minutes.
			wp_cache_set( $cache_key, $stats, 'top-10', 300 );
		}

		/**
		 * Filter the table statistics.
		 *
		 * @since 4.2.0
		 *
		 * @param array $stats Array of table statistics.
		 */
		return apply_filters( 'tptn_table_statistics', $stats );
	}

	/**
	 * Get entry count and estimated size for a single table.
	 *
	 * @since 4.3.0
	 *
	 * @param string $table_name Table name.
	 * @return array {
	 *     @type int   $entries Number of entries.
	 *     @type float $size    Estimated size in bytes.
	 * }
	 */
	private static function get_single_table_statistics( $table_name ) {
		global $wpdb;

		// Get row count.
		if ( is_network_admin() ) {
			// In network admin, count all entries.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
		} else {
			// In individual site admin, count only entries for this blog.
			$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$table_name}` WHERE blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					get_current_blog_id()
				)
			);
		}

		// Refresh InnoDB stats so information_schema reflects the current state.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ANALYZE TABLE `{$table_name}`" );

		// Get table size in bytes (always shows total size across all blogs).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$size = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT (data_length + index_length) FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s',
				defined( 'DB_NAME' ) ? DB_NAME : '', // @codingStandardsIgnoreLine - WordPress constant
				$table_name
			)
		);

		// Calculate size for individual sites in multisite.
		$calculated_size = $size ? (int) $size : 0;
		if ( is_multisite() && ! is_network_admin() && $calculated_size > 0 ) {
			// Get total entries to calculate ratio.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$total_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );

			if ( $total_count > 0 && $count > 0 ) {
				// Estimate size based on entry count ratio.
				$calculated_size = ( $count / $total_count ) * $calculated_size;
			}
		}

		return array(
			'entries' => absint( $count ),
			'size'    => $calculated_size,
		);
	}

	/**
	 * Clear the table statistics cache.
	 *
	 * @since 4.2.0
	 */
	public static function clear_table_statistics_cache() {
		wp_cache_delete( 'tptn_table_statistics', 'top-10' );
	}

	/**
	 * Check if a table exists.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table Table name to check.
	 * @return bool True if table exists, false otherwise.
	 */
	public static function is_table_installed( $table ) {
		global $wpdb;

		static $cache = array();

		if ( isset( $cache[ $table ] ) ) {
			return $cache[ $table ];
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
		$cache[ $table ] = ( $result === $table );

		return $cache[ $table ];
	}

	/**
	 * Get counts with post information (JOIN with wp_posts).
	 *
	 * @since 4.2.0
	 *
	 * @param array $args {
	 *     Optional. Array of arguments.
	 *
	 *     @type bool   $daily      Whether to get daily counts.
	 *     @type int    $blog_id    Blog ID to filter by.
	 *     @type string $from_date  From date for daily counts.
	 *     @type string $to_date    To date for daily counts.
	 *     @type int    $limit      Number of results to return.
	 *     @type int    $offset     Offset for pagination.
	 *     @type string $order      Order direction (ASC/DESC).
	 *     @type string $post_type  Post type to filter by.
	 *     @type array  $post_ids   Specific post IDs to include.
	 * }
	 * @return array Array of results with post and count information.
	 */
	public static function get_counts_with_posts( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'daily'     => false,
			'blog_id'   => null,
			'from_date' => '',
			'to_date'   => '',
			'limit'     => 10,
			'offset'    => 0,
			'order'     => 'DESC',
			'post_type' => 'post',
			'post_ids'  => array(),
		);
		$args     = wp_parse_args( $args, $defaults );

		$table      = self::get_table( $args['daily'] );
		$where      = array();
		$join       = " LEFT JOIN {$wpdb->posts} ON t.postnumber = {$wpdb->posts}.ID ";
		$select_col = $args['daily'] ? 'SUM(t.cntaccess) as cntaccess' : 't.cntaccess';

		if ( null !== $args['blog_id'] ) {
			$where[] = $wpdb->prepare( 't.blog_id = %d', $args['blog_id'] );
		}

		if ( $args['daily'] ) {
			if ( ! empty( $args['from_date'] ) ) {
				$where[] = $wpdb->prepare( 'DATE(t.dp_date) >= DATE(%s)', $args['from_date'] );
			}
			if ( ! empty( $args['to_date'] ) ) {
				$where[] = $wpdb->prepare( 'DATE(t.dp_date) <= DATE(%s)', $args['to_date'] );
			}
		}

		if ( ! empty( $args['post_ids'] ) ) {
			$post_ids = array_map( 'intval', $args['post_ids'] );
			$where[]  = 't.postnumber IN (' . implode( ',', $post_ids ) . ')';
		}

		$where[] = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", $args['post_type'] );
		$where[] = "{$wpdb->posts}.post_status = 'publish'";

		$sql  = "SELECT t.postnumber, {$select_col}, t.blog_id, {$wpdb->posts}.post_title, {$wpdb->posts}.post_date ";
		$sql .= "FROM {$table} t {$join}";
		$sql .= ' WHERE ' . implode( ' AND ', $where );

		if ( $args['daily'] ) {
			$sql .= " GROUP BY t.postnumber, t.blog_id, {$wpdb->posts}.post_title, {$wpdb->posts}.post_date ";
		}

		// Sanitize order parameter.
		$order = in_array( strtoupper( $args['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $args['order'] ) : 'DESC';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql .= $wpdb->prepare( " ORDER BY cntaccess {$order} LIMIT %d OFFSET %d", $args['limit'], $args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Bulk upsert counts for import operations.
	 *
	 * @since 4.2.0
	 *
	 * @param array $data Array of data to insert. Each element should be an array with postnumber, cntaccess, blog_id keys.
	 * @param bool  $daily Whether this is for daily table (includes dp_date).
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function bulk_upsert( $data, $daily = false ) {
		global $wpdb;

		if ( empty( $data ) ) {
			return false;
		}

		$table  = self::get_table( $daily );
		$values = array();

		foreach ( $data as $row ) {
			if ( $daily ) {
				$dp_date  = isset( $row['dp_date'] ) ? $row['dp_date'] : current_time( 'Y-m-d H' );
				$values[] = $wpdb->prepare( '( %d, %d, %s, %d )', $row['postnumber'], $row['cntaccess'], $dp_date, $row['blog_id'] );
			} else {
				$values[] = $wpdb->prepare( '( %d, %d, %d )', $row['postnumber'], $row['cntaccess'], $row['blog_id'] );
			}
		}

		if ( $daily ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "INSERT INTO {$table} (postnumber, cntaccess, dp_date, blog_id) VALUES " . implode( ',', $values ) . ' ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess)';
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "INSERT INTO {$table} (postnumber, cntaccess, blog_id) VALUES " . implode( ',', $values ) . ' ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess)';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->query( $sql );
	}

	/**
	 * Get total count for all posts.
	 *
	 * @since 4.2.0
	 *
	 * @param int    $blog_id Blog ID (optional, defaults to current blog).
	 * @param bool   $daily   Whether to get daily total.
	 * @param string $from_date From date for daily counts.
	 * @param string $to_date   To date for daily counts.
	 * @return int Total count.
	 */
	public static function get_total_count( $blog_id = null, $daily = false, $from_date = '', $to_date = '' ) {
		global $wpdb;

		$blog_id = $blog_id ?? get_current_blog_id();
		$table   = self::get_table( $daily );
		$where   = $wpdb->prepare( 'WHERE blog_id = %d', $blog_id );

		if ( $daily ) {
			if ( ! empty( $from_date ) ) {
				$where .= $wpdb->prepare( ' AND dp_date >= %s', $from_date );
			}
			if ( ! empty( $to_date ) ) {
				$where .= $wpdb->prepare( ' AND dp_date <= %s', $to_date );
			}
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "SELECT SUM(cntaccess) FROM {$table} {$where}";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get popular posts with caching support.
	 *
	 * @since 4.2.0
	 *
	 * @param array $args Query arguments (same format as Top_Ten_Core_Query).
	 * @return array Array of post IDs.
	 */
	public static function get_popular_posts( $args = array() ) {
		// This method integrates with the existing Top_Ten_Core_Query class
		// but provides a simpler interface for basic operations.

		$defaults = array(
			'daily'     => false,
			'limit'     => 10,
			'post_type' => 'post',
			'blog_id'   => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Use the existing query class for complex operations.
		$query = new \Top_Ten_Query( $args );
		$posts = $query->get_posts();

		return wp_list_pluck( $posts, 'ID' );
	}

	/**
	 * Check if the Top Ten tables exist.
	 *
	 * @since 4.2.0
	 *
	 * @return bool True if both tables exist, false otherwise.
	 */
	public static function are_tables_installed() {
		return self::is_table_installed( self::get_table( false ) )
			&& self::is_table_installed( self::get_table( true ) );
	}

	/**
	 * Create table SQL for the main top_ten table.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the main table.
	 */
	public static function create_full_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->base_prefix . 'top_ten';

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, blog_id),
			KEY idx_blog_id (blog_id)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Create table SQL for the daily top_ten_daily table.
	 *
	 * @since 4.2.0
	 *
	 * @return string SQL to create the daily table.
	 */
	public static function create_daily_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->base_prefix . 'top_ten_daily';

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			dp_date DATETIME NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, dp_date, blog_id),
			KEY blog_date (blog_id, dp_date, postnumber),
			KEY idx_dp_date (dp_date)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Get the name of the visits funnel table (hot buffer, drained every 5 minutes).
	 *
	 * @since 4.3.0
	 *
	 * @return string Table name.
	 */
	public static function get_funnel_table() {
		global $wpdb;
		return $wpdb->base_prefix . 'top_ten_visits_funnel';
	}

	/**
	 * SQL to create the visits funnel table.
	 *
	 * @since 4.3.0
	 *
	 * @return string CREATE TABLE SQL.
	 */
	public static function create_funnel_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = self::get_funnel_table();

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			id               bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			postnumber       bigint(20) UNSIGNED NOT NULL,
			blog_id          bigint(20) UNSIGNED NOT NULL DEFAULT '1',
			visited_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			activate_counter tinyint(2) UNSIGNED NOT NULL DEFAULT '11',
			source           tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY  (id)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Get the name of the visits log table (cold archive, pruned by maintenance cron).
	 *
	 * @since 4.3.0
	 *
	 * @return string Table name.
	 */
	public static function get_log_table() {
		global $wpdb;
		return $wpdb->base_prefix . 'top_ten_visits_log';
	}

	/**
	 * SQL to create the visits log table.
	 *
	 * @since 4.3.0
	 *
	 * @return string CREATE TABLE SQL.
	 */
	public static function create_log_table_sql() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = self::get_log_table();

		$sql = "CREATE TABLE {$table_name}" . // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		" (
			id         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			postnumber bigint(20) UNSIGNED NOT NULL,
			blog_id    bigint(20) UNSIGNED NOT NULL DEFAULT '1',
			visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			source     tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY  (id),
			KEY idx_visited_at (visited_at)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Append a single visit to the funnel table.
	 *
	 * @since 4.3.0
	 *
	 * @param int $post_id          Post ID.
	 * @param int $blog_id          Blog ID.
	 * @param int $activate_counter Counter flag: 1 = overall, 10 = daily, 11 = both.
	 * @param int $source           Traffic source: 0 = web, 1 = feed.
	 * @return int|false Rows inserted or false on error.
	 */
	public static function append_to_funnel( $post_id, $blog_id, $activate_counter = 11, $source = 0 ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->insert(
			self::get_funnel_table(),
			array(
				'postnumber'       => absint( $post_id ),
				'blog_id'          => absint( $blog_id ),
				'visited_at'       => current_time( 'mysql' ),
				'activate_counter' => (int) $activate_counter,
				'source'           => (int) $source,
			),
			array( '%d', '%d', '%s', '%d', '%d' )
		);
	}

	/**
	 * Drain the funnel into the log and count tables, then empty the funnel.
	 *
	 * All four operations (copy to log, aggregate to daily, aggregate to overall,
	 * delete from funnel) run inside one transaction. A failure rolls back cleanly
	 * and the next run retries the same rows with no double-counting risk.
	 *
	 * @since 4.3.0
	 *
	 * @param int $batch_size Maximum funnel rows to process per run.
	 * @return bool True if rows were processed, false if none found or lock not acquired.
	 */
	public static function aggregate_visit_log( $batch_size = 10000 ) {
		global $wpdb;

		// Detect SQLite (e.g. WordPress Playground) vs MySQL/MariaDB.
		$is_sqlite = false !== strpos( strtolower( $wpdb->db_server_info() ), 'sqlite' );

		$funnel_table = self::get_funnel_table();
		$log_table    = self::get_log_table();
		$daily_table  = self::get_table( true );
		$full_table   = self::get_table( false );

		// MySQL-specific locking and transactions.
		if ( ! $is_sqlite ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$lock_acquired = $wpdb->get_var( "SELECT GET_LOCK('tptn_aggregation', 0)" );
			if ( '1' !== (string) $lock_acquired ) {
				return false;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
				$wpdb->query( "SELECT RELEASE_LOCK('tptn_aggregation')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				return false;
			}
		}

		try {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$max_id = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$funnel_table}" );
			if ( 0 === $max_id ) {
				if ( ! $is_sqlite ) {
					$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				}
				return false;
			}

			$cap_id     = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$funnel_table} ORDER BY id ASC LIMIT %d, 1", $batch_size ) );
			$was_capped = false;
			if ( null !== $cap_id ) {
				$capped_max = (int) $cap_id - 1;
				if ( $capped_max > 0 ) {
					$max_id     = $capped_max;
					$was_capped = true;
				}
			}

			$r = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$log_table} (postnumber, blog_id, visited_at, source)
					 SELECT postnumber, blog_id, visited_at, source
					 FROM   {$funnel_table}
					 WHERE  id <= %d",
					$max_id
				)
			);
			if ( false === $r ) {
				if ( ! $is_sqlite ) {
					$wpdb->query( 'ROLLBACK' );
				}
				return false;
			}

			$r = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$daily_table} (postnumber, cntaccess, dp_date, blog_id)
					 SELECT * FROM (
					     SELECT postnumber, COUNT(*) AS cntaccess,
					            DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00') AS dp_date, blog_id
					     FROM   {$funnel_table}
					     WHERE  id <= %d AND activate_counter IN (10, 11)
					     GROUP  BY postnumber, DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00'), blog_id
					 ) AS new_row
					 ON DUPLICATE KEY UPDATE cntaccess = {$daily_table}.cntaccess + new_row.cntaccess",
					$max_id
				)
			);
			if ( false === $r ) {
				if ( ! $is_sqlite ) {
					$wpdb->query( 'ROLLBACK' );
				}
				return false;
			}

			$r = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$full_table} (postnumber, cntaccess, blog_id)
					 SELECT * FROM (
					     SELECT postnumber, COUNT(*) AS cntaccess, blog_id
					     FROM   {$funnel_table}
					     WHERE  id <= %d AND activate_counter IN (1, 11)
					     GROUP  BY postnumber, blog_id
					 ) AS new_row
					 ON DUPLICATE KEY UPDATE cntaccess = {$full_table}.cntaccess + new_row.cntaccess",
					$max_id
				)
			);
			if ( false === $r ) {
				if ( ! $is_sqlite ) {
					$wpdb->query( 'ROLLBACK' );
				}
				return false;
			}

			$r = $wpdb->query( $wpdb->prepare( "DELETE FROM {$funnel_table} WHERE id <= %d", $max_id ) );
			if ( false === $r ) {
				if ( ! $is_sqlite ) {
					$wpdb->query( 'ROLLBACK' );
				}
				return false;
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( ! $is_sqlite && false === $wpdb->query( 'COMMIT' ) ) {
				$wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				return false;
			}

			do_action( 'tptn_count_updated', 0, 0, false );

			if ( $was_capped && ! wp_next_scheduled( 'tptn_aggregation_cron_hook' ) ) {
				wp_schedule_single_event( time(), 'tptn_aggregation_cron_hook' );
			}

			return true;
		} finally {
			if ( ! $is_sqlite ) {
				$wpdb->query( "SELECT RELEASE_LOCK('tptn_aggregation')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}
		}
	}

	/**
	 * Recreate a table.
	 *
	 * This method recreates a table by creating a backup, dropping the original table,
	 * and then creating a new table with the original name and inserting the data from the backup.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name        The name of the table to recreate.
	 * @param string $create_table_sql  The SQL statement to create the new table.
	 * @param bool   $backup            Whether to backup the table or not.
	 * @param array  $fields            The fields to include in the temporary table and on duplicate key code.
	 * @param array  $group_by_fields   The fields to group by in the temporary table.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_table(
		$table_name,
		$create_table_sql,
		$backup = true,
		$fields = array( 'postnumber', 'cntaccess', 'blog_id' ),
		$group_by_fields = array( 'postnumber', 'blog_id' )
	) {
		global $wpdb;

		$backup_table_name = ( $backup ) ? $table_name . '_backup' : $table_name . '_temp';
		$success           = false;

		$fields_sql          = implode( ', ', $fields );
		$fields_sql_with_sum = str_replace( 'cntaccess', 'SUM(cntaccess) as cntaccess', $fields_sql );
		$group_by_sql        = implode( ', ', $group_by_fields );

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false !== $success ) {
				$success = $wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			} else {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'tptn_database_backup_failed', sprintf( esc_html__( 'Database backup failed on site %1$s. Error message: %2$s', 'top-10' ), get_site_url(), $wpdb->last_error ) );
			}
		} else {
			$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$success = $wpdb->query( "CREATE TEMPORARY TABLE $backup_table_name AS SELECT $fields_sql_with_sum FROM $table_name GROUP BY $group_by_sql" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( false !== $success ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			// Direct table creation without dbDelta for recreation.
			$wpdb->query( $create_table_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

			$insert_fields_sql = 'tt.' . implode( ', tt.', $fields );

			$success = $wpdb->query( "INSERT INTO $table_name ($fields_sql) SELECT $insert_fields_sql FROM $backup_table_name AS tt ON DUPLICATE KEY UPDATE $table_name.cntaccess = $table_name.cntaccess + VALUES(cntaccess)" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( false === $success ) {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'tptn_database_insert_failed', sprintf( esc_html__( 'Database insert failed on site %1$s. Error message: %2$s', 'top-10' ), get_site_url(), $wpdb->last_error ) );
			}
		}

		if ( ! $backup ) {
			$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return $success;
	}

	/**
	 * Recreate overall table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_overall_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->base_prefix . 'top_ten',
			self::create_full_table_sql(),
			$backup
		);
	}

	/**
	 * Recreate daily table.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $backup Whether to backup the table or not.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_daily_table( $backup = true ) {
		global $wpdb;
		return self::recreate_table(
			$wpdb->base_prefix . 'top_ten_daily',
			self::create_daily_table_sql(),
			$backup,
			array( 'postnumber', 'cntaccess', 'dp_date', 'blog_id' ),
			array( 'postnumber', 'dp_date', 'blog_id' )
		);
	}

	/**
	 * Truncate a table.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table_name Table name to truncate.
	 * @return bool True on success, false on failure.
	 */
	public static function truncate_table( $table_name ) {
		global $wpdb;

		// Table names cannot be parameterized in TRUNCATE statements.
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( "TRUNCATE TABLE $table_name" );
	}

	/**
	 * Count rows in the daily table that would be pruned up to a given date.
	 *
	 * @since 4.3.0
	 *
	 * @param string $to_date Rows with dp_date at or before this value are counted.
	 * @return int Row count.
	 */
	public static function count_deletable_daily_rows( string $to_date ): int {
		global $wpdb;
		$table = self::get_table( true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE dp_date <= %s", $to_date ) );
	}

	/**
	 * Count rows in the visits log table older than a given datetime.
	 *
	 * @since 4.3.0
	 *
	 * @param string $before_datetime Rows with visited_at before this value are counted.
	 * @return int Row count.
	 */
	public static function count_deletable_log_rows( string $before_datetime ): int {
		global $wpdb;
		$table = self::get_log_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$table}` WHERE visited_at < %s", $before_datetime ) );
	}

	/**
	 * Delete rows from the visits log table older than a given datetime.
	 *
	 * @since 4.3.0
	 *
	 * @param string $before_datetime Rows with visited_at before this value are deleted.
	 * @param int    $batch_size      Maximum rows to delete per call.
	 * @return int|false Rows deleted, or false on failure.
	 */
	public static function prune_log_table( string $before_datetime, int $batch_size = 1000 ) {
		global $wpdb;
		$table = self::get_log_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query( $wpdb->prepare( "DELETE FROM `{$table}` WHERE visited_at < %s LIMIT %d", $before_datetime, $batch_size ) );
	}

	/**
	 * Count rows in the visits funnel table.
	 *
	 * @since 4.3.0
	 *
	 * @return int Row count.
	 */
	public static function count_funnel_rows(): int {
		global $wpdb;
		$table = self::get_funnel_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
	}

	/**
	 * Count orphaned rows in a count table (rows with no matching post).
	 *
	 * Only inspects rows belonging to the current blog so that posts on other
	 * sites in a multisite network are not falsely reported as orphans.
	 *
	 * @since 4.3.0
	 *
	 * @param string $table_name Count table to inspect.
	 * @return int Row count.
	 */
	public static function count_orphan_counts( string $table_name ): int {
		global $wpdb;
		$blog_id = get_current_blog_id();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table_name}` t
				 LEFT JOIN `{$wpdb->posts}` p ON t.postnumber = p.ID
				 WHERE p.ID IS NULL AND t.blog_id = %d",
				$blog_id
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Delete orphaned rows from a count table (rows with no matching post).
	 *
	 * Only deletes rows belonging to the current blog so that posts on other
	 * sites in a multisite network are not falsely treated as orphans.
	 *
	 * @since 4.3.0
	 *
	 * @param string $table_name Count table to clean.
	 * @param int    $batch_size Maximum rows to delete per call.
	 * @return int|false Rows deleted, or false on failure.
	 */
	public static function delete_orphan_counts( string $table_name, int $batch_size = 1000 ) {
		global $wpdb;
		$blog_id = get_current_blog_id();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE t FROM `{$table_name}` t
				 LEFT JOIN `{$wpdb->posts}` p ON t.postnumber = p.ID
				 WHERE p.ID IS NULL AND t.blog_id = %d
				 LIMIT %d",
				$blog_id,
				$batch_size
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
