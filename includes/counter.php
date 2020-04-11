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
	$add_to              = tptn_get_option( 'add_to', false );

	if ( isset( $post ) ) {
		if ( in_array( $post->ID, $exclude_on_post_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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

	$add_to = tptn_get_option( 'add_to', false );

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
		$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '"><script type="text/javascript" data-cfasync="false" src="' . $home_url . '?top_ten_id=' . $id . '&amp;view_counter=1&amp;_wpnonce=' . $nonce . '"></script></div>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
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
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
			if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%totalcount%', $totalcntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%totalcount%', ( 0 === (int) $totalcntaccess ? $totalcntaccess + 1 : $totalcntaccess ), $count_disp_form );
			}
		}

		// Now process daily count.
		if ( ( false !== strpos( $count_disp_form, '%dailycount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%dailycount%' ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'daily' );
			if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%dailycount%', $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%dailycount%', ( 0 === (int) $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		// Now process overall count.
		if ( ( false !== strpos( $count_disp_form, '%overallcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%overallcount%' ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'overall' );
			if ( ( 0 === (int) $cntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( '%overallcount%', $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( '%overallcount%', ( 0 === (int) $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
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
				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess as visits FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				break;
			case 'daily':
				$from_date = tptn_get_from_date();

				$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as visits FROM {$table_name_daily} WHERE postnumber = %d AND blog_id = %d AND dp_date >= %s GROUP BY postnumber ", array( $id, $blog_id, $from_date ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				break;
			case 'overall':
				$resultscount = $wpdb->get_row( 'SELECT SUM(cntaccess) as visits FROM ' . $table_name ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				break;
		}

		$visits = ( $resultscount ) ? $resultscount->visits : 0;

		$string = tptn_number_format_i18n( $visits );

		/**
		 * Returns the post count.
		 *
		 * @since   2.6.0
		 *
		 * @param   string $string Formatted post count.
		 * @param   mixed  $id        Post ID.
		 * @param   string $count     Which count to return? total, daily or overall.
		 * @param   bool   $blog_id   Blog ID.
		 */
		return apply_filters( 'tptn_post_count_only', $string, $id, $count, $blog_id );
	} else {
		return 0;
	}
}


/**
 * Delete post count.
 *
 * @since 2.9.0
 *
 * @param int  $post_id Post ID.
 * @param int  $blog_id Blog ID.
 * @param bool $daily   Daily flag.
 * @return bool|int Number of rows affected or false if error.
 */
function tptn_delete_count( $post_id, $blog_id, $daily = false ) {
	global $wpdb;

	$post_id = intval( $post_id );
	$blog_id = intval( $blog_id );

	if ( empty( $post_id ) || empty( $blog_id ) ) {
		return false;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$results = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DELETE FROM {$table_name} WHERE postnumber = %d AND blog_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$post_id,
			$blog_id
		)
	);

	return $results;
}


/**
 * Function to update the Top 10 count with ajax.
 *
 * @since 2.9.0
 */
function tptn_edit_count_ajax() {

	if ( ! isset( $_REQUEST['total_count'] ) || ! isset( $_REQUEST['post_id'] ) || ! isset( $_REQUEST['total_count_original'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_die( 0 );
	}

	$results = 0;

	$post_id              = intval( $_REQUEST['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$blog_id              = get_current_blog_id();
	$total_count          = filter_var( $_REQUEST['total_count'], FILTER_SANITIZE_NUMBER_INT ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	$total_count_original = filter_var( $_REQUEST['total_count_original'], FILTER_SANITIZE_NUMBER_INT ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

	// If our current user can't edit this post, bail.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( 0 );
	}

	if ( $total_count_original !== $total_count ) {
		$results = tptn_edit_count( $post_id, $blog_id, $total_count );
	}
	echo wp_json_encode( $results );
	wp_die();
}
add_action( 'wp_ajax_tptn_edit_count_ajax', 'tptn_edit_count_ajax' );


/**
 * Function to edit the count.
 *
 * @since 2.9.0
 *
 * @param int $post_id Post ID.
 * @param int $blog_id Blog ID.
 * @param int $total_count Total count.
 * @return bool|int Number of rows affected or false if error.
 */
function tptn_edit_count( $post_id, $blog_id, $total_count ) {

	global $wpdb;

	$post_id     = intval( $post_id );
	$blog_id     = intval( $blog_id );
	$total_count = intval( $total_count );

	if ( empty( $post_id ) || empty( $blog_id ) || empty( $total_count ) ) {
		return false;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';

	$results = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, %d, %d ) ON DUPLICATE KEY UPDATE cntaccess= %d ", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$post_id,
			$total_count,
			$blog_id,
			$total_count
		)
	);

	return $results;
}

