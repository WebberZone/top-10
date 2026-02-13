<?php
/**
 * Styles Handler class.
 *
 * @package WebberZone\Top_Ten\Frontend
 */

namespace WebberZone\Top_Ten\Frontend;

use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Styles Handler Class.
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
		Hook_Registry::add_action( 'wp_head', array( $this, 'header' ) );
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
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
	public static function register_styles() {

		$style_array = self::get_style();

		if ( ! empty( $style_array['name'] ) ) {
			$style     = $style_array['name'];
			$extra_css = $style_array['extra_css'];
			$is_rtl    = is_rtl();

			wp_register_style(
				"tptn-style-{$style}",
				plugins_url( self::get_stylesheet_path( $style, $is_rtl ), TOP_TEN_PLUGIN_FILE ),
				array(),
				TOP_TEN_VERSION
			);
			wp_enqueue_style( "tptn-style-{$style}" );

			if ( ! empty( $extra_css ) ) {
				wp_add_inline_style( "tptn-style-{$style}", $extra_css );
			}
		}
	}

	/**
	 * Get stylesheet path accounting for minified and pro files.
	 *
	 * @since 4.2.0
	 *
	 * @param string $style  Style name.
	 * @param bool   $is_rtl Whether RTL stylesheet should be loaded.
	 *
	 * @return string
	 */
	public static function get_stylesheet_path( $style, $is_rtl = false ) {

		$pro = '';
		if ( false !== strpos( $style, '-pro' ) ) {
			$pro = 'pro/';
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$rtl    = $is_rtl ? '-rtl' : '';

		return "css/{$pro}{$style}{$rtl}{$suffix}.css";
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
			.tptn-left-thumbs {
				--tptn-thumb-width: {$thumb_width}px;
				--tptn-thumb-height: {$thumb_height}px;
			}
			.tptn-left-thumbs img.tptn_thumb {
				width: min( var(--tptn-thumb-width), 100% );
				max-height: var(--tptn-thumb-height);
				height: auto;
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
		 * @param string $tptn_style   Style name.
		 * @param int    $thumb_width  Thumbnail width.
		 * @param int    $thumb_height Thumbnail height.
		 */
		return apply_filters( 'tptn_get_style', $style_array, $tptn_style, $thumb_width, $thumb_height );
	}
}
