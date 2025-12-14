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
	 *
	 * @param int  $post_id Post ID.
	 * @param int  $blog_id Blog ID (optional, defaults to current blog).
	 * @param bool $daily   Whether to update daily count.
	 * @return int|false Number of rows affected or false on error.
	 */
	public static function update_count( $post_id, $blog_id = null, $daily = false ) {
		global $wpdb;

		$blog_id = $blog_id ?? get_current_blog_id();
		$table   = self::get_table( $daily );

		if ( $daily ) {
			$dp_date = current_time( 'Y-m-d H' );
			$sql     = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (postnumber, cntaccess, dp_date, blog_id) VALUES (%d, 1, %s, %d) ON DUPLICATE KEY UPDATE cntaccess = cntaccess+1",
				$post_id,
				$dp_date,
				$blog_id
			);
		} else {
			$sql = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO {$table} (postnumber, cntaccess, blog_id) VALUES (%d, 1, %d) ON DUPLICATE KEY UPDATE cntaccess = cntaccess+1",
				$post_id,
				$blog_id
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $sql );

		// Trigger action to clear cache.
		if ( false !== $result ) {
			do_action( 'tptn_count_updated', $post_id, $blog_id, $daily );
		}

		return $result;
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
		global $wpdb;

		$cache_key = 'tptn_table_statistics';
		$stats     = wp_cache_get( $cache_key, 'top-10' );

		if ( false === $stats ) {
			$table_name       = $wpdb->base_prefix . 'top_ten';
			$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';
			$stats            = array();

			// Get main table statistics.
			if ( self::is_table_installed( $table_name ) ) {
				// Get row count.
				if ( is_network_admin() ) {
					// In network admin, count all entries.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
				} else {
					// In individual site admin, count only entries for this blog.
					$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->prepare(
							"SELECT COUNT(*) FROM `{$table_name}` WHERE blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							get_current_blog_id()
						)
					);
				}

				// Get table size (always shows total size across all blogs).
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$size = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
						FROM information_schema.TABLES 
						WHERE table_schema = %s AND table_name = %s',
						defined( 'DB_NAME' ) ? DB_NAME : '', // @codingStandardsIgnoreLine - WordPress constant
						$table_name
					)
				);

				// Calculate size for individual sites in multisite.
				$calculated_size = $size ? (float) $size * 1024 * 1024 : 0; // Convert MB to bytes.
				if ( is_multisite() && ! is_network_admin() && $calculated_size > 0 ) {
					// Get total entries to calculate ratio.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$total_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );

					if ( $total_count > 0 && $count > 0 ) {
						// Estimate size based on entry count ratio.
						$calculated_size = ( $count / $total_count ) * $calculated_size;
					}
				}

				$stats['top_ten'] = array(
					'entries' => absint( $count ),
					'size'    => $calculated_size,
				);
			}

			// Get daily table statistics.
			if ( self::is_table_installed( $table_name_daily ) ) {
				// Get row count.
				if ( is_network_admin() ) {
					// In network admin, count all entries.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name_daily}`" );
				} else {
					// In individual site admin, count only entries for this blog.
					$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->prepare(
							"SELECT COUNT(*) FROM `{$table_name_daily}` WHERE blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
							get_current_blog_id()
						)
					);
				}

				// Get table size (always shows total size across all blogs).
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$size = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
						FROM information_schema.TABLES 
						WHERE table_schema = %s AND table_name = %s',
						defined( 'DB_NAME' ) ? DB_NAME : '', // @codingStandardsIgnoreLine - WordPress constant
						$table_name_daily
					)
				);

				// Calculate size for individual sites in multisite.
				$calculated_size = $size ? (float) $size * 1024 * 1024 : 0; // Convert MB to bytes.
				if ( is_multisite() && ! is_network_admin() && $calculated_size > 0 ) {
					// Get total entries to calculate ratio.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$total_count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name_daily}`" );

					if ( $total_count > 0 && $count > 0 ) {
						// Estimate size based on entry count ratio.
						$calculated_size = ( $count / $total_count ) * $calculated_size;
					}
				}

				$stats['top_ten_daily'] = array(
					'entries' => absint( $count ),
					'size'    => $calculated_size,
				);
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		return $result === $table;
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
			$sql .= ' GROUP BY t.postnumber, t.blog_id ';
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
		global $wpdb;

		$table_name       = $wpdb->base_prefix . 'top_ten';
		$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

		// Check if main table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );

		// Check if daily table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$daily_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name_daily}'" );

		return $table_exists === $table_name && $daily_table_exists === $table_name_daily;
	}

	/**
	 * Get table statistics.
	 *
	 * @since 4.2.0
	 *
	 * @param bool $daily Whether to get stats for daily table.
	 * @return array Statistics including total rows and total counts.
	 */
	public static function get_table_stats( $daily = false ) {
		global $wpdb;

		$table = self::get_table( $daily );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_rows = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_views = $wpdb->get_var( "SELECT SUM(cntaccess) FROM {$table}" );

		return array(
			'total_rows'  => (int) $total_rows,
			'total_views' => (int) $total_views,
		);
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
			PRIMARY KEY  (postnumber, blog_id)
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
			PRIMARY KEY  (postnumber, dp_date, blog_id)
		) $charset_collate;";

		return $sql;
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
			$wpdb->query( "DROP TABLE $backup_table_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
}
