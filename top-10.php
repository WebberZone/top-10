<?php
/*
Plugin Name: Top 10
Version:     1.3
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/top-10/
Description: Count daily and total visits per post and display the most popular posts based on the number of views. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>.  <a href="options-general.php?page=tptn_options">Configure...</a>
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

//if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
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
     load_plugin_textdomain('myald_tptn_plugin', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)));
}
add_action('init', 'ald_tptn_init');

define('ALD_TPTN_DIR', dirname(__FILE__));

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
// Update post views
add_action('wp_head','tptn_add_viewed_count');
function tptn_add_viewed_count() {
	global $post, $wpdb, $single;
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	
	$current_user = wp_get_current_user(); 
	$post_author = ( $current_user->ID == $post->post_author ? true : false );

	if((is_single() || is_page())) {
		if (!(($post_author)&&(!$tptn_settings['track_authors']))) {
			$id = intval($post->ID);
			$output = '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-addcount.js.php?top_ten_id='.$id.'"></script>';
			echo $output;
		}
	}
}

// Function to add count to content
function tptn_pc_content($content) {
	global $single, $post;
	$tptn_settings = tptn_read_options();
	$id = intval($post->ID);

	if(($single)&&($tptn_settings['add_to_content'])) {
		$output = '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
		return $content.$output;
	} else {
		return $content;
	}
}
add_filter('the_content', 'tptn_pc_content');

// Function to manually display count
function echo_tptn_post_count() {
	global $post;
	$id = intval($post->ID);

	$output = '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
	echo $output;
}

// Function to show popular posts
function tptn_show_pop_posts() {
	global $wpdb, $siteurl, $tableposts, $id;
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$sql = "SELECT postnumber, cntaccess , ID, post_type, post_status ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "AND post_status = 'publish' ";
	$sql .= "ORDER BY cntaccess DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	echo '<div id="crp_related">'.$tptn_settings['title'];
	echo '<ul>';
	if ($results) {
		foreach ($results as $result) {
			echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
			if ($tptn_settings['disp_list_count']) echo ' ('.$result->cntaccess.')';
			echo '</li>';
		}
	}
	if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
	echo '</ul>';
	echo '</div>';
}

// Function to show daily popular posts
function tptn_show_daily_pop_posts() {
	global $wpdb, $siteurl, $tableposts, $id;
	$table_name = $wpdb->prefix . "top_ten_daily";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$output = '';
	if ($tptn_settings['d_use_js']) {
		$output .= '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-daily.js.php"></script>';
	} else {
		$daily_range = $tptn_settings[daily_range]. ' DAY';
		$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL $daily_range), INTERVAL 1 DAY) ");
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";

		$results = $wpdb->get_results($sql);
		
		$output .= '<div id="crp_related">'.$tptn_settings['title_daily'];
		$output .= '<ul>';
		if ($results) {
			foreach ($results as $result) {
				$output .= '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
				if ($tptn_settings['disp_list_count']) $output .= ' ('.$result->sumCount.')';
				$output .= '</li>';
			}
		}
		if ($tptn_settings['show_credit']) $output .= '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
		$output .= '</ul>';
		$output .= '</div>';
	}
	echo $output;
}

// Default Options
function tptn_default_options() {
	$title = __('<h3>Popular Posts</h3>','ald_tptn_plugin');
	$title_daily = __('<h3>Daily Popular</h3>','ald_tptn_plugin');

	$tptn_settings = 	Array (
						show_credit => true,			// Add link to plugin page of my blog in top posts list
						add_to_content => true,			// Add post count to content (only on single pages)
						exclude_pages => true,			// Exclude Pages
						track_authors => false,			// Track Authors visits
						pv_in_admin => true,			// Add an extra column on edit posts/pages to display page views?
						disp_list_count => true,		// Display count in popular lists?
						d_use_js => false,				// Use JavaScript for displaying daily posts
						count_disp_form => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count
						title => $title,				// Title of Popular Posts
						title_daily => $title_daily,	// Title of Daily Popular
						limit => '10',					// How many posts to display?
						daily_range => '1',				// Daily Popular will contain posts of how many days?
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
	//register_activation_hook(__FILE__, 'tptn_cron_install');
}


// Function to delete all rows in the daily posts table
function tptn_trunc_count() {
	global $wpdb;
	$table_name_daily = $wpdb->prefix . "top_ten_daily";

	$sql = "TRUNCATE TABLE $table_name_daily";
	$wpdb->query($sql);
}

// Create a WordPress Widget for Daily Popular Posts
function widget_tptn_pop_daily($args) {	
	global $wpdb, $siteurl, $tableposts, $id;

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$table_name = $wpdb->prefix . "top_ten_daily";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$title = (($tptn_settings['title_daily']) ? strip_tags($tptn_settings['title_daily']) : __('Daily Popular'));
	echo $before_widget;
	echo $before_title.$title.$after_title;
		
	if ($tptn_settings['d_use_js']) {
		echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-daily.js.php?widget=1"></script>';
	} else {
		$daily_range = $tptn_settings[daily_range]. ' DAY';
		$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL $daily_range), INTERVAL 1 DAY) ");
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";

		$results = $wpdb->get_results($sql);
		
		echo '<ul>';
		if ($results) {
			foreach ($results as $result) {
				echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
				if ($tptn_settings['disp_list_count']) echo ' ('.$result->sumCount.')';
				echo '</li>';
			}
		}
		if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
		echo '</ul>';
	}
	
	echo $after_widget;
}

// Create a WordPress Widget for Popular Posts
function widget_tptn_pop($args) {	
	global $wpdb, $siteurl, $tableposts, $id;

	extract($args); // extracts before_widget,before_title,after_title,after_widget

	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$sql = "SELECT postnumber, cntaccess , ID, post_type ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "ORDER BY cntaccess DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	$title = (($tptn_settings['title']) ? strip_tags($tptn_settings['title']) : __('Popular Posts'));
	
	echo $before_widget;
	echo $before_title.$title.$after_title;
	echo '<ul>';
	if ($results) {
		foreach ($results as $result) {
			echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
			if ($tptn_settings['disp_list_count']) echo ' ('.$result->cntaccess.')';
			echo '</li>';
		}
	}
	if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
	echo '</ul>';

	echo $after_widget;
}

function init_tptn(){
	register_sidebar_widget(__('Popular Posts','ald_tptn_plugin'), 'widget_tptn_pop');
	register_sidebar_widget(__('Daily Popular','ald_tptn_plugin'), 'widget_tptn_pop_daily');
}
add_action("plugins_loaded", "init_tptn");
 
// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_TPTN_DIR . "/admin.inc.php");
}

?>