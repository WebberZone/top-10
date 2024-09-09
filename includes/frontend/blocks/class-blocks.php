<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Frontend\Blocks;

use WebberZone\Top_Ten\Admin\Settings\Settings;
use WebberZone\Top_Ten\Counter;
use WebberZone\Top_Ten\Frontend\Styles_Handler;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Widget to display the overall count.
 *
 * @since 3.3.0
 */
class Blocks {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'block_editor_rest_api_preload_paths', array( $this, 'add_custom_preload_paths' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @since 3.1.0
	 */
	public function register_blocks() {
		// Define an array of blocks with their paths and optional render callbacks.
		$blocks = array(
			'popular-posts' => array(
				'path'            => __DIR__ . '/build/popular-posts/',
				'render_callback' => array( $this, 'render_block_popular_posts' ),
			),
			'post-count'    => array(
				'path'            => __DIR__ . '/build/post-count/',
				'render_callback' => array( $this, 'render_block_post_count' ),
			),
		);

		/**
		 * Filters the blocks registered by the plugin.
		 *
		 * @since 4.0.0
		 *
		 * @param array $blocks Array of blocks registered by the plugin.
		 */
		$blocks = apply_filters( 'tptn_register_blocks', $blocks );

		// Loop through each block and register it.
		foreach ( $blocks as $block_name => $block_data ) {
			$args = array();

			// If a render callback is provided, add it to the args.
			if ( isset( $block_data['render_callback'] ) ) {
				$args['render_callback'] = $block_data['render_callback'];
			}

			register_block_type( $block_data['path'], $args );
		}
	}

	/**
	 * Renders the `top-10/popular-posts` block on server.
	 *
	 * @since 3.0.0
	 * @param array $attributes The block attributes.
	 *
	 * @return string Returns the post content with popular posts added.
	 */
	public static function render_block_popular_posts( $attributes ) {

		$attributes['extra_class'] = isset( $attributes['className'] ) ? esc_attr( $attributes['className'] ) : '';

		$defaults = array_merge(
			\tptn_settings_defaults(),
			\tptn_get_settings(),
			array(
				'is_block' => 1,
			)
		);

		$arguments = wp_parse_args( $attributes, $defaults );

		if ( isset( $attributes['other_attributes'] ) ) {
			$arguments = wp_parse_args( $attributes['other_attributes'], $arguments );
		}

		/**
		 * Filters arguments passed to get_tptn for the block.
		 *
		 * @since 3.0.0
		 *
		 * @param array $arguments  Top 10 block options array.
		 * @param array $attributes Block attributes array.
		 */
		$arguments = apply_filters( 'tptn_block_options', $arguments, $attributes );

		// Enqueue the stylesheet for the selected style for this block.
		$style_array = Styles_Handler::get_style( $arguments['tptn_styles'] );

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

		return \WebberZone\Top_Ten\Frontend\Display::pop_posts( $arguments );
	}

	/**
	 * Renders the `core/post-title` block on the server.
	 *
	 * @since 4.0.0
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content    Block default content.
	 * @param \WP_Block $block      Block instance.
	 *
	 * @return string Returns the post count.
	 */
	public static function render_block_post_count( $attributes, $content, $block ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}
		$output = '';
		$args   = array();

		$post_id            = absint( $block->context['postId'] );
		$counter            = isset( $attributes['counter'] ) ? $attributes['counter'] : 'total';
		$blog_id            = isset( $attributes['blogId'] ) ? $attributes['blogId'] : get_current_blog_id();
		$from_date          = isset( $attributes['fromDate'] ) ? $attributes['fromDate'] : '';
		$to_date            = isset( $attributes['toDate'] ) ? $attributes['toDate'] : '';
		$text_before        = isset( $attributes['textBefore'] ) ? $attributes['textBefore'] : '';
		$text_after         = isset( $attributes['textAfter'] ) ? $attributes['textAfter'] : '';
		$text_advanced      = isset( $attributes['textAdvanced'] ) ? $attributes['textAdvanced'] : '';
		$advanced_mode      = isset( $attributes['advancedMode'] ) ? $attributes['advancedMode'] : false;
		$svg_code           = isset( $attributes['svgCode'] ) ? $attributes['svgCode'] : '';
		$svg_icon_size      = isset( $attributes['svgIconSize'] ) ? $attributes['svgIconSize'] : '1';
		$svg_icon_size_unit = isset( $attributes['svgIconSizeUnit'] ) ? $attributes['svgIconSizeUnit'] : 'em';
		$svg_padding_values = isset( $attributes['svgPaddingValues'] ) ? $attributes['svgPaddingValues'] : array( 0, 0, 0, 0 );
		$svg_padding_units  = isset( $attributes['svgPaddingUnits'] ) ? $attributes['svgPaddingUnits'] : array( 'px', 'px', 'px', 'px' );
		$svg_icon_location  = isset( $attributes['svgIconLocation'] ) ? $attributes['svgIconLocation'] : 'before';

		$args['format_number'] = isset( $attributes['numberFormat'] ) ? $attributes['numberFormat'] : false;

		if ( ! empty( $from_date ) ) {
			$args['from_date'] = $from_date;
		}
		if ( ! empty( $to_date ) ) {
			$args['to_date'] = $to_date;
		}

		$counter = Counter::get_post_count_only( $post_id, $counter, $blog_id, $args );

		$classes   = array();
		$classes[] = 'wp-block-tptn-post-count';
		$classes[] = 'tptn-post-count';

		if ( isset( $attributes['textAlign'] ) ) {
			$classes[] = 'has-text-align-' . $attributes['textAlign'];
		}
		if ( isset( $attributes['style']['elements']['link']['color']['text'] ) ) {
			$classes[] = 'has-link-color';
		}

		if ( ! $advanced_mode ) {
			$output = sprintf(
				'%1$s%2$s%3$s',
				wp_kses_post( (string) $text_before ),
				esc_html( (string) $counter ),
				wp_kses_post( (string) $text_after )
			);
		} elseif ( ! empty( $text_advanced ) ) {
			$classes[] = 'tptn-advanced-mode';
			$output    = $text_advanced;

			foreach ( array( 'total', 'daily', 'overall' ) as $type ) {
				if ( false !== strpos( $text_advanced, "%{$type}count%" ) ) {
					$count = Counter::get_post_count_only( $post_id, $type, $blog_id, $args );

					$output = str_replace(
						"%{$type}count%",
						(string) $count,
						$output
					);
				}
			}
		}

		$output = sprintf(
			'<span class="tptn-post-count-text">%s</span>',
			$output
		);

		// Add the icon to the output.
		$icon = '';
		if ( ! empty( $svg_code ) ) {
			$padding_style = sprintf(
				'padding:%s%s %s%s %s%s %s%s;',
				esc_attr( $svg_padding_values[0] ),
				esc_attr( $svg_padding_units[0] ),
				esc_attr( $svg_padding_values[1] ),
				esc_attr( $svg_padding_units[1] ),
				esc_attr( $svg_padding_values[2] ),
				esc_attr( $svg_padding_units[2] ),
				esc_attr( $svg_padding_values[3] ),
				esc_attr( $svg_padding_units[3] )
			);

			$svg_style = sprintf(
				'width: %1$s%2$s; height: %1$s%2$s; %3$s',
				esc_attr( $svg_icon_size ),
				esc_attr( $svg_icon_size_unit ),
				$padding_style
			);

			$svg_style = esc_attr( $svg_style );

			if ( preg_match( '/<svg[^>]*style="([^"]*)"/i', $svg_code, $matches ) ) {
				$existing_style = $matches[1];
				$new_style      = $svg_style . $existing_style;

				$svg_code_with_style = preg_replace( '/(<svg[^>]*style=")[^"]*(")/i', '${1}' . $new_style . '${2}', $svg_code, 1 );
			} else {
				$svg_code_with_style = preg_replace( '/<svg /', '<svg style="' . $svg_style . '" ', $svg_code, 1 );
			}

			$icon = sprintf(
				'<span class="tptn-post-count-icon">%1$s</span>',
				wp_kses( $svg_code_with_style, self::get_allowed_svg_tags() )
			);
			$icon = '<span class="tptn-post-count-icon">' . $svg_code_with_style . '</span>';
		}
		if ( ! empty( $icon ) ) {
			if ( 'before' === $svg_icon_location ) {
				$output = $icon . $output;
			} else {
				$output = $output . $icon;
			}
		}

		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ) ) );

		return sprintf(
			'<span %1$s>%2$s</span>',
			$wrapper_attributes,
			wp_kses( $output, self::get_allowed_svg_tags() )
		);
	}

	/**
	 * Get allowed SVG tags for wp_kses.
	 *
	 * @since 4.0.0
	 *
	 * @return array Allowed SVG tags and attributes.
	 */
	private static function get_allowed_svg_tags() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

		$svg_tags = array(
			'svg'           => array(
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'fill'            => true,
				'id'              => true,
				'data-name'       => true,
				'viewbox'         => true,
				'style'           => true,
			),
			'g'             => array(
				'fill'            => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'id'              => true,
			),
			'defs'          => array(),
			'style'         => array(
				'type' => true,
			),
			'line'          => array(
				'class'             => true,
				'x1'                => true,
				'y1'                => true,
				'x2'                => true,
				'y2'                => true,
				'fill'              => true,
				'stroke'            => true,
				'stroke-width'      => true,
				'stroke-miterlimit' => true,
			),
			'path'          => array(
				'class'           => true,
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'stroke-width'    => true,
			),
			'polygon'       => array(
				'class'             => true,
				'points'            => true,
				'fill'              => true,
				'stroke'            => true,
				'stroke-width'      => true,
				'stroke-linecap'    => true,
				'stroke-linejoin'   => true,
				'stroke-miterlimit' => true,
			),
			'polyline'      => array(
				'class'             => true,
				'points'            => true,
				'fill'              => true,
				'stroke'            => true,
				'stroke-width'      => true,
				'stroke-linecap'    => true,
				'stroke-linejoin'   => true,
				'stroke-miterlimit' => true,
			),
			'circle'        => array(
				'class'        => true,
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'rect'          => array(
				'class'        => true,
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'rx'           => true,
				'ry'           => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'ellipse'       => array(
				'class'        => true,
				'cx'           => true,
				'cy'           => true,
				'rx'           => true,
				'ry'           => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'use'           => array(
				'class'      => true,
				'xlink:href' => true,
			),
			'text'          => array(
				'class'       => true,
				'x'           => true,
				'y'           => true,
				'dy'          => true,
				'text-anchor' => true,
				'fill'        => true,
			),
			'tspan'         => array(
				'class'       => true,
				'x'           => true,
				'y'           => true,
				'dy'          => true,
				'text-anchor' => true,
				'fill'        => true,
			),
			'symbol'        => array(
				'id'                  => true,
				'viewBox'             => true,
				'preserveAspectRatio' => true,
			),
			'clipPath'      => array(
				'id' => true,
			),
			'mask'          => array(
				'id' => true,
			),
			'image'         => array(
				'x'          => true,
				'y'          => true,
				'width'      => true,
				'height'     => true,
				'xlink:href' => true,
			),
			'foreignObject' => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
			),
		);

		return array_merge( $allowed_tags, $svg_tags );
	}

	/**
	 * Enqueue scripts and styles for the block editor.
	 *
	 * @since 3.1.0
	 */
	public static function enqueue_block_editor_assets() {

		$styles = Settings::get_styles();

		foreach ( $styles as $style ) {

			$style_array = Styles_Handler::get_style( $style['id'] );
			$file_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			if ( ! empty( $style_array['name'] ) ) {
				$style     = $style_array['name'];
				$extra_css = $style_array['extra_css'];

				$pro = '';
				if ( false !== strpos( $style, '-pro' ) ) {
					$pro = 'pro/';
				}

				wp_enqueue_style(
					"popular-posts-block-editor-{$style}",
					plugins_url( "css/{$pro}{$style}{$file_prefix}.css", TOP_TEN_PLUGIN_FILE ),
					array( 'wp-edit-blocks' ),
					TOP_TEN_VERSION
				);
				wp_add_inline_style( "popular-posts-block-editor-{$style}", $extra_css );
			}
		}
	}

	/**
	 * Add custom preload paths for the REST API.
	 *
	 * @since 4.0.0
	 *
	 * @param array $preload_paths Existing preload paths.
	 * @return array Modified preload paths.
	 */
	public static function add_custom_preload_paths( $preload_paths ) {
		$preload_paths[] = '/wp/v2/top-10/v1/counter';

		return $preload_paths;
	}
}
