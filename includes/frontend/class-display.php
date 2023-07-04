<?php
/**
 * Functions to fetch and display the posts.
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Display {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
	}

	/**
	 * Function to return formatted list of popular posts.
	 *
	 * @since 1.5
	 *
	 * @param  mixed $args   Arguments array.
	 * @return string  HTML output of the popular posts.
	 */
	public static function pop_posts( $args ) {
		global $tptn_settings, $post;

		$defaults = array(
			'daily'        => false,
			'instance_id'  => 1,
			'is_widget'    => false,
			'is_shortcode' => false,
			'is_manual'    => false,
			'is_block'     => false,
			'echo'         => false,
			'strict_limit' => false,
			'heading'      => 1,
			'offset'       => 0,
			'extra_class'  => '',
		);

		// Merge the $defaults array with the $tptn_settings array.
		$defaults = array_merge( $defaults, \tptn_settings_defaults(), $tptn_settings );

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		$output = '';

		/**
		 * Fires before the output processing begins.
		 *
		 * @since 3.1.0
		 *
		 * @param string  $output Formatted list of top posts.
		 * @param array   $args   Array of arguments.
		 * @param WP_Post $post   Current Post object.
		 */
		do_action( 'pre_tptn_pop_posts', $output, $args, $post );

		// Check exclusions.
		if ( \WebberZone\Top_Ten\Util\Helpers::exclude_on( $post, $args ) ) {
			return '';
		}

		// Check if the cache is enabled and if the output exists. If so, return the output.
		if ( $args['cache'] ) {
			$cache_name = self::cache_get_key( $args );

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
		list( $args['thumb_width'], $args['thumb_height'] ) = Media_Handler::get_thumb_size( $args['thumb_size'] );

		// Retrieve the popular posts.
		$results = self::get_posts( $args );

		$counter = 0;

		// Override tptn_styles if post_thumb_op is text_only.
		$args['tptn_styles'] = ( 'text_only' === $args['post_thumb_op'] ) ? 'text_only' : $args['tptn_styles'];
		$style_array         = Styles_Handler::get_style( $args['tptn_styles'] );

		$post_classes = array(
			'main'        => $args['daily'] ? 'tptn_posts_daily ' : 'tptn_posts ',
			'widget'      => $args['is_widget'] ? 'tptn_posts_widget tptn_posts_widget-' . $args['instance_id'] : '',
			'shortcode'   => $args['is_shortcode'] ? 'tptn_posts_shortcode' : '',
			'block'       => $args['is_block'] ? 'tptn_posts_block' : '',
			'extra_class' => $args['extra_class'],
			'style'       => ! empty( $style_array['name'] ) ? 'tptn-' . $style_array['name'] : '',
		);
		$post_classes = join( ' ', $post_classes );

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

			$output .= self::heading_title( $args );

			$output .= self::before_list( $args );

			foreach ( $results as $result ) {
				$switched_blog = false;
				if ( is_multisite() && ! empty( $result->blog_id ) && (int) get_current_blog_id() !== (int) $result->blog_id ) {
					add_action( 'switch_blog', 'wz_switch_site_rewrite' );
					switch_to_blog( $result->blog_id );
					$switched_blog = true;
				}

				// Store the count. We'll need this later.
				$count  = $args['daily'] ? 'daily' : 'total';
				$visits = empty( $result->visits ) ? \WebberZone\Top_Ten\Counter::get_post_count_only( $result->ID, $count ) : (int) $result->visits;

				$result = get_post( $result );

				$output .= self::before_list_item( $args, $result );

				$output .= self::list_link( $args, $result );

				if ( $args['show_author'] ) {
					$output .= self::get_the_author( $args, $result );
				}

				if ( $args['show_date'] ) {
					$output .= '<span class="tptn_date"> ' . self::get_the_date( $args, $result ) . '</span> ';
				}

				if ( $args['show_excerpt'] ) {
					$output .= '<span class="tptn_excerpt"> ' . self::get_the_excerpt( $result->ID, $args['excerpt_length'] ) . '</span>';
				}

				if ( $args['disp_list_count'] ) {

					$output .= ' <span class="tptn_list_count">' . self::get_list_count( $args, $result, $visits ) . '</span>';
				}

				$tptn_list = '';
				/**
				 * Filter to add content to the end of each item in the list.
				 *
				 * @since   2.2.0
				 *
				 * @param   string  $tptn_list Empty string at the end of each list item.
				 * @param   object  $result Object of the current post result
				 * @param   array   $args   Array of arguments
				 */
				$output .= apply_filters( 'tptn_list', $tptn_list, $result, $args );

				// Opening span created in tptn_list_link().
				if ( 'inline' === $args['post_thumb_op'] || 'text_only' === $args['post_thumb_op'] ) {
					$output .= '</span>';
				}

				$output .= self::after_list_item( $args, $result );

				++$counter;

				if ( $counter === (int) $args['limit'] ) {
					break;  // End loop when related posts limit is reached.
				}

				if ( $switched_blog ) {
					restore_current_blog();
					remove_action( 'switch_blog', 'wz_switch_site_rewrite' );
				}
			}
			if ( $args['show_credit'] ) {

				$output .= self::before_list_item( $args, $result );

				$output .= sprintf(
				/* translators: 1. Top 10 plugin page link, 2. Link attributes. */
					__( 'Popular posts by <a href="%1$s" rel="nofollow" %2$s>Top 10 plugin</a>', 'top-10' ),
					esc_url( 'https://webberzone.com/plugins/top-10/' ),
					self::link_attributes( $args, $result )
				);

				$output .= self::after_list_item( $args, $result );
			}

			$output .= self::after_list( $args );

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
			 * @param   int     $cache_time  Cache time in seconds
			 * @param   array   $args        Array of all the arguments
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
	public static function get_pop_posts( $args = array() ) {
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
		$defaults = array_merge( $defaults, \tptn_settings_defaults(), $tptn_settings );

		// Parse incomming $args into an array and merge it with $defaults.
		$args = wp_parse_args( $args, $defaults );

		$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( $args['daily'] );

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

		$from_date = \WebberZone\Top_Ten\Util\Helpers::get_from_date( null, $args['daily_range'], $args['hour_range'] );

		/**
		 *
		 * We're going to create a mySQL query that is fully extendable which would look something like this:
		 * "SELECT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits"
		 */

		// Fields to return.
		$fields[] = "{$table_name}.postnumber";
		$fields[] = ( $args['daily'] ) ? "SUM({$table_name}.cntaccess) as visits" : "{$table_name}.cntaccess as visits";
		$fields[] = "{$wpdb->posts}.ID";

		$fields = implode( ', ', $fields );

		// Create the JOIN clause.
		$join = " INNER JOIN {$wpdb->posts} ON {$table_name}.postnumber={$wpdb->posts}.ID ";

		// Create the base WHERE clause.
		$where .= $wpdb->prepare( " AND {$table_name}.blog_id = %d ", $blog_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$where .= " AND ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'inherit') ";   // Show published posts and attachments.

		if ( $args['daily'] ) {
			$where .= $wpdb->prepare( " AND {$table_name}.dp_date >= %s ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Convert exclude post IDs string to array so it can be filtered.
		$exclude_post_ids = explode( ',', $args['exclude_post_ids'] );

		/** This filter is documented in class-top-ten-query.php */
		$exclude_post_ids = apply_filters( 'tptn_exclude_post_ids', $exclude_post_ids );

		// Convert it back to string.
		$exclude_post_ids = implode( ',', array_filter( $exclude_post_ids ) );

		if ( $exclude_post_ids ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			$where .= " AND $wpdb->posts.ID NOT IN ({$exclude_post_ids}) ";
		}
		$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";    // Array of post types.

		// How old should the posts be?
		if ( $args['how_old'] ) {
			$how_old_date = \WebberZone\Top_Ten\Util\Helpers::get_from_date( null, $args['how_old'] + 1, 0 );

			$where .= $wpdb->prepare( " AND $wpdb->posts.post_date > %s ", $how_old_date );
		}

		if ( isset( $args['include_cat_ids'] ) && ! empty( $args['include_cat_ids'] ) ) {
			$include_cat_ids = $args['include_cat_ids'];

			$where .= " AND $wpdb->posts.ID IN ( SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id IN ($include_cat_ids) )";
		}

		// Create the base GROUP BY clause.
		if ( $args['daily'] ) {
			$groupby = ' postnumber ';
		}

		// Create the base ORDER BY clause.
		$orderby = ' visits DESC ';

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
			$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

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

		$results = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

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
	public function show_pop_posts( $args = null ) {
		if ( is_array( $args ) ) {
			$args['manual'] = 1;
		} else {
			$args .= '&is_manual=1';
		}

		echo $this->pop_posts( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Function to show daily popular posts.
	 *
	 * @since   1.2
	 *
	 * @param   mixed $args   Arguments list.
	 */
	public function show_daily_pop_posts( $args = null ) {
		if ( is_array( $args ) || ! isset( $args ) ) {
			$args['daily'] = 1;
		} else {
			$args .= '&daily=1';
		}

		$this->show_pop_posts( $args );
	}

	/**
	 * Get the key based on a list of parameters.
	 *
	 * @since 2.9.3
	 *
	 * @param array $attr   Array of attributes.
	 * @return string Cache key
	 */
	public static function cache_get_key( $attr ) {

		$key = 'tptn_cache_' . md5( wp_json_encode( $attr ) );

		return $key;
	}

	/**
	 * Retrieves an array of the related posts.
	 *
	 * The defaults are as follows:
	 *
	 * @since 3.0.0
	 *
	 * @see Top_Ten_Query::prepare_query_args()
	 *
	 * @param array $args Optional. Arguments to retrieve posts. See WP_Query::parse_query() for all available arguments.
	 * @return \WP_Post[]|int[] Array of post objects or post IDs.
	 */
	public static function get_posts( $args = array() ) {

		$get_posts = new \Top_Ten_Query( $args );

		/**
		 * Filter array of post IDs or objects.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Post[]|int[] $posts Array of post objects or post IDs.
		 * @param array            $args  Arguments to retrieve posts.
		 */
		return apply_filters( 'get_tptn_posts', $get_posts->posts, $args );
	}

	/**
	 * Returns the link attributes.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args Array of arguments.
	 * @param   \WP_Post $result Result object.
	 * @return  string  Space separated list of link attributes
	 */
	public static function link_attributes( $args, $result ) {

		$rel_attribute = ( $args['link_nofollow'] ) ? ' rel="nofollow" ' : ' ';

		$target_attribute = ( $args['link_new_window'] ) ? ' target="_blank" ' : ' ';

		$link_attributes = array(
			'rel_attribute'    => $rel_attribute,
			'target_attribute' => $target_attribute,
		);

		/**
		 * Filter the title of the popular posts list
		 *
		 * @since   2.2.0
		 *
		 * @param   array    $link_attributes    Array of link attributes
		 * @param   array    $args               Array of arguments
		 * @param   \WP_Post $result            Result object
		 */
		$link_attributes = apply_filters( 'tptn_link_attributes', $link_attributes, $args, $result );

		// Convert it to a string.
		$link_attributes = implode( ' ', $link_attributes );

		return $link_attributes;
	}


	/**
	 * Returns the heading of the popular posts.
	 *
	 * @since   2.2.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function heading_title( $args ) {

		$title = '';

		if ( $args['heading'] && ! $args['is_widget'] ) {
			$title = $args['daily'] ? $args['title_daily'] : $args['title'];
		}

		/**
		 * Filter the title of the Top posts.
		 *
		 * @since   1.9.5
		 *
		 * @param   string  $title  Title/heading of the popular posts list
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_heading_title', $title, $args );
	}


	/**
	 * Returns the opening tag of the popular posts list.
	 *
	 * @since   2.2.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function before_list( $args ) {

		$before_list = $args['before_list'];

		/**
		 * Filter the opening tag of the popular posts list
		 *
		 * @since   1.9.10.1
		 *
		 * @param   string  $before_list    Opening tag set in the Settings Page
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_before_list', $before_list, $args );
	}


	/**
	 * Returns the closing tag of the popular posts list.
	 *
	 * @since   2.2.0
	 *
	 * @param   array $args   Array of arguments.
	 * @return  string  Space separated list of link attributes
	 */
	public static function after_list( $args ) {

		$after_list = $args['after_list'];

		/**
		 * Filter the closing tag of the popular posts list
		 *
		 * @since   1.9.10.1
		 *
		 * @param   string  $after_list Closing tag set in the Settings Page
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_after_list', $after_list, $args );
	}


	/**
	 * Returns the opening tag of each list item.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function before_list_item( $args, $result ) {

		$before_list_item = $args['before_list_item'];

		/**
		 * Filter the opening tag of each list item.
		 *
		 * @since   1.9.10.1
		 *
		 * @param   string  $before_list_item   Tag before each list item. Can be defined in the Settings page.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_before_list_item', $before_list_item, $result, $args );
	}


	/**
	 * Returns the closing tag of each list item.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function after_list_item( $args, $result ) {

		$after_list_item = $args['after_list_item'];

		/**
		 * Filter the closing tag of each list item.
		 *
		 * @since   1.9.10.1
		 *
		 * @param   string  $after_list_item    Tag after each list item. Can be defined in the Settings page.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_after_list_item', $after_list_item, $result, $args );   // Pass the post object to the filter.
	}


	/**
	 * Returns the title of each list item.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function get_the_title( $args, $result ) {

		$title = \WebberZone\Top_Ten\Util\Helpers::trim_char( get_the_title( $result->ID ), $args['title_length'] ); // Get the post title and crop it if needed.

		/**
		 * Filter the post title of each list item.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $title  Title of the post.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_post_title', $title, $result, $args );
	}


	/**
	 * Returns the author of each list item.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function get_the_author( $args, $result ) {

		$author_info = get_userdata( (int) $result->post_author );
		$author_link = ( false === $author_info ) ? '' : get_author_posts_url( $author_info->ID );
		$author_name = ( false === $author_info ) ? '' : ucwords( trim( stripslashes( $author_info->display_name ) ) );

		/**
		 * Filter the author name.
		 *
		 * @since   1.9.1
		 *
		 * @param   string  $author_name    Proper name of the post author.
		 * @param   object  $author_info    WP_User object of the post author
		 */
		$author_name = apply_filters( 'tptn_author_name', $author_name, $author_info );

		if ( ! empty( $author_name ) ) {
			$tptn_author = '<span class="tptn_author"> ' . __( ' by ', 'top-10' ) . '<a href="' . $author_link . '">' . $author_name . '</a></span> ';
		} else {
			$tptn_author = '';
		}

		/**
		 * Filter the text with the author details.
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $tptn_author    Formatted string with author details and link
		 * @param   object  $author_info    WP_User object of the post author
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_author', $tptn_author, $author_info, $result, $args );
	}


	/**
	 * Returns the formatted list item with link and and thumbnail for each list item.
	 *
	 * @since   2.2.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string  Space separated list of link attributes
	 */
	public static function list_link( $args, $result ) {

		$output          = '';
		$title           = self::get_the_title( $args, $result );
		$link_attributes = self::link_attributes( $args, $result );

		if ( 'after' === $args['post_thumb_op'] ) {
			$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.
			$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title if post thumbnail is to be displayed after.
			$output .= '</a>'; // Close the link.
		}

		if ( 'inline' === $args['post_thumb_op'] || 'after' === $args['post_thumb_op'] || 'thumbs_only' === $args['post_thumb_op'] ) {
			$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.

			if ( 'text_only' !== $args['tptn_styles'] ) {
				$output .= Media_Handler::get_the_post_thumbnail(
					array(
						'post'               => $result,
						'size'               => $args['thumb_size'],
						'thumb_meta'         => $args['thumb_meta'],
						'thumb_html'         => $args['thumb_html'],
						'thumb_default'      => $args['thumb_default'],
						'thumb_default_show' => $args['thumb_default_show'],
						'scan_images'        => $args['scan_images'],
						'class'              => 'tptn_thumb',
					)
				);
			}

			$output .= '</a>'; // Close the link.
		}

		if ( 'inline' === $args['post_thumb_op'] || 'text_only' === $args['post_thumb_op'] ) {
			$output .= '<span class="tptn_after_thumb">';
			$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.
			$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title when required by settings.
			$output .= '</a>'; // Close the link.
		}

		/**
		 * Filter Formatted list item with link and and thumbnail.
		 *
		 * @since   2.2.0
		 *
		 * @param   string  $output Formatted list item with link and and thumbnail
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_list_link', $output, $result, $args );
	}


	/**
	 * Returns the title of each list item.
	 *
	 * @since   2.6.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @return  string Formatted post date
	 */
	public static function get_the_date( $args, $result ) {

		$date = mysql2date( get_option( 'date_format', 'd/m/y' ), $result->post_date );

		/**
		 * Filter the post title of each list item.
		 *
		 * @since   2.6.0
		 *
		 * @param   string  $date   Title of the post.
		 * @param   object  $result Object of the current post result
		 * @param   array   $args   Array of arguments
		 */
		return apply_filters( 'tptn_date', $date, $result, $args );
	}


	/**
	 * Returns the title of each list item.
	 *
	 * @since   2.6.0
	 *
	 * @param   array    $args   Array of arguments.
	 * @param   \WP_Post $result Object of the current post result.
	 * @param   int      $visits Number of visits.
	 * @return  string Formatted post date
	 */
	public static function get_list_count( $args, $result, $visits ) {

		$tptn_list_count = '(' . \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( $visits ) . ')';

		/**
		 * Filter the formatted list count text.
		 *
		 * @since   2.1.0
		 *
		 * @param   string  $tptn_list_count  Formatted list count
		 * @param   int     $visits           Post count
		 * @param   \WP_Post $result          Post object
		 * @param   array   $args             Array of arguments.
		 */
		return apply_filters( 'tptn_list_count', $tptn_list_count, $visits, $result, $args );
	}


	/**
	 * Function to create an excerpt for the post.
	 *
	 * @since   1.6
	 * @param   int        $id             Post ID.
	 * @param   int|string $excerpt_length Length of the excerpt in words.
	 * @param   bool       $use_excerpt Use Excerpt.
	 * @return  string     Excerpt
	 */
	public static function get_the_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
		$content = '';

		$post = get_post( $id );
		if ( empty( $post ) ) {
			return '';
		}
		if ( $use_excerpt ) {
			$content = $post->post_excerpt;
		}
		if ( empty( $content ) ) {
			$content = $post->post_content;
		}

		$output = wp_strip_all_tags( strip_shortcodes( $content ) );

		if ( $excerpt_length > 0 ) {
			$output = wp_trim_words( $output, $excerpt_length );
		}

		if ( post_password_required( $post ) ) {
			$output = __( 'There is no excerpt because this is a protected post.', 'top-10' );
		}

		/**
		 * Filters excerpt generated by tptn.
		 *
		 * @since   1.9.10.1
		 *
		 * @param   string  $output         Formatted excerpt
		 * @param   int     $id             Post ID
		 * @param   int     $excerpt_length Length of the excerpt
		 * @param   boolean $use_excerpt    Use the excerpt?
		 */
		return apply_filters( 'tptn_excerpt', $output, $id, $excerpt_length, $use_excerpt );
	}

}
