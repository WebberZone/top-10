<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package   Top_Ten
 */

/**
 * Renders the `top-10/popular-posts` block on server.
 *
 * @since 3.0.0
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with popular posts added.
 */
function render_tptn_block( $attributes ) {

	$attributes['extra_class'] = $attributes['className'];

	$arguments = array_merge(
		$attributes,
		array(
			'is_block' => 1,
		)
	);

	$arguments = wp_parse_args( $attributes['other_attributes'], $arguments );

	/**
	 * Filters arguments passed to get_tptn for the block.
	 *
	 * @since 3.0.0
	 *
	 * @param array $arguments  Top 10 block options array.
	 * @param array $attributes Block attributes array.
	 */
	$arguments = apply_filters( 'tptn_block_options', $arguments, $attributes );

	return tptn_pop_posts( $arguments );
}

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @since 3.0.0
 */
function tptn_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dir = dirname( __FILE__ );

	$index_js = 'popular-posts/index.min.js';
	wp_register_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		'popular-posts-block-editor',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-block-editor',
			'wp-editor',
		),
		filemtime( "$dir/$index_js" )
	);

	$style_array = tptn_get_style();

	if ( ! empty( $style_array ) ) {
		$style     = $style_array['name'];
		$extra_css = $style_array['extra_css'];

		wp_register_style(
			'popular-posts-block-editor',
			plugins_url( "css/{$style}.min.css", TOP_TEN_PLUGIN_FILE ),
			array( 'wp-edit-blocks' ),
			'1.0'
		);
		wp_add_inline_style( 'popular-posts-block-editor', $extra_css );
	}

	$args = array(
		'editor_script'   => 'popular-posts-block-editor',
		'render_callback' => 'render_tptn_block',
		'attributes'      => array(
			'className'        => array(
				'type'    => 'string',
				'default' => '',
			),
			'heading'          => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'daily'            => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'daily_range'      => array(
				'type'    => 'number',
				'default' => 1,
			),
			'hour_range'       => array(
				'type'    => 'number',
				'default' => 0,
			),
			'limit'            => array(
				'type'    => 'number',
				'default' => 6,
			),
			'offset'           => array(
				'type'    => 'number',
				'default' => 0,
			),
			'show_excerpt'     => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'show_author'      => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'show_date'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'disp_list_count'  => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'post_thumb_op'    => array(
				'type'    => 'string',
				'default' => 'inline',
			),
			'other_attributes' => array(
				'type'    => 'string',
				'default' => '',
			),
		),
	);
	if ( ! empty( $style ) ) {
		$args['editor_style'] = 'popular-posts-block-editor';
	}

	register_block_type(
		'top-10/popular-posts',
		$args
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( 'popular-posts-block-editor', 'top-10' );
	}
}
add_action( 'init', 'tptn_block_init' );
