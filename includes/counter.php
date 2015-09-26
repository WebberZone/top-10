<?php
/**
 * Functions controlling the counter/tracker
 *
 * @package Top_Ten
 */


/**
 * Function to update the post views for the current post. Filters `the_content`.
 *
 * @since	1.0
 *
 * @param	string $content    Post content
 * @return	string	Filtered content
 */
function tptn_add_viewed_count( $content ) {
	global $post, $wpdb, $single, $tptn_url, $tptn_path, $tptn_settings;

	$table_name = $wpdb->base_prefix . 'top_ten';

	$home_url = home_url( '/' );

	/**
	 * Filter the script URL of the counter.
	 *
	 * Create a filter function to overwrite the script URL to use the external top-10-counter.js.php
	 * You can use $tptn_url . '/top-10-addcount.js.php' as a source
	 * $tptn_url is a global variable
	 *
	 * @since	2.0
	 */
	$home_url = apply_filters( 'tptn_add_counter_script_url', $home_url );

	if ( is_singular() ) {

		$current_user = wp_get_current_user();	// Let's get the current user
		$post_author = ( $current_user->ID == $post->post_author ) ? true : false;	// Is the current user the post author?
		$current_user_admin = ( current_user_can( 'manage_options' ) ) ? true : false;	// Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;	// Is the current user an editor?

		$include_code = true;
		if ( ( $post_author ) && ( ! $tptn_settings['track_authors'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_admin ) && ( ! $tptn_settings['track_admins'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_editor ) && ( ! $tptn_settings['track_editors'] ) ) {
			$include_code = false;
		}

		if ( $include_code ) {

			$output = '';
			$id = intval( $post->ID );
			$blog_id = get_current_blog_id();
			$activate_counter = $tptn_settings['activate_overall'] ? 1 : 0;		// It's 1 if we're updating the overall count
			$activate_counter = $activate_counter + ( $tptn_settings['activate_daily'] ? 10 : 0 );	// It's 10 if we're updating the daily count

			if ( $activate_counter > 0 ) {
				if ( $tptn_settings['cache_fix'] ) {
					$output = '<script type="text/javascript">jQuery.ajax({url: "' . $home_url . '", data: {top_ten_id: ' . $id . ', top_ten_blog_id: ' . $blog_id . ', activate_counter: ' . $activate_counter . ', top10_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random()*100000)}});</script>';
				} else {
					$output = '<script type="text/javascript" async src="' . $home_url . '?top_ten_id=' . $id . '&amp;top_ten_blog_id=' . $blog_id . '&amp;activate_counter=' . $activate_counter . '"></script>';
				}
			}

			/**
			 * Filter the counter script
			 *
			 * @since	1.9.8.5
			 *
			 * @param	string	$output	Counter script code
			 */
			$output = apply_filters( 'tptn_viewed_count', $output );

			return $content.$output;
		} else {
			return $content;
		}
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'tptn_add_viewed_count' );


/**
 * Enqueue Scripts.
 *
 * @since	1.9.7
 */
function tptn_enqueue_scripts() {
	global $tptn_settings;

	if ( $tptn_settings['cache_fix'] ) {
		wp_enqueue_script( 'jquery' );
	}
}
add_action( 'wp_enqueue_scripts', 'tptn_enqueue_scripts' ); // wp_enqueue_scripts action hook to link only on the front-end


/**
 * Function to add additional queries to query_vars.
 *
 * @since	2.0.0
 *
 * @param	array $vars   Query variables array
 * @return	array	$Query variables array with Top 10 parameters appended
 */
function tptn_query_vars( $vars ) {
	// add these to the list of queryvars that WP gathers
	$vars[] = 'top_ten_id';
	$vars[] = 'top_ten_blog_id';
	$vars[] = 'activate_counter';
	$vars[] = 'view_counter';
	return $vars;
}
add_filter( 'query_vars', 'tptn_query_vars' );


/**
 * Function to update the .
 *
 * @since	2.0.0
 *
 * @param	object $wp WordPress object
 */
function tptn_parse_request( $wp ) {
	global $wpdb, $tptn_settings;

	if ( empty( $wp ) ) {
		global $wp;
	}

	if ( ! isset( $wp->query_vars ) || ! is_array( $wp->query_vars ) ) {
		return;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	$top_ten_daily = $wpdb->base_prefix . 'top_ten_daily';
	$str = '';

	if ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'activate_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );
		$blog_id = intval( $wp->query_vars['top_ten_blog_id'] );
		$activate_counter = intval( $wp->query_vars['activate_counter'] );

		if ( $id > 0 ) {

			if ( ( 1 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '1', '%d') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) );

				$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
			}

			if ( ( 10 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$current_date = gmdate( 'Y-m-d H', current_time( 'timestamp', 0 ) );

				$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$top_ten_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES('%d', '1', '%s', '%d' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) );

				$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
			}
		}
		Header( 'content-type: application/x-javascript' );
		echo '<!-- ' . $str . ' -->';

		// stop anything else from loading as it is not needed.
		exit;

	} elseif ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'view_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );

		if ( $id > 0 ) {

			$output = get_tptn_post_count( $id );

			Header( 'content-type: application/x-javascript' );
			echo 'document.write("' . $output . '")';

			// stop anything else from loading as it is not needed.
			exit;
		}
	} else {
		return;
	}
}
add_action( 'parse_request', 'tptn_parse_request' );


/**
 * Function to add the viewed count to the post content. Filters `the_content`.
 *
 * @since	1.0
 * @param	string $content    Post content
 * @return	string	Filtered post content
 */
function tptn_pc_content( $content ) {
	global $single, $post, $tptn_settings;

	$exclude_on_post_ids = explode( ',', $tptn_settings['exclude_on_post_ids'] );

	if ( in_array( $post->ID, $exclude_on_post_ids ) ) {
		return $content;	// Exit without adding related posts
	}

	if ( ( is_single() ) && ( $tptn_settings['add_to_content'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_page() ) && ( $tptn_settings['count_on_pages'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_home() ) && ( $tptn_settings['add_to_home'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_category() ) && ( $tptn_settings['add_to_category_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( is_tag() ) && ( $tptn_settings['add_to_tag_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ( $tptn_settings['add_to_archives'] ) ) {
		return $content . echo_tptn_post_count( 0 );
	} else {
		return $content;
	}
}
add_filter( 'the_content', 'tptn_pc_content' );


/**
 * Filter to add related posts to feeds.
 *
 * @since	1.9.8
 *
 * @param	string $content    Post content
 * @return	string	Filtered post content
 */
function tptn_rss_filter( $content ) {
	global $post, $tptn_settings;

	$id = intval( $post->ID );

	if ( $tptn_settings['add_to_feed'] ) {
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
 * @since	1.0
 * @param	int|boolean	$echo Flag to echo the output?
 * @return	string	Formatted string if $echo is set to 0|false
 */
function echo_tptn_post_count( $echo = 1 ) {
	global $post, $tptn_url, $tptn_path, $tptn_settings;

	$home_url = home_url( '/' );

	/**
	 * Filter the script URL of the counter.
	 *
	 * Create a filter function to overwrite the script URL to use the external top-10-counter.js.php
	 * You can use $tptn_url . '/top-10-counter.js.php' as a source
	 * $tptn_url is a global variable
	 *
	 * @since	2.0
	 */
	$home_url = apply_filters( 'tptn_view_counter_script_url', $home_url );

	$id = intval( $post->ID );

	$nonce_action = 'tptn-nonce-' . $id ;
	$nonce = wp_create_nonce( $nonce_action );

	if ( $tptn_settings['dynamic_post_count'] ) {
		$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '"><script type="text/javascript" data-cfasync="false" src="' . $home_url . '?top_ten_id='.$id.'&amp;view_counter=1&amp;_wpnonce=' . $nonce . '"></script></div>';
	} else {
		$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . get_tptn_post_count( $id ) . '</div>';
	}

	/**
	 * Filter the viewed count script
	 *
	 * @since	2.0.0
	 *
	 * @param	string	$output	Counter viewed count code
	 */
	$output = apply_filters( 'tptn_view_post_count', $output );

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}


/**
 * Return the formatted post count for the supplied ID.
 *
 * @since	1.9.2
 * @param	int|string $id         Post ID
 * @param	int|string $blog_id    Blog ID
 * @return	int|string	Formatted post count
 */
function get_tptn_post_count( $id = false, $blog_id = false ) {
	global $wpdb, $tptn_settings;

	$table_name = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	$count_disp_form = stripslashes( $tptn_settings['count_disp_form'] );
	$count_disp_form_zero = stripslashes( $tptn_settings['count_disp_form_zero'] );
	$totalcntaccess = get_tptn_post_count_only( $id, 'total', $blog_id );

	if ( $id > 0 ) {

		// Total count per post
		if ( ( false !== strpos( $count_disp_form, '%totalcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%totalcount%' ) ) ) {
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%totalcount%', $totalcntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%totalcount%', ( 0 == $totalcntaccess ? $totalcntaccess + 1 : $totalcntaccess ), $count_disp_form );
			}
		}

		// Now process daily count
		if ( ( false !== strpos( $count_disp_form, '%dailycount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%dailycount%' ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'daily' );
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%dailycount%', $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%dailycount%', ( 0 == $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		// Now process overall count
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
 * @since	1.9.8.5
 *
 * @param	mixed  $id     Post ID
 * @param	string $count  Which count to return? total, daily or overall
 * @return	int		Post count
 */
function get_tptn_post_count_only( $id = false, $count = 'total', $blog_id = false ) {
	global $wpdb, $tptn_settings;

	$table_name = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	if ( empty( $blog_id ) ) {
		$blog_id = get_current_blog_id();
	}

	if ( $id > 0 ) {
		switch ( $count ) {
			case 'total':
				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d " , $id, $blog_id ) );
				$cntaccess = number_format_i18n( ( ( $resultscount ) ? $resultscount->cntaccess : 0 ) );
				break;
			case 'daily':
				$daily_range = $tptn_settings['daily_range'];
				$hour_range = $tptn_settings['hour_range'];

				if ( $tptn_settings['daily_midnight'] ) {
					$current_time = current_time( 'timestamp', 0 );
					$from_date = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
					$from_date = gmdate( 'Y-m-d 0' , $from_date );
				} else {
					$current_time = current_time( 'timestamp', 0 );
					$from_date = $current_time - ( $daily_range * DAY_IN_SECONDS + $hour_range * HOUR_IN_SECONDS );
					$from_date = gmdate( 'Y-m-d H' , $from_date );
				}

				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as sumCount FROM {$table_name_daily} WHERE postnumber = %d AND blog_id = %d AND dp_date >= '%s' GROUP BY postnumber ", array( $id, $blog_id, $from_date ) ) );
				$cntaccess = number_format_i18n( ( ( $resultscount ) ? $resultscount->sumCount : 0 ) );
				break;
			case 'overall':
				$resultscount = $wpdb->get_row( 'SELECT SUM(cntaccess) as sumCount FROM ' . $table_name );
				$cntaccess = number_format_i18n( ( ( $resultscount ) ? $resultscount->sumCount : 0 ) );
				break;
		}
		return apply_filters( 'tptn_post_count_only', $cntaccess );
	} else {
		return 0;
	}
}
