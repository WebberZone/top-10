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
