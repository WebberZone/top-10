<?php
/**
 * Top 10.
 *
 * Count daily and total visits per post and display the most popular posts based on the number of views.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
 * @copyright 2008-2015 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name:	Top 10
 * Plugin URI:	http://ajaydsouza.com/wordpress/plugins/top-10/
 * Description:	Count daily and total visits per post and display the most popular posts based on the number of views
 * Version: 	2.0.3
 * Author: 		Ajay D'Souza
 * Author URI: 	http://ajaydsouza.com
 * Text Domain:	tptn
 * License: 	GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:	/languages
 * GitHub Plugin URI: https://github.com/ajaydsouza/top-10/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the text domain.
 *
 * @since	1.4
 */
define( 'TPTN_LOCAL_NAME', 'tptn' );


/**
 * Holds the filesystem directory path.
 *
 * @since	1.0
 */
define( 'ALD_TPTN_DIR', dirname( __FILE__ ) );


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
$tptn_db_version = "5.0";


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
	load_plugin_textdomain( TPTN_LOCAL_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'tptn_lang_init' );


/**
 * Function to update the post views for the current post. Filters `the_content`.
 *
 * @since	1.0
 *
 * @param	string	$content	Post content
 * @return	string	Filtered content
 */
function tptn_add_viewed_count( $content ) {
	global $post, $wpdb, $single, $tptn_url, $tptn_path, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";

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
 *
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
 * @param	array	$vars	Query variables array
 * @return	array	$Query variables array with Top 10 parameters appended
 */
function tptn_query_vars( $vars ) {
	//add these to the list of queryvars that WP gathers
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
 * @param	object	$wp	WordPress object
 */
function tptn_parse_request( $wp ) {
   	global $wpdb, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";
	$top_ten_daily = $wpdb->base_prefix . "top_ten_daily";
	$str = '';

	if ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'activate_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );
		$blog_id = intval( $wp->query_vars['top_ten_blog_id'] );
		$activate_counter = intval( $wp->query_vars['activate_counter'] );

		if ( $id > 0 ) {

			if ( ( 1 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '1', '%d') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) );

				$str .= ( FALSE === $tt ) ? 'tte' : 'tt' . $tt;
			}

			if ( ( 10 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$current_date = gmdate( 'Y-m-d H', current_time( 'timestamp', 0 ) );

				$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$top_ten_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES('%d', '1', '%s', '%d' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) );

				$str .= ( FALSE === $ttd ) ? ' ttde' : ' ttd' . $ttd;
			}
		}
		Header( "content-type: application/x-javascript" );
		echo '<!-- ' . $str . ' -->';

		//stop anything else from loading as it is not needed.
		exit;

	} elseif ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'view_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );

		if ( $id > 0 ) {

			$output = get_tptn_post_count( $id );

			Header( "content-type: application/x-javascript" );
			echo 'document.write("' . $output . '")';

			//stop anything else from loading as it is not needed.
			exit;
		}

	} else {
		return;
	}
}
add_action( 'wp', 'tptn_parse_request' );


/**
 * Function to add the viewed count to the post content. Filters `the_content`.
 *
 * @since	1.0
 * @param	string	$content	Post content
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
 * @param	string	$content	Post content
 * @return	string	Filtered post content
 */
function ald_tptn_rss( $content ) {
	global $post, $tptn_settings;

	$id = intval( $post->ID );

	if ( $tptn_settings['add_to_feed'] ) {
        return $content . '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . get_tptn_post_count( $id ) . '</div>';
    } else {
        return $content;
    }
}
add_filter( 'the_excerpt_rss', 'ald_tptn_rss' );
add_filter( 'the_content_feed', 'ald_tptn_rss' );


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
 * @param	int|string	$id			Post ID
 * @param	int|string	$blog_id	Blog ID
 * @return	int|string	Formatted post count
 */
function get_tptn_post_count( $id = FALSE, $blog_id = FALSE ) {
	global $wpdb, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";
	$table_name_daily = $wpdb->base_prefix . "top_ten_daily";

	$count_disp_form = stripslashes( $tptn_settings['count_disp_form'] );
	$count_disp_form_zero = stripslashes( $tptn_settings['count_disp_form_zero'] );
	$totalcntaccess = get_tptn_post_count_only( $id, 'total', $blog_id );

	if ( $id > 0 ) {

		// Total count per post
		if ( ( false !== strpos( $count_disp_form, "%totalcount%" ) ) || ( false !== strpos( $count_disp_form_zero, "%totalcount%" ) ) ) {
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( "%totalcount%", $totalcntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( "%totalcount%", ( 0 == $totalcntaccess ? $totalcntaccess + 1 : $totalcntaccess ), $count_disp_form );
			}
		}

		// Now process daily count
		if ( ( false !== strpos( $count_disp_form, "%dailycount%" ) ) || ( false !== strpos( $count_disp_form_zero, "%dailycount%" ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'daily' );
			if ( ( 0 == $totalcntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( "%dailycount%", $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( "%dailycount%", ( 0 == $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
			}
		}

		// Now process overall count
		if ( ( false !== strpos( $count_disp_form, "%overallcount%" ) ) || ( false !== strpos( $count_disp_form_zero, "%overallcount%" ) ) ) {
			$cntaccess = get_tptn_post_count_only( $id, 'overall' );
			if ( ( 0 == $cntaccess ) && ( ! is_singular() ) ) {
				$count_disp_form_zero = str_replace( "%overallcount%", $cntaccess, $count_disp_form_zero );
			} else {
				$count_disp_form = str_replace( "%overallcount%", ( 0 == $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
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
 * @param	mixed	$id 	Post ID
 * @param	string	$count	Which count to return? total, daily or overall
 * @return	int		Post count
 */
function get_tptn_post_count_only( $id = FALSE, $count = 'total', $blog_id = FALSE ) {
	global $wpdb, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";
	$table_name_daily = $wpdb->base_prefix . "top_ten_daily";

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
				$resultscount = $wpdb->get_row( "SELECT SUM(cntaccess) as sumCount FROM " . $table_name );
				$cntaccess = number_format_i18n( ( ( $resultscount ) ? $resultscount->sumCount : 0 ) );
				break;
		}
		return apply_filters( 'tptn_post_count_only', $cntaccess );
	} else {
		return 0;
	}
}


/**
 * Function to return popular posts.
 *
 * @since	1.5
 *
 * @param	mixed	$args	Arguments array
 * @return	array|string	Array of posts if posts_only = 0 or a formatted string if posts_only = 1
 */
function tptn_pop_posts( $args ) {
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
		'is_widget' => FALSE,
		'daily' => FALSE,
		'echo' => FALSE,
		'strict_limit' => FALSE,
		'posts_only' => FALSE,
		'is_shortcode' => FALSE,
		'heading' => 1,
	);

	// Merge the $defaults array with the $tptn_settings array
	$defaults = array_merge( $defaults, $tptn_settings );

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$tptn_thumb_size = tptn_get_all_image_sizes( $thumb_size );

	if ( isset( $tptn_thumb_size['width'] ) ) {
		$thumb_width = $tptn_thumb_size['width'];
		$thumb_height = $tptn_thumb_size['height'];
	}

	if ( empty( $thumb_width ) ) {
		$thumb_width = $tptn_settings['thumb_width'];
	}

	if ( empty( $thumb_height ) ) {
		$thumb_height = $tptn_settings['thumb_height'];
	}

	if ( $daily ) {
		$table_name = $wpdb->base_prefix . "top_ten_daily";
	} else {
		$table_name = $wpdb->base_prefix . "top_ten";
	}

	$limit = ( $strict_limit ) ? $limit : ( $limit * 5 );

	$exclude_categories = explode( ',', $exclude_categories );

	$target_attribute = ( $link_new_window ) ? ' target="_blank" ' : ' ';	// Set Target attribute
	$rel_attribute = ( $link_nofollow ) ? 'bookmark nofollow' : 'bookmark';	// Set nofollow attribute

	parse_str( $post_types, $post_types );	// Save post types in $post_types variable

	if ( empty( $post_types ) ) {
		$post_types = get_post_types( array(
			'public'	=> true,
		) );
	}

	$blog_id = get_current_blog_id();


	if ( $daily_midnight ) {
		$current_time = current_time( 'timestamp', 0 );
		$from_date = $current_time - ( max( 0, ( $daily_range - 1 ) ) * DAY_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d 0' , $from_date );
	} else {
		$current_time = current_time( 'timestamp', 0 );
		$from_date = $current_time - ( $daily_range * DAY_IN_SECONDS + $hour_range * HOUR_IN_SECONDS );
		$from_date = gmdate( 'Y-m-d H' , $from_date );
	}

	/**
	 *
	 * We're going to create a mySQL query that is fully extendable which would look something like this:
	 * "SELECT $fields FROM $wpdb->posts $join WHERE 1=1 $where $groupby $orderby $limits"
	 *
	 */

	// Fields to return
	$fields = " postnumber, ";
	$fields .= ( $daily ) ? "SUM(cntaccess) as sumCount, dp_date, " : "cntaccess as sumCount, ";
	$fields .= "ID ";

	// Create the JOIN clause
	$join = " INNER JOIN {$wpdb->posts} ON postnumber=ID ";

	// Create the base WHERE clause
	$where .= $wpdb->prepare( " AND blog_id = %d ", $blog_id );				// Posts need to be from the current blog only
	$where .= " AND $wpdb->posts.post_status = 'publish' ";					// Only show published posts

	if ( $daily ) {
		$where .= $wpdb->prepare( " AND dp_date >= '%s' ", $from_date );	// Only fetch posts that are tracked after this date
	}

	if ( '' != $exclude_post_ids ) {
		$where .= " AND $wpdb->posts.ID NOT IN ({$exclude_post_ids}) ";
	}
	$where .= " AND $wpdb->posts.post_type IN ('" . join( "', '", $post_types ) . "') ";	// Array of post types

	// Create the base GROUP BY clause
	if ( $daily ) {
		$groupby = " postnumber ";
	}

	// Create the base ORDER BY clause
	$orderby = " sumCount DESC ";

	// Create the base LIMITS clause
	$limits .= $wpdb->prepare( " LIMIT %d ", $limit );

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

	if ( $posts_only ) {	// Return the array of posts only if the variable is set

		$tptn_pop_posts_array = $wpdb->get_results( $sql , ARRAY_A );

		/**
		 * Filter the array of top post IDs.
		 *
		 * @since	1.9.8.5
		 *
		 * @param	array   $tptn_pop_posts_array	Posts array.
		 */
		return apply_filters( 'tptn_pop_posts_array', $tptn_pop_posts_array );
	}

	$results = $wpdb->get_results( $sql );

	$counter = 0;

	$output = '';

	$shortcode_class = $is_shortcode ? ' tptn_posts_shortcode' : '';
	$widget_class = $is_widget ? ' tptn_posts_widget' : '';

	if ( $heading ) {
		if ( ! $daily ) {
			$output .= '<div id="tptn_related" class="tptn_posts ' . $widget_class . $shortcode_class . '">';

			/**
			 * Filter the title of the Top posts.
			 *
			 * @since	1.9.5
			 *
			 * @param	string   $title	Title of the popular posts.
			 */
			$output .= apply_filters( 'tptn_heading_title', $title );
		} else {
			$output .= '<div id="tptn_related_daily" class="tptn_posts_daily' . $shortcode_class . '">';

			/**
			 * Filter the title of the Top posts.
			 *
			 * @since	1.9.5
			 *
			 * @param	string   $title	Title of the popular posts.
			 */
			$output .= apply_filters( 'tptn_heading_title', $title_daily );
		}
	} else {
		if ( ! $daily ) {
			$output .= '<div class="tptn_posts' . $widget_class . $shortcode_class . '">';
		} else {
			$output .= '<div class="tptn_posts_daily' . $widget_class . $shortcode_class . '">';
		}
	}

	if ( $results ) {

		/**
		 * Filter the opening tag of the popular posts list
		 *
		 * @since	1.9.10.1
		 *
		 * @param	string	$before_list	Opening tag set in the Settings Page
		 */
		$output .= apply_filters( 'tptn_before_list', $before_list );

		foreach ( $results as $result ) {
			$sumcount = $result->sumCount;

			$result = get_post( $result->ID );	// Let's get the Post using the ID

			/**
			 * Filter the post ID for each result. Allows a custom function to hook in and change the ID if needed.
			 *
			 * @since	1.9.8.5
			 *
			 * @param	int	$result->ID	ID of the post
			 */
			$postid = apply_filters( 'tptn_post_id', $result->ID );

			/**
			 * Filter the post ID for each result. This filtered ID is passed as a parameter to fetch categories.
			 *
			 * This is useful since you might want to fetch a different set of categories for a linked post ID,
			 * typically in the case of plugins that let you set mutiple languages
			 *
			 * @since	1.9.8.5
			 *
			 * @param	int	$result->ID	ID of the post
			 */
			$categorys = get_the_category( apply_filters( 'tptn_post_cat_id', $result->ID ) );	//Fetch categories of the plugin

			$p_in_c = false;	// Variable to check if post exists in a particular category

			foreach ( $categorys as $cat ) {	// Loop to check if post exists in excluded category
				$p_in_c = ( in_array( $cat->cat_ID, $exclude_categories ) ) ? true : false;
				if ( $p_in_c ) break;	// End loop if post found in category
			}

			$post_title = tptn_max_formatted_content( get_the_title( $postid ), $title_length );

			/**
			 * Filter the post title of each list item.
			 *
			 * @since	2.0.0
			 *
			 * @param	string	$post_title	Post title in the list.
			 * @param	object	$result	Object of the current post result
			 */
			$post_title = apply_filters( 'tptn_post_title', $post_title, $result );


			if ( ! $p_in_c ) {

				/**
				 * Filter the opening tag of each list item.
				 *
				 * @since	1.9.10.1
				 *
				 * @param	string	$before_list_item	Tag before each list item. Can be defined in the Settings page.
				 * @param	object	$result				Object of the current post result
				 */
				$output .= apply_filters( 'tptn_before_list_item', $before_list_item, $result );

				/**
				 * Filter the `rel` attribute each list item.
				 *
				 * @since	1.9.10.1
				 *
				 * @param	string	$rel_attribute	rel attribute
				 * @param	object	$result			Object of the current post result
				 */
				$rel_attribute = apply_filters( 'tptn_rel_attribute', $rel_attribute, $result );

				/**
				 * Filter the target attribute each list item.
				 *
				 * @since	1.9.10.1
				 *
				 * @param	string	$target_attribute	target attribute
				 * @param	object	$result				Object of the current post result
				 */
				$target_attribute = apply_filters( 'tptn_rel_attribute', $target_attribute, $result );


				if ( 'after' == $post_thumb_op ) {
					$output .= '<a href="' . get_permalink( $postid ) . '" rel="' . $rel_attribute . '" ' . $target_attribute . 'class="tptn_link">'; // Add beginning of link
					$output .= '<span class="tptn_title">' . $post_title . '</span>'; // Add title if post thumbnail is to be displayed after
					$output .= '</a>'; // Close the link
				}

				if ( 'inline' == $post_thumb_op || 'after' == $post_thumb_op || 'thumbs_only' == $post_thumb_op ) {
					$output .= '<a href="' . get_permalink( $postid ) . '" rel="' . $rel_attribute . '" ' . $target_attribute . 'class="tptn_link">'; // Add beginning of link

					$output .= tptn_get_the_post_thumbnail( array(
						'postid' => $postid,
						'thumb_height' => $thumb_height,
						'thumb_width' => $thumb_width,
						'thumb_meta' => $thumb_meta,
						'thumb_html' => $thumb_html,
						'thumb_default' => $thumb_default,
						'thumb_default_show' => $thumb_default_show,
						'thumb_timthumb' => $thumb_timthumb,
						'thumb_timthumb_q' => $thumb_timthumb_q,
						'scan_images' => $scan_images,
						'class' => "tptn_thumb",
						'filter' => "tptn_postimage",
					) );

					$output .= '</a>'; // Close the link
				}

				if ( 'inline' == $post_thumb_op || 'text_only' == $post_thumb_op ) {
					$output .= '<span class="tptn_after_thumb">';
					$output .= '<a href="' . get_permalink( $postid ) . '" rel="' . $rel_attribute . '" ' . $target_attribute . 'class="tptn_link">'; // Add beginning of link
					$output .= '<span class="tptn_title">' . $post_title . '</span>'; // Add title when required by settings
					$output .= '</a>'; // Close the link
				}

				if ( $show_author ) {
					$author_info = get_userdata( $result->post_author );
					$author_name = ucwords( trim( stripslashes( $author_info->display_name ) ) );
					$author_link = get_author_posts_url( $author_info->ID );

					/**
					 * Filter the author name.
					 *
					 * @since	1.9.1
					 *
					 * @param	string	$author_name	Proper name of the post author.
					 * @param	object	$author_info	WP_User object of the post author
					 */
					$author_name = apply_filters( 'tptn_author_name', $author_name, $author_info );

					$tptn_author = '<span class="tptn_author"> ' . __( ' by ', TPTN_LOCAL_NAME ).'<a href="' . $author_link . '">' . $author_name . '</a></span> ';

					/**
					 * Filter the text with the author details.
					 *
					 * @since	2.0.0
					 *
					 * @param	string	$tptn_author	Formatted string with author details and link
					 * @param	object	$author_info	WP_User object of the post author
					 */
					$tptn_author = apply_filters( 'tptn_author', $tptn_author, $author_info );

					$output .= $tptn_author;
				}

				if ( $show_date ) {
					$output .= '<span class="tptn_date"> ' . mysql2date( get_option( 'date_format', 'd/m/y' ), $result->post_date ).'</span> ';
				}

				if ( $show_excerpt ) {
					$output .= '<span class="tptn_excerpt"> ' . tptn_excerpt( $postid, $excerpt_length ).'</span>';
				}

				if ( $disp_list_count ) {
					$output .= ' <span class="tptn_list_count">(' . number_format_i18n( $sumcount ) . ')</span>';
				}

				if ( 'inline' == $post_thumb_op || 'text_only' == $post_thumb_op ) {
					$output .= '</span>';
				}

				/**
				 * Filter the closing tag of each list item.
				 *
				 * @since	1.9.10.1
				 *
				 * @param	string	$after_list_item	Tag after each list item. Can be defined in the Settings page.
				 * @param	object	$result	Object of the current post result
				 */
				$output .= apply_filters( 'tptn_after_list_item', $after_list_item, $result );

				$counter++;
			}
			if ( $counter == $limit/5 ) break;	// End loop when related posts limit is reached
		}
		if ( $show_credit ) {

			/** This filter is documented in contextual-related-posts.php */
			$output .= apply_filters( 'tptn_before_list_item', $before_list_item, $result );

			$output .= sprintf(
				__( 'Popular posts by <a href="%s" rel="nofollow" %s>Top 10 plugin</a>', TPTN_LOCAL_NAME ),
				esc_url( 'http://ajaydsouza.com/wordpress/plugins/top-10/' ),
				$target_attribute
			);

			/** This filter is documented in contextual-related-posts.php */
			$output .= apply_filters( 'tptn_after_list_item', $after_list_item, $result );
		}

		/**
		 * Filter the closing tag of the related posts list
		 *
		 * @since	1.9.10.1
		 *
		 * @param	string	$after_list	Closing tag set in the Settings Page
		 */
		$output .= apply_filters( 'tptn_after_list', $after_list );
	} else {
		$output .= ( $blank_output ) ? '' : $blank_output_text;
	}
	$output .= '</div>';

	/**
	 * Filter the output
	 *
	 * @since	1.9.8.5
	 *
	 * @param	string	$output	Formatted list of top posts
	 */
	return apply_filters( 'tptn_pop_posts', $output, $args );
}


/**
 * Function to echo popular posts.
 *
 * @since	1.0
 */
function tptn_show_pop_posts( $args = NULL ) {
	echo tptn_pop_posts( $args );
}


/**
 * Function to show daily popular posts.
 *
 * @since	1.2
 */
function tptn_show_daily_pop_posts() {
	global $tptn_url, $tptn_settings;

	if ( $tptn_settings['d_use_js'] ) {
		echo '<script type="text/javascript" src="' . $tptn_url . '/top-10-daily.js.php?widget=1"></script>';
	} else {
		echo tptn_pop_posts( 'daily=1&is_widget=0' );
	}
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
 *
 */
function tptn_heading_styles() {
	global $tptn_settings;

	if ( $tptn_settings['include_default_style'] ) {
		wp_register_style( 'tptn_list_style', plugins_url( 'css/default-style.css', __FILE__ ) );
		wp_enqueue_style( 'tptn_list_style' );
	}
}
add_action( 'wp_enqueue_scripts', 'tptn_heading_styles' );


/**
 * Default Options.
 *
 */
function tptn_default_options() {
	global $tptn_url;

	$title = __( '<h3>Popular Posts</h3>', TPTN_LOCAL_NAME );
	$title_daily = __( '<h3>Daily Popular</h3>', TPTN_LOCAL_NAME );
	$blank_output_text = __( 'No top posts yet', TPTN_LOCAL_NAME );
	$thumb_default = $tptn_url . '/default.png';

	$tptn_get_all_image_sizes = tptn_get_all_image_sizes();

	// get relevant post types
	$args = array (
		'public' => true,
		'_builtin' => true
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$tptn_settings = array (

		/* General options */
		'activate_daily' => true,	// Activate the daily count
		'activate_overall' => true,	// activate overall count
		'cache_fix' => true,		// Temporary fix for W3 Total Cache - Uses Ajax
		'daily_midnight' => true,		// Start daily counts from midnight (default as old behaviour)
		'daily_range' => '1',				// Daily Popular will contain posts of how many days?
		'hour_range' => '0',				// Daily Popular will contain posts of how many days?
		'uninstall_clean_options' => true,	// Cleanup options
		'uninstall_clean_tables' => false,	// Cleanup tables
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

		'd_use_js' => false,				// Use JavaScript for displaying daily posts	- TO BE DEPRECATED

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

		'thumb_timthumb' => false,	// Use timthumb	- TO BE DEPRECATED
		'thumb_timthumb_q' => '75',	// Quality attribute for timthumb	- TO BE DEPRECATED

		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'scan_images' => true,			// Scan post for images
		'thumb_default' => $thumb_default,	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)

		/* Custom styles */
		'custom_CSS' => '',			// Custom CSS to style the output
		'include_default_style' => false,	// Include default Top 10 style

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

	foreach ( $defaults as $k=>$v ) {
		if ( ! isset( $tptn_settings[$k] ) ) {
			$tptn_settings[ $k ] = $v;
			$tptn_settings_changed = true;
		}
	}
	if ( $tptn_settings_changed == true ) {
		update_option('ald_tptn_settings', $tptn_settings);
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
 * @param    boolean    $network_wide    True if WPMU superadmin uses
 *                                       "Network Activate" action, false if
 *                                       WPMU is disabled or plugin is
 *                                       activated on an individual blog.
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

	$table_name = $wpdb->base_prefix . "top_ten";
	$table_name_daily = $wpdb->base_prefix . "top_ten_daily";

	if ( $wpdb->get_var( "show tables like '$table_name'" ) != $table_name ) {

		$sql = "CREATE TABLE " . $table_name . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			blog_id bigint(20) NOT NULL,
			PRIMARY KEY  (postnumber, blog_id)
			);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_site_option( "tptn_db_version", $tptn_db_version );
	}

	if ( $wpdb->get_var( "show tables like '$table_name_daily'" ) != $table_name_daily ) {

		$sql = "CREATE TABLE " . $table_name_daily . " (
			postnumber bigint(20) NOT NULL,
			cntaccess bigint(20) NOT NULL,
			dp_date DATETIME NOT NULL,
			blog_id bigint(20) NOT NULL,
			PRIMARY KEY  (postnumber, dp_date, blog_id)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_site_option( "tptn_db_version", $tptn_db_version );
	}

	// Upgrade table code
	$installed_ver = get_site_option( "tptn_db_version" );

	if ( $installed_ver != $tptn_db_version ) {

		$wpdb->hide_errors();

		switch ( $installed_ver ) {

			case '4.0':
			case 4.0:
				$wpdb->query( "ALTER TABLE " . $table_name . " CHANGE blog_id blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " CHANGE blog_id blog_id bigint(20) NOT NULL DEFAULT '1'" );
				break;

			default:

				$wpdb->query( "ALTER TABLE " . $table_name . " MODIFY postnumber bigint(20) " );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " MODIFY postnumber bigint(20) " );
				$wpdb->query( "ALTER TABLE " . $table_name . " MODIFY cntaccess bigint(20) " );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " MODIFY cntaccess bigint(20) " );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " MODIFY dp_date DATETIME " );
				$wpdb->query( "ALTER TABLE " . $table_name . " DROP PRIMARY KEY, ADD PRIMARY KEY(postnumber, blog_id) " );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " DROP PRIMARY KEY, ADD PRIMARY KEY(postnumber, dp_date, blog_id) " );
				$wpdb->query( "ALTER TABLE " . $table_name . " ADD blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( "ALTER TABLE " . $table_name_daily . " ADD blog_id bigint(20) NOT NULL DEFAULT '1'" );
				$wpdb->query( "UPDATE " . $table_name . " SET blog_id = 1 WHERE blog_id = 0 " );
				$wpdb->query( "UPDATE " . $table_name_daily . " SET blog_id = 1 WHERE blog_id = 0 " );

		}

		$wpdb->show_errors();

		update_site_option( "tptn_db_version", $tptn_db_version );
	}

}


/**
 * Fired when a new site is activated with a WPMU environment.
 *
 * @since 2.0.0
 *
 * @param    int    $blog_id    ID of the new blog.
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
 * @param    array    $tables    Tables in the blog.
 */
function tptn_on_delete_blog( $tables ) {
    global $wpdb;

	$tables[] = $wpdb->prefix . "top_ten";
	$tables[] = $wpdb->prefix . "top_ten_daily";

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

    if ( get_site_option('tptn_db_version') != $tptn_db_version ) {
        tptn_activation_hook( $network_wide );
    }
}
add_action( 'plugins_loaded', 'tptn_update_db_check' );


/**
 * Function to delete all rows in the posts table.
 *
 * @since	1.3
 * @param	bool	$daily	Daily flag
 */
function tptn_trunc_count( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . "top_ten";
	if ( $daily ) {
		$table_name .= "_daily";
	}

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );
}


/**
 * Add custom image size of thumbnail. Filters `init`.
 *
 * @since 2.0.0
 *
 */
function tptn_add_image_sizes() {
	global $tptn_settings;

	if ( ! in_array( $tptn_settings['thumb_size'], get_intermediate_image_sizes() ) ) {
		$tptn_settings['thumb_size'] = 'tptn_thumbnail';
		update_option( 'ald_tptn_settings', $tptn_settings );
	}

	// Add image sizes if 'tptn_thumbnail' is selected or the selected thumbnail size is no longer valid
	if ( 'tptn_thumbnail' == $tptn_settings['thumb_size'] ) {
		$width = empty( $tptn_settings['thumb_width'] ) ? 150 : $tptn_settings['thumb_width'];
		$height = empty( $tptn_settings['thumb_height'] ) ? 150 : $tptn_settings['thumb_height'];
		$crop = isset( $tptn_settings['thumb_crop'] ) ? $tptn_settings['thumb_crop'] : false;

		add_image_size( 'tptn_thumbnail', $width, $height, $crop );
	}
}
add_action( 'init', 'tptn_add_image_sizes' );


/**
 * Function to get the post thumbnail.
 *
 * @since	1.8
 * @param	array	$args	Query string of options related to thumbnails
 * @return	string	Image tag
 */
function tptn_get_the_post_thumbnail( $args = array() ) {

	global $tptn_url, $tptn_settings;

	$defaults = array(
		'postid' => '',
		'thumb_height' => '150',			// Max height of thumbnails
		'thumb_width' => '150',			// Max width of thumbnails
		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'thumb_html' => 'html',		// HTML / CSS for width and height attributes
		'thumb_default' => '',	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
		'thumb_timthumb' => true,	// Use timthumb
		'thumb_timthumb_q' => '75',	// Quality attribute for timthumb
		'scan_images' => false,			// Scan post for images
		'class' => 'tptn_thumb',			// Class of the thumbnail
		'filter' => 'tptn_postimage',			// Class of the thumbnail
	);

	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );

	// Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$result = get_post( $postid );
	$post_title = get_the_title( $postid );

	$output = '';
	$postimage = '';

	// Let's start fetching the thumbnail. First place to look is in the post meta defined in the Settings page
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, $thumb_meta, true );	// Check the post meta first
		$pick = 'meta';
	}

	// If there is no thumbnail found, check the post thumbnail
	if ( ! $postimage ) {
		if ( ( false != wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ) ) ) ) {
			$postthumb = wp_get_attachment_image_src( get_post_thumbnail_id( $result->ID ), $tptn_settings['thumb_size'] );
			$postimage = $postthumb[0];
		}
		$pick = 'featured';
	}

	// If there is no thumbnail found, fetch the first image in the post, if enabled
	if ( ! $postimage && $scan_images ) {
		preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $result->post_content, $matches );
		if ( isset( $matches[1][0] ) && $matches[1][0] ) { 			// any image there?
			$postimage = $matches[1][0]; // we need the first one only!
		}
		$pick = 'first';
	}

	// If there is no thumbnail found, fetch the first child image
	if ( ! $postimage ) {
		$postimage = tptn_get_first_image( $result->ID );	// Get the first image
		$pick = 'firstchild';
	}

	// If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin
	if ( ! $postimage ) {
		$postimage = get_post_meta( $result->ID, '_video_thumbnail', true );
	}

	// If no thumb found and settings permit, use default thumb
	if ( $thumb_default_show && ! $postimage ) {
		$postimage = $thumb_default;
	}

	// Hopefully, we've found a thumbnail by now. If so, run it through the custom filter, check for SSL and create the image tag
	if ( $postimage ) {

		/**
		 * Get the first image in the post.
		 *
		 * @since 1.8.10
		 *
		 * @param mixed $postID	Post ID
		 */
		$postimage = apply_filters( $filter, $postimage, $thumb_width, $thumb_height, $thumb_timthumb, $thumb_timthumb_q, $result );

		if ( is_ssl() ) {
		    $postimage = preg_replace( '~http://~', 'https://', $postimage );
		}

		$thumb_html = ( 'css' == $thumb_html ) ? 'style="max-width:' . $thumb_width . 'px;max-height:' . $thumb_height . 'px;"' : 'width="' . $thumb_width . '" height="' .$thumb_height . '"';

		$class .= ' tptn_' . $pick;
		$output .= '<img src="' . $postimage . '" alt="' . $post_title . '" title="' . $post_title . '" ' . $thumb_html . ' class="' . $class . '" />';
	}

	/**
	 * Filters post thumbnail created for Top 10.
	 *
	 * @since	1.9.10.1
	 *
	 * @param	array	$output	Formatted output
	 * @param	array	$args	Argument list
	 */
	return apply_filters( 'tptn_get_the_post_thumbnail', $output, $args );
}


/**
 * Get the first image in the post.
 *
 * @since	1.9.8
 * @param	mixed	$postID	Post ID
 * @return	string	Location of thumbnail
 */
function tptn_get_first_image( $postID ) {
	$args = array(
		'numberposts' => 1,
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $postID,
		'post_status' => null,
		'post_type' => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' )  ? wp_get_attachment_image_src( $attachment->ID, 'thumbnail' ) : wp_get_attachment_image_src( $attachment->ID, 'full' );

			/**
			 * Filters first child attachment from the post.
			 *
			 * @since	1.9.10.1
			 *
			 * @param	array	$image_attributes[0]	URL of the image
			 * @param	int		$postID					Post ID
			 */
			return apply_filters( 'tptn_get_first_image', $image_attributes[0] );
		}
	} else {
		return false;
	}
}


/**
 * Function to create an excerpt for the post.
 *
 * @since	1.6
 * @param	int			$id				Post ID
 * @param	int|string	$excerpt_length	Length of the excerpt in words
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
 * @param	string 	$content 	Content to be used to make an excerpt
 * @param	int 	$no_of_char	Maximum length of excerpt in characters
 * @return 	string				Formatted content
 */
function tptn_max_formatted_content( $content, $no_of_char = -1 ) {
	$content = strip_tags( $content );  // Remove CRLFs, leaving space in their wake

	if ( ( $no_of_char > 0 ) && ( strlen( $content ) > $no_of_char ) ) {
		$aWords = preg_split( "/[\s]+/", substr( $content, 0, $no_of_char ) );

		// Break back down into a string of words, but drop the last one if it's chopped off
		if ( substr( $content, $no_of_char, 1 ) == " " ) {
		  $content = implode( " ", $aWords );
		} else {
		  $content = implode( " ", array_slice( $aWords, 0, -1 ) ) .'&hellip;';
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
function ald_tptn_cron() {
	global $tptn_settings, $wpdb;

	$table_name_daily = $wpdb->base_prefix . "top_ten_daily";

	$current_time = current_time( 'timestamp', 0 );
	$from_date = strtotime( '-90 DAY' , $current_time );
	$from_date = gmdate( 'Y-m-d H' , $from_date );

	$resultscount = $wpdb->query( $wpdb->prepare(
		"DELETE FROM {$table_name_daily} WHERE dp_date <= '%s' ",
		$from_date
	) );

}
add_action( 'ald_tptn_hook', 'ald_tptn_cron' );


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
	if ( ! wp_next_scheduled( 'ald_tptn_hook' ) ) {
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'ald_tptn_hook' );
	} else {
		wp_clear_scheduled_hook( 'ald_tptn_hook' );
		wp_schedule_event( mktime( $hour, $min, 0 ), $recurrence, 'ald_tptn_hook' );
	}
}


/**
 * Function to disable daily run or actions.
 *
 * @since	1.9
 */
function tptn_disable_run() {
	if ( wp_next_scheduled( 'ald_tptn_hook' ) ) {
		wp_clear_scheduled_hook( 'ald_tptn_hook' );
	}
}

// Let's declare this conditional function to add more schedules. It will be a generic function across all plugins that I develop
if  ( ! function_exists( 'ald_more_reccurences' ) ) :

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
		'display' => __( 'Once Weekly', TPTN_LOCAL_NAME )
	);
	$schedules['fortnightly'] = array(
		'interval' => 2 * WEEK_IN_SECONDS,
		'display' => __( 'Once Fortnightly', TPTN_LOCAL_NAME )
	);
	$schedules['monthly'] = array(
		'interval' => 30 * DAY_IN_SECONDS,
		'display' => __( 'Once Monthly', TPTN_LOCAL_NAME )
	);
	$schedules['quarterly'] = array(
		'interval' => 90 * DAY_IN_SECONDS,
		'display' => __( 'Once quarterly', TPTN_LOCAL_NAME )
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'ald_more_reccurences' );

endif;


/**
 * Creates a shortcode [tptn_list limit="5" heading="1" daily="0"].
 *
 * @since	1.9.9
 * @param	array	$atts		Shortcode attributes
 * @param	string	$content	Content
 * @return	string	Formatted list of posts generated by tptn_pop_posts
 */
function tptn_shortcode( $atts, $content = null ) {
	global $tptn_settings;

	$atts = shortcode_atts( array_merge(
		$tptn_settings,
		array(
			'heading' => 1,
			'daily' => 0,
			'is_shortcode' => 1,
		)
	), $atts, 'tptn' );

	return tptn_pop_posts( $atts );
}
add_shortcode( 'tptn_list', 'tptn_shortcode' );


/**
 * Creates a shortcode [tptn_views daily="0"].
 *
 * @since	1.9.9
 * @param	array	$atts		Shortcode attributes
 * @param	string	$content	Content
 * @return	string	Views of the post
 */
function tptn_shortcode_views( $atts , $content=null ) {
	extract( shortcode_atts( array(
	  'daily' => '0',
	  ), $atts ) );

	return get_tptn_post_count_only( get_the_ID(), ( $daily ? 'daily' : 'total' ) );
}
add_shortcode( 'tptn_views', 'tptn_shortcode_views' );



/**
 * Get all image sizes.
 *
 * @since	2.0.0
 * @param	string	$size	Get specific image size
 * @return	array	Image size names along with width, height and crop setting
 */
function tptn_get_all_image_sizes( $size = '' ) {
	global $_wp_additional_image_sizes;

	/* Get the intermediate image sizes and add the full size to the array. */
	$intermediate_image_sizes = get_intermediate_image_sizes();

	foreach( $intermediate_image_sizes as $_size ) {
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
	return apply_filters( 'tptn_get_all_image_sizes', $sizes );
}

/*----------------------------------------------------------------------------*
 * WordPress widget
 *----------------------------------------------------------------------------*/

/**
 * Include Widget class.
 *
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-top-10-widget.php' );

/**
 * Initialise the widget.
 *
 */
function tptn_register_widget() {
	register_widget( 'Top_Ten_Widget' );
}
add_action( 'widgets_init', 'tptn_register_widget', 1 );


/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

// This function adds an Options page in WP Admin
if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-metabox.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-columns.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/admin-dashboard.php' );

} // End admin.inc

/*----------------------------------------------------------------------------*
 * Deprecated functions
 *----------------------------------------------------------------------------*/

	require_once( plugin_dir_path( __FILE__ ) . 'includes/deprecated.php' );

?>