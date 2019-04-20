<?php
/**
 * Language functions
 *
 * @package Top_Ten
 */

/**
 * Function to load translation files.
 *
 * @since   1.9.10.1
 */
function tptn_lang_init() {
	load_plugin_textdomain( 'top-10', false, dirname( plugin_basename( TOP_TEN_PLUGIN_FILE ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'tptn_lang_init' );


/**
 * Fetch the post of the correct language.
 *
 * @since   2.1.0
 *
 * @param   int $post_id    Post ID.
 */
function tptn_object_id_cur_lang( $post_id ) {

	$return_original_if_missing = false;

	/**
	 * Filter to modify if the original language ID is returned.
	 *
	 * @since   2.2.3
	 *
	 * @param   bool    $return_original_if_missing
	 * @param   int $post_id    Post ID
	 */
	$return_original_if_missing = apply_filters( 'tptn_wpml_return_original', $return_original_if_missing, $post_id );

	if ( function_exists( 'pll_get_post' ) ) {
		$post_id = pll_get_post( $post_id );
	} elseif ( function_exists( 'wpml_object_id' ) ) {
		$post_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), $return_original_if_missing );
	} elseif ( function_exists( 'icl_object_id' ) ) {
		$post_id = icl_object_id( $post_id, get_post_type( $post_id ), $return_original_if_missing );
	}

	/**
	 * Filters object ID for current language (WPML).
	 *
	 * @since   2.1.0
	 *
	 * @param   int $post_id    Post ID
	 */
	return apply_filters( 'tptn_object_id_cur_lang', $post_id );
}


