<?php
/**
 * Server-side renderer for the Post Count block.
 *
 * @package WebberZone\Top_Ten
 */

if ( ! isset( $attributes ) || ! is_array( $attributes ) ) {
	$attributes = array();
}

if ( ! isset( $content ) ) {
	$content = '';
}

if ( ! isset( $block ) ) {
	$block = null;
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The block renderer returns escaped markup.
echo \WebberZone\Top_Ten\Frontend\Blocks\Blocks::render_block_post_count( $attributes, $content, $block );
