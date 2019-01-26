<?php
/**
 * Exclusion modules
 *
 * @package Top_Ten
 */

/**
 * Function to filter exclude post IDs.
 *
 * @since   2.2.0
 *
 * @param   array $exclude_post_ids   Original excluded post IDs.
 * @return  array   Updated excluded post ID
 */
function tptn_exclude_post_ids( $exclude_post_ids ) {
	global $wpdb;

	$exclude_post_ids = (array) $exclude_post_ids;

	$tptn_post_metas = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = 'tptn_post_meta'", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	foreach ( $tptn_post_metas as $tptn_post_meta ) {
		$meta_value = maybe_unserialize( $tptn_post_meta['meta_value'] );

		if ( $meta_value['exclude_this_post'] ) {
			$exclude_post_ids[] = $tptn_post_meta['post_id'];
		}
	}
	return $exclude_post_ids;

}
add_filter( 'tptn_exclude_post_ids', 'tptn_exclude_post_ids' );

