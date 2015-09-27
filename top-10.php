<?php
/**
 * Top 10.
 *
 * Count daily and total visits per post and display the most popular posts based on the number of views.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name:	Top 10
 * Plugin URI:	https://webberzone.com/plugins/top-10/
 * Description:	Count daily and total visits per post and display the most popular posts based on the number of views
 * Version: 	2.2.4
 * Author: 		Ajay D'Souza
 * Author URI: 	https://webberzone.com
 * License: 	GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	top-10
 * Domain Path:	/languages
 * GitHub Plugin URI: https://github.com/ajaydsouza/top-10/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since	1.5
 *
 * @var string
 */
$tptn_path = plugin_dir_path( __FILE__ );


/**
 * Holds the URL for Top 10
 *
 * @since	1.5
 *
 * @var string
 */
$tptn_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Global variable holding the current database version of Top 10
 *
 * @since	1.0
 *
 * @var string
 */
global $tptn_db_version;
$tptn_db_version = '5.0';


/**
 * Global variable holding the current settings for Top 10
 *
 * @since	1.9.3
 *
 * @var array
 */
global $tptn_settings;
$tptn_settings = tptn_read_options();


/**
 * Function to load translation files.
 *
 * @since	1.9.10.1
 */
function tptn_lang_init() {
	load_plugin_textdomain( 'tptn', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'tptn_lang_init' );


/**
 * Function to return formatted list of popular posts.
 *
 * @since	1.5
 *
 * @param	mixed $args   Arguments array
 * @return	array|string	Array of posts if posts_only = 0 or a formatted string if posts_only = 1
 */
function tptn_pop_posts( $args ) {
	global $wpdb, $id, $tptn_settings;

	// if set, save $exclude_categories
	if ( isset( $args['exclude_categories'] ) && '' != $args['exclude_categories'] ) {
		$exclude_categories = explode( ',', $args['exclude_categories'] );
		$args['strict_limit'] = false;
	}

	$defaults = array(
		'daily' => false,
		'is_widget' => false,
		'is_shortcode' => false,
		'is_manual' => false,
		'echo' => false,
		'strict_limit' => false,
		'posts_only' => false,
		'heading' => 1,
	);

	// Merge the $defaults array with the $tptn_settings array
	$defaults = array_merge( $defaults, $tptn_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	$output = '';

	/**
	 * Fires before the output processing begins.
	 *
	 * @since	2.2.0
	 *
	 * @param	string	$output	Formatted list of top posts
	 * @param	array	$args	Array of arguments
	 */
	do_action( 'pre_tptn_pop_posts', $output, $args );

	// Check if the cache is enabled and if the output exists. If so, return the output
	if ( $args['cache'] && ! $args['posts_only'] ) {
		$cache_name = 'tptn';
		$cache_name .= $args['daily'] ? '_daily' : '_total';
		$cache_name .= $args['is_widget'] ? '_widget' : '';
		$cache_name .= $args['is_shortcode'] ? '_shortcode' : '';
		$cache_name .= $args['is_manual'] ? '_manual' : '';

		$output = get_transient( $cache_name );

		if ( false !== $output ) {

			/**
			 * Filter the output
			 *
			 * @since	1.9.8.5
			 *
			 * @param	string	$output	Formatted list of top posts
			 * @param	array	$args	Array of arguments
			 */
			return apply_filters( 'tptn_pop_posts', $output, $args );
		}
	}

	// Get thumbnail size
	list( $args['thumb_width'], $args['thumb_height'] ) = tptn_get_thumb_size( $args );

	// Retrieve the popular posts
	$results = get_tptn_pop_posts( $args );

	if ( $args['posts_only'] ) {	// Return the array of posts only if the variable is set
		_deprecated_argument( __FUNCTION__, '2.2.0', __( 'posts_only argument has been deprecated. Use get_tptn_pop_posts() to get the posts only.', 'top-10' ) );
		return $results;
	}

	$counter = 0;

	$daily_class = $args['daily'] ? 'tptn_posts_daily ' : 'tptn_posts ';
	$widget_class = $args['is_widget'] ? ' tptn_posts_widget' : '';
	$shortcode_class = $args['is_shortcode'] ? ' tptn_posts_shortcode' : '';

	$post_classes = $daily_class . $widget_class . $shortcode_class;

	/**
	 * Filter the classes added to the div wrapper of the Top 10.
	 *
	 * @since	2.1.0
	 *
	 * @param	string   $post_classes	Post classes string.
	 */
	$post_classes = apply_filters( 'tptn_post_class', $post_classes );

	$output .= '<div class="' . $post_classes . '">';

	if ( $results ) {

		$output .= tptn_heading_title( $args );

		$output .= tptn_before_list( $args );

		// We need this for WPML support
		$processed_results = array();

		foreach ( $results as $result ) {

			/* Support WPML */
		    $resultid = tptn_object_id_cur_lang( $result->ID );

			// If this is NULL or already processed ID or matches current post then skip processing this loop.
			if ( ! $resultid || in_array( $resultid, $processed_results ) ) {
			    continue;
			}

			// Push the current ID into the array to ensure we're not repeating it
			array_push( $processed_results, $resultid );

			$sumcount = $result->sumCount;		// Store the count. We'll need this later

			/**
			 * Filter the post ID for each result. Allows a custom function to hook in and change the ID if needed.
			 *
			 * @since	1.9.8.5
			 *
			 * @param	int	$resultid	ID of the post
			 */
			$resultid = apply_filters( 'tptn_post_id', $resultid );

			$result = get_post( $resultid );	// Let's get the Post using the ID

			// Process the category exclusion if passed in the shortcode
			if ( isset( $exclude_categories ) ) {

				$categorys = get_the_category( $result->ID );	// Fetch categories of the plugin

				$p_in_c = false;	// Variable to check if post exists in a particular category
				foreach ( $categorys as $cat ) {	// Loop to check if post exists in excluded category
					$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories ) ) ? true : false;
					if ( $p_in_c ) { break;	// Skip loop execution and go to the next step
					}
				}
				if ( $p_in_c ) { continue;	// Skip loop execution and go to the next step
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

				$tptn_list_count = '(' . number_format_i18n( $sumcount ) . ')';

				/**
				 * Filter the formatted list count text.
				 *
				 * @since	2.1.0
				 *
				 * @param	string	$tptn_list_count	Formatted list count
				 * @param	int		$sumcount			Post count
				 * @param	object	$result				Post object
				 */
				$tptn_list_count = apply_filters( 'tptn_list_count', $tptn_list_count, $sumcount, $result );

				$output .= ' <span class="tptn_list_count">' . $tptn_list_count . '</span>';
			}

			$tptn_list = '';
			/**
			 * Filter Formatted list item with link and and thumbnail.
			 *
			 * @since	2.2.0
			 *
			 * @param	string	$tptn_list
			 * @param	object	$result	Object of the current post result
			 * @param	array	$args	Array of arguments
			 */
			$output .= apply_filters( 'tptn_list', $tptn_list, $result, $args );

			// Opening span created in tptn_list_link()
			if ( 'inline' == $args['post_thumb_op'] || 'text_only' == $args['post_thumb_op'] ) {
				$output .= '</span>';
			}

			$output .= tptn_after_list_item( $args, $result );

			$counter++;

			if ( $counter == $args['limit'] ) {
				break;	// End loop when related posts limit is reached
			}
		}
		if ( $args['show_credit'] ) {

			$output .= tptn_before_list_item( $args, $result );

			$output .= sprintf(
				__( 'Popular posts by <a href="%s" rel="nofollow" %s>Top 10 plugin</a>', 'top-10' ),
				esc_url( 'https://webberzone.com/plugins/top-10/' ),
				$target_attribute
			);

			$output .= tptn_after_list_item( $args, $result );
		}

		$output .= tptn_after_list( $args );

		$clearfix = '<div class="tptn_clear"></div>';

		/**
		 * Filter the clearfix div tag. This is included after the closing tag to clear any miscellaneous floating elements;
		 *
		 * @since	2.2.0
		 *
		 * @param	string	$clearfix	Contains: <div style="clear:both"></div>
		 */
		$output .= apply_filters( 'tptn_clearfix', $clearfix );

	} else {
		$output .= ( $args['blank_output'] ) ? '' : $args['blank_output_text'];
	}
	$output .= '</div>';

	// Check if the cache is enabled and if the output exists. If so, return the output
	if ( $args['cache'] ) {
		/**
		 * Filter the cache time which allows a function to override this
		 *
		 * @since	2.2.0
		 *
		 * @param	int		$args['cache_time']	Cache time in seconds
		 * @param	array	$args				Array of all the arguments
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
 * @since	2.1.0
 *
 * @param	mixed $args   Arguments list
 */
function get_tptn_pop_posts( $args = array() ) {
	global $wpdb, $id, $tptn_settings;

	// Initialise some variables
	$fields = '';
	$where = '';
	$join = '';
	$groupby = '';
	$orderby = '';
	$limits = '';
	$match_fields = '';

	$defaults = array(
		'daily' => false,
		'strict_limit' => true,
		'posts_only' => false,
	);

	// Merge the $defaults array with the $tptn_settings array
	$defaults = array_merge( $defaults, $tptn_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	if ( $args['daily'] ) {
		$table_name = $wpdb->base_prefix . 'top_ten_daily';
	} else {
		$table_name = $wpdb->base_prefix . 'top_ten';
	}

	$limit = ( $args['strict_limit'] ) ? $args['limit'] : ( $args['limit'] * 5 );

	$target_attribute = ( $args['link_new_window'] ) ? ' target="_blank" ' : ' ';	// Set Target attribute
	$rel_attribute = ( $args['link_nofollow'] ) ? 'bookmark nofollow' : 'bookmark';	// Set nofollow attribute

	parse_str( $args['post_types'], $post_types );	// Save post types in $post_types variable

	if ( empty( $post_types ) ) {
		$post_types = get_post_types( array(
			'public'	=> true,
		) );
	}

	$blog_id = get_current_blog_id();

	if ( $args['daily_midnight'] ) {
		$current_time = current_time( 'timestamp', 0 );
		$from_date = $current_time - ( max( 0, ( $args['daily_range'] - 1 ) ) * DAY_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d 0' , $from_date );
	} else {
		$current_time = current_time( 'timestamp', 0 );
		$from_date = $current_time - ( $args['daily_range'] * DAY_IN_SECONDS + $args['hour_range'] * HOUR_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d H' , $from_date );
	}

	/**
	 *
	 * We're going to create a mySQL query that is fully extendable which would look something like this:
	 * "SELECT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits"
	 */

	// Fields to return
	$fields[] = 'ID';
	$fields[] = 'postnumber';
	$fields[] = 'post_title';
	$fields[] = 'post_type';
	$fields[] = 'post_content';
	$fields[] = 'post_date';
	$fields[] = 'post_author';
	$fields[] = ( $args['daily'] ) ? 'SUM(cntaccess) as sumCount' : 'cntaccess as sumCount';

	$fields = implode( ', ', $fields );

	// Create the JOIN clause
	$join = " INNER JOIN {$wpdb->posts} ON postnumber=ID ";

	// Create the base WHERE clause
	$where .= $wpdb->prepare( ' AND blog_id = %d ', $blog_id );				// Posts need to be from the current blog only
	$where .= " AND $wpdb->posts.post_status = 'publish' ";					// Only show published posts

	if ( $args['daily'] ) {
		$where .= $wpdb->prepare( " AND dp_date >= '%s' ", $from_date );	// Only fetch posts that are tracked after this date
	}

	// Convert exclude post IDs string to array so it can be filtered
	$exclude_post_ids = explode( ',', $args['exclude_post_ids'] );

	/**
	 * Filter exclude post IDs array.
	 *
	 * @param array   $exclude_post_ids  Array of post IDs.
	 */
	$exclude_post_ids = apply_filters( 'tptn_exclude_post_ids', $exclude_post_ids );

	// Convert it back to string
	$exclude_post_ids = implode( ',', array_filter( $exclude_post_ids ) );

	if ( '' != $exclude_post_ids ) {
		$where .= " AND $wpdb->posts.ID NOT IN ({$exclude_post_ids}) ";
	}
	$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";	// Array of post types

	// How old should the posts be?
	if ( $args['how_old'] ) {
		$where .= $wpdb->prepare( " AND $wpdb->posts.post_date > '%s' ", gmdate( 'Y-m-d H:m:s', $current_time - ( $args['how_old'] * DAY_IN_SECONDS ) ) );
	}

	// Create the base GROUP BY clause
	if ( $args['daily'] ) {
		$groupby = ' postnumber ';
	}

	// Create the base ORDER BY clause
	$orderby = ' sumCount DESC ';

	// Create the base LIMITS clause
	$limits .= $wpdb->prepare( ' LIMIT %d ', $limit );

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

	$sql = "SELECT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

	if ( $args['posts_only'] ) {	// Return the array of posts only if the variable is set
		$results = $wpdb->get_results( $sql, ARRAY_A );

		/**
		 * Filter the array of top post IDs.
		 *
		 * @since	1.9.8.5
		 *
		 * @param	array   $tptn_pop_posts_array	Posts array.
		 * @param	mixed	$args		Arguments list
		 */
		return apply_filters( 'tptn_pop_posts_array', $results, $args );
	}

	$results = $wpdb->get_results( $sql );

	/**
	 * Filter object containing post IDs of popular posts
	 *
	 * @since	2.1.0
	 *
	 * @param	object	$results	Top 10 popular posts object
	 * @param	mixed	$args		Arguments list
	 */
	return apply_filters( 'get_tptn_pop_posts', $results, $args );
}


/**
 * Function to echo popular posts.
 *
 * @since	1.0
 *
 * @param	mixed $args   Arguments list
 */
function tptn_show_pop_posts( $args = null ) {
	if ( is_array( $args ) ) {
		$args['manual'] = 1;
	} else {
		$args .= '&is_manual=1';
	}

	echo tptn_pop_posts( $args );
}


/**
 * Function to show daily popular posts.
 *
 * @since	1.2
 *
 * @param	mixed $args   Arguments list
 */
function tptn_show_daily_pop_posts( $args = null ) {
	if ( is_array( $args ) ) {
		$args['daily'] = 1;
		$args['manual'] = 1;
	} else {
		$args .= '&daily=1&is_manual=1';
	}

	tptn_show_pop_posts( $args );
}


/**
 * Function to add CSS to header.
 *
 * @since	1.9
 */
function tptn_header() {
	global $tptn_settings;

	$tptn_custom_CSS = stripslashes( $tptn_settings['custom_CSS'] );

	// Add CSS to header
	if ( '' != $tptn_custom_CSS ) {
		echo '<style type="text/css">' . $tptn_custom_CSS . '</style>';
	}
}
add_action( 'wp_head', 'tptn_header' );


/**
 * Enqueue styles.
 */
function tptn_heading_styles() {
	global $tptn_settings;

	if ( 'left_thumbs' == $tptn_settings['tptn_styles'] ) {
		wp_register_style( 'tptn-style-left-thumbs', plugins_url( 'css/default-style.css', __FILE__ ) );
		wp_enqueue_style( 'tptn-style-left-thumbs' );

		$custom_css = "
img.tptn_thumb {
  width: {$tptn_settings['thumb_width']}px !important;
  height: {$tptn_settings['thumb_height']}px !important;
}
                ";

		wp_add_inline_style( 'tptn-style-left-thumbs', $custom_css );

	}

}
add_action( 'wp_enqueue_scripts', 'tptn_heading_styles' );


/**
 * Default Options.
 */
function tptn_default_options() {
	global $tptn_url;

	$title = __( '<h3>Popular Posts</h3>', 'top-10' );
	$title_daily = __( '<h3>Daily Popular</h3>', 'top-10' );
	$blank_output_text = __( 'No top posts yet', 'top-10' );
	$thumb_default = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) . '/default.png';

	// get relevant post types
	$args = array(
		'public' => true,
		'_builtin' => true,
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$tptn_settings = array(

		/* General options */
		'activate_daily' => true,	// Activate the daily count
		'activate_overall' => true,	// activate overall count
		'cache' => false,			// Enable Caching using Transienst API
		'cache_time' => HOUR_IN_SECONDS,			// Cache for 1 Hour
		'cache_fix' => true,		// Fix for W3 Total Cache - Uses Ajax
		'daily_midnight' => true,		// Start daily counts from midnight (default as old behaviour)
		'daily_range' => '1',				// Daily Popular will contain posts of how many days?
		'hour_range' => '0',				// Daily Popular will contain posts of how many days?
		'uninstall_clean_options' => true,	// Cleanup options
		'uninstall_clean_tables' => false,	// Cleanup tables
		'show_metabox'	=> true,	// Show metabox to admins
		'show_metabox_admins'	=> false,	// Limit to admins as well
		'show_credit' => false,			// Add link to plugin page of my blog in top posts list

		/* Counter and tracker options */
		'add_to_content' => true,			// Add post count to content (only on single posts)
		'count_on_pages' => true,			// Add post count to pages
		'add_to_feed' => false,		// Add post count to feed (full)
		'add_to_home' => false,		// Add post count to home page
		'add_to_category_archives' => false,		// Add post count to category archives
		'add_to_tag_archives' => false,		// Add post count to tag archives
		'add_to_archives' => false,		// Add post count to other archives

		'count_disp_form' => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count
		'count_disp_form_zero' => 'No visits yet',	// What to display where there are no hits?
		'dynamic_post_count' => false,		// Use JavaScript for displaying the post count

		'track_authors' => false,			// Track Authors visits
		'track_admins' => true,			// Track Admin visits
		'track_editors' => true,			// Track Admin visits
		'pv_in_admin' => true,			// Add an extra column on edit posts/pages to display page views?
		'show_count_non_admins' => true,	// Show counts to non-admins

		/* Popular post list options */
		'limit' => '10',					// How many posts to display?
		'how_old' => '0',					// How old posts? Default is no limit
		'post_types' => $post_types,		// WordPress custom post types
		'exclude_categories' => '',		// Exclude these categories
		'exclude_cat_slugs' => '',		// Exclude these categories (slugs)
		'exclude_post_ids' => '',	// Comma separated list of page / post IDs that are to be excluded in the results

		'title' => $title,				// Title of Popular Posts
		'title_daily' => $title_daily,	// Title of Daily Popular
		'blank_output' => false,		// Blank output? Default is "blank Output test"
		'blank_output_text' => $blank_output_text,		// Blank output text

		'show_excerpt' => false,			// Show description in list item
		'excerpt_length' => '10',			// Length of characters
		'show_date' => false,			// Show date in list item
		'show_author' => false,			// Show author in list item
		'title_length' => '60',		// Limit length of post title
		'disp_list_count' => true,		// Display count in popular lists?

		'link_new_window' => false,			// Open link in new window - Includes target="_blank" to links
		'link_nofollow' => false,			// Includes rel="nofollow" to links
		'exclude_on_post_ids' => '', 	// Comma separate list of page/post IDs to not display related posts on

		// List HTML options
		'before_list' => '<ul>',			// Before the entire list
		'after_list' => '</ul>',			// After the entire list
		'before_list_item' => '<li>',		// Before each list item
		'after_list_item' => '</li>',		// After each list item

		/* Thumbnail options */
		'post_thumb_op' => 'text_only',	// Display only text in posts
		'thumb_size' => 'tptn_thumbnail',	// Default thumbnail size
		'thumb_width' => '150',			// Max width of thumbnails
		'thumb_height' => '150',			// Max height of thumbnails
		'thumb_crop' => true,		// Crop mode. default is hard crop
		'thumb_html' => 'html',		// Use HTML or CSS for width and height of the thumbnail?

		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'scan_images' => true,			// Scan post for images
		'thumb_default' => $thumb_default,	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)

		/* Custom styles */
		'custom_CSS' => '',			// Custom CSS to style the output
		'include_default_style' => false,	// Include default Top 10 style
		'tptn_styles'	=> 'no_style',	// Defaault style is left thubnails

		/* Maintenance cron */
		'cron_on' => false,		// Run cron daily?
		'cron_hour' => '0',		// Cron Hour
		'cron_min' => '0',		// Cron Minute
		'cron_recurrence' => 'weekly',	// Frequency of cron
	);

	/**
	 * Filters the default options array.
	 *
	 * @since	1.9.10.1
	 *
	 * @param	array	$tptn_settings	Default options
	 */
	return apply_filters( 'tptn_default_options', $tptn_settings );
}


/**
 * Function to read options from the database.
 *
 * @access public
 * @return void
 */
function tptn_read_options() {

	// Upgrade table code
	global $tptn_db_version, $network_wide;

	$tptn_settings_changed = false;

	$defaults = tptn_default_options();

	$tptn_settings = array_map( 'stripslashes', (array) get_option( 'ald_tptn_settings' ) );
	unset( $tptn_settings[0] ); // produced by the (array) casting when there's nothing in the DB

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $tptn_settings[ $k ] ) ) {
			$tptn_settings[ $k ] = $v;
			$tptn_settings_changed = true;
		}
	}
	if ( $tptn_settings_changed == true ) {
		update_option( 'ald_tptn_settings', $tptn_settings );
	}

	/**
	 * Filters the options array.
	 *
	 * @since	1.9.10.1
	 *
	 * @param	array	$tptn_settings	Options read from the database
	 */
	return apply_filters( 'tptn_read_options', $tptn_settings );
}


/**
 * Fired when the plugin is Network Activated.
 *
 * @since 1.9.10.1
 *
 * @param    boolean $network_wide    True if WPMU superadmin uses
 *                                    "Network Activate" action, false if
 *                                    WPMU is disabled or plugin is
 *                                    activated on an individual blog.
 */
function tptn_activation_hook( $network_wide ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "
        	SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0' AND deleted = '0'
		" );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			tptn_single_activate();
		}

		// Switch back to the current blog
		restore_current_blog();

	} else {
		tptn_single_activate();
	}
}
register_activation_hook( __FILE__, 'tptn_activation_hook' );


/**
 * Fired for each blog when the plugin is activated.
 *
 * @since 2.0.0
 */
function tptn_single_activate() {
	global $wpdb, $tptn_db_version;

	$tptn_settings = tptn_read_options();

	$table_name = $wpdb->base_prefix . 'top_ten';
	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

		$sql = 'CREATE TABLE ' . $table_name . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, blog_id)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_site_option( 'tptn_db_version', $tptn_db_version );
	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) != $table_name_daily ) {

		$sql = 'CREATE TABLE ' . $table_name_daily . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			dp_date DATETIME NOT NULL,
			blog_id bigint(20) NOT NULL DEFAULT '1',
			PRIMARY KEY  (postnumber, dp_date, blog_id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_site_option( 'tptn_db_version', $tptn_db_version );
	}

	// Upgrade table code
	$installed_ver = get_site_option( 'tptn_db_version' );

	if ( $installed_ver != $tptn_db_version ) {

		$wpdb->hide_errors();

		switch ( $installed_ver ) {

			case '4.0':
			case 4.0:
				$wpdb->query( 'ALTER TABLE ' . $table_name . " CHANGE blog_id blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . " CHANGE blog_id blog_id bigint(20) NOT NULL DEFAULT '1'" );
				break;

			default:

				$wpdb->query( 'ALTER TABLE ' . $table_name . ' MODIFY postnumber bigint(20) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' MODIFY postnumber bigint(20) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name . ' MODIFY cntaccess bigint(20) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' MODIFY cntaccess bigint(20) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' MODIFY dp_date DATETIME ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY, ADD PRIMARY KEY(postnumber, blog_id) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . ' DROP PRIMARY KEY, ADD PRIMARY KEY(postnumber, dp_date, blog_id) ' );
				$wpdb->query( 'ALTER TABLE ' . $table_name . " ADD blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( 'ALTER TABLE ' . $table_name_daily . " ADD blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( 'UPDATE ' . $table_name . ' SET blog_id = 1 WHERE blog_id = 0 ' );
				$wpdb->query( 'UPDATE ' . $table_name_daily . ' SET blog_id = 1 WHERE blog_id = 0 ' );

		}

		$wpdb->show_errors();

		update_site_option( 'tptn_db_version', $tptn_db_version );
	}

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    int $blog_id    ID of the new blog.
 */
function tptn_activate_new_site( $blog_id ) {

	if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
		return;
	}

	switch_to_blog( $blog_id );
	tptn_single_activate();
	restore_current_blog();

}
add_action( 'wpmu_new_blog', 'tptn_activate_new_site' );


/**
 * Fired when a site is deleted in a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    array $tables    Tables in the blog.
 */
function tptn_on_delete_blog( $tables ) {
	global $wpdb;

	$tables[] = $wpdb->prefix . 'top_ten';
	$tables[] = $wpdb->prefix . 'top_ten_daily';

	return $tables;
}
add_filter( 'wpmu_drop_tables', 'tptn_on_delete_blog' );


/**
 * Function to call install function if needed.
 *
 * @since	1.9
 */
function tptn_update_db_check() {
	global $tptn_db_version, $network_wide;

	if ( get_site_option( 'tptn_db_version' ) != $tptn_db_version ) {
		tptn_activation_hook( $network_wide );
	}
}
add_action( 'plugins_loaded', 'tptn_update_db_check' );


/**
 * Function to delete all rows in the posts table.
 *
 * @since	1.3
 * @param	bool $daily  Daily flag
 */
function tptn_trunc_count( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );
}


/**
 * Function to create an excerpt for the post.
 *
 * @since	1.6
 * @param	int        $id             Post ID
 * @param	int|string $excerpt_length Length of the excerpt in words
 * @return	string 		Excerpt
 */
function tptn_excerpt( $id, $excerpt_length = 0, $use_excerpt = true ) {
	$content = $excerpt = '';

	if ( $use_excerpt ) {
		$content = get_post( $id )->post_excerpt;
	}

	if ( '' == $content ) {
		$content = get_post( $id )->post_content;
	}

	$output = strip_tags( strip_shortcodes( $content ) );

	if ( $excerpt_length > 0 ) {
		$output = wp_trim_words( $output, $excerpt_length );
	}

	/**
	 * Filters excerpt generated by tptn.
	 *
	 * @since	1.9.10.1
	 *
	 * @param	array	$output			Formatted excerpt
	 * @param	int		$id				Post ID
	 * @param	int		$excerpt_length	Length of the excerpt
	 * @param	boolean	$use_excerpt	Use the excerpt?
	 */
	return apply_filters( 'tptn_excerpt', $output, $id, $excerpt_length, $use_excerpt );
}


/**
 * Function to limit content by characters.
 *
 * @since	1.9.8
 * @param	string $content    Content to be used to make an excerpt
 * @param	int    $no_of_char Maximum length of excerpt in characters
 * @return 	string				Formatted content
 */
function tptn_max_formatted_content( $content, $no_of_char = -1 ) {
	$content = strip_tags( $content );  // Remove CRLFs, leaving space in their wake

	if ( ( $no_of_char > 0 ) && ( strlen( $content ) > $no_of_char ) ) {
		$aWords = preg_split( '/[\s]+/', substr( $content, 0, $no_of_char ) );

		// Break back down into a string of words, but drop the last one if it's chopped off
		if ( substr( $content, $no_of_char, 1 ) == ' ' ) {
			$content = implode( ' ', $aWords );
		} else {
			$content = implode( ' ', array_slice( $aWords, 0, -1 ) ) .'&hellip;';
		}
	}

	/**
	 * Filters formatted content after cropping.
	 *
	 * @since	1.9.10.1
	 *
	 * @param	string	$content	Formatted content
	 * @param	int		$no_of_char	Maximum length of excerpt in characters
	 */
	return apply_filters( 'tptn_max_formatted_content' , $content, $no_of_char );
}


/**
 * Function to truncate daily run.
 *
 * @since	1.9.9.1
 */
function tptn_cron() {
	global $tptn_settings, $wpdb;

	$table_name_daily = $wpdb->base_prefix . 'top_ten_daily';

	$current_time = current_time( 'timestamp', 0 );
	$from_date = strtotime( '-90 DAY' , $current_time );
	$from_date = gmdate( 'Y-m-d H' , $from_date );

	$resultscount = $wpdb->query( $wpdb->prepare(
		"DELETE FROM {$table_name_daily} WHERE dp_date <= '%s' ",
		$from_date
	) );

}
add_action( 'tptn_cron_hook', 'tptn_cron' );


/**
 * Function to enable run or actions.
 *
 * @since	1.9
 * @param	int	$hour		Hour
 * @param	int	$min		Minute
 * @param	int	$recurrence	Frequency
 */
function tptn_enable_run( $hour, $min, $recurrence ) {
	// Invoke WordPress internal cron
	if ( ! wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'tptn_cron_hook' );
	} else {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'tptn_cron_hook' );
	}
}


/**
 * Function to disable daily run or actions.
 *
 * @since	1.9
 */
function tptn_disable_run() {
	if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
		wp_clear_scheduled_hook( 'tptn_cron_hook' );
	}
}

// Let's declare this conditional function to add more schedules. It will be a generic function across all plugins that I develop
if ( ! function_exists( 'ald_more_reccurences' ) ) :

	/**
	 * Function to add weekly and fortnightly recurrences. Filters `cron_schedules`.
	 *
	 * @param	array	Array of existing schedules
	 * @return	array	Filtered array with new schedules
	 */
	function ald_more_reccurences( $schedules ) {
		// add a 'weekly' interval
		$schedules['weekly'] = array(
		'interval' => WEEK_IN_SECONDS,
		'display' => __( 'Once Weekly', 'top-10' ),
		);
		$schedules['fortnightly'] = array(
		'interval' => 2 * WEEK_IN_SECONDS,
		'display' => __( 'Once Fortnightly', 'top-10' ),
		);
		$schedules['monthly'] = array(
		'interval' => 30 * DAY_IN_SECONDS,
		'display' => __( 'Once Monthly', 'top-10' ),
		);
		$schedules['quarterly'] = array(
		'interval' => 90 * DAY_IN_SECONDS,
		'display' => __( 'Once quarterly', 'top-10' ),
		);
		return $schedules;
	}
	add_filter( 'cron_schedules', 'ald_more_reccurences' );

endif;


/**
 * Get all image sizes.
 *
 * @since	2.0.0
 * @param	string $size   Get specific image size
 * @return	array	Image size names along with width, height and crop setting
 */
function tptn_get_all_image_sizes( $size = '' ) {
	global $_wp_additional_image_sizes;

	/* Get the intermediate image sizes and add the full size to the array. */
	$intermediate_image_sizes = get_intermediate_image_sizes();

	foreach ( $intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

			$sizes[ $_size ]['name'] = $_size;
			$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

	        if ( ( 0 == $sizes[ $_size ]['width'] ) && ( 0 == $sizes[ $_size ]['height'] ) ) {
	            unset( $sizes[ $_size ] );
	        }
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

			$sizes[ $_size ] = array(
	            'name' => $_size,
				'width' => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop' => (bool) $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	/* Get only 1 size if found */
	if ( $size ) {
		if ( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}

	/**
	 * Filters array of image sizes.
	 *
	 * @since	2.0.0
	 *
	 * @param	array	$sizes	Image sizes
	 */
	return apply_filters( 'tptn_get_all_image_sizes', $sizes );
}


/**
 * Returns the object identifier for the current language (WPML).
 *
 * @since	2.1.0
 *
 * @param	$post_id	Post ID
 */
function tptn_object_id_cur_lang( $post_id ) {

	$return_original_if_missing = false;

	/**
	 * Filter to modify if the original language ID is returned.
	 *
	 * @since	2.2.3
	 *
	 * @param	bool	$return_original_if_missing
	 * @param	int	$post_id	Post ID
	 */
	$return_original_if_missing = apply_filters( 'tptn_wpml_return_original', $return_original_if_missing, $post_id );

	if ( function_exists( 'wpml_object_id_filter' ) ) {
		$post_id = wpml_object_id_filter( $post_id, 'any', $return_original_if_missing );
	} elseif ( function_exists( 'icl_object_id' ) ) {
		$post_id = icl_object_id( $post_id, 'any', $return_original_if_missing );
	}

	/**
	 * Filters object ID for current language (WPML).
	 *
	 * @since	2.1.0
	 *
	 * @param	int	$post_id	Post ID
	 */
	return apply_filters( 'tptn_object_id_cur_lang', $post_id );
}


/*
 ----------------------------------------------------------------------------*
 * WordPress widget
 *----------------------------------------------------------------------------*/

/**
 * Include Widget class.
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-top-10-widget.php' );


/*
 ----------------------------------------------------------------------------*
 * Top 10 modules
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'includes/counter.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/media.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/output-generator.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/exclusions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/modules/taxonomies.php' );


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

// This function adds an Options page in WP Admin
if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-metabox.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-columns.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-dashboard.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-stats.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/cache.php' );

} // End admin.inc

/*
 ----------------------------------------------------------------------------*
 * Deprecated functions
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'includes/deprecated.php' );

if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/deprecated.php' );
}
