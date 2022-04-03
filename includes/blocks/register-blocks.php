<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package Top_Ten
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @since 3.1.0
 */
function tptn_register_blocks() {
	// Register Popular Posts block.
	register_block_type_from_metadata(
		TOP_TEN_PLUGIN_DIR . 'includes/blocks/popular-posts/',
		array(
			'render_callback' => 'render_tptn_block',
		)
	);
}
add_action( 'init', 'tptn_register_blocks' );


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
 * Enqueue scripts and styles for the block editor.
 *
 * @since 3.1.0
 */
function tptn_enqueue_block_editor_assets() {

	$style_array = tptn_get_style();
	$file_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if ( ! empty( $style_array ) ) {
		$style     = $style_array['name'];
		$extra_css = $style_array['extra_css'];

		wp_enqueue_style(
			'popular-posts-block-editor',
			plugins_url( "css/{$style}{$file_prefix}.css", TOP_TEN_PLUGIN_FILE ),
			array( 'wp-edit-blocks' ),
			filemtime( TOP_TEN_PLUGIN_DIR . "css/{$style}{$file_prefix}.css" )
		);
		wp_add_inline_style( 'popular-posts-block-editor', $extra_css );
	}
}
add_action( 'enqueue_block_editor_assets', 'tptn_enqueue_block_editor_assets' );
