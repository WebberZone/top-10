<?php
/**
 * Koko Analytics integration.
 *
 * @package WebberZone\Top_Ten\Integrations
 */

namespace WebberZone\Top_Ten\Integrations;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Lets Koko Analytics power the popular posts lists.
 *
 * @since 4.6.0
 */
class Koko_Analytics {

	/**
	 * Top 10 tracks views and powers the lists.
	 *
	 * @since 4.6.0
	 */
	const MODE_TOP10 = 'top10';

	/**
	 * Koko Analytics powers the lists while Top 10 keeps tracking.
	 *
	 * @since 4.6.0
	 */
	const MODE_HYBRID = 'hybrid';

	/**
	 * Koko Analytics powers the lists and Top 10 tracking is off.
	 *
	 * @since 4.6.0
	 */
	const MODE_KOKO = 'koko';

	/**
	 * Availability per blog ID.
	 *
	 * @since 4.6.0
	 *
	 * @var array<int, bool>
	 */
	private static $available = array();

	/**
	 * Get the selected data mode.
	 *
	 * @since 4.6.0
	 *
	 * @return string One of the MODE_* constants.
	 */
	public static function get_mode(): string {
		$mode = (string) \tptn_get_option( 'data_mode' );

		return in_array( $mode, array( self::MODE_TOP10, self::MODE_HYBRID, self::MODE_KOKO ), true ) ? $mode : self::MODE_TOP10;
	}

	/**
	 * Get the mode in effect, falling back to Top 10 when Koko Analytics is unavailable.
	 *
	 * @since 4.6.0
	 *
	 * @return string One of the MODE_* constants.
	 */
	public static function get_effective_mode(): string {
		$mode = self::get_mode();

		return ( self::MODE_TOP10 !== $mode && self::is_available() ) ? $mode : self::MODE_TOP10;
	}

	/**
	 * Whether popular posts lists should read from Koko Analytics.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function is_source(): bool {
		return self::MODE_TOP10 !== self::get_effective_mode();
	}

	/**
	 * Get the Koko Analytics version.
	 *
	 * @since 4.6.0
	 *
	 * @return string Version, or an empty string when Koko Analytics is not loaded.
	 */
	public static function get_version(): string {
		return defined( 'KOKO_ANALYTICS_VERSION' ) ? (string) KOKO_ANALYTICS_VERSION : '';
	}

	/**
	 * Get the source that powers popular posts lists. Sites in a network list without Koko Analytics use Top 10.
	 *
	 * @since 4.6.0
	 *
	 * @return string 'koko' or 'top10'.
	 */
	public static function get_list_source(): string {
		return self::is_source() ? 'koko' : 'top10';
	}

	/**
	 * Whether Top 10 tracking is switched off.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function is_tracking_disabled(): bool {
		return self::MODE_KOKO === self::get_effective_mode();
	}

	/**
	 * Whether Koko Analytics is active and its post stats table is usable.
	 *
	 * @since 4.6.0
	 *
	 * @param int $blog_id Blog ID. Defaults to the current blog.
	 * @return bool
	 */
	public static function is_available( $blog_id = 0 ): bool {
		global $wpdb;

		$blog_id = $blog_id ? (int) $blog_id : get_current_blog_id();
		if ( isset( self::$available[ $blog_id ] ) ) {
			return self::$available[ $blog_id ];
		}

		if ( ! self::is_active( $blog_id ) ) {
			self::$available[ $blog_id ] = false;
			return false;
		}

		$cache_key = 'tptn_koko_status_' . $blog_id;
		$ready     = get_site_transient( $cache_key );

		if ( false === $ready ) {
			$table   = self::get_table( $blog_id );
			$columns = array();

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$columns = $wpdb->get_col( $wpdb->prepare( 'SHOW COLUMNS FROM %i', $table ) );
			}

			$ready = array() === array_diff( array( 'date', 'post_id', 'pageviews' ), $columns ) ? 'ready' : 'missing';
			set_site_transient( $cache_key, $ready, 'ready' === $ready ? DAY_IN_SECONDS : 5 * MINUTE_IN_SECONDS );
		}

		self::$available[ $blog_id ] = 'ready' === $ready;

		return self::$available[ $blog_id ];
	}

	/**
	 * Whether Koko Analytics is active on a site.
	 *
	 * @since 4.6.0
	 *
	 * @param int $blog_id Blog ID.
	 * @return bool
	 */
	private static function is_active( $blog_id ): bool {
		$plugin = defined( 'KOKO_ANALYTICS_PLUGIN_FILE' ) ? plugin_basename( KOKO_ANALYTICS_PLUGIN_FILE ) : 'koko-analytics/koko-analytics.php';

		if ( ! is_multisite() || get_current_blog_id() === $blog_id ) {
			return defined( 'KOKO_ANALYTICS_VERSION' );
		}

		if ( array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) ) {
			return true;
		}

		return in_array( $plugin, (array) get_blog_option( $blog_id, 'active_plugins', array() ), true );
	}

	/**
	 * Get the Koko Analytics post stats table for a blog.
	 *
	 * @since 4.6.0
	 *
	 * @param int $blog_id Blog ID.
	 * @return string Table name.
	 */
	public static function get_table( $blog_id = 0 ): string {
		global $wpdb;

		return $wpdb->get_blog_prefix( $blog_id ? (int) $blog_id : get_current_blog_id() ) . 'koko_analytics_post_stats';
	}

	/**
	 * Build a derived table with the same columns as the Top 10 count tables.
	 *
	 * @since 4.6.0
	 *
	 * @param string $from_date From date. Empty for no lower bound.
	 * @param string $to_date   To date. Empty for no upper bound.
	 * @param int    $blog_id   Blog ID.
	 * @return string SELECT statement exposing postnumber, cntaccess and blog_id.
	 */
	public static function get_counts_sql( $from_date = '', $to_date = '', $blog_id = 0 ): string {
		global $wpdb;

		$blog_id = $blog_id ? (int) $blog_id : get_current_blog_id();

		return $wpdb->prepare(
			'SELECT post_id AS postnumber, SUM(pageviews) AS cntaccess, %d AS blog_id FROM %i WHERE post_id > 0', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$blog_id,
			self::get_table( $blog_id )
		) . self::get_date_where( $from_date, $to_date ) . ' GROUP BY post_id';
	}

	/**
	 * Get Koko Analytics page views for specific posts.
	 *
	 * @since 4.6.0
	 *
	 * @param int[]  $ids       Post IDs.
	 * @param string $from_date From date. Empty for no lower bound.
	 * @param string $to_date   To date. Empty for no upper bound.
	 * @return array<int, int> Page views keyed by post ID.
	 */
	public static function get_counts( array $ids, $from_date = '', $to_date = '' ): array {
		global $wpdb;

		$ids = wp_parse_id_list( $ids );
		if ( ! $ids ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = $wpdb->prepare( "SELECT post_id, SUM(pageviews) AS visits FROM %i WHERE post_id IN ($placeholders)", array_merge( array( self::get_table() ), $ids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$sql         .= self::get_date_where( $from_date, $to_date ) . ' GROUP BY post_id';

		$counts = array();
		foreach ( $wpdb->get_results( $sql ) as $row ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
			$counts[ (int) $row->post_id ] = (int) $row->visits;
		}

		return $counts;
	}

	/**
	 * Build the date condition. Koko Analytics stores one row per day, so times round down to the day.
	 *
	 * @since 4.6.0
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return string SQL fragment.
	 */
	private static function get_date_where( $from_date, $to_date ): string {
		global $wpdb;

		$where = '';
		$from  = $from_date ? strtotime( $from_date ) : false;
		$to    = $to_date ? strtotime( $to_date ) : false;

		if ( $from ) {
			$where .= $wpdb->prepare( ' AND date >= %s', gmdate( 'Y-m-d', $from ) );
		}
		if ( $to ) {
			$where .= $wpdb->prepare( ' AND date <= %s', gmdate( 'Y-m-d', $to ) );
		}

		return $where;
	}
}
