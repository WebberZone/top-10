<?php
/**
 * Functions dealing with styles.
 *
 * @package   Top_Ten
 */


/**
 * Function to add CSS to header.
 *
 * @since	1.9
 */
function tptn_header() {
	global $tptn_settings;

	$tptn_custom_CSS = stripslashes( $tptn_settings['custom_CSS'] );

	// Add CSS to header
	if ( '' != $tptn_custom_CSS ) {
		echo '<style type="text/css">' . $tptn_custom_CSS . '</style>';
	}
}
add_action( 'wp_head', 'tptn_header' );


/**
 * Enqueue styles.
 */
function tptn_heading_styles() {
	global $tptn_settings;

	if ( 'left_thumbs' == $tptn_settings['tptn_styles'] ) {
		wp_register_style( 'tptn-style-left-thumbs', plugins_url( 'css/default-style.css', TOP_TEN_PLUGIN_FILE ) );
		wp_enqueue_style( 'tptn-style-left-thumbs' );

		$custom_css = "
img.tptn_thumb {
  width: {$tptn_settings['thumb_width']}px !important;
  height: {$tptn_settings['thumb_height']}px !important;
}
                ";

		wp_add_inline_style( 'tptn-style-left-thumbs', $custom_css );

	}

}
add_action( 'wp_enqueue_scripts', 'tptn_heading_styles' );


