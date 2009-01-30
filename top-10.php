<?php
/*
Plugin Name: Top 10
Version:     1.0
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/top-10/
Description: Count visits per post and display the top 10 posts. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>.  <a href="options-general.php?page=tptn_options">Configure...</a>
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

$tptn_db_version = "1.0";

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
	
	if(is_single() || is_page()) {
		$id = intval($post->ID);
		if(defined('WP_CACHE') && WP_CACHE) { ?>
			<!-- Start of Top 10 JS -->
			<?php wp_print_scripts(array('sack')); ?>
			<script type="text/javascript">
			//<![CDATA[
				top_ten_count = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/top-10/top-10_ajax.php");    
				top_ten_count.setVar( "top_ten_id", <?php echo $id ?> );
				top_ten_count.method = 'GET';
				top_ten_count.onError = function() { alert('Ajax error' )};
				top_ten_count.runAJAX();
				top_ten_count = null;
			//]]>
			</script>
			<!-- Start of Top 10 JS -->
		<?php } else {
			$results = $wpdb->get_results("select postnumber, cntaccess from $table_name where postnumber = '$id'");
			$test = 0;
			if ($results) {
				foreach ($results as $result) {
					$wpdb->query("update $table_name set cntaccess = cntaccess + 1 where postnumber = $result->postnumber");
					$test = 1;
				}
			}
			if ($test == 0) {
				$wpdb->query("insert into $table_name (postnumber, cntaccess) values('$id', '1')");
			}
		}
	}
}

// Function to show post count
function tptn_show_post_count() {
	global $wpdb, $post;
	$id = intval($post->ID);
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$before_count = $tptn_settings['before_count'];
	$after_count = $tptn_settings['after_count'];
	
	$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $id");
	$cntaccess = (($resultscount) ? $resultscount->cntaccess : 0);

	return $before_count.$cntaccess.$after_count;
}

function tptn_pc_content($content) {
	
	global $single, $post;
	$tptn_settings = tptn_read_options();
	$id = intval($post->ID);

	if(($single)&&($tptn_settings['add_to_content'])) {
		if(defined('WP_CACHE') && WP_CACHE) {
			$output = '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/wp-content/plugins/top-10/top-10-counter.js.php?top_ten_id='.$id.'"></script>';
		} else {
			$output = tptn_show_post_count();
		}
		return $content.$output;
	} else {
		return $content;
	}
}
add_filter('the_content', 'tptn_pc_content');

function echo_tptn_post_count() {
	$output = tptn_show_post_count();
	echo $output;
}


// Function to show popular posts
function tptn_show_pop_posts() {
	global $wpdb, $siteurl, $tableposts, $id;
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$results = $wpdb->get_results("select postnumber, cntaccess from $table_name ORDER BY cntaccess DESC LIMIT $limit");
	
	echo '<div id="crp_related">'.$tptn_settings['title'];
	echo '<ul>';
	if ($results) {
		foreach ($results as $result) {
			echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a> ('.$result->cntaccess.')</li>';
		}
	}
	if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
	echo '</ul>';
	echo '</div><br/><br/>';

}

// Default Options
function tptn_default_options() {
	$title = __('<h3>Popular Posts:</h3>');

	$tptn_settings = 	Array (
						show_credit => true,	// Add link to plugin page of my blog in top posts list
						add_to_content => true,		// Add post count to content (only on single pages)
						before_count => '(Visited ',	// Text to display before the count
						after_count => ' times)',	// Text to display after the count
						title => $title,		// Add before the content
						limit => '10',	// How many posts to display?
						);
	return $tptn_settings;
}

// Function to read options from the database
function tptn_read_options() {

	$tptn_settings_changed = false;
	
	$defaults = tptn_default_options();
	
	$tptn_settings = array_map('stripslashes',(array)get_option('ald_tptn_settings'));
	unset($tptn_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($tptn_settings[$k]))
			$tptn_settings[$k] = $v;
		$tptn_settings_changed = true;	
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

      update_option( "tptn_db_version", $tptn_db_version );
  }

}
if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__,'tptn_install');
}

// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_TPTN_DIR . "/admin.inc.php");
}


?>