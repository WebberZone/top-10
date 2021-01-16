<?php
/**
 * Taxonomies control module
 *
 * @package   Top_Ten
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Filter WHERE clause of tptn query to exclude posts belonging to certain categories.
 *
 * @since 2.2.0
 *
 * @param   mixed $where WHERE clause.
 * @return  string  Filtered WHERE clause
 */
function tptn_exclude_categories_where( $where ) {
	global $wpdb, $tptn_settings;

	if ( '' === tptn_get_option( 'exclude_categories' ) ) {
		return $where;
	} else {

		$terms = tptn_get_option( 'exclude_categories' );

		$sql = $where;

		$sql .= " AND $wpdb->posts.ID NOT IN (
			SELECT object_id
			FROM $wpdb->term_relationships
			WHERE term_taxonomy_id IN ($terms)
        )";

		return $sql;
	}

}
add_filter( 'tptn_posts_where', 'tptn_exclude_categories_where' );

