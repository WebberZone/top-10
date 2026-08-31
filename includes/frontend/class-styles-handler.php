<?php
/**
 * Styles Handler class.
 *
 * @package WebberZone\Top_Ten\Frontend
 */

namespace WebberZone\Top_Ten\Frontend;

use WebberZone\Top_Ten\Admin\Settings;
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
		Hook_Registry::add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_all_styles' ) );
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
	 * Enqueue every registered style. Elementor's preview iframe can switch widget styles without
	 * a full reload, so all style choices need to be available there.
	 *
	 * @since 4.5.0
	 */
	public static function enqueue_all_styles() {
		foreach ( wp_list_pluck( Settings::get_styles(), 'id' ) as $style_id ) {
			self::enqueue_style( $style_id );
		}
	}

	/**
	 * Register and enqueue one style by ID.
	 *
	 * @since 4.5.0
	 *
	 * @param string $style_id Style ID.
	 */
	public static function enqueue_style( $style_id ) {
		$style_array = self::get_style( $style_id );

		if ( empty( $style_array['name'] ) ) {
			return;
		}

		$style     = $style_array['name'];
		$extra_css = $style_array['extra_css'];
		$is_rtl    = is_rtl();
		$handle    = "tptn-style-{$style}";

		wp_register_style(
			$handle,
			plugins_url( self::get_stylesheet_path( $style, $is_rtl ), TOP_TEN_PLUGIN_FILE ),
			array(),
			TOP_TEN_VERSION
		);
		wp_enqueue_style( $handle );

		if ( ! empty( $extra_css ) ) {
			wp_add_inline_style( $handle, $extra_css );
		}
	}

	/**
	 * Enqueue and print one style immediately when rendering happens after wp_head.
	 *
	 * @since 4.5.0
	 *
	 * @param string $style_id Style ID.
	 */
	public static function enqueue_style_now( $style_id ) {
		self::enqueue_style( $style_id );

		if ( ! did_action( 'wp_head' ) ) {
			return;
		}

		$style_array = self::get_style( $style_id );

		if ( empty( $style_array['name'] ) ) {
			return;
		}

		wp_print_styles( array( "tptn-style-{$style_array['name']}" ) );
	}

	/**
	 * Resolve a style ID using the same thumbnail-placement precedence everywhere.
	 *
	 * @since 4.5.0
	 *
	 * @param string|null $post_thumb_op Thumbnail placement override.
	 * @param string|null $style_id      Style override.
	 * @return string Resolved style ID.
	 */
	public static function resolve_style_id( $post_thumb_op = null, $style_id = null ) {
		if ( null === $post_thumb_op || '' === (string) $post_thumb_op ) {
			$post_thumb_op = tptn_get_option( 'post_thumb_op', 'text_only' );
		}

		if ( 'text_only' === $post_thumb_op ) {
			return 'text_only';
		}

		if ( null === $style_id || '' === (string) $style_id ) {
			$style_id = tptn_get_option( 'tptn_styles', 'no_style' );
		}

		return (string) $style_id;
	}

	/**
	 * Enqueue styles for the site-wide setting and any builder/shortcode instance on the queried
	 * post.
	 *
	 * @since 4.5.0
	 */
	public static function register_styles() {
		foreach ( self::get_style_ids_to_load() as $style_id ) {
			self::enqueue_style( $style_id );
		}
	}

	/**
	 * Get the style IDs required for the current request.
	 *
	 * Builder editors load every registered style because an element can change its style live.
	 * Normal requests scan shortcode, Elementor, and Bricks data on the queried post; elements in
	 * theme templates are covered by Builder_Atts::ensure_style_enqueued() at render time.
	 *
	 * @since 4.5.0
	 *
	 * @return string[] Style IDs.
	 */
	protected static function get_style_ids_to_load() {
		if ( function_exists( 'vc_is_inline' ) && vc_is_inline() ) {
			return wp_list_pluck( Settings::get_styles(), 'id' );
		}

		if ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) {
			return wp_list_pluck( Settings::get_styles(), 'id' );
		}

		$style_ids = array( (string) tptn_get_option( 'tptn_styles', 'no_style' ) );
		$post      = get_post();

		if ( ! $post ) {
			return array_unique( $style_ids );
		}

		if ( preg_match_all( '/\[(?:tptn_list|tptn_popular_posts)\b[^\]]*\]/', $post->post_content, $shortcodes ) ) {
			foreach ( $shortcodes[0] as $shortcode ) {
				if ( preg_match( '/\bpost_thumb_op=["\']text_only["\']/', $shortcode ) ) {
					$style_ids[] = 'text_only';
				} elseif ( preg_match( '/\btptn_styles=["\']([a-z_]+)["\']/', $shortcode, $match ) ) {
					$style_ids[] = $match[1];
				}
			}
		}

		array_push( $style_ids, ...self::get_elementor_style_ids( $post->ID ) );
		array_push( $style_ids, ...self::get_bricks_style_ids( $post->ID ) );

		return array_unique( $style_ids );
	}

	/**
	 * Extract styles from Top 10 Elementor widgets saved in `_elementor_data`.
	 *
	 * @since 4.5.0
	 *
	 * @param int $post_id Post ID.
	 * @return string[] Style IDs.
	 */
	protected static function get_elementor_style_ids( $post_id ) {
		$data = get_post_meta( $post_id, '_elementor_data', true );

		if ( empty( $data ) ) {
			return array();
		}

		$elements = is_array( $data ) ? $data : json_decode( (string) $data, true );

		if ( ! is_array( $elements ) ) {
			return array();
		}

		$style_ids = array();
		self::walk_elementor_elements( $elements, $style_ids );

		return array_map( 'strval', $style_ids );
	}

	/**
	 * Recursively collect style IDs from Top 10 Elementor widgets.
	 *
	 * @since 4.5.0
	 *
	 * @param array $elements Element tree.
	 * @param array $style_ids Collected style IDs, by reference.
	 */
	protected static function walk_elementor_elements( array $elements, array &$style_ids ) {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['widgetType'] ) && 'tptn_popular_posts' === $element['widgetType'] ) {
				$settings    = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
				$style_ids[] = self::resolve_style_id(
					$settings['post_thumb_op'] ?? null,
					$settings['tptn_styles'] ?? null
				);
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				self::walk_elementor_elements( $element['elements'], $style_ids );
			}
		}
	}

	/**
	 * Extract styles from Top 10 Bricks elements saved on a post.
	 *
	 * @since 4.5.0
	 *
	 * @param int $post_id Post ID.
	 * @return string[] Style IDs.
	 */
	protected static function get_bricks_style_ids( $post_id ) {
		$meta_keys = array();

		foreach ( array( 'BRICKS_DB_PAGE_CONTENT', 'BRICKS_DB_PAGE_HEADER', 'BRICKS_DB_PAGE_FOOTER' ) as $constant ) {
			if ( defined( $constant ) ) {
				$meta_keys[] = constant( $constant );
			}
		}

		$style_ids = array();

		foreach ( $meta_keys as $meta_key ) {
			$elements = get_post_meta( $post_id, $meta_key, true );

			if ( ! is_array( $elements ) ) {
				continue;
			}

			foreach ( $elements as $element ) {
				if ( ! is_array( $element ) || 'tptn-popular-posts' !== ( $element['name'] ?? '' ) ) {
					continue;
				}

				$settings    = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
				$style_ids[] = self::resolve_style_id(
					$settings['post_thumb_op'] ?? null,
					$settings['tptn_styles'] ?? null
				);
			}
		}

		return $style_ids;
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

		$base_path = 'includes/frontend/css/';
		if ( false !== strpos( $style, '-pro' ) ) {
			$base_path = 'includes/pro/frontend/css/';
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$rtl    = $is_rtl ? '-rtl' : '';

		return "{$base_path}{$style}{$rtl}{$suffix}.css";
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
		$thumb_width  = max( 1, (int) tptn_get_option( 'thumb_width', 150 ) );
		$thumb_height = max( 1, (int) tptn_get_option( 'thumb_height', 150 ) );
		$aspect_ratio = $thumb_width / $thumb_height;
		$tptn_style   = ! empty( $style ) ? $style : tptn_get_option( 'tptn_styles' );

		switch ( $tptn_style ) {
			case 'left_thumbs':
				$style_array['name']      = 'left-thumbs';
				$style_array['extra_css'] = "
			.tptn-left-thumbs {
				--tptn-thumb-width: {$thumb_width}px;
				--tptn-thumb-height: {$thumb_height}px;
				--tptn-thumb-aspect-ratio: {$aspect_ratio};
			}
			.tptn-left-thumbs img.tptn_thumb {
				width: min( var(--tptn-thumb-width), 100% );
				height: var(--tptn-thumb-height);
				max-height: var(--tptn-thumb-height);
				aspect-ratio: var(--tptn-thumb-aspect-ratio);
				object-fit: cover;
			}
			.tptn-left-thumbs li > a.tptn_link {
				display: block;
				flex: 0 0 auto;
				align-self: flex-start;
				padding: var(--tptn-thumb-frame-padding, 0.25rem );
				border-width: var(--tptn-thumb-frame-border-width, 1px );
				border-style: solid;
				border-color: var(--tptn-thumb-border, #ccc );
				border-radius: var(--tptn-border-radius, 8px );
				box-shadow: var(--tptn-shadow, 0 2px 4px rgba(0, 0, 0, 0.15) );
				transition: transform var(--tptn-transition, 0.2s ease), box-shadow var(--tptn-transition, 0.2s ease);
				will-change: transform;
			}
			.tptn-left-thumbs ul li:hover > a.tptn_link {
				transform: scale(1.03);
				box-shadow: var(--tptn-shadow-hover, 0 4px 8px rgba(0, 0, 0, 0.2) );
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
