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
	 * Cached status of the required Top 10 tables for this request.
	 *
	 * @var array<string,bool>
	 */
	private static $table_installation_cache = array();

	/**
	 * Database version used to populate the request cache.
	 *
	 * @var string|null
	 */
	private static $table_installation_cache_version = null;

	/**
	 * Cached status of non-standard tables checked during this request.
	 *
	 * @var array<string,bool>
	 */
	private static $individual_table_cache = array();

	/**
	 * Cached table metadata for this request.
	 *
	 * @var array<string,array<string,array<string,int|string>>>
	 */
	private static $table_metadata_cache = array();

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
	 * Get the count for a site-wide request context.
	 *
	 * Site-wide contexts are resolved by the Pro-only fixed-ID map and then
	 * addressed by their reserved numeric ID in the shared count tables.
	 *
	 * @since 4.5.0
	 *
	 * @param string   $context    Site-wide context key.
	 * @param int|null $blog_id    Blog ID (optional, defaults to current blog).
	 * @param bool     $daily      Whether to get the daily count.
	 * @param array    $date_range Date range array for daily counts.
	 * @return int Site-wide count.
	 */
	public static function get_sitewide_count( $context, $blog_id = null, $daily = false, $date_range = array() ) {
		return (int) apply_filters( 'tptn_get_sitewide_count', 0, $context, $blog_id, $daily, $date_range );
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
	 * Get estimated table statistics including entry count and size.
	 *
	 * @since 4.2.0
	 *
	 * @return array Array of table statistics with entry count and size.
	 */
	public static function get_table_statistics() {
		$cache_key = 'tptn_table_statistics';
		$stats     = wp_cache_get( $cache_key, 'top-10' );

		if ( false === $stats ) {
			$tables   = array(
				'top_ten'               => self::get_table( false ),
				'top_ten_daily'         => self::get_table( true ),
				'top_ten_visits_funnel' => self::get_funnel_table(),
				'top_ten_visits_log'    => self::get_log_table(),
			);
			$metadata = self::get_table_metadata( array_values( $tables ) );
			$stats    = array();

			foreach ( $tables as $key => $table_name ) {
				if ( isset( $metadata[ $table_name ] ) ) {
					$stats[ $key ] = array(
						'entries'   => $metadata[ $table_name ]['table_rows'],
						'size'      => $metadata[ $table_name ]['data_length'] + $metadata[ $table_name ]['index_length'],
						'estimated' => true,
					);
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
	 * Get table metadata for a set of tables.
	 *
	 * @since 4.5.0
	 *
	 * @param string[] $tables Tables to inspect.
	 * @param bool     $force  Whether to bypass the request cache.
	 * @return array<string,array<string,int|string>> Table metadata keyed by name.
	 */
	private static function get_table_metadata( $tables, $force = false ) {
		global $wpdb;

		$tables = array_values( array_unique( array_filter( $tables, 'is_string' ) ) );
		if ( empty( $tables ) ) {
			return array();
		}

		$cache_key = implode( '|', $tables );
		if ( ! $force && isset( self::$table_metadata_cache[ $cache_key ] ) ) {
			return self::$table_metadata_cache[ $cache_key ];
		}

		$placeholders = implode( ', ', array_fill( 0, count( $tables ), '%s' ) );
		if ( self::is_sqlite() ) {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT name AS TABLE_NAME, 0 AS TABLE_ROWS, 0 AS DATA_LENGTH, 0 AS INDEX_LENGTH FROM sqlite_master WHERE type = 'table' AND name IN ({$placeholders})",
				...$tables
			);
		} else {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ({$placeholders})",
				...$tables
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$rows     = $wpdb->get_results( $sql, ARRAY_A );
		$metadata = array();
		foreach ( $rows as $row ) {
			$table_name = isset( $row['TABLE_NAME'] ) ? (string) $row['TABLE_NAME'] : '';
			if ( '' !== $table_name && in_array( $table_name, $tables, true ) ) {
				$metadata[ $table_name ] = array(
					'table_rows'   => absint( $row['TABLE_ROWS'] ?? 0 ),
					'data_length'  => absint( $row['DATA_LENGTH'] ?? 0 ),
					'index_length' => absint( $row['INDEX_LENGTH'] ?? 0 ),
				);
			}
		}

		// Temporary tables are not exposed through information_schema or sqlite_master.
		foreach ( array_diff( $tables, array_keys( $metadata ) ) as $table_name ) {
			$query           = $wpdb->prepare( 'SELECT 1 FROM %i LIMIT 0', $table_name );
			$suppress_errors = $wpdb->suppress_errors();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$table_exists = false !== $wpdb->query( $query );
			$wpdb->suppress_errors( $suppress_errors );

			if ( $table_exists ) {
				$metadata[ $table_name ] = array(
					'table_rows'   => 0,
					'data_length'  => 0,
					'index_length' => 0,
				);
			}
		}

		self::$table_metadata_cache[ $cache_key ] = $metadata;

		return $metadata;
	}

	/**
	 * Get installation status for a set of tables.
	 *
	 * @since 4.5.0
	 *
	 * @param string[] $tables Tables to inspect.
	 * @param bool     $force  Whether to bypass the request cache.
	 * @return array<string,bool> Table statuses keyed by name.
	 */
	private static function get_table_statuses( $tables, $force = false ) {
		$tables   = array_values( array_unique( array_filter( $tables, 'is_string' ) ) );
		$metadata = self::get_table_metadata( $tables, $force );
		$statuses = array_fill_keys( $tables, false );

		foreach ( array_keys( $metadata ) as $table ) {
			$statuses[ $table ] = true;
		}

		return $statuses;
	}

	/**
	 * Determine whether the current database is SQLite.
	 *
	 * @since 4.5.0
	 *
	 * @return bool Whether SQLite is in use.
	 */
	private static function is_sqlite() {
		global $wpdb;

		if ( defined( 'DATABASE_TYPE' ) && 'sqlite' === strtolower( (string) DATABASE_TYPE ) ) {
			return true;
		}

		return method_exists( $wpdb, 'db_server_info' ) && false !== strpos( strtolower( (string) $wpdb->db_server_info() ), 'sqlite' );
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
	 * Invalidate the persistent and request-level table installation caches.
	 *
	 * @since 4.5.0
	 */
	public static function clear_table_installation_cache() {
		self::$table_installation_cache         = array();
		self::$table_installation_cache_version = null;
		self::$individual_table_cache           = array();
		self::$table_metadata_cache             = array();

		delete_site_option( 'tptn_tables_installed' );
	}

	/**
	 * Get the installation status of the four required Top 10 tables.
	 *
	 * The status is persisted in a network option so normal admin requests do
	 * not need to enumerate the database tables. Explicit diagnostic requests
	 * can bypass the cache by setting $force to true.
	 *
	 * @since 4.5.0
	 *
	 * @param bool $force Whether to perform a live check.
	 * @return array<string,bool> Table names mapped to their installation status.
	 */
	public static function get_table_installation_status( $force = false ) {
		global $tptn_db_version;

		$tables  = array(
			self::get_table( false ),
			self::get_table( true ),
			self::get_funnel_table(),
			self::get_log_table(),
		);
		$version = isset( $tptn_db_version ) ? (string) $tptn_db_version : '';

		$has_cached_tables = self::$table_installation_cache_version === $version && count( self::$table_installation_cache ) === count( $tables ) && ! array_diff_key( array_fill_keys( $tables, true ), self::$table_installation_cache );
		if ( ! $force && $has_cached_tables ) {
			return self::$table_installation_cache;
		}

		if ( ! $force ) {
			$cached         = get_site_option( 'tptn_tables_installed', array() );
			$has_all_tables = is_array( $cached ) && isset( $cached['db_version'], $cached['tables'] ) && (string) $cached['db_version'] === $version && is_array( $cached['tables'] ) && ! array_diff_key( array_fill_keys( $tables, true ), $cached['tables'] );
			if ( $has_all_tables ) {
				self::$table_installation_cache = array();
				self::$individual_table_cache   = array();
				foreach ( $tables as $table ) {
					self::$table_installation_cache[ $table ] = (bool) $cached['tables'][ $table ];
				}
				self::$table_installation_cache_version = $version;

				return self::$table_installation_cache;
			}
		}

		$statuses = self::get_table_statuses( $tables, $force );

		self::$table_installation_cache         = $statuses;
		self::$table_installation_cache_version = $version;
		update_site_option(
			'tptn_tables_installed',
			array(
				'db_version' => $version,
				'tables'     => $statuses,
			)
		);

		return $statuses;
	}

	/**
	 * Check if a table exists.
	 *
	 * @since 4.2.0
	 *
	 * @param string $table Table name to check.
	 * @param bool   $force Whether to perform a live check.
	 * @return bool True if table exists, false otherwise.
	 */
	public static function is_table_installed( $table, $force = false ) {
		$required_tables = array(
			self::get_table( false ),
			self::get_table( true ),
			self::get_funnel_table(),
			self::get_log_table(),
		);

		if ( in_array( $table, $required_tables, true ) ) {
			$statuses = self::get_table_installation_status( $force );
			return ! empty( $statuses[ $table ] );
		}

		if ( ! $force && array_key_exists( $table, self::$individual_table_cache ) ) {
			return self::$individual_table_cache[ $table ];
		}

		$statuses                               = self::get_table_statuses( array( $table ), $force );
		self::$individual_table_cache[ $table ] = ! empty( $statuses[ $table ] );

		return self::$individual_table_cache[ $table ];
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
				$where[] = $wpdb->prepare( 't.dp_date >= %s', gmdate( 'Y-m-d 00:00:00', strtotime( $args['from_date'] ) ) );
			}
			if ( ! empty( $args['to_date'] ) ) {
				$where[] = $wpdb->prepare( 't.dp_date < %s', gmdate( 'Y-m-d 00:00:00', strtotime( $args['to_date'] . ' +1 day' ) ) );
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
		$statuses = self::get_table_installation_status();

		return ! empty( $statuses[ self::get_table( false ) ] )
			&& ! empty( $statuses[ self::get_table( true ) ] );
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
			KEY idx_blog_id (blog_id),
			KEY idx_cntaccess (cntaccess),
			KEY idx_blog_cntaccess (blog_id, cntaccess)
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
	 * Record a single visit using the configured tracking method.
	 *
	 * Funnel tracking (default) appends the visit to the funnel table which is
	 * drained into the count tables by the aggregation cron. Legacy tracking
	 * writes directly to the count tables on every visit (pre-4.3 behaviour)
	 * and does not populate the visits log.
	 *
	 * @since 4.3.3
	 *
	 * @param int $post_id          Post ID.
	 * @param int $blog_id          Blog ID.
	 * @param int $activate_counter Counter flag: 1 = overall, 10 = daily, 11 = both.
	 * @param int $source           Traffic source: 0 = web, 1 = feed. Only stored by funnel tracking.
	 * @return int|false Rows inserted/updated or false on error.
	 */
	public static function record_view( $post_id, $blog_id, $activate_counter = 11, $source = 0 ) {
		if ( 'legacy' === \tptn_get_option( 'tracking_method', 'funnel' ) ) {
			return self::update_counts_direct( $post_id, $blog_id, $activate_counter );
		}

		return self::append_to_funnel( $post_id, $blog_id, $activate_counter, $source );
	}

	/**
	 * Write a single visit directly to the overall and daily count tables.
	 *
	 * This is the legacy (pre-4.3) tracking method: an immediate upsert per view,
	 * bypassing the funnel table and the aggregation cron. The visits log is not
	 * populated by this method.
	 *
	 * @since 4.3.3
	 *
	 * @param int $post_id          Post ID.
	 * @param int $blog_id          Blog ID.
	 * @param int $activate_counter Counter flag: 1 = overall, 10 = daily, 11 = both.
	 * @return int|false Rows inserted/updated or false on error.
	 */
	public static function update_counts_direct( $post_id, $blog_id, $activate_counter = 11 ) {
		global $wpdb;

		$post_id          = absint( $post_id );
		$blog_id          = absint( $blog_id );
		$activate_counter = (int) $activate_counter;
		$rows             = 0;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( in_array( $activate_counter, array( 1, 11 ), true ) ) {
			$table  = self::get_table( false );
			$result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table} (postnumber, cntaccess, blog_id) VALUES (%d, 1, %d) ON DUPLICATE KEY UPDATE cntaccess = cntaccess + 1",
					$post_id,
					$blog_id
				)
			);
			if ( false === $result ) {
				self::clear_table_installation_cache();
				return false;
			}
			$rows += (int) $result;
		}

		if ( in_array( $activate_counter, array( 10, 11 ), true ) ) {
			$table   = self::get_table( true );
			$dp_date = current_time( 'Y-m-d H' ) . ':00:00';
			$result  = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table} (postnumber, cntaccess, dp_date, blog_id) VALUES (%d, 1, %s, %d) ON DUPLICATE KEY UPDATE cntaccess = cntaccess + 1",
					$post_id,
					$dp_date,
					$blog_id
				)
			);
			if ( false === $result ) {
				self::clear_table_installation_cache();
				return false;
			}
			$rows += (int) $result;
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $rows;
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
		$result = $wpdb->insert(
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

		if ( false === $result ) {
			self::clear_table_installation_cache();
		}

		return $result;
	}

	/**
	 * Drain the funnel into the log and count tables, then empty the funnel.
	 *
	 * Each of the four steps (copy to log, aggregate to daily, aggregate to overall,
	 * delete from funnel) commits independently rather than inside one app-level
	 * transaction. wpdb silently reconnects and retries a query if the DB connection
	 * drops mid-request, which would otherwise void an in-flight transaction and let
	 * later steps (e.g. the funnel delete) commit on a fresh connection while earlier
	 * ones were rolled back — losing visits with no error. Without a wrapping
	 * transaction, a crash between steps can at worst cause one batch to be
	 * re-aggregated (a bounded, self-correcting over-count), never a silent loss.
	 *
	 * @since 4.3.0
	 *
	 * @param int      $batch_size Maximum funnel rows to process per run.
	 * @param int|null $blog_id   Optional blog ID. When set, only that site's buffered visits are processed.
	 * @return true|false|int|\WP_Error True if rows processed, false if lock not acquired, 0 if funnel empty, WP_Error on DB failure.
	 */
	public static function aggregate_visit_log( $batch_size = 10000, $blog_id = null ) {
		global $wpdb;

		$batch_size = max( 1, absint( $batch_size ) );
		$blog_id    = null === $blog_id ? null : absint( $blog_id );
		$blog_where = null === $blog_id ? '' : $wpdb->prepare( ' AND blog_id = %d', $blog_id );

		// Detect SQLite (e.g. WordPress Playground) vs MySQL/MariaDB.
		// DATABASE_TYPE is defined by the WordPress SQLite Database Integration drop-in.
		$is_sqlite = ( defined( 'DATABASE_TYPE' ) && 'sqlite' === DATABASE_TYPE )
			|| false !== strpos( strtolower( (string) $wpdb->db_server_info() ), 'sqlite' );

		$funnel_table = self::get_funnel_table();
		$log_table    = self::get_log_table();
		$daily_table  = self::get_table( true );
		$full_table   = self::get_table( false );

		// GET_LOCK is a MySQL-only concurrency guard against overlapping cron runs; not needed for correctness.
		if ( ! $is_sqlite ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$lock_acquired = $wpdb->get_var( "SELECT GET_LOCK('tptn_aggregation', 0)" );
			if ( '1' !== (string) $lock_acquired ) {
				return false;
			}
		}

		try {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$max_id = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$funnel_table} WHERE 1=1{$blog_where}" );
			if ( 0 === $max_id ) {
				return 0;
			}

			$cap_id     = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$funnel_table} WHERE 1=1{$blog_where} ORDER BY id ASC LIMIT %d, 1", $batch_size ) );
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
					 WHERE  id <= %d{$blog_where}",
					$max_id
				)
			);
			if ( false === $r ) {
				return new \WP_Error( 'tptn_log_insert_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Failed to copy visits to log table.', 'top-10' ) );
			}

			$r = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$daily_table} (postnumber, cntaccess, dp_date, blog_id)
					 SELECT postnumber, COUNT(*) AS cntaccess,
					        DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00') AS dp_date, blog_id
					 FROM   {$funnel_table}
					 WHERE  id <= %d AND activate_counter IN (10, 11){$blog_where}
					 GROUP  BY postnumber, DATE_FORMAT(visited_at, '%%Y-%%m-%%d %%H:00:00'), blog_id
					 ON DUPLICATE KEY UPDATE cntaccess = {$daily_table}.cntaccess + VALUES(cntaccess)",
					$max_id
				)
			);
			if ( false === $r ) {
				return new \WP_Error( 'tptn_daily_insert_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Failed to aggregate visits into daily table.', 'top-10' ) );
			}

			$r = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$full_table} (postnumber, cntaccess, blog_id)
					 SELECT postnumber, COUNT(*) AS cntaccess, blog_id
					 FROM   {$funnel_table}
					 WHERE  id <= %d AND activate_counter IN (1, 11){$blog_where}
					 GROUP  BY postnumber, blog_id
					 ON DUPLICATE KEY UPDATE cntaccess = {$full_table}.cntaccess + VALUES(cntaccess)",
					$max_id
				)
			);
			if ( false === $r ) {
				return new \WP_Error( 'tptn_overall_insert_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Failed to aggregate visits into overall table.', 'top-10' ) );
			}

			$r = $wpdb->query( $wpdb->prepare( "DELETE FROM {$funnel_table} WHERE id <= %d{$blog_where}", $max_id ) );
			if ( false === $r ) {
				return new \WP_Error( 'tptn_funnel_delete_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Failed to drain funnel table.', 'top-10' ) );
			}
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * Recreate visits funnel table.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $backup Whether to create a permanent backup table before recreating.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_funnel_table( $backup = true ) {
		global $wpdb;

		$table_name        = self::get_funnel_table();
		$backup_table_name = $backup ? $table_name . '_backup' : $table_name . '_temp';
		$fields_sql        = 'postnumber, blog_id, visited_at, activate_counter, source';
		$success           = false;

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false === $success ) {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'tptn_database_backup_failed', sprintf( esc_html__( 'Database backup failed on site %1$s. Error message: %2$s', 'top-10' ), get_site_url(), $wpdb->last_error ) );
			}
			$wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$success = $wpdb->query( "CREATE TEMPORARY TABLE $backup_table_name AS SELECT $fields_sql FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( false !== $success ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( self::create_funnel_table_sql() ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

			$success = $wpdb->query( "INSERT INTO $table_name ($fields_sql) SELECT $fields_sql FROM $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * Recreate visits log table.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $backup Whether to create a permanent backup table before recreating.
	 *
	 * @return bool|\WP_Error True if recreated, error message if failed.
	 */
	public static function recreate_log_table( $backup = true ) {
		global $wpdb;

		$table_name        = self::get_log_table();
		$backup_table_name = $backup ? $table_name . '_backup' : $table_name . '_temp';
		$fields_sql        = 'postnumber, blog_id, visited_at, source';
		$success           = false;

		if ( $backup ) {
			$success = $wpdb->query( "CREATE TABLE $backup_table_name LIKE $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( false === $success ) {
				/* translators: 1: Site number, 2: Error message */
				return new \WP_Error( 'tptn_database_backup_failed', sprintf( esc_html__( 'Database backup failed on site %1$s. Error message: %2$s', 'top-10' ), get_site_url(), $wpdb->last_error ) );
			}
			$wpdb->query( "INSERT INTO $backup_table_name SELECT * FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$success = $wpdb->query( "CREATE TEMPORARY TABLE $backup_table_name AS SELECT $fields_sql FROM $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( false !== $success ) {
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( self::create_log_table_sql() ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared

			$success = $wpdb->query( "INSERT INTO $table_name ($fields_sql) SELECT $fields_sql FROM $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
	 * Get the daily-table row counts before and after a rollup.
	 *
	 * The projected row count groups rows by post, blog, and calendar date.
	 *
	 * @since 4.5.0
	 *
	 * @param string   $before_date Rows before this date are included.
	 * @param int|null $blog_id    Blog ID. Defaults to the current blog.
	 * @return array|\WP_Error Rollup statistics or an error.
	 */
	public static function get_daily_rollup_stats( string $before_date, $blog_id = null ) {
		global $wpdb;

		$before_date = self::normalize_daily_rollup_date( $before_date );
		if ( is_wp_error( $before_date ) ) {
			return $before_date;
		}

		$blog_id = null === $blog_id ? get_current_blog_id() : absint( $blog_id );
		$table   = self::get_table( true );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows_before = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE blog_id = %d AND dp_date < %s",
				$blog_id,
				$before_date
			)
		);
		if ( null === $rows_before ) {
			return new \WP_Error( 'tptn_rollup_count_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not count daily rows before the rollup.', 'top-10' ) );
		}

		$rows_after = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT postnumber, blog_id, DATE(dp_date) AS rollup_date
					FROM `{$table}`
					WHERE blog_id = %d AND dp_date < %s
					GROUP BY postnumber, blog_id, DATE(dp_date)
				) AS rollup_groups",
				$blog_id,
				$before_date
			)
		);
		if ( null === $rows_after ) {
			return new \WP_Error( 'tptn_rollup_projection_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not calculate the projected daily row count.', 'top-10' ) );
		}

		$dates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT DATE(dp_date)) FROM `{$table}` WHERE blog_id = %d AND dp_date < %s",
				$blog_id,
				$before_date
			)
		);
		if ( null === $dates ) {
			return new \WP_Error( 'tptn_rollup_dates_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not count daily rollup dates.', 'top-10' ) );
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return array(
			'rows_before'  => (int) $rows_before,
			'rows_after'   => (int) $rows_after,
			'rows_reduced' => max( 0, (int) $rows_before - (int) $rows_after ),
			'dates'        => (int) $dates,
		);
	}

	/**
	 * Roll up hourly daily rows older than a date into one midnight row per post.
	 *
	 * Each calendar date is processed in its own transaction so an interrupted
	 * operation can safely resume on the next date. The overall count table is
	 * never modified.
	 *
	 * @since 4.5.0
	 *
	 * @param string   $before_date Rows before this date are rolled up.
	 * @param int|null $blog_id    Blog ID. Defaults to the current blog.
	 * @return array|\WP_Error Rollup statistics or an error.
	 */
	public static function rollup_daily( string $before_date, $blog_id = null ) {
		global $wpdb;

		$before_date = self::normalize_daily_rollup_date( $before_date );
		if ( is_wp_error( $before_date ) ) {
			return $before_date;
		}

		$blog_id = null === $blog_id ? get_current_blog_id() : absint( $blog_id );
		$table   = self::get_table( true );
		$before  = self::get_daily_rollup_stats( $before_date, $blog_id );
		if ( is_wp_error( $before ) ) {
			return $before;
		}

		$last_date       = '';
		$dates_processed = 0;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		while ( true ) {
			// Select one unprocessed date at a time. Existing midnight rows are
			// already rolled up and are therefore skipped on subsequent runs.
			if ( '' === $last_date ) {
				$next_date = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT DATE(dp_date) FROM `{$table}` WHERE blog_id = %d AND dp_date < %s AND TIME(dp_date) <> '00:00:00' ORDER BY dp_date ASC LIMIT 1",
						$blog_id,
						$before_date
					)
				);
			} else {
				$next_date_start = ( new \DateTimeImmutable( $last_date, new \DateTimeZone( 'UTC' ) ) )->modify( '+1 day' )->format( 'Y-m-d 00:00:00' );
				$next_date       = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT DATE(dp_date) FROM `{$table}` WHERE blog_id = %d AND dp_date >= %s AND dp_date < %s AND TIME(dp_date) <> '00:00:00' ORDER BY dp_date ASC LIMIT 1",
						$blog_id,
						$next_date_start,
						$before_date
					)
				);
			}

			if ( null === $next_date ) {
				if ( ! empty( $wpdb->last_error ) ) {
					return new \WP_Error( 'tptn_rollup_date_failed', $wpdb->last_error );
				}
				break;
			}

			$day_start        = $next_date . ' 00:00:00';
			$day_end          = ( new \DateTimeImmutable( $next_date, new \DateTimeZone( 'UTC' ) ) )->modify( '+1 day' )->format( 'Y-m-d 00:00:00' );
			$transaction_open = false;

			if ( false === $wpdb->query( 'START TRANSACTION' ) ) {
				return new \WP_Error( 'tptn_rollup_start_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not start the daily rollup transaction.', 'top-10' ) );
			}
			$transaction_open = true;

			try {
				$daily_rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT postnumber, cntaccess
						 FROM `{$table}`
						 WHERE blog_id = %d AND dp_date >= %s AND dp_date < %s
						 ORDER BY postnumber ASC
						 FOR UPDATE",
						$blog_id,
						$day_start,
						$day_end
					),
					ARRAY_A
				);
				if ( null === $daily_rows ) {
					return new \WP_Error( 'tptn_rollup_select_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not read the daily rows for the rollup.', 'top-10' ) );
				}

				$rollup_counts = array();
				foreach ( $daily_rows as $daily_row ) {
					$postnumber = (int) $daily_row['postnumber'];
					if ( ! isset( $rollup_counts[ $postnumber ] ) ) {
						$rollup_counts[ $postnumber ] = 0;
					}
					$rollup_counts[ $postnumber ] += (int) $daily_row['cntaccess'];
				}

				foreach ( array_chunk( $rollup_counts, 500, true ) as $rollup_batch ) {
					$values = array();
					foreach ( $rollup_batch as $postnumber => $count ) {
						$values[] = $wpdb->prepare(
							'( %d, %d, %s, %d )',
							$postnumber,
							$count,
							$day_start,
							$blog_id
						);
					}

					$result = $wpdb->query(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						"INSERT INTO `{$table}` (postnumber, cntaccess, dp_date, blog_id) VALUES " . implode( ',', $values ) . ' ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess)'
					);
					if ( false === $result ) {
						return new \WP_Error( 'tptn_rollup_insert_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not write the daily rollup.', 'top-10' ) );
					}
				}

				$result = $wpdb->query(
					$wpdb->prepare(
						"DELETE FROM `{$table}` WHERE blog_id = %d AND dp_date >= %s AND dp_date < %s AND dp_date <> %s",
						$blog_id,
						$day_start,
						$day_end,
						$day_start
					)
				);
				if ( false === $result ) {
					return new \WP_Error( 'tptn_rollup_delete_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not remove the hourly daily rows.', 'top-10' ) );
				}

				if ( false === $wpdb->query( 'COMMIT' ) ) {
					return new \WP_Error( 'tptn_rollup_commit_failed', $wpdb->last_error ? $wpdb->last_error : __( 'Could not commit the daily rollup.', 'top-10' ) );
				}
				$transaction_open = false;
			} finally {
				if ( $transaction_open ) {
					$wpdb->query( 'ROLLBACK' );
				}
			}
			++$dates_processed;
			$last_date = $next_date;
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$after = self::get_daily_rollup_stats( $before_date, $blog_id );
		if ( is_wp_error( $after ) ) {
			return $after;
		}

		if ( $dates_processed > 0 ) {
			do_action( 'tptn_count_updated', 0, 0, false );
		}

		return array(
			'rows_before'     => $before['rows_before'],
			'rows_after'      => $after['rows_before'],
			'rows_reduced'    => max( 0, $before['rows_before'] - $after['rows_before'] ),
			'dates'           => $after['dates'],
			'dates_processed' => $dates_processed,
		);
	}

	/**
	 * Normalize a rollup boundary to midnight.
	 *
	 * @since 4.5.0
	 *
	 * @param string $before_date Rollup boundary in Y-m-d or Y-m-d 00:00:00 format.
	 * @return string|\WP_Error Normalized date or an error.
	 */
	private static function normalize_daily_rollup_date( string $before_date ) {
		$before_date = trim( $before_date );
		$date        = preg_replace( '/ 00:00:00$/', '', $before_date );

		if ( ! is_string( $date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return new \WP_Error( 'tptn_invalid_rollup_date', __( 'The daily rollup boundary must be a valid date in Y-m-d format.', 'top-10' ) );
		}

		$date_object = \DateTimeImmutable::createFromFormat( '!Y-m-d', $date, new \DateTimeZone( 'UTC' ) );
		$errors      = \DateTimeImmutable::getLastErrors();
		if ( false === $date_object || ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) ) {
			return new \WP_Error( 'tptn_invalid_rollup_date', __( 'The daily rollup boundary must be a valid date in Y-m-d format.', 'top-10' ) );
		}

		return $date_object->format( 'Y-m-d 00:00:00' );
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
		$blog_id       = get_current_blog_id();
		$context_ids   = array_map( 'intval', (array) apply_filters( 'tptn_sitewide_context_ids', array() ) );
		$context_where = '';
		if ( $context_ids ) {
			$context_where = ' AND t.postnumber NOT IN (' . implode( ',', $context_ids ) . ')';
		}
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table_name}` t
				 LEFT JOIN `{$wpdb->posts}` p ON t.postnumber = p.ID
				 WHERE p.ID IS NULL AND t.blog_id = %d{$context_where}",
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
		$blog_id       = get_current_blog_id();
		$context_ids   = array_map( 'intval', (array) apply_filters( 'tptn_sitewide_context_ids', array() ) );
		$context_where = '';
		if ( $context_ids ) {
			$context_where = ' AND t.postnumber NOT IN (' . implode( ',', $context_ids ) . ')';
		}
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE t FROM `{$table_name}` t
				 LEFT JOIN `{$wpdb->posts}` p ON t.postnumber = p.ID
				 WHERE p.ID IS NULL AND t.blog_id = %d{$context_where}
				 LIMIT %d",
				$blog_id,
				$batch_size
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
