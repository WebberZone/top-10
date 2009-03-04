<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
function tptn_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$tptn_settings = tptn_read_options();

	if($_POST['tptn_save']){
		$tptn_settings[title] = ($_POST['title']);
		$tptn_settings[title_daily] = ($_POST['title_daily']);
		$tptn_settings[limit] = ($_POST['limit']);
		$tptn_settings[count_disp_form] = ($_POST['count_disp_form']);
		$tptn_settings[add_to_content] = (($_POST['add_to_content']) ? true : false);
		$tptn_settings[exclude_pages] = (($_POST['exclude_pages']) ? true : false);
		$tptn_settings[track_authors] = (($_POST['track_authors']) ? true : false);
		$tptn_settings[pv_in_admin] = (($_POST['pv_in_admin']) ? true : false);
		$tptn_settings[show_credit] = (($_POST['show_credit']) ? true : false);
		
		update_option('ald_tptn_settings', $tptn_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.','ald_tptn_plugin') .'</p></div>';
		echo $str;
	}
	
	if ($_POST['tptn_default']){
		delete_option('ald_tptn_settings');
		$tptn_settings = tptn_default_options();
		update_option('ald_tptn_settings', $tptn_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.','ald_tptn_plugin') .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2>Top 10 </h2>
  <div style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Support the Development','ald_tptn_plugin'); ?>
    </h3>
    </legend>
    <p>
      <?php _e('If you find ','ald_tptn_plugin'); ?>
      <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10</a>
      <?php _e('useful, please do','ald_tptn_plugin'); ?>
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business=donate@ajaydsouza.com&amp;item_name=Top%10%20(From%20WP-Admin)&amp;no_shipping=1&amp;return=http://ajaydsouza.com/wordpress/plugins/top-10/&amp;cancel_return=http://ajaydsouza.com/wordpress/plugins/top-10/&amp;cn=Note%20to%20Author&amp;tax=0&amp;currency_code=USD&amp;bn=PP-DonationsBF&amp;charset=UTF-8" title="Donate via PayPal"><?php _e('drop in your contribution','ald_tptn_plugin'); ?></a>.
	  (<a href="http://ajaydsouza.com/donate/"><?php _e('Some reasons why you should.','ald_tptn_plugin'); ?></a>)</p>
    </fieldset>
  </div>
  <form method="post" id="tptn_options" name="tptn_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:','ald_tptn_plugin'); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Format to display the count in: ','ald_tptn_plugin'); ?><br />
      <textarea name="count_disp_form" id="count_disp_form" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($tptn_settings[count_disp_form])); ?></textarea>
      </label>
    </p>
	<p><?php _e('Use <code>%totalcount%</code> to display the total count and <code>%dailycount%</code> to display the daily count. e.g. the default options displays <code>(Visited 123 times, 23 visits today)</code>','ald_tptn_plugin'); ?></p>
    <p>
      <label>
      <?php _e('Number of popular posts to display: ','ald_tptn_plugin'); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($tptn_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of popular posts: ','ald_tptn_plugin'); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($tptn_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of daily popular posts: ','ald_tptn_plugin'); ?>
      <input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes($tptn_settings[title_daily]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="exclude_pages" id="exclude_pages" <?php if ($tptn_settings[exclude_pages]) echo 'checked="checked"' ?> />
      <?php _e('Exclude Pages in display of Popular Posts? Number of views on Pages will continue to be counted.','ald_tptn_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="track_authors" id="track_authors" <?php if ($tptn_settings[track_authors]) echo 'checked="checked"' ?> />
      <?php _e('Track visits of authors on their own posts?','ald_tptn_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="pv_in_admin" id="pv_in_admin" <?php if ($tptn_settings[pv_in_admin]) echo 'checked="checked"' ?> />
      <?php _e('Display page views on Edit posts/pages in WP-Admin? An extra column is added with the count','ald_tptn_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_content" id="add_to_content" <?php if ($tptn_settings[add_to_content]) echo 'checked="checked"' ?> />
      <?php _e('Add post count to content?','ald_tptn_plugin'); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($tptn_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('A link to the plugin is added as an extra list item to the list of popular posts. Not mandatory, but thanks if you do it!','ald_tptn_plugin'); ?>
      </label>
    </p>
    <p>
      <input type="submit" name="tptn_save" id="tptn_save" value="Save Options" style="border:#00CC00 1px solid" />
      <input name="tptn_default" type="submit" id="tptn_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default? If you don\'t have a copy of the username, please hit Cancel and copy it first.','ald_tptn_plugin'); ?>')) return false;" />
    </p>
    </fieldset>
  </form>
</div>
<?php

}

/* Add menu item in WP-Admin */
function tptn_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$tptn_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$tptn_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($tptn_is_admin)) {
		add_options_page(__("Top 10", 'myald_tptn_plugin'), __("Top 10", 'myald_tptn_plugin'), 9, 'tptn_options', 'tptn_options');
		}
}
add_action('admin_menu', 'tptn_adminmenu');


/* Create a Dashboard Widget */
// Dashboard for Popular Posts
function tptn_pop_dashboard() {
	global $wpdb, $siteurl, $tableposts, $id;

	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$sql = "SELECT postnumber, cntaccess , ID, post_type ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "AND post_status = 'publish' ";
	$sql .= "ORDER BY cntaccess DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	echo '<ul>';
	if ($results) {
		foreach ($results as $result) {
			echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a> ('.$result->cntaccess.')</li>';
		}
	}
	if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
	echo '</ul>';
}
 
// Dashboard for Daily Popular Posts
function tptn_pop_daily_dashboard() {
	global $wpdb, $siteurl, $tableposts, $id;

	$table_name = $wpdb->prefix . "top_ten_daily";
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	
	$sql = "SELECT postnumber, cntaccess , ID, post_type ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "AND post_status = 'publish' ";
	$sql .= "ORDER BY cntaccess DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	echo '<ul>';
	if ($results) {
		foreach ($results as $result) {
			echo '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a> ('.$result->cntaccess.')</li>';
		}
	}
	if ($tptn_settings['show_credit']) echo '<li>Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></li>';
	echo '</ul>';
}
 
function tptn_pop_dashboard_setup() {
	if (function_exists('wp_add_dashboard_widget')) {
		wp_add_dashboard_widget( 'tptn_pop_dashboard', __( 'Popular Posts' ), 'tptn_pop_dashboard' );
		wp_add_dashboard_widget( 'tptn_pop_daily_dashboard', __( 'Daily Popular' ), 'tptn_pop_daily_dashboard' );
	}
}
add_action('wp_dashboard_setup', 'tptn_pop_dashboard_setup');

/* Display page views on the Edit Posts / Pages screen */
// Add an extra column
function tptn_column($cols) {
	$tptn_settings = tptn_read_options();
	
	if ($tptn_settings[pv_in_admin])	$cols['tptn'] = __('Total / Today\'s Views','ald_tptn_plugin');
	return $cols;
}

// Display page views for each column
function tptn_value($column_name, $id) {
	$tptn_settings = tptn_read_options();
	if (($column_name == 'tptn')&&($tptn_settings[pv_in_admin])) {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "top_ten";
		
		$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $id");
		$cntaccess = number_format((($resultscount) ? $resultscount->cntaccess : 0));

		$cntaccess .= ' / ';
		
		$table_name = $wpdb->prefix . "top_ten_daily";
		
		$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $id");
		$cntaccess .= number_format((($resultscount) ? $resultscount->cntaccess : 0));
		echo $cntaccess;
	}
}

// Output CSS for width of new column
function tptn_css() {
?>
<style type="text/css">
	#tptn { width: 50px; }
</style>
<?php	
}

// Actions/Filters for various tables and the css output
add_filter('manage_posts_columns', 'tptn_column');
add_action('manage_posts_custom_column', 'tptn_value', 10, 2);
add_filter('manage_pages_columns', 'tptn_column');
add_action('manage_pages_custom_column', 'tptn_value', 10, 2);
add_filter('manage_media_columns', 'tptn_column');
add_action('manage_media_custom_column', 'tptn_value', 10, 2);
add_filter('manage_link-manager_columns', 'tptn_column');
add_action('manage_link_custom_column', 'tptn_value', 10, 2);
add_action('admin_head', 'tptn_css');

?>