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
 * @since 2.2.0
 *
 * @param   mixed $join Join clause.
 * @return  string  Filtered JOIN clause
 */
function tptn_exclude_categories_join( $join ) {
	global $wpdb, $tptn_settings;

	if ( '' !== tptn_get_option( 'exclude_categories' ) ) {

		$sql  = $join;
		$sql .= " LEFT JOIN $wpdb->term_relationships AS excat_tr ON ($wpdb->posts.ID = excat_tr.object_id) ";
		$sql .= " LEFT JOIN $wpdb->term_taxonomy AS excat_tt ON (excat_tr.term_taxonomy_id = excat_tt.term_taxonomy_id) ";

		return $sql;
	} else {
		return $join;
	}
}
add_filter( 'tptn_posts_join', 'tptn_exclude_categories_join' );


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


/**
 * Filter GROUP BY clause of tptn query to exclude posts belonging to certain categories.
 *
 * @since 2.3.0
 *
 * @param   mixed $groupby GROUP BY clause.
 * @return  string  Filtered GROUP BY clause
 */
function tptn_exclude_categories_groupby( $groupby ) {
	global $tptn_settings;

	if ( '' !== tptn_get_option( 'exclude_categories' ) && '' !== $groupby ) {

		$sql  = $groupby;
		$sql .= ', excat_tt.term_taxonomy_id ';

		return $sql;
	} else {
		return $groupby;
	}
}
add_filter( 'tptn_posts_groupby', 'tptn_exclude_categories_groupby' );


