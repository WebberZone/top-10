<?php
/**
 * Functions dealing with styles.
 *
 * @package   Top_Ten
 */

/**
 * Function to add CSS to header.
 *
 * @since   1.9
 */
function tptn_header() {

	$tptn_custom_css = stripslashes( tptn_get_option( 'custom_css' ) );

	// Add CSS to header.
	if ( '' != $tptn_custom_css ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		echo '<style type="text/css">' . $tptn_custom_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'tptn_header' );


/**
 * Enqueue styles.
 */
function tptn_heading_styles() {

	if ( 'left_thumbs' === tptn_get_option( 'tptn_styles' ) ) {
		wp_register_style( 'tptn-style-left-thumbs', plugins_url( 'css/default-style.css', TOP_TEN_PLUGIN_FILE ), array(), '1.0' );
		wp_enqueue_style( 'tptn-style-left-thumbs' );

		$width  = tptn_get_option( 'thumb_width' );
		$height = tptn_get_option( 'thumb_height' );

		$custom_css = "
img.tptn_thumb {
  width: {$width}px !important;
  height: {$height}px !important;
}
                ";

		wp_add_inline_style( 'tptn-style-left-thumbs', $custom_css );

	}

}
add_action( 'wp_enqueue_scripts', 'tptn_heading_styles' );


