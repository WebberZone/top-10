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
 * Filter JOIN clause of tptn query to add taxonomy tables.
 *
 * @since 1.0.0
 *
 * @param	mixed $join
 * @return	string	Filtered tptn JOIN clause
 */
function tptn_exclude_categories_join( $join ) {
	global $wpdb, $tptn_settings;

	if ( '' != $tptn_settings['exclude_categories'] ) {

		$sql = $join;
		$sql .= " INNER JOIN $wpdb->term_relationships AS excat_tr ON ($wpdb->posts.ID = excat_tr.object_id) ";
		$sql .= " INNER JOIN $wpdb->term_taxonomy AS excat_tt ON (excat_tr.term_taxonomy_id = excat_tt.term_taxonomy_id) ";

		return $sql;
	} else {
		return $join;
	}
}
add_filter( 'tptn_posts_join', 'tptn_exclude_categories_join' );

/**
 * Filter WHERE clause of tptn query to exclude posts belonging to certain categories.
 *
 * @since 1.0.0
 *
 * @param	mixed $where
 * @return	string	Filtered tptn WHERE clause
 */
function tptn_exclude_categories_where( $where ) {
	global $wpdb, $post, $tptn_settings;

	$term_ids = $category_ids = $tag_ids = $taxonomies = array();

	if ( '' == $tptn_settings['exclude_categories'] ) {
		return $where;
	} else {

		$terms = $tptn_settings['exclude_categories'];

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


