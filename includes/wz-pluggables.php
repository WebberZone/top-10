<?php
/**
 * Pluggable functions.
 *
 * @package Top_Ten
 */

if ( ! function_exists( 'wz_more_recurrences' ) ) :

	/**
	 * Function to add weekly and fortnightly recurrences. Filters `cron_schedules`.
	 *
	 * @param   array $schedules Array of existing schedules.
	 * @return  array Filtered array with new schedules
	 */
	function wz_more_recurrences( $schedules ) {
		// Add a 'weekly' interval.
		$schedules['weekly']      = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Once Weekly', 'top-10' ),
		);
		$schedules['fortnightly'] = array(
			'interval' => 2 * WEEK_IN_SECONDS,
			'display'  => __( 'Once Fortnightly', 'top-10' ),
		);
		$schedules['monthly']     = array(
			'interval' => 30 * DAY_IN_SECONDS,
			'display'  => __( 'Once Monthly', 'top-10' ),
		);
		$schedules['quarterly']   = array(
			'interval' => 90 * DAY_IN_SECONDS,
			'display'  => __( 'Once quarterly', 'top-10' ),
		);
		return $schedules;
	}
	add_filter( 'cron_schedules', 'wz_more_recurrences' );

endif;


if ( ! function_exists( 'wz_switch_site_rewrite' ) ) :

	/**
	 * Refreshes $wp_rewrite when switching sites.
	 *
	 * Deal with permalinks and cat and tag base structures. Can slow down your site loading - handle with care!
	 * Use add_action( 'switch_blog', 'wz_switch_site_rewrite' ) when needed and remove_action after processing.
	 *
	 * @global object $wp_rewrite
	 */
	function wz_switch_site_rewrite() {
		global $wp_rewrite;

		if ( is_object( $wp_rewrite ) ) {

			$permalink_structure = get_option( 'permalink_structure' );

			if ( ! empty( $permalink_structure ) ) {
				$wp_rewrite->set_permalink_structure( $permalink_structure );
			}

			$category_base = get_option( 'category_base' );

			if ( ! empty( $category_base ) ) {
				$wp_rewrite->set_category_base( $category_base );
			}

			$tag_base = get_option( 'tag_base' );

			if ( ! empty( $tag_base ) ) {
				$wp_rewrite->set_tag_base( $tag_base );
			}
		}
	}

endif;

if ( ! function_exists( 'wz_get_all_parent_ids' ) ) :

	/**
	 * Get all parent term_taxonomy_ids for a given array of term_taxonomy_ids.
	 *
	 * @param  int|int[] $term_taxonomy_ids   Array of term_taxonomy_ids or a single term_taxonomy_id.
	 * @param  string    $levels              Use 'all' for ancestors, 'parent' for the immediate parent.
	 * @return int[] Array of all parent term_taxonomy_ids merged with $term_taxonomy_ids.
	 */
	function wz_get_all_parent_ids( $term_taxonomy_ids, $levels = 'parent' ) {
		$all_ids = array();
		$cache   = array();

		foreach ( (array) $term_taxonomy_ids as $term_taxonomy_id ) {
			if ( isset( $cache[ $term_taxonomy_id ] ) ) {
				$term = $cache[ $term_taxonomy_id ];
			} else {
				$term                       = WP_Term::get_instance( $term_taxonomy_id );
				$cache[ $term_taxonomy_id ] = $term;
			}

			if ( $term && ! is_wp_error( $term ) ) {
				$taxonomy = $term->taxonomy;

				if ( 'all' === $levels ) {
					$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

					foreach ( $ancestors as $ancestor_term_id ) {
						if ( isset( $cache[ $ancestor_term_id ] ) ) {
							$ancestor_term = $cache[ $ancestor_term_id ];
						} else {
							$ancestor_term              = WP_Term::get_instance( $ancestor_term_id );
							$cache[ $ancestor_term_id ] = $ancestor_term;
						}

						if ( $ancestor_term && ! is_wp_error( $ancestor_term ) ) {
							$all_ids[] = $ancestor_term->term_taxonomy_id;
						}
					}
				} else {
					$parent_id = $term->parent;
					if ( $parent_id > 0 ) {
						if ( isset( $cache[ $parent_id ] ) ) {
							$parent_term = $cache[ $parent_id ];
						} else {
							$parent_term         = WP_Term::get_instance( $parent_id );
							$cache[ $parent_id ] = $parent_term;
						}

						if ( $parent_term && ! is_wp_error( $parent_term ) ) {
							$all_ids[] = $parent_term->term_taxonomy_id;
						}
					}
				}
			}
		}

		// Perform array operations outside the loop for better performance.
		$result_ids = array_merge( $term_taxonomy_ids, $all_ids );

		return array_unique( $result_ids );
	}

endif;
