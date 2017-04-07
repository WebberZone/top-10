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
 * @copyright 2008-2016 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name:	Top 10
 * Plugin URI:	https://webberzone.com/plugins/top-10/
 * Description:	Count daily and total visits per post and display the most popular posts based on the number of views
 * Version: 	2.4.4
 * Author: 		Ajay D'Souza
 * Author URI: 	https://webberzone.com
 * License: 	GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	top-10
 * Domain Path:	/languages
 * GitHub Plugin URI: https://github.com/WebberZone/top-10/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'TOP_TEN_PLUGIN_DIR' ) ) {
	define( 'TOP_TEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'TOP_TEN_PLUGIN_URL' ) ) {
	define( 'TOP_TEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'TOP_TEN_PLUGIN_FILE' ) ) {
	define( 'TOP_TEN_PLUGIN_FILE', __FILE__ );
}


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
 * Default Options.
 */
function tptn_default_options() {

	$title = __( '<h3>Popular Posts</h3>', 'top-10' );
	$title_daily = __( '<h3>Daily Popular</h3>', 'top-10' );
	$blank_output_text = __( 'No top posts yet', 'top-10' );
	$thumb_default = plugins_url( 'default.png' , __FILE__ );

	// Get relevant post types.
	$args = array(
		'public' => true,
		'_builtin' => true,
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$tptn_settings = array(

		/* General options */
		'activate_daily'           => true,			// Activate the daily count
		'activate_overall'         => true,			// Activate overall count.
		'cache'                    => false,		// Enable Caching using Transienst API.
		'cache_time'               => HOUR_IN_SECONDS,	// Cache for 1 Hour.
		'daily_midnight'           => true,			// Start daily counts from midnight (default as old behaviour).
		'daily_range'              => '1',			// Daily Popular will contain posts of how many days?
		'hour_range'               => '0',			// Daily Popular will contain posts of how many hours?
		'uninstall_clean_options'  => true,			// Cleanup options.
		'uninstall_clean_tables'   => false,		// Cleanup tables.
		'show_metabox'             => true,			// Show metabox to admins.
		'show_metabox_admins'      => false,		// Limit to admins as well.
		'show_credit'              => false,		// Add link to plugin page of my blog in top posts list.

		/* Counter and tracker options */
		'add_to_content'           => true,			// Add post count to content (only on single posts).
		'count_on_pages'           => true,			// Add post count to pages.
		'add_to_feed'              => false,		// Add post count to feed (full).
		'add_to_home'              => false,		// Add post count to home page.
		'add_to_category_archives' => false,		// Add post count to category archives.
		'add_to_tag_archives'      => false,		// Add post count to tag archives.
		'add_to_archives'          => false,		// Add post count to other archives.

		'count_disp_form'          => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count.
		'count_disp_form_zero'     => 'No visits yet',	// What to display where there are no hits?
		'dynamic_post_count'       => false,		// Use JavaScript for displaying the post count.

		'tracker_type'             => 'query_based',	// Tracker type.
		'track_authors'            => false,		// Track Authors visits.
		'track_admins'             => true,			// Track Admin visits.
		'track_editors'            => true,			// Track Admin visits.
		'pv_in_admin'              => true,			// Add an extra column on edit posts/pages to display page views?
		'show_count_non_admins'    => true,			// Show counts to non-admins.

		/* Popular post list options */
		'limit'                    => '10',			// How many posts to display?
		'how_old'                  => '0',			// How old posts? Default is no limit.
		'post_types'               => $post_types,	// WordPress custom post types.
		'exclude_categories'       => '',			// Exclude these categories.
		'exclude_cat_slugs'        => '',			// Exclude these categories (slugs).
		'exclude_post_ids'         => '',			// Comma separated list of page / post IDs that are to be excluded in the results.

		'title'                    => $title,		// Title of Popular Posts.
		'title_daily'              => $title_daily,	// Title of Daily Popular.
		'blank_output'             => false,		// Blank output? Default is "blank Output test".
		'blank_output_text'        => $blank_output_text,		// Blank output text.

		'show_excerpt'             => false,		// Show description in list item.
		'excerpt_length'           => '10',			// Length of characters.
		'show_date'                => false,		// Show date in list item.
		'show_author'              => false,		// Show author in list item.
		'title_length'             => '60',			// Limit length of post title.
		'disp_list_count'          => true,			// Display count in popular lists?

		'link_new_window'          => false,		// Open links in new window.
		'link_nofollow'            => false,		// Add no-follow to links.
		'exclude_on_post_ids'      => '', 			// Comma separate list of page/post IDs to not display related posts on.

		// List HTML options.
		'before_list'              => '<ul>',		// Before the entire list.
		'after_list'               => '</ul>',		// After the entire list.
		'before_list_item'         => '<li>',		// Before each list item.
		'after_list_item'          => '</li>',		// After each list item.

		/* Thumbnail options */
		'post_thumb_op'            => 'text_only',	// Display only text in posts.
		'thumb_size'               => 'tptn_thumbnail',	// Default thumbnail size.
		'thumb_width'              => '150',		// Max width of thumbnails.
		'thumb_height'             => '150',		// Max height of thumbnails.
		'thumb_crop'               => true,			// Crop mode. default is hard crop.
		'thumb_html'               => 'html',		// Use HTML or CSS for width and height of the thumbnail?

		'thumb_meta'               => 'post-image',	// Meta field that is used to store the location of default thumbnail image.
		'scan_images'              => true,			// Scan post for images.
		'thumb_default'            => $thumb_default,	// Default thumbnail image.
		'thumb_default_show'       => true,			// Show default thumb if none found (if false, don't show thumb at all).

		/* Custom styles */
		'custom_CSS'               => '',			// Custom CSS to style the output.
		'include_default_style'    => false,		// Include default Top 10 style.
		'tptn_styles'              => 'no_style',	// Defaault style is left thubnails.

		/* Maintenance cron */
		'cron_on'                  => false,		// Run cron daily?
		'cron_hour'                => '0',			// Cron Hour.
		'cron_min'                 => '0',			// Cron Minute.
		'cron_recurrence'          => 'weekly',		// Frequency of cron.
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
 * @return array
 */
function tptn_read_options() {

	$tptn_settings_changed = false;

	$defaults = tptn_default_options();

	$tptn_settings = array_map( 'stripslashes', (array) get_option( 'ald_tptn_settings' ) );
	unset( $tptn_settings[0] ); // Produced by the (array) casting when there's nothing in the DB.

	foreach ( $defaults as $k => $v ) {
		if ( ! isset( $tptn_settings[ $k ] ) ) {
			$tptn_settings[ $k ] = $v;
			$tptn_settings_changed = true;
		}
	}
	if ( true === $tptn_settings_changed ) {
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
 * Function to delete all rows in the posts table.
 *
 * @since	1.3
 * @param	bool $daily  Daily flag.
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


/*
 ----------------------------------------------------------------------------*
 * Top 10 modules
 *---------------------------------------------------------------------------*
 */

require_once( TOP_TEN_PLUGIN_DIR . 'includes/activate-deactivate.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/public/display-posts.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/public/styles.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/public/output-generator.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/public/media.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/l10n.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/counter.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/tracker.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/cron.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/formatting.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/shortcode.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/exclusions.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/taxonomies.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/class-top-10-widget.php' );


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *---------------------------------------------------------------------------*
 */

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( TOP_TEN_PLUGIN_DIR . 'admin/admin.php' );
	require_once( TOP_TEN_PLUGIN_DIR . 'admin/admin-metabox.php' );
	require_once( TOP_TEN_PLUGIN_DIR . 'admin/admin-columns.php' );
	require_once( TOP_TEN_PLUGIN_DIR . 'admin/admin-dashboard.php' );
	require_once( TOP_TEN_PLUGIN_DIR . 'admin/class-stats.php' );
	require_once( TOP_TEN_PLUGIN_DIR . 'admin/cache.php' );

} // End admin.inc

/*
 ----------------------------------------------------------------------------*
 * Deprecated functions
 *---------------------------------------------------------------------------*
 */

require_once( TOP_TEN_PLUGIN_DIR . 'includes/deprecated.php' );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( TOP_TEN_PLUGIN_DIR . 'admin/deprecated.php' );
}
