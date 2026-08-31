<?php
/**
 * Server-side renderer for the Popular Posts block.
 *
 * @package WebberZone\Top_Ten
 */

if ( ! isset( $attributes ) || ! is_array( $attributes ) ) {
	$attributes = array();
}

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The block renderer returns escaped markup.
echo \WebberZone\Top_Ten\Frontend\Blocks\Blocks::render_block_popular_posts( $attributes );
