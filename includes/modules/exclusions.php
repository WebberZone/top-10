<?php
/**
 * Exclusion modules
 *
 * @package Top_Ten
 */

/**
 * Add additional post IDs to exclude. Filters `tptn_exclude_post_ids`.
 *
 * @since   2.2.0
 *
 * @param   int[] $exclude_post_ids   Original excluded post IDs.
 * @return  int[] Updated excluded post IDs array.
 */
function tptn_exclude_post_ids( $exclude_post_ids ) {
	global $wpdb;

	$exclude_post_ids = (array) $exclude_post_ids;

	// Find all posts that have `exclude_this_post` set.
	$tptn_post_metas = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = 'tptn_post_meta'", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	foreach ( $tptn_post_metas as $tptn_post_meta ) {
		$meta_value = maybe_unserialize( $tptn_post_meta['meta_value'] );

		if ( $meta_value['exclude_this_post'] ) {
			$exclude_post_ids[] = $tptn_post_meta['post_id'];
		}
	}

	// Exclude page_on_front and page_for_posts.
	if ( 'page' === get_option( 'show_on_front' ) && tptn_get_option( 'exclude_front' ) ) {
		$page_on_front  = get_option( 'page_on_front' );
		$page_for_posts = get_option( 'page_for_posts' );
		if ( $page_on_front > 0 ) {
			$exclude_post_ids[] = $page_on_front;
		}
		if ( $page_for_posts > 0 ) {
			$exclude_post_ids[] = $page_for_posts;
		}
	}

	return $exclude_post_ids;
}
add_filter( 'tptn_exclude_post_ids', 'tptn_exclude_post_ids' );


/**
 * Processes exclusion settings to return if the related posts should not be displayed on the current post.
 *
 * @since 3.1.0
 *
 * @param int|WP_Post|null $post Post ID or post object. Defaults to global $post. Default null.
 * @param array            $args Parameters in a query string format.
 * @return bool True if any exclusion setting is matched.
 */
function tptn_exclude_on( $post = null, $args = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	// If this post ID is in the DO NOT DISPLAY list.
	$exclude_on_post_ids_list = isset( $args['exclude_on_post_ids_list'] ) ? $args['exclude_on_post_ids_list'] : tptn_get_option( 'exclude_on_post_ids_list' );
	$exclude_on_post_ids_list = explode( ',', $exclude_on_post_ids_list );
	if ( in_array( $post->ID, $exclude_on_post_ids_list ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		return true;
	}

	// If this post type is in the DO NOT DISPLAY list.
	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	$exclude_on_post_types = isset( $args['exclude_on_post_types'] ) ? $args['exclude_on_post_types'] : tptn_get_option( 'exclude_on_post_types' );
	$exclude_on_post_types = $exclude_on_post_types ? explode( ',', $exclude_on_post_types ) : array();

	if ( in_array( $post->post_type, $exclude_on_post_types, true ) ) {
		return true;
	}

	// If this post's category is in the DO NOT DISPLAY list.
	$exclude_on_categories = isset( $args['exclude_on_categories'] ) ? $args['exclude_on_categories'] : tptn_get_option( 'exclude_on_categories' );
	$exclude_on_categories = explode( ',', $exclude_on_categories );
	$post_categories       = get_the_terms( $post->ID, 'category' );
	$categories            = array();
	if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
		$categories = wp_list_pluck( $post_categories, 'term_taxonomy_id' );
	}
	if ( ! empty( array_intersect( $exclude_on_categories, $categories ) ) ) {
		return true;
	}

	// If the DO NOT DISPLAY meta field is set.
	if ( ( isset( $args['is_shortcode'] ) && ! $args['is_shortcode'] ) &&
	( isset( $args['is_manual'] ) && ! $args['is_manual'] ) &&
	( isset( $args['is_block'] ) && ! $args['is_block'] ) ) {
		$tptn_post_meta = get_post_meta( $post->ID, 'tptn_post_meta', true );

		if ( isset( $tptn_post_meta['disable_here'] ) ) {
			$disable_here = $tptn_post_meta['disable_here'];
		} else {
			$disable_here = 0;
		}

		if ( $disable_here ) {
			return true;
		}
	}

	return false;
}
