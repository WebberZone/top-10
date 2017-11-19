<?php
/**
 * Functions controlling the counter/tracker
 *
 * @package Top_Ten
 */

/**
 * Function to add the viewed count to the post content. Filters `the_content`.
 *
 * @since   1.0
 * @param   string $content Post content.
 * @return  string  Filtered post content
 */
function tptn_pc_content( $content ) {
	global $post;

	$exclude_on_post_ids = explode( ',', tptn_get_option( 'exclude_on_post_ids' ) );
	$add_to              = tptn_get_option( 'add_to' );

	if ( isset( $post ) ) {
		if ( in_array( $post->ID, $exclude_on_post_ids ) ) {
			return $content;    // Exit without adding related posts.
		}
	}

	if ( ( is_single() ) && ! empty( $add_to['single'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_page() ) && ! empty( $add_to['page'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_home() ) && ! empty( $add_to['home'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_category() ) && ! empty( $add_to['category_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_tag() ) && ! empty( $add_to['tag_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ! empty( $add_to['other_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'tptn_pc_content' );


/**
 * Filter to display the post count when viewing feeds.
 *
 * @since   1.9.8
 *
 * @param   string $content    Post content.
 * @return  string  Filtered post content
 */
function tptn_rss_filter( $content ) {
	global $post;

	$id = intval( $post->ID );

	$add_to = tptn_get_option( 'add_to' );

	if ( ! empty( $add_to['feed'] ) ) {
		return $content . '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . get_tptn_post_count( $id ) . '</div>';
	} else {
		return $content;
	}
}
add_filter( 'the_excerpt_rss', 'tptn_rss_filter' );
add_filter( 'the_content_feed', 'tptn_rss_filter' );


/**
 * Function to manually display count.
 *
 * @since   1.0
 * @param   int|boolean $echo Flag to echo the output.
 * @return  string  Formatted string if $echo is set to 0|false
 */
function echo_tptn_post_count( $echo = 1 ) {
	global $post;

	$home_url = home_url( '/' );

	/**
	 * Filter the script URL of the counter.
	 *
	 * @since   2.0
	 */
	$home_url = apply_filters( 'tptn_view_counter_script_url', $home_url );

	// Strip any query strings since we don't need them.
	$home_url = strtok( $home_url, '?' );

	$id = intval( $post->ID );

	$nonce_action = 'tptn-nonce-' . $id;
	$nonce        = wp_create_nonce( $nonce_action );

	if ( tptn_get_option( 'dynamic_post_count' ) ) {
		$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '"><script type="text/javascript" data-cfasync="false" src="' . $home_url . '?top_ten_id=' . $id . '&amp;view_counter=1&amp;_wpnonce=' . $nonce . '"></script></div>';
	} else {
		$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . get_tptn_post_count( $id ) . '</div>';
	}

	/**
	 * Filter the viewed count script
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $output Counter viewed count code
	 */
	$output = apply_filters( 'tptn_view_post_count', $output );

	if ( $echo ) {
		echo $output; // WPCS: XSS OK.
	} else {
		return $output;
	}
}


/**
 * Return the formatted post count for the supplied ID.
 *
 * @since   1.9.2
 * @param   int|string $id         Post ID.
 * @param   int|string $blog_id    Blog ID.
 * @return  int|string  Formatted post count
 */
function get_tptn_post_count( $id = false, $blog_id = false ) {

	$count_disp_form      = stripslashes( tptn_get_option( 'count_disp_form' ) );
	$count_disp_form_zero = stripslashes( tptn_get_option( 'count_disp_form_zero' ) );
	$totalcntaccess       = get_tptn_post_count_only( $id, 'total', $blog_id );

	if ( $id > 0 ) {

		// Total count per post.
		if ( ( false !== strpos( $count_disp_form, '%totalcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%totalcount%' ) ) ) {
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%totalcount%', $totalcntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%totalcount%', ( 0 == $totalcntaccess ? $totalcntaccess + 1 : $totalcntaccess ), $count_disp_form );
			}
		}

		// Now process daily count.
		if ( ( false !== strpos( $count_disp_form, '%dailycount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%dailycount%' ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'daily' );
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%dailycount%', $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%dailycount%', ( 0 == $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		// Now process overall count.
		if ( ( false !== strpos( $count_disp_form, '%overallcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%overallcount%' ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'overall' );
			if ( ( 0 == $cntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%overallcount%', $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%overallcount%', ( 0 == $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
			return apply_filters( 'tptn_post_count', $count_disp_form_zero );
		} else {
			return apply_filters( 'tptn_post_count', $count_disp_form );
		}
	} else {
		return 0;
	}
}


/**
 * Returns the post count.
 *
 * @since   1.9.8.5
 *
 * @param   mixed  $id     Post ID.
 * @param   string $count  Which count to return? total, daily or overall.
 * @param   bool   $blog_id Blog ID.
 * @return  int     Post count
 */
function get_tptn_post_count_only( $id = false, $count = 'total', $blog_id = false ) {
	global $wpdb;

	$table_name       = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	if ( empty( $blog_id ) ) {
		$blog_id = get_current_blog_id();
	}

	if ( $id > 0 ) {
		switch ( $count ) {
			case 'total':
				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $id, $blog_id ) ); // WPCS: unprepared SQL OK.
				$cntaccess    = number_format_i18n( ( ( $resultscount ) ? $resultscount->cntaccess : 0 ) );
				break;
			case 'daily':
				$daily_range = tptn_get_option( 'daily_range' );
				$hour_range  = tptn_get_option( 'hour_range' );

				if ( tptn_get_option( 'daily_midnight' ) ) {
					$current_time = current_time( 'timestamp', 0 );
					$from_date    = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
					$from_date    = gmdate( 'Y-m-d 0', $from_date );
				} else {
					$current_time = current_time( 'timestamp', 0 );
					$from_date    = $current_time - ( $daily_range * DAY_IN_SECONDS + $hour_range * HOUR_IN_SECONDS );
					$from_date    = gmdate( 'Y-m-d H', $from_date );
				}

				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as sum_count FROM {$table_name_daily} WHERE postnumber = %d AND blog_id = %d AND dp_date >= %s GROUP BY postnumber ", array( $id, $blog_id, $from_date ) ) ); // WPCS: unprepared SQL OK.
				$cntaccess    = number_format_i18n( ( ( $resultscount ) ? $resultscount->sum_count : 0 ) );
				break;
			case 'overall':
				$resultscount = $wpdb->get_row( 'SELECT SUM(cntaccess) as sum_count FROM ' . $table_name ); // WPCS: unprepared SQL OK.
				$cntaccess    = number_format_i18n( ( ( $resultscount ) ? $resultscount->sum_count : 0 ) );
				break;
		}
		return apply_filters( 'tptn_post_count_only', $cntaccess );
	} else {
		return 0;
	}
}


