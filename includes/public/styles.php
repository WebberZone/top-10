<?php
/**
 * Functions dealing with styles.
 *
 * @package   Top_Ten
 */

/**
 * Function to add CSS to header.
 *
 * @since 1.9
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

	$style_array = tptn_get_style();

	if ( ! empty( $style_array['name'] ) ) {
		$style     = $style_array['name'];
		$extra_css = $style_array['extra_css'];

		wp_register_style( "tptn-style-{$style}", plugins_url( "css/{$style}.min.css", TOP_TEN_PLUGIN_FILE ), array(), '1.0.1' );
		wp_enqueue_style( "tptn-style-{$style}" );
		wp_add_inline_style( "tptn-style-{$style}", $extra_css );
	}
}
add_action( 'wp_enqueue_scripts', 'tptn_heading_styles' );


/**
 * Get the current style for the popular posts.
 *
 * @since 3.0.0
 *
 * @return array Contains two elements:
 *               'name' holding style name and 'extra_css' to be added inline.
 */
function tptn_get_style() {

	$style        = array();
	$thumb_width  = tptn_get_option( 'thumb_width' );
	$thumb_height = tptn_get_option( 'thumb_height' );
	$tptn_style   = tptn_get_option( 'tptn_styles' );

	switch ( $tptn_style ) {
		case 'left_thumbs':
			$style['name']      = 'left-thumbs';
			$style['extra_css'] = "
			.tptn_related a {
			  width: {$thumb_width}px;
			  height: {$thumb_height}px;
			  text-decoration: none;
			}
			.tptn_related img {
			  max-width: {$thumb_width}px;
			  margin: auto;
			}
			.tptn_related .tptn_title {
			  width: 100%;
			}
			";
			break;

		default:
			$style['name']      = '';
			$style['extra_css'] = '';
			break;
	}

	return $style;
}
