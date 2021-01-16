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

