<?php
/*
Plugin Name: Top 10
Version:     1.4.1
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/top-10/
Description: Count daily and total visits per post and display the most popular posts based on the number of views. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
define('ALD_TPTN_DIR', dirname(__FILE__));
define('TPTN_LOCAL_NAME', 'tptn');

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
// Guess the location
$tptn_path = WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__));
$tptn_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));



if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

global $tptn_db_version;
$tptn_db_version = "2.8";

function ald_tptn_init() {
	//* Begin Localization Code */
	$tc_localizationName = TPTN_LOCAL_NAME;
	$tc_comments_locale = get_locale();
	$tc_comments_mofile = ALD_TPTN_DIR . "/languages/" . $tc_localizationName . "-". $tc_comments_locale.".mo";
	load_textdomain($tc_localizationName, $tc_comments_mofile);
	//* End Localization Code */
}
add_action('init', 'ald_tptn_init');

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
// Update post views
add_filter('the_content','tptn_add_viewed_count',9000);
function tptn_add_viewed_count($content) {
	global $post, $wpdb, $single,$tptn_url,$tptn_path;
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	
	$current_user = wp_get_current_user(); 
	$post_author = ( $current_user->ID == $post->post_author ? true : false );

	if((is_single() || is_page())) {
		if (!(($post_author)&&(!$tptn_settings['track_authors']))) {
			$id = intval($post->ID);
			$output = '<script type="text/javascript" src="'.$tptn_url.'/top-10-addcount.js.php?top_ten_id='.$id.'"></script>';
			return $content.$output;
		}
		else {
			return $content;
		} 
	}
	else {
		return $content;
	} 
}


// Function to add count to content
function tptn_pc_content($content) {
	global $single, $post,$tptn_url,$tptn_path;
	$tptn_settings = tptn_read_options();
	$id = intval($post->ID);

	if((is_single())&&($tptn_settings['add_to_content'])) {
		$output = '<script type="text/javascript" src="'.$tptn_url.'/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
		return $content.$output;
	} elseif((is_page())&&($tptn_settings['count_on_pages'])) {
		$output = '<script type="text/javascript" src="'.$tptn_url.'/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
		return $content.$output;
	} else {
		return $content;
	}
}
add_filter('the_content', 'tptn_pc_content',9001);

// Function to manually display count
function echo_tptn_post_count() {
	global $post,$tptn_url,$tptn_path;
	$id = intval($post->ID);

	$output = '<script type="text/javascript" src="'.$tptn_url.'/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
	echo $output;
}

// Function to return popular posts
function tptn_pop_posts( $daily = false , $widget = false ) {
	global $wpdb, $siteurl, $tableposts, $id;
	if ($daily) $table_name = $wpdb->prefix . "top_ten_daily"; 
		else $table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];

	if (!$daily) {
		$sql = "SELECT postnumber, cntaccess as sumCount, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";
	} else {
		$daily_range = $tptn_settings[daily_range]. ' DAY';
		$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL ".$daily_range."), INTERVAL 1 DAY) ");
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";
	}
	$results = $wpdb->get_results($sql);
	$output = '';

	if (!$widget) {
		if (!$daily) {
			$output .= '<div id="tptn_related">'.$tptn_settings['title'];
		} else {
			$output .= '<div id="tptn_related_daily">'.$tptn_settings['title_daily'];
		}
	}
	
	if ($results) {
		$output .= $tptn_settings['before_list'];
		foreach ($results as $result) {
			$title = trim(stripslashes(get_the_title($result->postnumber)));
			$output .= $tptn_settings['before_list_item'];

			if (($tptn_settings['post_thumb_op']=='inline')||($tptn_settings['post_thumb_op']=='thumbs_only')) {
				$output .= '<a href="'.get_permalink($result->postnumber).'" rel="bookmark">';
				if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail($result->postnumber))) {
					$output .= get_the_post_thumbnail( $result->postnumber, array($tptn_settings[thumb_width],$tptn_settings[thumb_height]), array('title' => $title,'alt' => $title));
				} else {
					$postimage = get_post_meta($result->postnumber, 'post-image', true);
					if ($postimage) {
						$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" width="'.$tptn_settings[thumb_width].'" height="'.$tptn_settings[thumb_height].'" />';
					} else {
						$output .= '<img src="'.$tptn_settings[thumb_default].'" alt="'.$title.'" title="'.$title.'" width="'.$tptn_settings[thumb_width].'" height="'.$tptn_settings[thumb_height].'" />';
					}
				}
				$output .= '</a> ';
			}
			if (($tptn_settings['post_thumb_op']=='inline')||($tptn_settings['post_thumb_op']=='text_only')) {
				$output .= '<a href="'.get_permalink($result->postnumber).'" rel="bookmark">'.$title.'</a>';
			}		
			if ($tptn_settings['disp_list_count']) $output .= ' ('.$result->sumCount.')';
			$output .= $tptn_settings['after_list_item'];
		}
		if ($tptn_settings['show_credit']) $output .= '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
		$output .= $tptn_settings['after_list'];
	}
	if (!$widget) $output .= '</div>';

	return $output;
}

// Function to show popular posts
function tptn_show_pop_posts() {
	echo tptn_pop_posts(false,false);
}

// Function to show daily popular posts
function tptn_show_daily_pop_posts() {
	global $tptn_url;
	$tptn_settings = tptn_read_options();
	if ($tptn_settings['d_use_js']) {
		echo '<script type="text/javascript" src="'.$tptn_url.'/top-10-daily.js.php?widget=1"></script>';
	} else {
		echo tptn_pop_posts(true,false);
	}
}

// Create a WordPress Widget for Daily Popular Posts
function widget_tptn_pop_daily($args) {	
	global $wpdb, $siteurl, $tableposts, $id,$tptn_url;

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$tptn_settings = tptn_read_options();
	$title = (($tptn_settings['title_daily']) ? strip_tags($tptn_settings['title_daily']) : __('Daily Popular',TPTN_LOCAL_NAME));

	echo $before_widget;
	echo $before_title.$title.$after_title;
		
	if ($tptn_settings['d_use_js']) {
		echo '<script type="text/javascript" src="'.$tptn_url.'/top-10-daily.js.php?widget=1"></script>';
	} else {
		echo tptn_pop_posts(true,true);
	}

	echo $after_widget;
}

// Create a WordPress Widget for Popular Posts
function widget_tptn_pop($args) {	
	global $wpdb, $siteurl, $tableposts, $id;

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$tptn_settings = tptn_read_options();
	$title = (($tptn_settings['title']) ? strip_tags($tptn_settings['title']) : __('Popular Posts',TPTN_LOCAL_NAME));
	
	echo $before_widget;
	echo $before_title.$title.$after_title;
	echo tptn_pop_posts(false,true);
	echo $after_widget;
}

// Default Options
function tptn_default_options() {
	global $tptn_url;
	$title = __('<h3>Popular Posts</h3>',TPTN_LOCAL_NAME);
	$title_daily = __('<h3>Daily Popular</h3>',TPTN_LOCAL_NAME);
	$thumb_default = $tptn_url.'/default.png';

	$tptn_settings = 	Array (
						show_credit => true,			// Add link to plugin page of my blog in top posts list
						add_to_content => true,			// Add post count to content (only on single posts)
						exclude_pages => true,			// Exclude Pages
						count_on_pages => true,			// Display on pages
						track_authors => false,			// Track Authors visits
						pv_in_admin => true,			// Add an extra column on edit posts/pages to display page views?
						disp_list_count => true,		// Display count in popular lists?
						d_use_js => false,				// Use JavaScript for displaying daily posts
						count_disp_form => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count
						title => $title,				// Title of Popular Posts
						title_daily => $title_daily,	// Title of Daily Popular
						limit => '10',					// How many posts to display?
						daily_range => '1',				// Daily Popular will contain posts of how many days?
						before_list => '<ul>',			// Before the entire list
						after_list => '</ul>',			// After the entire list
						before_list_item => '<li>',		// Before each list item
						after_list_item => '</li>',		// After each list item
						post_thumb_op => 'text_only',	// Display only text in posts
						thumb_height => '100',			// Height of thumbnails
						thumb_width => '100',			// Width of thumbnails
						thumb_meta => 'post-image',		// Meta field that is used to store the location of default thumbnail image
						thumb_default => $thumb_default,	// Default thumbnail image
						);
	return $tptn_settings;
}

// Function to read options from the database
function tptn_read_options() {

	// Upgrade table code
	global $tptn_db_version;
	$installed_ver = get_option( "tptn_db_version" );

	if( $installed_ver != $tptn_db_version ) tptn_install();

	$tptn_settings_changed = false;
	
	$defaults = tptn_default_options();
	
	$tptn_settings = array_map('stripslashes',(array)get_option('ald_tptn_settings'));
	unset($tptn_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($tptn_settings[$k])) {
			$tptn_settings[$k] = $v;
			$tptn_settings_changed = true;
		}
	}
	if ($tptn_settings_changed == true)
		update_option('ald_tptn_settings', $tptn_settings);
	
	return $tptn_settings;

}

// Create tables to store pageviews
function tptn_install() {
   global $wpdb;
   global $tptn_db_version;

   $table_name = $wpdb->prefix . "top_ten";
   $table_name_daily = $wpdb->prefix . "top_ten_daily";
   
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
	$sql = "CREATE TABLE " . $table_name . " (
		accessedid int NOT NULL AUTO_INCREMENT,
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		PRIMARY KEY  (accessedid)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	add_option("tptn_db_version", $tptn_db_version);
   }
   
   if($wpdb->get_var("show tables like '$table_name_daily'") != $table_name_daily) {
      
	$sql = "CREATE TABLE " . $table_name_daily . " (
		accessedid int NOT NULL AUTO_INCREMENT,
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		dp_date date NOT NULL,
		PRIMARY KEY  (accessedid)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	add_option("tptn_db_version", $tptn_db_version);
   }
   
   // Upgrade table code
   $installed_ver = get_option( "tptn_db_version" );

   if( $installed_ver != $tptn_db_version ) {

	$sql = "CREATE TABLE " . $table_name . " (
		accessedid int NOT NULL AUTO_INCREMENT,
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		PRIMARY KEY  (accessedid)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
	  
	$sql = "DROP TABLE $table_name_daily";
	$wpdb->query($sql);
	
	$sql = "CREATE TABLE " . $table_name_daily . " (
		accessedid int NOT NULL AUTO_INCREMENT,
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		dp_date date NOT NULL,
		PRIMARY KEY  (accessedid)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

      update_option( "tptn_db_version", $tptn_db_version );
  }

}
if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__,'tptn_install');
}


// Function to delete all rows in the posts table
function tptn_trunc_count($daily = false) {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query($sql);
}


function init_tptn(){
	register_sidebar_widget(__('Popular Posts',TPTN_LOCAL_NAME), 'widget_tptn_pop');
	register_sidebar_widget(__('Daily Popular',TPTN_LOCAL_NAME), 'widget_tptn_pop_daily');
}
add_action("plugins_loaded", "init_tptn");
 
// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_TPTN_DIR . "/admin.inc.php");

// Add meta links
function tptn_plugin_actions( $links, $file ) {
	$plugin = plugin_basename(__FILE__);
 
	// create link
	if ($file == $plugin) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=tptn_options' ) . '">' . __('Settings', tptn_LOCAL_NAME ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.org">' . __('Support', tptn_LOCAL_NAME ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.com/donate/">' . __('Donate', tptn_LOCAL_NAME ) . '</a>';
	}
	return $links;
}
global $wp_version;
if ( version_compare( $wp_version, '2.8alpha', '>' ) )
	add_filter( 'plugin_row_meta', 'tptn_plugin_actions', 10, 2 ); // only 2.8 and higher
else add_filter( 'plugin_action_links', 'tptn_plugin_actions', 10, 2 );

}


?>