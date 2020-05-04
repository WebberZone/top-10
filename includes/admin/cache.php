<?php
/**
 * Top 10 Cache interface.
 *
 * @package   Top_Ten
 */

/**
 * Function to clear the Top 10 Cache with Ajax.
 *
 * @since   2.2.0
 */
function tptn_ajax_clearcache() {

	$count = tptn_cache_delete();

	exit(
		wp_json_encode(
			array(
				'success' => 1,
				/* translators: 1: Number of entries. */
				'message' => sprintf( _n( '%s entry cleared', '%s entries cleared', $count, 'text-domain' ), number_format_i18n( $count ) ),
			)
		)
	);
}
add_action( 'wp_ajax_tptn_clear_cache', 'tptn_ajax_clearcache' );


/**
 * Get the default meta keys used for the cache
 *
 * @return  array   Transient meta keys
 */
function tptn_cache_get_keys() {

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

	$meta_keys = array_merge( $meta_keys, tptn_cache_get_widget_keys() );

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
 * Delete the Top 10 cache.
 *
 * @since 2.3.0
 *
 * @param array $transients Array of transients to delete.
 * @return int Number of transients deleted.
 */
function tptn_cache_delete( $transients = array() ) {
	$loop = 0;

	$default_transients = tptn_cache_get_keys();

	if ( ! empty( $transients ) ) {
		$transients = array_intersect( $default_transients, (array) $transients );
	} else {
		$transients = $default_transients;
	}

	foreach ( $transients as $transient ) {
		$del = delete_transient( $transient );
		if ( $del ) {
			$loop++;
		}
	}
	return $loop;
}


/**
 * Get the transient names for the Top 10 widgets.
 *
 * @since 2.3.0
 *
 * @return array Top 10 Cache widget keys.
 */
function tptn_cache_get_widget_keys() {
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

