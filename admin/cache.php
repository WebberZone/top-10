<?php
/**
 * Top 10 Cache interface.
 *
 * @package   Top_Ten
 */

/**
 * Function to clear the Top 10 Cache with Ajax.
 *
 * @since	2.2.0
 */
function tptn_ajax_clearcache() {

	tptn_cache_delete();

	exit( json_encode( array(
		'success' => 1,
		'message' => __( 'Top 10 cache has been cleared', 'top-10' ),
	) ) );
}
add_action( 'wp_ajax_tptn_clear_cache', 'tptn_ajax_clearcache' );


/**
 * Get the default meta keys used for the cache
 *
 * @return	array	Transient meta keys
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

	/**
	 * Filters the array containing the various cache keys.
	 *
	 * @since	1.9
	 *
	 * @param	array	$default_meta_keys	Array of meta keys
	 */
	return apply_filters( 'tptn_cache_keys', $meta_keys );
}


/**
 * Delete the Top 10 cache.
 *
 * @param	array $transients
 */
function tptn_cache_delete( $transients = array() ) {

	$default_transients = tptn_cache_get_keys();

	if ( ! empty( $transients ) ) {
		$transients = array_intersect( $default_transients, (array) $transients );
	} else {
		$transients = $default_transients;
	}

	foreach ( $transients as $transient ) {
		delete_transient( $transient );
	}
}

