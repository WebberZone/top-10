<?php
/**
 * Functions to fetch and display the posts
 *
 * @package   Top_Ten
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to return formatted list of popular posts.
 *
 * @since   1.5
 *
 * @param   mixed $args   Arguments array.
 * @return  array|string  Array of posts if posts_only = 0 or a formatted string if posts_only = 1
 */
function tptn_pop_posts( $args ) {
	global $tptn_settings;

	// if set, save $exclude_categories.
	if ( isset( $args['exclude_categories'] ) && '' != $args['exclude_categories'] ) {
		$exclude_categories   = explode( ',', $args['exclude_categories'] );
		$args['strict_limit'] = false;
	}

	$defaults = array(
		'daily'        => false,
		'is_widget'    => false,
		'instance_id'  => 1,
		'is_shortcode' => false,
		'is_manual'    => false,
		'echo'         => false,
		'strict_limit' => false,
		'posts_only'   => false,
		'heading'      => 1,
		'offset'       => 0,
	);

	// Merge the $defaults array with the $tptn_settings array.
	$defaults = array_merge( $defaults, tptn_settings_defaults(), $tptn_settings );

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	$output = '';

	/**
	 * Fires before the output processing begins.
	 *
	 * @since   2.2.0
	 *
	 * @param   string  $output Formatted list of top posts
	 * @param   array   $args   Array of arguments
	 */
	do_action( 'pre_tptn_pop_posts', $output, $args );

	// Check if the cache is enabled and if the output exists. If so, return the output.
	if ( $args['cache'] && ! $args['posts_only'] ) {
		$cache_name  = 'tptn';
		$cache_name .= $args['daily'] ? '_daily' : '_total';
		$cache_name .= $args['is_widget'] ? '_widget' . $args['instance_id'] : '';
		$cache_name .= $args['is_shortcode'] ? '_shortcode' : '';
		$cache_name .= $args['is_manual'] ? '_manual' : '';

		$output = get_transient( $cache_name );

		if ( false !== $output ) {

			/**
			 * Filter the output
			 *
			 * @since   1.9.8.5
			 *
			 * @param   string  $output Formatted list of top posts
			 * @param   array   $args   Array of arguments
			 */
			return apply_filters( 'tptn_pop_posts', $output, $args );
		}
	}

	// Get thumbnail size.
	list( $args['thumb_width'], $args['thumb_height'] ) = tptn_get_thumb_size( $args );

	// Retrieve the popular posts.
	$results = get_tptn_pop_posts( $args );

	if ( $args['posts_only'] ) {    // Return the array of posts only if the variable is set.
		_deprecated_argument( __FUNCTION__, '2.2.0', esc_html__( 'posts_only argument has been deprecated. Use get_tptn_pop_posts() to get the posts only.', 'top-10' ) );
		return $results;
	}

	$counter = 0;

	$daily_class     = $args['daily'] ? 'tptn_posts_daily ' : 'tptn_posts ';
	$widget_class    = $args['is_widget'] ? ' tptn_posts_widget tptn_posts_widget' . $args['instance_id'] : '';
	$shortcode_class = $args['is_shortcode'] ? ' tptn_posts_shortcode' : '';

	$post_classes = $daily_class . $widget_class . $shortcode_class;

	/**
	 * Filter the classes added to the div wrapper of the Top 10.
	 *
	 * @since   2.1.0
	 *
	 * @param   string   $post_classes  Post classes string.
	 */
	$post_classes = apply_filters( 'tptn_post_class', $post_classes );

	$output .= '<div class="' . $post_classes . '">';

	if ( $results ) {

		$output .= tptn_heading_title( $args );

		$output .= tptn_before_list( $args );

		// We need this for WPML support.
		$processed_results = array();

		foreach ( $results as $result ) {

			/* Support WPML */
			$resultid = tptn_object_id_cur_lang( $result->ID );

			// If this is NULL or already processed ID or matches current post then skip processing this loop.
			if ( ! $resultid || in_array( $resultid, $processed_results ) ) {
				continue;
			}

			// Push the current ID into the array to ensure we're not repeating it.
			array_push( $processed_results, $resultid );

			$sum_count = $result->sum_count;        // Store the count. We'll need this later.

			/**
			 * Filter the post ID for each result. Allows a custom function to hook in and change the ID if needed.
			 *
			 * @since   1.9.8.5
			 *
			 * @param   int $resultid   ID of the post
			 */
			$resultid = apply_filters( 'tptn_post_id', $resultid );

			$result = get_post( $resultid );    // Let's get the Post using the ID.

			// Process the category exclusion if passed in the shortcode.
			if ( isset( $exclude_categories ) ) {

				$categorys = get_the_category( $result->ID );   // Fetch categories of the plugin.

				$p_in_c = false;    // Variable to check if post exists in a particular category.
				foreach ( $categorys as $cat ) {    // Loop to check if post exists in excluded category.
					$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories ) ) ? true : false;
					if ( $p_in_c ) {
						break; // Skip loop execution and go to the next step.
					}
				}
				if ( $p_in_c ) {
					continue;  // Skip loop execution and go to the next step.
				}
			}

			$output .= tptn_before_list_item( $args, $result );

			$output .= tptn_list_link( $args, $result );

			if ( $args['show_author'] ) {
				$output .= tptn_author( $args, $result );
			}

			if ( $args['show_date'] ) {
				$output .= '<span class="tptn_date"> ' . mysql2date( get_option( 'date_format', 'd/m/y' ), $result->post_date ) . '</span> ';
			}

			if ( $args['show_excerpt'] ) {
				$output .= '<span class="tptn_excerpt"> ' . tptn_excerpt( $result->ID, $args['excerpt_length'] ) . '</span>';
			}

			if ( $args['disp_list_count'] ) {

				$tptn_list_count = '(' . number_format_i18n( $sum_count ) . ')';

				/**
				 * Filter the formatted list count text.
				 *
				 * @since   2.1.0
				 *
				 * @param   string  $tptn_list_count    Formatted list count
				 * @param   int     $sum_count          Post count
				 * @param   object  $result             Post object
				 */
				$tptn_list_count = apply_filters( 'tptn_list_count', $tptn_list_count, $sum_count, $result );

				$output .= ' <span class="tptn_list_count">' . $tptn_list_count . '</span>';
			}

			$tptn_list = '';
			/**
			 * Filter Formatted list item with link and and thumbnail.
			 *
			 * @since   2.2.0
			 *
			 * @param   string  $tptn_list
			 * @param   object  $result Object of the current post result
			 * @param   array   $args   Array of arguments
			 */
			$output .= apply_filters( 'tptn_list', $tptn_list, $result, $args );

			// Opening span created in tptn_list_link().
			if ( 'inline' === $args['post_thumb_op'] || 'text_only' === $args['post_thumb_op'] ) {
				$output .= '</span>';
			}

			$output .= tptn_after_list_item( $args, $result );

			$counter++;

			if ( $counter === (int) $args['limit'] ) {
				break;  // End loop when related posts limit is reached.
			}
		}
		if ( $args['show_credit'] ) {

			$output .= tptn_before_list_item( $args, $result );

			$output .= sprintf(
				/* translators: 1. Top 10 plugin page link, 2. Link attributes. */
				__( 'Popular posts by <a href="%1$s" rel="nofollow" %2$s>Top 10 plugin</a>', 'top-10' ),
				esc_url( 'https://webberzone.com/plugins/top-10/' ),
				tptn_link_attributes( $args, $result )
			);

			$output .= tptn_after_list_item( $args, $result );
		}

		$output .= tptn_after_list( $args );

		$clearfix = '<div class="tptn_clear"></div>';

		/**
		 * Filter the clearfix div tag. This is included after the closing tag to clear any miscellaneous floating elements;
		 *
		 * @since   2.2.0
		 *
		 * @param   string  $clearfix   Contains: <div style="clear:both"></div>
		 */
		$output .= apply_filters( 'tptn_clearfix', $clearfix );

	} else {
		$output .= ( $args['blank_output'] ) ? '' : $args['blank_output_text'];
	}
	$output .= '</div>';

	// Check if the cache is enabled and if the output exists. If so, return the output.
	if ( $args['cache'] ) {
		/**
		 * Filter the cache time which allows a function to override this
		 *
		 * @since   2.2.0
		 *
		 * @param   int     $args['cache_time'] Cache time in seconds
		 * @param   array   $args               Array of all the arguments
		 */
		$cache_time = apply_filters( 'tptn_cache_time', $args['cache_time'], $args );

		$output .= "<br /><!-- Cached output. Cached time is {$cache_time} seconds -->";

		set_transient( $cache_name, $output, $cache_time );

	}

	/**
	 * Filter already documented in top-10.php
	 */
	return apply_filters( 'tptn_pop_posts', $output, $args );
}


/**
 * Function to retrieve the popular posts.
 *
 * @since   2.1.0
 *
 * @param   mixed $args   Arguments list.
 */
function get_tptn_pop_posts( $args = array() ) {
	global $wpdb, $tptn_settings;

	// Initialise some variables.
	$fields  = array();
	$where   = '';
	$join    = '';
	$groupby = '';
	$orderby = '';
	$limits  = '';

	$defaults = array(
		'daily'        => false,
		'strict_limit' => true,
		'posts_only'   => false,
		'offset'       => 0,
	);

	// Merge the $defaults array with the $tptn_settings array.
	$defaults = array_merge( $defaults, tptn_settings_defaults(), $tptn_settings );

	// Parse incomming $args into an array and merge it with $defaults.
	$args = wp_parse_args( $args, $defaults );

	if ( $args['daily'] ) {
		$table_name = $wpdb->base_prefix . 'top_ten_daily';
	} else {
		$table_name = $wpdb->base_prefix . 'top_ten';
	}

	$limit  = ( $args['strict_limit'] ) ? $args['limit'] : ( $args['limit'] * 5 );
	$offset = isset( $args['offset'] ) ? $args['offset'] : 0;

	// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
	if ( ! empty( $args['post_types'] ) && is_array( $args['post_types'] ) ) {
		$post_types = $args['post_types'];
	} elseif ( ! empty( $args['post_types'] ) && false === strpos( $args['post_types'], '=' ) ) {
		$post_types = explode( ',', $args['post_types'] );
	} else {
		parse_str( $args['post_types'], $post_types );  // Save post types in $post_types variable.
	}

	// If post_types is empty or if we want all the post types.
	if ( empty( $post_types ) || 'all' === $args['post_types'] ) {
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);
	}

	$blog_id = get_current_blog_id();

	if ( $args['daily_midnight'] ) {
		$current_time = current_time( 'timestamp', 0 );
		$from_date    = $current_time - ( max( 0, ( $args['daily_range'] - 1 ) ) * DAY_IN_SECONDS );
		$from_date    = gmdate( 'Y-m-d 0', $from_date );
	} else {
		$current_time = current_time( 'timestamp', 0 );
		$from_date    = $current_time - ( $args['daily_range'] * DAY_IN_SECONDS + $args['hour_range'] * HOUR_IN_SECONDS );
		$from_date    = gmdate( 'Y-m-d H', $from_date );
	}

	/**
	 *
	 * We're going to create a mySQL query that is fully extendable which would look something like this:
	 * "SELECT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits"
	 */

	// Fields to return.
	$fields[] = 'ID';
	$fields[] = 'postnumber';
	$fields[] = ( $args['daily'] ) ? 'SUM(cntaccess) as sum_count' : 'cntaccess as sum_count';

	$fields = implode( ', ', $fields );

	// Create the JOIN clause.
	$join = " INNER JOIN {$wpdb->posts} ON postnumber=ID ";

	// Create the base WHERE clause.
	$where .= $wpdb->prepare( ' AND blog_id = %d ', $blog_id );             // Posts need to be from the current blog only.
	$where .= " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit') ";   // Show published posts and attachments.

	if ( $args['daily'] ) {
		$where .= $wpdb->prepare( ' AND dp_date >= %s ', $from_date );    // Only fetch posts that are tracked after this date.
	}

	// Convert exclude post IDs string to array so it can be filtered.
	$exclude_post_ids = explode( ',', $args['exclude_post_ids'] );

	/**
	 * Filter exclude post IDs array.
	 *
	 * @param array   $exclude_post_ids  Array of post IDs.
	 */
	$exclude_post_ids = apply_filters( 'tptn_exclude_post_ids', $exclude_post_ids );

	// Convert it back to string.
	$exclude_post_ids = implode( ',', array_filter( $exclude_post_ids ) );

	if ( '' != $exclude_post_ids ) {
		$where .= " AND $wpdb->posts.ID NOT IN ({$exclude_post_ids}) ";
	}
	$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";    // Array of post types.

	// How old should the posts be?
	if ( $args['how_old'] ) {
		$where .= $wpdb->prepare( " AND $wpdb->posts.post_date > %s ", gmdate( 'Y-m-d H:m:s', $current_time - ( $args['how_old'] * DAY_IN_SECONDS ) ) );
	}

	// Create the base GROUP BY clause.
	if ( $args['daily'] ) {
		$groupby = ' postnumber ';
	}

	// Create the base ORDER BY clause.
	$orderby = ' sum_count DESC ';

	// Create the base LIMITS clause.
	$limits .= $wpdb->prepare( ' LIMIT %d, %d ', $offset, $limit );

	/**
	 * Filter the SELECT clause of the query.
	 *
	 * @param string   $fields  The SELECT clause of the query.
	 */
	$fields = apply_filters( 'tptn_posts_fields', $fields );

	/**
	 * Filter the JOIN clause of the query.
	 *
	 * @param string   $join  The JOIN clause of the query.
	 */
	$join = apply_filters( 'tptn_posts_join', $join );

	/**
	 * Filter the WHERE clause of the query.
	 *
	 * @param string   $where  The WHERE clause of the query.
	 */
	$where = apply_filters( 'tptn_posts_where', $where );

	/**
	 * Filter the GROUP BY clause of the query.
	 *
	 * @param string   $groupby  The GROUP BY clause of the query.
	 */
	$groupby = apply_filters( 'tptn_posts_groupby', $groupby );

	/**
	 * Filter the ORDER BY clause of the query.
	 *
	 * @param string   $orderby  The ORDER BY clause of the query.
	 */
	$orderby = apply_filters( 'tptn_posts_orderby', $orderby );

	/**
	 * Filter the LIMIT clause of the query.
	 *
	 * @param string   $limits  The LIMIT clause of the query.
	 */
	$limits = apply_filters( 'tptn_posts_limits', $limits );

	if ( ! empty( $groupby ) ) {
		$groupby = " GROUP BY {$groupby} ";
	}
	if ( ! empty( $orderby ) ) {
		$orderby = " ORDER BY {$orderby} ";
	}

	$sql = "SELECT DISTINCT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

	if ( $args['posts_only'] ) {    // Return the array of posts only if the variable is set.
		$results = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL OK.

		/**
		 * Filter the array of top post IDs.
		 *
		 * @since   1.9.8.5
		 *
		 * @param   array   $tptn_pop_posts_array   Posts array.
		 * @param   mixed   $args       Arguments list
		 */
		return apply_filters( 'tptn_pop_posts_array', $results, $args );
	}

	$results = $wpdb->get_results( $sql ); // WPCS: unprepared SQL OK.

	/**
	 * Filter object containing post IDs of popular posts
	 *
	 * @since   2.1.0
	 *
	 * @param   object  $results    Top 10 popular posts object
	 * @param   mixed   $args       Arguments list
	 */
	return apply_filters( 'get_tptn_pop_posts', $results, $args );
}


/**
 * Function to echo popular posts.
 *
 * @since   1.0
 *
 * @param   mixed $args   Arguments list.
 */
function tptn_show_pop_posts( $args = null ) {
	if ( is_array( $args ) ) {
		$args['manual'] = 1;
	} else {
		$args .= '&is_manual=1';
	}

	echo tptn_pop_posts( $args ); // WPCS: XSS OK.
}


/**
 * Function to show daily popular posts.
 *
 * @since   1.2
 *
 * @param   mixed $args   Arguments list.
 */
function tptn_show_daily_pop_posts( $args = null ) {
	if ( is_array( $args ) || ! isset( $args ) ) {
		$args['daily']  = 1;
		$args['manual'] = 1;
	} else {
		$args .= '&daily=1&is_manual=1';
	}

	tptn_show_pop_posts( $args );
}


