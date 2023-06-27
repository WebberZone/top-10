<?php
/**
 * Functions dealing with styles.
 *
 * @package   Top_Ten
 */

namespace WebberZone\Top_Ten\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Styles_Handler {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'wp_head', array( __CLASS__, 'header' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'heading_styles' ) );
	}

	/**
	 * Function to add CSS to header.
	 *
	 * @since 1.9
	 */
	public static function header() {

		$custom_css = stripslashes( tptn_get_option( 'custom_css' ) );

		// Add CSS to header.
		if ( $custom_css ) {
			echo '<style type="text/css">' . $custom_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Enqueue styles.
	 */
	public static function heading_styles() {

		$style_array = self::get_style();

		if ( ! empty( $style_array['name'] ) ) {
			$style     = $style_array['name'];
			$extra_css = $style_array['extra_css'];

			wp_register_style(
				"tptn-style-{$style}",
				plugins_url( "css/{$style}.min.css", TOP_TEN_PLUGIN_FILE ),
				array(),
				TOP_TEN_VERSION
			);
			wp_enqueue_style( "tptn-style-{$style}" );
			wp_add_inline_style( "tptn-style-{$style}", $extra_css );
		}
	}

	/**
	 * Get the current style for the popular posts.
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Added parameter $style
	 *
	 * @param string $style Style parameter.
	 *
	 * @return array Contains two elements:
	 *               'name' holding style name and 'extra_css' to be added inline.
	 */
	public static function get_style( $style = '' ) {

		$style_array  = array();
		$thumb_width  = tptn_get_option( 'thumb_width' );
		$thumb_height = tptn_get_option( 'thumb_height' );
		$tptn_style   = ! empty( $style ) ? $style : tptn_get_option( 'tptn_styles' );

		switch ( $tptn_style ) {
			case 'left_thumbs':
				$style_array['name']      = 'left-thumbs';
				$style_array['extra_css'] = "
			.tptn-left-thumbs a {
				width: {$thumb_width}px;
				height: {$thumb_height}px;
				text-decoration: none;
			}
			.tptn-left-thumbs img {
				width: {$thumb_width}px;
				max-height: {$thumb_height}px;
				margin: auto;
			}
			.tptn-left-thumbs .tptn_title {
				width: 100%;
			}
			";
				break;

			case 'text_only':
				$style_array['name']      = 'text-only';
				$style_array['extra_css'] = '';
				break;

			default:
				$style_array['name']      = '';
				$style_array['extra_css'] = '';
				break;
		}

		/**
		 * Filter the style array which contains the name and extra_css.
		 *
		 * @since 3.2.0
		 *
		 * @param array  $style_array  Style array containing name and extra_css.
		 * @param string $tptn_style    Style name.
		 * @param int    $thumb_width  Thumbnail width.
		 * @param int    $thumb_height Thumbnail height.
		 */
		return apply_filters( 'tptn_get_style', $style_array, $tptn_style, $thumb_width, $thumb_height );
	}
}
