<?php
/**
 * Top 10 Cache functions.
 *
 * @since 3.3.0
 *
 * @package Top 10
 * @subpackage Util\Cache
 */

namespace WebberZone\Top_Ten\Util;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Cache {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_tptn_clear_cache', array( $this, 'ajax_clearcache' ) );
	}

	/**
	 * Function to clear the Top 10 Cache with Ajax.
	 *
	 * @since   2.2.0
	 */
	public function ajax_clearcache() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		check_ajax_referer( 'tptn-admin', 'security' );

		$count = $this->delete();

		wp_send_json_success(
			array(
				'message' => sprintf( _n( '%s entry cleared', '%s entries cleared', $count, 'top-10' ), number_format_i18n( $count ) ),
			)
		);
	}

	/**
	 * Delete the Top 10 cache.
	 *
	 * @since 2.3.0
	 *
	 * @param array $transients Array of transients to delete.
	 * @return int Number of transients deleted.
	 */
	public static function delete( $transients = array() ) {
		$loop = 0;

		$default_transients = self::get_keys();

		if ( ! empty( $transients ) ) {
			$transients = array_intersect( $default_transients, (array) $transients );
		} else {
			$transients = $default_transients;
		}

		foreach ( $transients as $transient ) {
			$del = delete_transient( $transient );
			if ( $del ) {
				++$loop;
			}
		}
		return $loop;
	}

	/**
	 * Get the default meta keys used for the cache
	 *
	 * @return  array   Transient meta keys
	 */
	public static function get_keys() {

		$meta_keys = array(
			'tptn_total',
			'tptn_daily',
			'tptn_total_shortcode',
			'tptn_daily_shortcode',
			'tptn_total_widget',
			'tptn_daily_widget',
			'tptn_total_manual',
			'tptn_daily_manual',
		);

		$meta_keys = array_merge( $meta_keys, self::get_widget_keys() );

		/**
		 * Filters the array containing the various cache keys.
		 *
		 * @since   1.9
		 *
		 * @param   array   $default_meta_keys  Array of meta keys
		 */
		return apply_filters( 'tptn_cache_keys', $meta_keys );
	}

	/**
	 * Get the transient names for the Top 10 widgets.
	 *
	 * @since 2.3.0
	 *
	 * @return array Top 10 Cache widget keys.
	 */
	public static function get_widget_keys() {
		global $wpdb;

		$keys = array();

		$sql = "
		SELECT option_name
		FROM {$wpdb->options}
		WHERE `option_name` LIKE '_transient_tptn_%'
		";

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$keys[] = str_replace( '_transient_', '', $result->option_name );
			}
		}

		return apply_filters( 'tptn_cache_get_widget_keys', $keys );
	}
}
