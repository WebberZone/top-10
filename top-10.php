<?php
/*
Plugin Name: Top 10
Version:     1.9.8.1
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/top-10/
Description: Count daily and total visits per post and display the most popular posts based on the number of views. Based on the plugin by <a href="http://weblogtoolscollection.com">Mark Ghosh</a>
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
define('ALD_TPTN_DIR', dirname(__FILE__));
define('TPTN_LOCAL_NAME', 'tptn');

// Guess the location
$tptn_path = plugin_dir_path(__FILE__);
$tptn_url = plugins_url().'/'.plugin_basename(dirname(__FILE__));

global $tptn_db_version;
$tptn_db_version = "3.0";

/**
 * Function to load translation files.
 * 
 * @access public
 * @return void
 */
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
*				Main Functions 										*
********************************************************************/
global $tptn_settings;
$tptn_settings = tptn_read_options();


/**
 * Filter for content to update post views.
 * 
 * @access public
 * @param string $content Post content
 * @return string Filtered content
 */
function tptn_add_viewed_count($content) {
	global $post, $wpdb, $single,$tptn_url,$tptn_path;
	$table_name = $wpdb->prefix . "top_ten";

	if(is_singular()) {

		global $tptn_settings;
	
		$current_user = wp_get_current_user(); 
		$post_author = ( $current_user->ID == $post->post_author ? true : false );
		$current_user_admin = ( (current_user_can( 'manage_options' )) ? true : false );
		
		$include_code = true;
		if ( ($post_author) && (!$tptn_settings['track_authors']) ) $include_code = false;
		if ( ($current_user_admin) && (!$tptn_settings['track_admins']) ) $include_code = false;

		if ($include_code) {
			$output = '';
			$id = intval($post->ID);
			$activate_counter = ($tptn_settings['activate_overall'] ? 1 : 0);
			$activate_counter = $activate_counter + ($tptn_settings['activate_daily'] ? 10 : 0 );
			if ($activate_counter>0) {
				if ($tptn_settings['cache_fix']) {
					$output = '<script type="text/javascript">jQuery.ajax({url: "' .$tptn_url. '/top-10-addcount.js.php", data: {top_ten_id: ' .$id. ', activate_counter: ' . $activate_counter . ', top10_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random()*100000)}});</script>';				
				} else {
					$output = '<script type="text/javascript" src="'.$tptn_url.'/top-10-addcount.js.php?top_ten_id='.$id.'"></script>';
				}
			}
			
			return $content.$output;
		} else {
			return $content;
		} 
	} else {
		return $content;
	}
}
add_filter('the_content','tptn_add_viewed_count');


/**
 * Enqueue Scripts.
 * 
 * @access public
 * @return void
 */
function tptn_enqueue_scripts() {
		global $tptn_settings;
	
		if ($tptn_settings['cache_fix']) wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'tptn_enqueue_scripts' ); // wp_enqueue_scripts action hook to link only on the front-end


/**
 * Filter to add visited count to content.
 * 
 * @access public
 * @param string $content
 * @return string
 */
function tptn_pc_content($content) {
	global $single, $post;
	global $tptn_settings;

	$exclude_on_post_ids = explode(',',$tptn_settings['exclude_on_post_ids']);
	//$p_in_c = (in_array($post->ID, $exclude_on_post_ids)) ? true : false;
	if (in_array($post->ID, $exclude_on_post_ids)) return $content;	// Exit without adding related posts
	
	if((is_single())&&($tptn_settings['add_to_content'])) {
		return $content.echo_tptn_post_count(0);
	} elseif((is_page())&&($tptn_settings['count_on_pages'])) {
		return $content.echo_tptn_post_count(0);
    } elseif((is_home())&&($tptn_settings['add_to_home'])) {
        return $content.echo_tptn_post_count(0);
    } elseif((is_category())&&($tptn_settings['add_to_category_archives'])) {
        return $content.echo_tptn_post_count(0);
    } elseif((is_tag())&&($tptn_settings['add_to_tag_archives'])) {
        return $content.echo_tptn_post_count(0);
    } elseif( ( (is_tax()) || (is_author()) || (is_date()) ) &&($tptn_settings['add_to_archives'])) {
        return $content.echo_tptn_post_count(0);
	} else {
		return $content;
	}
}
add_filter('the_content', 'tptn_pc_content');


/**
 * Filter to add related posts to feeds.
 * 
 * @access public
 * @param string $content
 * @return string
 */
function ald_tptn_rss($content) {
	global $post;
	global $tptn_settings;
	$id = intval($post->ID);

	if($tptn_settings['add_to_feed']) {
        return $content.'<div class="tptn_counter" id="tptn_counter_'.$id.'">'.get_tptn_post_count($id).'</div>';
    } else {
        return $content;
    }
}
add_filter('the_excerpt_rss', 'ald_tptn_rss');
add_filter('the_content_feed', 'ald_tptn_rss');


/**
 * Function to manually display count.
 * 
 * @access public
 * @param int|boolean $echo (default: 1)
 * @return string
 */
function echo_tptn_post_count($echo=1) {
	global $post,$tptn_url,$tptn_path;
	global $tptn_settings;
	$id = intval($post->ID);
	
	$nonce_action = 'tptn-nonce-'.$id;
    $nonce = wp_create_nonce($nonce_action);

	if ($tptn_settings['dynamic_post_count']) {
		$output = '<div class="tptn_counter" id="tptn_counter_'.$id.'"><script type="text/javascript" data-cfasync="false" src="'.$tptn_url.'/top-10-counter.js.php?top_ten_id='.$id.'&amp;_wpnonce='.$nonce.'"></script></div>';
	} else {
		$output = '<div class="tptn_counter" id="tptn_counter_'.$id.'">'.get_tptn_post_count($id).'</div>';
	}
	
	if ($echo) {
		echo $output;
	} else {
		return $output;
	}
}


/**
 * Return the post count.
 * 
 * @access public
 * @param int|string $id Post ID
 * @return int Post count
 */
function get_tptn_post_count($id) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "top_ten";
	$table_name_daily = $wpdb->prefix . "top_ten_daily";
	global $tptn_settings;
	$count_disp_form = stripslashes($tptn_settings['count_disp_form']);
	
	if($id > 0) {

		// Total count per post
		if (strpos($count_disp_form, "%totalcount%") !== false) {
			$resultscount = $wpdb->get_row("SELECT postnumber, cntaccess FROM ".$table_name." WHERE postnumber = ".$id);
			$cntaccess = number_format_i18n((($resultscount) ? $resultscount->cntaccess : 1));
			$count_disp_form = str_replace("%totalcount%", $cntaccess, $count_disp_form);
		}
		
		// Now process daily count
		if (strpos($count_disp_form, "%dailycount%") !== false) {
			$daily_range = $tptn_settings['daily_range'];
			$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
			$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
			$current_date = date ( 'Y-m-j' , $current_date );
	
			$resultscount = $wpdb->get_row("SELECT postnumber, SUM(cntaccess) as sumCount FROM ".$table_name_daily." WHERE postnumber = ".$id." AND dp_date >= '".$current_date."' GROUP BY postnumber ");
			$cntaccess = number_format_i18n((($resultscount) ? $resultscount->sumCount : 1));
			$count_disp_form = str_replace("%dailycount%", $cntaccess, $count_disp_form);
		}
		
		// Now process overall count
		if (strpos($count_disp_form, "%overallcount%") !== false) {
			$resultscount = $wpdb->get_row("SELECT SUM(cntaccess) as sumCount FROM ".$table_name);
			$cntaccess = number_format_i18n((($resultscount) ? $resultscount->sumCount : 1));
			$count_disp_form = str_replace("%overallcount%", $cntaccess, $count_disp_form);
		}
				
		
		return $count_disp_form;
	} else {
		return 0;
	}
}


/**
 * Function to return popular posts.
 * 
 * @access public
 * @param mixed $args
 * @return void
 */
function tptn_pop_posts( $args ) {
	$defaults = array(
		'is_widget' => FALSE,
		'daily' => FALSE,
		'echo' => FALSE,
	);
	$defaults = array_merge($defaults, tptn_read_options());
	
	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	if ($echo) { 	
		echo get_tptn_pop_posts($daily, $is_widget, $limit, $show_excerpt, $post_thumb_op, $daily_range);
	} else { 
		return get_tptn_pop_posts($daily, $is_widget, $limit, $show_excerpt, $post_thumb_op, $daily_range); 
	}
}

/**
 * Get the popular posts.
 * 
 * @access public
 * @param bool $daily (default: false)
 * @param bool $widget (default: false)
 * @param string $limit (default: '10')
 * @param bool $show_excerpt (default: false)
 * @param string $post_thumb_op (default: 'text_only')
 * @param string $daily_range (default: '1')
 * @return string Output of posts
 */
function get_tptn_pop_posts( $daily = false , $widget = false, $limit = false, $show_excerpt = false, $post_thumb_op = false, $daily_range = false, $strict_limit = false ) {
	global $wpdb, $siteurl, $tableposts, $id;
	if ($daily) $table_name = $wpdb->prefix . "top_ten_daily"; 
		else $table_name = $wpdb->prefix . "top_ten";
	global $tptn_settings;
	
	if (empty($limit)) $limit = stripslashes($tptn_settings['limit']);
	$limit = ($strict_limit) ? $limit : ($limit*5);	

	$show_excerpt = empty($show_excerpt) ? $tptn_settings['show_excerpt'] : $show_excerpt;
	$post_thumb_op = empty($post_thumb_op) ? $tptn_settings['post_thumb_op'] : $post_thumb_op;
	$daily_range = empty($daily_range) ? $tptn_settings['daily_range'] : $daily_range;
	
	$exclude_categories = explode(',',$tptn_settings['exclude_categories']);

	$target_attribute = (($tptn_settings['link_new_window']) ? ' target="_blank" ' : ' ' );
	$rel_attribute = (($tptn_settings['link_nofollow']) ? ' nofollow' : '' );
	$show_date = $tptn_settings['show_date'];
	
	parse_str($tptn_settings['post_types'],$post_types);	// Save post types in $post_types variable

	if (!$daily) {
		$sql = "SELECT postnumber, cntaccess as sumCount, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' ";
		if ($tptn_settings['exclude_post_ids']!='') $sql .= "AND ID NOT IN (".$tptn_settings['exclude_post_ids'].") ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";
	} else {
		$daily_range = $daily_range - 1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		if ($tptn_settings['exclude_post_ids']!='') $sql .= "AND ID NOT IN (".$tptn_settings['exclude_post_ids'].") ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $limit";
	}
	$results = $wpdb->get_results($sql);
	$counter = 0;

	$output = '';

	if (!$widget) {
		if (!$daily) {
			$output .= '<div id="tptn_related" class="tptn_posts">'.apply_filters('tptn_heading_title',$tptn_settings['title']);
		} else {
			$output .= '<div id="tptn_related_daily" class="tptn_posts_daily">'.apply_filters('tptn_heading_title',$tptn_settings['title_daily']);
		}
	} else {
		if (!$daily) {
			$output .= '<div class="tptn_posts">';
		} else {
			$output .= '<div class="tptn_posts_daily">';
		}
	}
	
	if ($results) {
		$output .= $tptn_settings['before_list'];
		foreach ($results as $result) {
			$sumcount = $result->sumCount;
			$result = get_post($result->ID);	// Let's get the Post using the ID
			$categorys = get_the_category($result->ID);	//Fetch categories of the plugin
			$p_in_c = false;	// Variable to check if post exists in a particular category

			foreach ($categorys as $cat) {	// Loop to check if post exists in excluded category
				$p_in_c = (in_array($cat->cat_ID, $exclude_categories)) ? true : false;
				if ($p_in_c) break;	// End loop if post found in category
			}

			//$title = trim(stripslashes(get_the_title($result->ID)));
			$title = tptn_max_formatted_content(get_the_title($result->ID),$tptn_settings['title_length']);

			if (!$p_in_c) {
				$output .= $tptn_settings['before_list_item'];

				$output .= '<a href="'.get_permalink($result->ID).'" rel="bookmark'.$rel_attribute.'" '.$target_attribute.'class="tptn_link">'; // Add beginning of link
				if ($post_thumb_op=='after') {
					$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title if post thumbnail is to be displayed after
				}
				if ($post_thumb_op=='inline' || $post_thumb_op=='after' || $post_thumb_op=='thumbs_only') {
					$output .= tptn_get_the_post_thumbnail('postid='.$result->ID.'&thumb_height='.$tptn_settings['thumb_height'].'&thumb_width='.$tptn_settings['thumb_width'].'&thumb_meta='.$tptn_settings['thumb_meta'].'&thumb_html='.$tptn_settings['thumb_html'].'&thumb_default='.$tptn_settings['thumb_default'].'&thumb_default_show='.$tptn_settings['thumb_default_show'].'&thumb_timthumb='.$tptn_settings['thumb_timthumb'].'&scan_images='.$tptn_settings['scan_images'].'&class=tptn_thumb&filter=tptn_postimage');
				}
				if ($post_thumb_op=='inline' || $post_thumb_op=='text_only') {
					$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title when required by settings
				}
				$output .= '</a>'; // Close the link
				if ($show_date) {
					$output .= '<span class="tptn_date"> '.mysql2date(get_option('date_format','d/m/y'), $result->post_date).'</span> ';
				}
				if ($show_excerpt) {
					$output .= '<span class="tptn_excerpt"> '.tptn_excerpt($result->ID,$tptn_settings['excerpt_length']).'</span>';
				}
				if ($tptn_settings['disp_list_count']) $output .= ' <span class="tptn_list_count">('.number_format_i18n($sumcount).')</span>';
		        
				$output .= $tptn_settings['after_list_item'];
				$counter++; 
			}
			if ($counter == $limit/5) break;	// End loop when related posts limit is reached
		}
		if ($tptn_settings['show_credit']) $output .= $tptn_settings['before_list_item'].'Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/" rel="nofollow">Top 10 plugin</a>'.$tptn_settings['after_list_item'];
		$output .= $tptn_settings['after_list'];
	} else {
		$output .= ($tptn_settings['blank_output']) ? '' : $tptn_settings['blank_output_text'];
	}
	$output .= '</div>';

	return $output;
}


/**
 * Function to echo popular posts.
 * 
 * @access public
 * @return void
 */
function tptn_show_pop_posts() {
	echo tptn_pop_posts('daily=0&is_widget=0');
}


/**
 * Function to show daily popular posts.
 * 
 * @access public
 * @return void
 */
function tptn_show_daily_pop_posts() {
	global $tptn_url;
	global $tptn_settings;
	if ($tptn_settings['d_use_js']) {
		echo '<script type="text/javascript" src="'.$tptn_url.'/top-10-daily.js.php?widget=1"></script>';
	} else {
		echo tptn_pop_posts('daily=1&is_widget=0');
	}
}


/**
 * Function to add CSS to header.
 * 
 * @access public
 * @return void
 */
function tptn_header() {
	global $wpdb, $post, $single;

	global $tptn_settings;
	$tptn_custom_CSS = stripslashes($tptn_settings['custom_CSS']);
	
	// Add CSS to header 
	if ($tptn_custom_CSS != '') {
			echo '<style type="text/css">'.$tptn_custom_CSS.'</style>';
	}
}
add_action('wp_head','tptn_header');
	
/**
 * Top 10 Widget.
 * 
 * @extends WP_Widget
 */
class WidgetTopTen extends WP_Widget
{
	function WidgetTopTen()
	{
		$widget_ops = array('classname' => 'widget_tptn_pop', 'description' => __( 'Display the posts popular this week',TPTN_LOCAL_NAME) );
		$this->WP_Widget('widget_tptn_pop',__('Popular Posts',TPTN_LOCAL_NAME), $widget_ops);
	}
	
	function form($instance) {
		$title = esc_attr($instance['title']);
		$limit = esc_attr($instance['limit']);
		$show_excerpt = esc_attr($instance['show_excerpt']);
		$post_thumb_op = esc_attr($instance['post_thumb_op']);
		$daily = esc_attr($instance['daily']);
		$daily_range = esc_attr($instance['daily_range']);
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title', TPTN_LOCAL_NAME); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /> 
		</label>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('limit'); ?>">
		<?php _e('No. of posts', TPTN_LOCAL_NAME); ?>: <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo attribute_escape($limit); ?>" /> 
		</label>
		</p>
		<p>
		<select class="widefat" id="<?php echo $this->get_field_id('daily'); ?>" name="<?php echo $this->get_field_name('daily'); ?>">
		  <option value="overall" <?php if ($daily=='overall') echo 'selected="selected"' ?>><?php _e('Overall', TPTN_LOCAL_NAME); ?></option>
		  <option value="daily" <?php if ($daily=='daily') echo 'selected="selected"' ?>><?php _e('Custom time period (Enter below)', TPTN_LOCAL_NAME); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('daily_range'); ?>">
		<?php _e('Range in number of days (applies only to custom option above)', TPTN_LOCAL_NAME); ?>: <input class="widefat" id="<?php echo $this->get_field_id('daily_range'); ?>" name="<?php echo $this->get_field_name('daily_range'); ?>" type="text" value="<?php echo attribute_escape($daily_range); ?>" /> 
		</label>
		</p>
		<p>
		<?php _e('Thumbnail options', TPTN_LOCAL_NAME); ?>: <br />
		<select class="widefat" id="<?php echo $this->get_field_id('post_thumb_op'); ?>" name="<?php echo $this->get_field_name('post_thumb_op'); ?>">
		  <option value="inline" <?php if ($post_thumb_op=='inline') echo 'selected="selected"' ?>><?php _e('Thumbnails inline, before title',TPTN_LOCAL_NAME); ?></option>
		  <option value="after" <?php if ($post_thumb_op=='after') echo 'selected="selected"' ?>><?php _e('Thumbnails inline, after title',TPTN_LOCAL_NAME); ?></option>
		  <option value="thumbs_only" <?php if ($post_thumb_op=='thumbs_only') echo 'selected="selected"' ?>><?php _e('Only thumbnails, no text',TPTN_LOCAL_NAME); ?></option>
		  <option value="text_only" <?php if ($post_thumb_op=='text_only') echo 'selected="selected"' ?>><?php _e('No thumbnails, only text.',TPTN_LOCAL_NAME); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_excerpt'); ?>">
		<input id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>" type="checkbox" <?php if ($show_excerpt) echo 'checked="checked"' ?> /> <?php _e(' Show excerpt?', TPTN_LOCAL_NAME); ?>
		</label>
		</p>
		<?php
	} //ending form creation
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['limit'] = ($new_instance['limit']);
		$instance['daily'] = ($new_instance['daily']);
		$instance['daily_range'] = strip_tags($new_instance['daily_range']);
		$instance['show_excerpt'] = ($new_instance['show_excerpt']);
		$instance['post_thumb_op'] = ($new_instance['post_thumb_op']);
		return $instance;
	} //ending update
	function widget($args, $instance) {
		global $wpdb, $tptn_url;
		
		extract($args, EXTR_SKIP);
		
		global $tptn_settings;

		$title = apply_filters('widget_title', empty($instance['title']) ? strip_tags($tptn_settings['title']) : $instance['title']);
		$limit = $instance['limit'];
		$show_excerpt = $instance['show_excerpt'];
		$post_thumb_op = $instance['post_thumb_op'];
		if (empty($limit)) $limit = $tptn_settings['limit'];
		$daily_range = $instance['daily_range'];
		if (empty($daily_range)) $daily_range = $tptn_settings['daily_range'];
		$daily = $instance['daily'];
		$daily = (($daily=="daily") ? true : false);

		$output = $before_widget;
		$output .= $before_title . $title . $after_title;

		if ($daily) {
			if ($tptn_settings['d_use_js']) {
				$output .= '<script type="text/javascript" src="'.$tptn_url.'/top-10-daily.js.php?widget=1"></script>';
			} else {
				$output .= tptn_pop_posts('daily=1&is_widget=1&limit='.$limit.'&show_excerpt='.$show_excerpt.'&post_thumb_op='.$post_thumb_op.'&daily_range='.$daily_range);
			}
		} else {
			$output .= tptn_pop_posts('daily=0&is_widget=1&limit='.$limit.'&show_excerpt='.$show_excerpt.'&post_thumb_op='.$post_thumb_op.'&daily_range='.$daily_range);
		}

		$output .= $after_widget;
	
		echo $output;

	} //ending function widget
}

/**
 * Initialise the plugin.
 * 
 * @access public
 * @return void
 */
function init_tptn(){

	if (function_exists('register_widget')) { 
		register_widget('WidgetTopTen');
	}
}
add_action('init', 'init_tptn', 1); 


/**
 * Default Options.
 * 
 * @access public
 * @return void
 */
function tptn_default_options() {
	global $tptn_url;
	$title = __('<h3>Popular Posts</h3>',TPTN_LOCAL_NAME);
	$title_daily = __('<h3>Daily Popular</h3>',TPTN_LOCAL_NAME);
	$blank_output_text = __('No top posts yet',TPTN_LOCAL_NAME);
	$thumb_default = $tptn_url.'/default.png';

	// get relevant post types
	$args = array (
				'public' => true,
				'_builtin' => true
			);
	$post_types	= http_build_query(get_post_types($args), '', '&');

	$tptn_settings = 	Array (
						'show_credit' => false,			// Add link to plugin page of my blog in top posts list
						'add_to_content' => true,			// Add post count to content (only on single posts)
						'count_on_pages' => true,			// Display on pages
						'add_to_feed' => true,		// Add related posts to feed (full)
						'add_to_home' => false,		// Add related posts to home page
						'add_to_category_archives' => false,		// Add related posts to category archives
						'add_to_tag_archives' => false,		// Add related posts to tag archives
						'add_to_archives' => false,		// Add related posts to other archives
						'track_authors' => false,			// Track Authors visits
						'track_admins' => true,			// Track Admin visits
						'pv_in_admin' => true,			// Add an extra column on edit posts/pages to display page views?
						'blank_output' => false,		// Blank output? Default is "blank Output test"
						'blank_output_text' => $blank_output_text,		// Blank output text
						'disp_list_count' => true,		// Display count in popular lists?
						'd_use_js' => false,				// Use JavaScript for displaying daily posts
						'dynamic_post_count' => true,		// Use JavaScript for displaying the post count
						'count_disp_form' => '(Visited %totalcount% times, %dailycount% visits today)',	// Format to display the count
						'title' => $title,				// Title of Popular Posts
						'title_daily' => $title_daily,	// Title of Daily Popular
						'limit' => '10',					// How many posts to display?
						'daily_range' => '1',				// Daily Popular will contain posts of how many days?
						'before_list' => '<ul>',			// Before the entire list
						'after_list' => '</ul>',			// After the entire list
						'before_list_item' => '<li>',		// Before each list item
						'after_list_item' => '</li>',		// After each list item
						'post_thumb_op' => 'inline',	// Display only text in posts
						'thumb_height' => '50',			// Max height of thumbnails
						'thumb_width' => '50',			// Max width of thumbnails
						'thumb_html' => 'html',		// Use HTML or CSS for width and height of the thumbnail?
						'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
						'thumb_default' => $thumb_default,	// Default thumbnail image
						'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
						'thumb_timthumb' => true,	// Use timthumb
						'scan_images' => true,			// Scan post for images
						'show_excerpt' => false,			// Show description in list item
						'show_date' => false,			// Show date in list item
						'excerpt_length' => '10',			// Length of characters
						'title_length' => '60',		// Limit length of post title
						'exclude_categories' => '',		// Exclude these categories
						'exclude_cat_slugs' => '',		// Exclude these categories (slugs)
						'exclude_post_ids' => '',	// Comma separated list of page / post IDs that are to be excluded in the results
						'exclude_on_post_ids' => '', 	// Comma separate list of page/post IDs to not display related posts on
						'custom_CSS' => '',			// Custom CSS to style the output
						'cron_on' => false,		// Run cron daily?
						'cron_hour' => '0',		// Cron Hour
						'cron_min' => '0',		// Cron Minute
						'cron_recurrence' => 'weekly',	// Frequency of cron
						'activate_daily' => true,	// Activate the daily count
						'activate_overall' => true,	// activate overall count
						'cache_fix' => false,		// Temporary fix for W3 Total Cache
						'post_types' => $post_types,		// WordPress custom post types
						'link_new_window' => false,			// Open link in new window - Includes target="_blank" to links
						'link_nofollow' => false,			// Includes rel="nofollow" to links
						);
	return $tptn_settings;
}


/**
 * Function to read options from the database.
 * 
 * @access public
 * @return void
 */
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


/**
 * Create tables to store pageviews.
 * 
 * @access public
 * @return void
 */
function tptn_install() {
   global $wpdb;
   global $tptn_db_version;

   $table_name = $wpdb->prefix . "top_ten";
   $table_name_daily = $wpdb->prefix . "top_ten_daily";
   
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
	$sql = "CREATE TABLE " . $table_name . " (
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		PRIMARY KEY  (postnumber)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	add_option("tptn_db_version", $tptn_db_version);
   }
   
   if($wpdb->get_var("show tables like '$table_name_daily'") != $table_name_daily) {
      
	$sql = "CREATE TABLE " . $table_name_daily . " (
		postnumber int NOT NULL,
		cntaccess int NOT NULL,
		dp_date date NOT NULL,
		PRIMARY KEY  (postnumber, dp_date)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	add_option("tptn_db_version", $tptn_db_version);
   }
   
   // Upgrade table code
   $installed_ver = get_option( "tptn_db_version" );

   if( $installed_ver != $tptn_db_version ) {

	$sql = "ALTER TABLE " . $table_name . " DROP COLUMN accessedid ";
	$wpdb->query($sql);
	$sql = "ALTER IGNORE TABLE " . $table_name . " ADD PRIMARY KEY (postnumber) ";
	$wpdb->query($sql);

	$sql = "ALTER TABLE " . $table_name_daily . " DROP COLUMN accessedid ";
	$wpdb->query($sql);
	$sql = "ALTER IGNORE TABLE " . $table_name_daily . " ADD PRIMARY KEY (postnumber, dp_date) ";
	$wpdb->query($sql);
	  	  
	update_option( "tptn_db_version", $tptn_db_version );
  }

}
if (function_exists('register_activation_hook')) {
	register_activation_hook(__FILE__,'tptn_install');
}
/**
 * Function to call install function if needed.
 * 
 * @access public
 * @return void
 */
function tptn_update_db_check() {
    global $tptn_db_version;
    if (get_site_option('tptn_db_version') != $tptn_db_version) {
        tptn_install();
    }
}
add_action('plugins_loaded', 'tptn_update_db_check');


/**
 * Function to delete all rows in the posts table.
 * 
 * @access public
 * @param bool $daily (default: false)
 * @return void
 */
function tptn_trunc_count($daily = false) {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query($sql);
}

/*********************************************************************
*				Utility Functions									*
********************************************************************/
/**
 * Filter function to resize post thumbnail. Filters out tp10_postimage.
 * 
 * @access public
 * @param string $postimage
 * @param string|int $thumb_width
 * @param string|int $thumb_height
 * @param string|int $thumb_timthumb
 * @return string
 */
function tptn_scale_thumbs($postimage, $thumb_width, $thumb_height, $thumb_timthumb) {
	global $tptn_url;
	
	if ($thumb_timthumb) {
		$new_pi = $tptn_url.'/timthumb/timthumb.php?src='.urlencode($postimage).'&amp;w='.$thumb_width.'&amp;h='.$thumb_height.'&amp;zc=1&amp;q=75';		
	} else {
		$new_pi = $postimage;
	}
	return $new_pi;
}
add_filter('tptn_postimage', 'tptn_scale_thumbs', 10, 4);


/**
 * Function to get the post thumbnail.
 * 
 * @access public
 * @param array $args (default: array()) Query string of options related to thumbnails
 * @return string
 */
function tptn_get_the_post_thumbnail($args = array()) {

	global $tptn_url;
	$defaults = array(
		'postid' => '',
		'thumb_height' => '50',			// Max height of thumbnails
		'thumb_width' => '50',			// Max width of thumbnails
		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'thumb_html' => 'html',		// HTML / CSS for width and height attributes
		'thumb_default' => '',	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
		'thumb_timthumb' => true,	// Use timthumb
		'scan_images' => false,			// Scan post for images
		'class' => 'tptn_thumb',			// Class of the thumbnail
		'filter' => 'tptn_postimage',			// Class of the thumbnail
	);
	
	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$result = get_post($postid);

	$output = '';
	$title = get_the_title($postid);
	$thumb_html = ($thumb_html=='css') ? 'style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;"' : 'width="'.$thumb_width.'" height="'.$thumb_height.'"';
	
	if (function_exists('has_post_thumbnail') && ( (wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) )!='') || (wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) )!= false) ) ) {
		$postimage = wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) );
		
		if ( ($postimage[1] < $thumb_width) || ($postimage[2] < $thumb_height) ) $postimage = wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) , 'full' ); 
		$postimage = apply_filters( $filter, $postimage[0], $thumb_width, $thumb_height, $thumb_timthumb );
		$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" '.$thumb_html.' border="0" class="'.$class.'" />';

	} else {
		$postimage = get_post_meta($result->ID, $thumb_meta, true);	// Check
		if (!$postimage && $scan_images) {
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $result->post_content, $matches );
			// any image there?
			if (isset($matches[1][0]) && $matches[1][0]) {
				if (((strpos($matches[1][0], parse_url(get_option('home'),PHP_URL_HOST)) !== false) && (strpos($matches[1][0], 'http://') !== false))|| ((strpos($matches[1][0], 'http://') === false))) {
					$postimage = preg_replace('/\?.*/', '', $matches[1][0]); // we need the first one only!
				}
			}
		}
		if (!$postimage) $postimage = tptn_get_first_image($result->ID);	// Get the first image
		if (!$postimage) $postimage = get_post_meta($result->ID, '_video_thumbnail', true); // If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin
		if ($thumb_default_show && !$postimage) $postimage = $thumb_default; // If no thumb found and settings permit, use default thumb
		if ($postimage) {
			if ($thumb_timthumb) {
				$output .= '<img src="'.$tptn_url.'/timthumb/timthumb.php?src='.urlencode($postimage).'&amp;w='.$thumb_width.'&amp;h='.$thumb_height.'&amp;zc=1&amp;q=75" alt="'.$title.'" title="'.$title.'" '.$thumb_html.' border="0" class="'.$class.'" />';
			} else {
				$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" '.$thumb_html.' border="0" class="'.$class.'" />';
			}
		}
	}
	
	return $output;
}

/**
 * Get the first image in the post.
 * 
 * @access public
 * @param mixed $postID	Post ID
 * @return string
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

			return $image_attributes[0];
		}
	} else {
		return false;
	}
}


/**
 * Function to create an excerpt for the post.
 * 
 * @access public
 * @param string|int $postid Post ID
 * @param int $excerpt_length Length of the excerpt
 * @return string Formatted excerpt
 */
function tptn_excerpt($postid,$excerpt_length){
	$content = get_post($postid)->post_excerpt;
	if ($content=='') $content = get_post($postid)->post_content;
	$out = strip_tags($content);
	$blah = explode(' ',$out);
	if (!$excerpt_length) $excerpt_length = 10;
	if(count($blah) > $excerpt_length){
		$k = $excerpt_length;
		$use_dotdotdot = 1;
	}else{
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for($i=0; $i<$k; $i++){
		$excerpt .= $blah[$i].' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	$out = $excerpt;
	return $out;
}

/**
 * Function to limit content by characters.
 * 
 * @access public
 * @param string $content Content to be used to make an excerpt
 * @param int $MaxLength (default: -1) Maximum length of excerpt in characters
 * @return string Formatted content
 */
function tptn_max_formatted_content($content, $MaxLength = -1) {
  $content = strip_tags($content);  // Remove CRLFs, leaving space in their wake

  if (($MaxLength > 0) && (strlen($content) > $MaxLength)) {
    $aWords = preg_split("/[\s]+/", substr($content, 0, $MaxLength));

    // Break back down into a string of words, but drop the last one if it's chopped off
    if (substr($content, $MaxLength, 1) == " ") {
      $content = implode(" ", $aWords);
    } else {
      $content = implode(" ", array_slice($aWords, 0, -1)).'&hellip;';
    }
  }

  return $content;
}

/*********************************************************************
*				Cron Functions 										*
********************************************************************/
/**
 * Function to truncate daily run.
 * 
 * @access public
 * @return void
 */
function ald_tptn() {
	tptn_trunc_count(true);
}
add_action('ald_tptn_hook', 'ald_tptn');


/**
 * Function to enable run or actions.
 * 
 * @access public
 * @param int $hour
 * @param int $min
 * @param int $recurrence
 * @return void
 */
function tptn_enable_run($hour, $min, $recurrence) {
	if (function_exists('wp_schedule_event')) {
		// Invoke WordPress internal cron
		if (!wp_next_scheduled('ald_tptn_hook')) {
			wp_schedule_event( mktime($hour,$min, 0), $recurrence, 'ald_tptn_hook' );
		} else {
			wp_clear_scheduled_hook('ald_tptn_hook');
			wp_schedule_event( mktime($hour,$min, 0), $recurrence, 'ald_tptn_hook' );
		}
	}
}


/**
 * Function to disable daily run or actions.
 * 
 * @access public
 * @return void
 */
function tptn_disable_run() {
	if (function_exists('wp_schedule_event')) {
		if (wp_next_scheduled('ald_tptn_hook')) {
			wp_clear_scheduled_hook('ald_tptn_hook');
		}
	}
}

if (!function_exists('ald_more_reccurences')) {
/**
 * Function to add weekly and fortnightly recurrences - Sample Code courtesy http://blog.slaven.net.au/archives/2007/02/01/timing-is-everything-scheduling-in-wordpress/.
 * 
 * @access public
 * @return void
 */
function ald_more_reccurences() {
	return array(
		'weekly' => array('interval' => 604800, 'display' => __( 'Once Weekly', TPTN_LOCAL_NAME )),
		'fortnightly' => array('interval' => 1209600, 'display' => __( 'Once Fortnightly', TPTN_LOCAL_NAME )),
		'monthly' => array('interval' => 2419200, 'display' => __( 'Once Monthly', TPTN_LOCAL_NAME )),
	);
}
add_filter('cron_schedules', 'ald_more_reccurences');
}


/*********************************************************************
*				Admin interface										*
********************************************************************/
// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_TPTN_DIR . "/admin.inc.php");

	// Adding WordPress plugin action links
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tptn_plugin_actions_links' );
	function tptn_plugin_actions_links( $links ) {
	
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=tptn_options' ) . '">' . __('Settings', TPTN_LOCAL_NAME ) . '</a>'
			),
			$links
		);
	
	}
	// Add meta links
	function tptn_plugin_actions( $links, $file ) {
		$plugin = plugin_basename(__FILE__);
	 
		// create link
		if ($file == $plugin) {
			$links[] = '<a href="http://ajaydsouza.com/support/">' . __('Support', TPTN_LOCAL_NAME ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/donate/">' . __('Donate', TPTN_LOCAL_NAME ) . '</a>';
		}
		return $links;
	}
	global $wp_version;
	if ( version_compare( $wp_version, '2.8alpha', '>' ) )
		add_filter( 'plugin_row_meta', 'tptn_plugin_actions', 10, 2 ); // only 2.8 and higher
	else add_filter( 'plugin_action_links', 'tptn_plugin_actions', 10, 2 );

} // End admin.inc


?>