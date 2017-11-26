<?php
/**
 * Deprecated functions and variables from Top 10. You shouldn't
 * use these functions or variables and look for the alternatives instead.
 * The functions will be removed in a later version.
 *
 * @package Top_Ten
 */

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since   1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_path = plugin_dir_path( TOP_TEN_PLUGIN_FILE );


/**
 * Holds the URL for Top 10
 *
 * @since   1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_url = plugins_url() . '/' . plugin_basename( dirname( TOP_TEN_PLUGIN_FILE ) );


/**
 * Filter to add related posts to feeds.
 *
 * @since   1.9.8
 * @deprecated  2.2.0
 *
 * @param   string $content    Post content.
 * @return  string  Filtered post content
 */
function ald_tptn_rss( $content ) {

	_deprecated_function( __FUNCTION__, '2.2.0', 'tptn_rss_filter()' );

	return tptn_rss_filter( $content );
}


/**
 * Function to update the post views for the current post. Filters `the_content`.
 *
 * @since   1.0
 *
 * @deprecated 2.4.0
 *
 * @param   string $content Post content.
 */
function tptn_add_viewed_count( $content = '' ) {
	global $post, $tptn_settings;

	_deprecated_function( __FUNCTION__, '2.4.0' );

	$home_url    = home_url( '/' );
	$track_users = tptn_get_option( 'track_users' );
	$trackers    = tptn_get_option( 'trackers' );

	/**
	 * Filter the script URL of the counter.
	 *
	 * @since   2.0
	 */
	$home_url = apply_filters( 'tptn_add_counter_script_url', $home_url );

	// Strip any query strings since we don't need them.
	$home_url = strtok( $home_url, '?' );

	if ( is_singular() && 'draft' !== $post->post_status ) {

		$current_user        = wp_get_current_user();  // Let's get the current user.
		$post_author         = ( $current_user->ID == $post->post_author ) ? true : false;  // Is the current user the post author?
		$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user an editor?

		$include_code = true;
		if ( ( $post_author ) && ( empty( $track_users['authors'] ) ) ) {
			$include_code = false;
		}
		if ( ( $current_user_admin ) && ( empty( $track_users['admins'] ) ) ) {
			$include_code = false;
		}
		if ( ( $current_user_editor ) && ( empty( $track_users['editors'] ) ) ) {
			$include_code = false;
		}
		if ( ( $current_user->exists() ) && ( ! tptn_get_option( 'logged_in' ) ) ) {
			$include_code = false;
		}

		if ( $include_code ) {

			$output           = '';
			$id               = intval( $post->ID );
			$blog_id          = get_current_blog_id();
			$activate_counter = ! empty( $trackers['overall'] ) ? 1 : 0;     // It's 1 if we're updating the overall count.
			$activate_counter = $activate_counter + ( ! empty( $trackers['daily'] ) ? 10 : 0 );  // It's 10 if we're updating the daily count.

			if ( $activate_counter > 0 ) {
					$output = '
					<script type="text/javascript"> jQuery(document).ready(function() {
						jQuery.ajax({
							url: "' . $home_url . '",
							data: {
								top_ten_id: ' . $id . ',
								top_ten_blog_id: ' . $blog_id . ',
								activate_counter: ' . $activate_counter . ',
								top10_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random() * 100000)
							}
						});
					});</script>';
			}

			/**
			 * Filter the counter script
			 *
			 * @since   1.9.8.5
			 *
			 * @param   string  $output Counter script code
			 */
			$output = apply_filters( 'tptn_viewed_count', $output );

			echo $output; // WPCS: XSS OK.
		}
	}
}


/**
 * Add tracker code.
 *
 * @since 2.3.0
 *
 * @deprecated 2.4.0
 *
 * @param bool $echo Echo the code or return it.
 * @return string|void
 */
function tptn_add_tracker( $echo = true ) {

	_deprecated_function( __FUNCTION__, '2.4.0' );

	if ( $echo ) {
		echo tptn_add_viewed_count( '' ); // WPCS: XSS OK.
	} else {
		return tptn_add_viewed_count( '' );
	}
}


/**
 * Default Options.
 *
 * @since 1.0
 *
 * @deprecated 2.5.0
 *
 * @return array
 */
function tptn_default_options() {

	_deprecated_function( __FUNCTION__, '2.5.0' );

	$title             = __( '<h3>Popular Posts</h3>', 'top-10' );
	$title_daily       = __( '<h3>Daily Popular</h3>', 'top-10' );
	$blank_output_text = __( 'No top posts yet', 'top-10' );
	$thumb_default     = plugins_url( 'default.png', __FILE__ );

	// Get relevant post types.
	$args       = array(
		'public'   => true,
		'_builtin' => true,
	);
	$post_types = http_build_query( get_post_types( $args ), '', '&' );

	$tptn_settings = array(

		/* General options */
		'activate_daily'           => true,         // Activate the daily count.
		'activate_overall'         => true,         // Activate overall count.
		'cache'                    => false,        // Enable Caching using Transienst API.
		'cache_time'               => HOUR_IN_SECONDS,  // Cache for 1 Hour.
		'daily_midnight'           => true,         // Start daily counts from midnight (default as old behaviour).
		'daily_range'              => '1',          // Daily Popular will contain posts of how many days?
		'hour_range'               => '0',          // Daily Popular will contain posts of how many hours?
		'uninstall_clean_options'  => true,         // Cleanup options.
		'uninstall_clean_tables'   => false,        // Cleanup tables.
		'show_metabox'             => true,         // Show metabox to admins.
		'show_metabox_admins'      => false,        // Limit to admins as well.
		'show_credit'              => false,        // Add link to plugin page of my blog in top posts list.

		/* Counter and tracker options */
		'add_to_content'           => true,         // Add post count to content (only on single posts).
		'count_on_pages'           => true,         // Add post count to pages.
		'add_to_feed'              => false,        // Add post count to feed (full).
		'add_to_home'              => false,        // Add post count to home page.
		'add_to_category_archives' => false,        // Add post count to category archives.
		'add_to_tag_archives'      => false,        // Add post count to tag archives.
		'add_to_archives'          => false,        // Add post count to other archives.

		'count_disp_form'          => '(Visited %totalcount% times, %dailycount% visits today)',    // Format to display the count.
		'count_disp_form_zero'     => 'No visits yet',  // What to display where there are no hits?
		'dynamic_post_count'       => false,        // Use JavaScript for displaying the post count.

		'tracker_type'             => 'query_based',    // Tracker type.
		'track_authors'            => false,        // Track Authors visits.
		'track_admins'             => true,         // Track Admin visits.
		'track_editors'            => true,         // Track Admin visits.
		'pv_in_admin'              => true,         // Add an extra column on edit posts/pages to display page views?
		'show_count_non_admins'    => true,         // Show counts to non-admins.

		/* Popular post list options */
		'limit'                    => '10',         // How many posts to display?
		'how_old'                  => '0',          // How old posts? Default is no limit.
		'post_types'               => $post_types,  // WordPress custom post types.
		'exclude_categories'       => '',           // Exclude these categories.
		'exclude_cat_slugs'        => '',           // Exclude these categories (slugs).
		'exclude_post_ids'         => '',           // Comma separated list of page / post IDs that are to be excluded in the results.

		'title'                    => $title,       // Title of Popular Posts.
		'title_daily'              => $title_daily, // Title of Daily Popular.
		'blank_output'             => false,        // Blank output? Default is "blank Output test".
		'blank_output_text'        => $blank_output_text,       // Blank output text.

		'show_excerpt'             => false,        // Show description in list item.
		'excerpt_length'           => '10',         // Length of characters.
		'show_date'                => false,        // Show date in list item.
		'show_author'              => false,        // Show author in list item.
		'title_length'             => '60',         // Limit length of post title.
		'disp_list_count'          => true,         // Display count in popular lists?

		'link_new_window'          => false,        // Open links in new window.
		'link_nofollow'            => false,        // Add no-follow to links.
		'exclude_on_post_ids'      => '',           // Comma separate list of page/post IDs to not display related posts on.

		// List HTML options.
		'before_list'              => '<ul>',       // Before the entire list.
		'after_list'               => '</ul>',      // After the entire list.
		'before_list_item'         => '<li>',       // Before each list item.
		'after_list_item'          => '</li>',      // After each list item.

		/* Thumbnail options */
		'post_thumb_op'            => 'text_only',  // Display only text in posts.
		'thumb_size'               => 'tptn_thumbnail', // Default thumbnail size.
		'thumb_width'              => '150',        // Max width of thumbnails.
		'thumb_height'             => '150',        // Max height of thumbnails.
		'thumb_crop'               => true,         // Crop mode. default is hard crop.
		'thumb_html'               => 'html',       // Use HTML or CSS for width and height of the thumbnail?

		'thumb_meta'               => 'post-image', // Meta field that is used to store the location of default thumbnail image.
		'scan_images'              => true,         // Scan post for images.
		'thumb_default'            => $thumb_default,   // Default thumbnail image.
		'thumb_default_show'       => true,         // Show default thumb if none found (if false, don't show thumb at all).

		/* Custom styles */
		'custom_css'               => '',           // Custom CSS to style the output.
		'include_default_style'    => false,        // Include default Top 10 style.
		'tptn_styles'              => 'no_style',   // Defaault style is left thubnails.

		/* Maintenance cron */
		'cron_on'                  => false,        // Run cron daily?
		'cron_hour'                => '0',          // Cron Hour.
		'cron_min'                 => '0',          // Cron Minute.
		'cron_recurrence'          => 'weekly',     // Frequency of cron.
	);

	/**
	 * Filters the default options array.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   array   $tptn_settings  Default options
	 */
	return apply_filters( 'tptn_default_options', $tptn_settings );
}


/**
 * Function to read options from the database.
 *
 * @since 1.0
 *
 * @deprecated 2.5.0
 *
 * @return array
 */
function tptn_read_options() {

	_deprecated_function( __FUNCTION__, '2.5.0', 'tptn_get_settings()' );

	$tptn_settings_changed = false;

	$defaults = tptn_default_options();

	$tptn_settings = array_map( 'stripslashes', (array) get_option( 'ald_tptn_settings' ) );
	unset( $tptn_settings[0] ); // Produced by the (array) casting when there's nothing in the DB.

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $tptn_settings[ $k ] ) ) {
			$tptn_settings[ $k ]   = $v;
			$tptn_settings_changed = true;
		}
	}
	if ( true === $tptn_settings_changed ) {
		update_option( 'ald_tptn_settings', $tptn_settings );
	}

	/**
	 * Filters the options array.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   array   $tptn_settings  Options read from the database
	 */
	return apply_filters( 'tptn_read_options', $tptn_settings );
}


/**
 * Function to limit content by characters.
 *
 * @since   1.9.8
 *
 * @deprecated 2.5.4
 *
 * @param   string $content    Content to be used to make an excerpt.
 * @param   int    $no_of_char Maximum length of excerpt in characters.
 * @return  string             Formatted content.
 */
function tptn_max_formatted_content( $content, $no_of_char = -1 ) {
	_deprecated_function( __FUNCTION__, '2.5.4', 'tptn_trim_char()' );

	$content = tptn_trim_char( $content, $no_of_char );

	/**
	 * Filters formatted content after cropping.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   string  $content    Formatted content
	 * @param   int     $no_of_char Maximum length of excerpt in characters
	 */
	return apply_filters( 'tptn_max_formatted_content', $content, $no_of_char );
}


