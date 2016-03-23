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
 * Version: 	2.3.0-beta20160420
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
	$thumb_default = plugins_url() . '/' . plugin_basename( dirname( TOP_TEN_PLUGIN_FILE ) ) . '/default.png';

	// get relevant post types
	$args = array(
		'public' => true,
		'_builtin' => true,
	);
	$post_types	= http_build_query( get_post_types( $args ), '', '&' );

	$tptn_settings = array(

		/* General options */
		'activate_daily'           => true,	// Activate the daily count
		'activate_overall'         => true,	// activate overall count
		'cache'                    => false,			// Enable Caching using Transienst API
		'cache_time'               => HOUR_IN_SECONDS,			// Cache for 1 Hour
		'cache_fix'                => true,		// Fix for W3 Total Cache - Uses Ajax
		'external_tracker'         => true,		// Use external JS tracker file
		'daily_midnight'           => true,		// Start daily counts from midnight (default as old behaviour)
		'daily_range'              => '1',				// Daily Popular will contain posts of how many days?
		'hour_range'               => '0',				// Daily Popular will contain posts of how many days?
		'uninstall_clean_options'  => true,	// Cleanup options
		'uninstall_clean_tables'   => false,	// Cleanup tables
		'show_metabox'             => true,	// Show metabox to admins
		'show_metabox_admins'      => false,	// Limit to admins as well
		'show_credit'              => false,			// Add link to plugin page of my blog in top posts list

		/* Counter and tracker options */
		'add_to_content'           => true,			// Add post count to content (only on single posts)
		'count_on_pages'           => true,			// Add post count to pages
		'add_to_feed'              => false,		// Add post count to feed (full)
		'add_to_home'              => false,		// Add post count to home page
		'add_to_category_archives' => false,		// Add post count to category archives
		'add_to_tag_archives'      => false,		// Add post count to tag archives
		'add_to_archives'          => false,		// Add post count to other archives

		'count_disp_form'          => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count
		'count_disp_form_zero'     => 'No visits yet',	// What to display where there are no hits?
		'dynamic_post_count'       => false,		// Use JavaScript for displaying the post count

		'track_authors'            => false,			// Track Authors visits
		'track_admins'             => true,			// Track Admin visits
		'track_editors'            => true,			// Track Admin visits
		'pv_in_admin'              => true,			// Add an extra column on edit posts/pages to display page views?
		'show_count_non_admins'    => true,	// Show counts to non-admins

		/* Popular post list options */
		'limit'                    => '10',					// How many posts to display?
		'how_old'                  => '0',					// How old posts? Default is no limit
		'post_types'               => $post_types,		// WordPress custom post types
		'exclude_categories'       => '',		// Exclude these categories
		'exclude_cat_slugs'        => '',		// Exclude these categories (slugs)
		'exclude_post_ids'         => '',	// Comma separated list of page / post IDs that are to be excluded in the results

		'title'                    => $title,				// Title of Popular Posts
		'title_daily'              => $title_daily,	// Title of Daily Popular
		'blank_output'             => false,		// Blank output? Default is "blank Output test"
		'blank_output_text'        => $blank_output_text,		// Blank output text

		'show_excerpt'             => false,			// Show description in list item
		'excerpt_length'           => '10',			// Length of characters
		'show_date'                => false,			// Show date in list item
		'show_author'              => false,			// Show author in list item
		'title_length'             => '60',		// Limit length of post title
		'disp_list_count'          => true,		// Display count in popular lists?

		'link_new_window'          => false,	// Open links in new window
		'link_nofollow'            => false,	// Add no-follow to links
		'exclude_on_post_ids'      => '', 	// Comma separate list of page/post IDs to not display related posts on

		// List HTML options
		'before_list'              => '<ul>',			// Before the entire list
		'after_list'               => '</ul>',			// After the entire list
		'before_list_item'         => '<li>',		// Before each list item
		'after_list_item'          => '</li>',		// After each list item

		/* Thumbnail options */
		'post_thumb_op'            => 'text_only',	// Display only text in posts
		'thumb_size'               => 'tptn_thumbnail',	// Default thumbnail size
		'thumb_width'              => '150',			// Max width of thumbnails
		'thumb_height'             => '150',			// Max height of thumbnails
		'thumb_crop'               => true,		// Crop mode. default is hard crop
		'thumb_html'               => 'html',		// Use HTML or CSS for width and height of the thumbnail?

		'thumb_meta'               => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'scan_images'              => true,			// Scan post for images
		'thumb_default'            => $thumb_default,	// Default thumbnail image
		'thumb_default_show'       => true,	// Show default thumb if none found (if false, don't show thumb at all)

		/* Custom styles */
		'custom_CSS'               => '',			// Custom CSS to style the output
		'include_default_style'    => false,	// Include default Top 10 style
		'tptn_styles'              => 'no_style',	// Defaault style is left thubnails

		/* Maintenance cron */
		'cron_on'                  => false,		// Run cron daily?
		'cron_hour'                => '0',		// Cron Hour
		'cron_min'                 => '0',		// Cron Minute
		'cron_recurrence'          => 'weekly',	// Frequency of cron
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
	$content = '';

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


/*
 ----------------------------------------------------------------------------*
 * Top 10 modules
 *----------------------------------------------------------------------------*/

require_once( TOP_TEN_PLUGIN_DIR . 'includes/activate-deactivate.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/display-posts.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/styles.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/l10n.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/counter.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/media.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/cron.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/output-generator.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/shortcode.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/exclusions.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/taxonomies.php' );
require_once( TOP_TEN_PLUGIN_DIR . 'includes/modules/class-top-10-widget.php' );


/*
 ----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

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
 *----------------------------------------------------------------------------*/

require_once( TOP_TEN_PLUGIN_DIR . 'includes/deprecated.php' );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once( TOP_TEN_PLUGIN_DIR . 'admin/deprecated.php' );
}
