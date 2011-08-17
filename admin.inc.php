<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
define('ALD_TPTN_DIR', dirname(__FILE__));
if (!defined('TPTN_LOCAL_NAME')) define('TPTN_LOCAL_NAME', 'tptn');

function tptn_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$tptn_settings = tptn_read_options();

	if($_POST['tptn_save']){
		$tptn_settings[title] = ($_POST['title']);
		$tptn_settings[title_daily] = ($_POST['title_daily']);
		$tptn_settings[daily_range] = intval($_POST['daily_range']);
		$tptn_settings[limit] = intval($_POST['limit']);
		$tptn_settings[count_disp_form] = ($_POST['count_disp_form']);
		$tptn_settings[add_to_content] = (($_POST['add_to_content']) ? true : false);
		$tptn_settings[exclude_pages] = (($_POST['exclude_pages']) ? true : false);
		$tptn_settings[count_on_pages] = (($_POST['count_on_pages']) ? true : false);
		$tptn_settings[track_authors] = (($_POST['track_authors']) ? true : false);
		$tptn_settings[pv_in_admin] = (($_POST['pv_in_admin']) ? true : false);
		$tptn_settings[disp_list_count] = (($_POST['disp_list_count']) ? true : false);
		$tptn_settings[d_use_js] = (($_POST['d_use_js']) ? true : false);
		$tptn_settings[show_credit] = (($_POST['show_credit']) ? true : false);

		$tptn_settings[post_thumb_op] = $_POST['post_thumb_op'];
		$tptn_settings[before_list] = $_POST['before_list'];
		$tptn_settings[after_list] = $_POST['after_list'];
		$tptn_settings[before_list_item] = $_POST['before_list_item'];
		$tptn_settings[after_list_item] = $_POST['after_list_item'];
		$tptn_settings[thumb_meta] = $_POST['thumb_meta'];
		$tptn_settings[thumb_default] = $_POST['thumb_default'];
		$tptn_settings[thumb_height] = intval($_POST['thumb_height']);
		$tptn_settings[thumb_width] = intval($_POST['thumb_width']);
		$tptn_settings[scan_images] = (($_POST['scan_images']) ? true : false);
		$tptn_settings[show_excerpt] = (($_POST['show_excerpt']) ? true : false);
		$tptn_settings[excerpt_length] = intval($_POST['excerpt_length']);
		$tptn_settings[exclude_cat_slugs] = ($_POST['exclude_cat_slugs']);

		$exclude_categories_slugs = explode(", ",$tptn_settings[exclude_cat_slugs]);
		
		$exclude_categories = '';
		foreach ($exclude_categories_slugs as $exclude_categories_slug) {
			$catObj = get_category_by_slug($exclude_categories_slug);
			$exclude_categories .= $catObj->term_id . ',';
		}
		$tptn_settings[exclude_categories] = substr($exclude_categories, 0, -2);

		
		update_option('ald_tptn_settings', $tptn_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	
	if ($_POST['tptn_default']){
		delete_option('ald_tptn_settings');
		$tptn_settings = tptn_default_options();
		update_option('ald_tptn_settings', $tptn_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if ($_POST['tptn_trunc_all']){
		tptn_trunc_count(false);
		$str = '<div id="message" class="updated fade"><p>'. __('Top 10 popular posts reset',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if ($_POST['tptn_trunc_daily']){
		tptn_trunc_count(true);
		$str = '<div id="message" class="updated fade"><p>'. __('Top 10 daily popular posts reset',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if ($_POST['tptn_clean_duplicates']){
		tptn_clean_duplicates(true);
		tptn_clean_duplicates(false);
		$str = '<div id="message" class="updated fade"><p>'. __('Tables cleaned of duplicate rows',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
  <h2>Top 10</h2>
  <div id="options-div">
  <form method="post" id="tptn_options" name="tptn_options" style="border: #ccc 1px solid; padding: 10px">
    <fieldset class="options">
    <legend>
    <h3>
      <?php _e('Options:',TPTN_LOCAL_NAME); ?>
    </h3>
    </legend>
    <p>
      <label>
      <?php _e('Format to display the count in: ',TPTN_LOCAL_NAME); ?><br />
      <textarea name="count_disp_form" id="count_disp_form" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($tptn_settings[count_disp_form])); ?></textarea>
      </label>
    </p>
	<p><?php _e('Use <code>%totalcount%</code> to display the total count and <code>%dailycount%</code> to display the daily count. e.g. the default options displays <code>(Visited 123 times, 23 visits today)</code>',TPTN_LOCAL_NAME); ?></p>
    <p>
      <label>
      <?php _e('Number of popular posts to display: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="limit" id="limit" value="<?php echo stripslashes($tptn_settings[limit]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Daily Popular should contain views of how many days? ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes($tptn_settings[daily_range]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="exclude_pages" id="exclude_pages" <?php if ($tptn_settings[exclude_pages]) echo 'checked="checked"' ?> />
      <?php _e('Exclude Pages in display of Popular Posts? Number of views on Pages will continue to be counted.',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="add_to_content" id="add_to_content" <?php if ($tptn_settings[add_to_content]) echo 'checked="checked"' ?> />
      <?php _e('Display number of views on posts?',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="count_on_pages" id="count_on_pages" <?php if ($tptn_settings[count_on_pages]) echo 'checked="checked"' ?> />
      <?php _e('Display number of views on pages?',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="track_authors" id="track_authors" <?php if ($tptn_settings[track_authors]) echo 'checked="checked"' ?> />
      <?php _e('Track visits of authors on their own posts?',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="disp_list_count" id="disp_list_count" <?php if ($tptn_settings[disp_list_count]) echo 'checked="checked"' ?> />
      <?php _e('Display number of page views in popular lists?',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="d_use_js" id="d_use_js" <?php if ($tptn_settings[d_use_js]) echo 'checked="checked"' ?> />
      <?php _e('Force daily posts\' list to be dynamic? This option uses JavaScript to load the post and can increase your page load time',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="pv_in_admin" id="pv_in_admin" <?php if ($tptn_settings[pv_in_admin]) echo 'checked="checked"' ?> />
      <?php _e('Display page views on Edit posts/pages in WP-Admin? An extra column is added with the count',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_credit" id="show_credit" <?php if ($tptn_settings[show_credit]) echo 'checked="checked"' ?> />
      <?php _e('A link to the plugin is added as an extra list item to the list of popular posts. Not mandatory, but thanks if you do it!',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <h4>
      <?php _e('Output Options:',TPTN_LOCAL_NAME); ?>
    </h4>
    <p>
      <label>
      <?php _e('Title of popular posts: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="title" id="title" value="<?php echo stripslashes($tptn_settings[title]); ?>">
      </label>
    </p>
    <p>
      <label>
      <?php _e('Title of daily popular posts: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="title_daily" id="title_daily" value="<?php echo stripslashes($tptn_settings[title_daily]); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ($tptn_settings[show_excerpt]) echo 'checked="checked"' ?> />
      <strong><?php _e('Show post excerpt in list?',TPTN_LOCAL_NAME); ?></strong>
      </label>
    </p>
    <p>
      <label>
      <?php _e('Length of excerpt (in words): ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes($tptn_settings[excerpt_length]); ?>">
      </label>
    </p>
    <p><?php _e('Exclude Categories: ',tptn_LOCAL_NAME); ?></p>
	<div style="position:relative;text-align:left">
		<table id="MYCUSTOMFLOATER" class="myCustomFloater" style="position:absolute;top:50px;left:0;background-color:#cecece;display:none;visibility:hidden">
		<tr><td><!--
				please see: http://chrisholland.blogspot.com/2004/09/geekstuff-css-display-inline-block.html
				to explain why i'm using a table here.
				You could replace the table/tr/td with a DIV, but you'd have to specify it's width and height
				-->
			<div class="myCustomFloaterContent">
			you should never be seeing this
			</div>
		</td></tr>
		</table>
		<textarea class="wickEnabled:MYCUSTOMFLOATER" cols="50" rows="3" wrap="virtual" name="exclude_cat_slugs"><?php echo (stripslashes($tptn_settings[exclude_cat_slugs])); ?></textarea>
	</div>
	<h4><?php _e('Customize the output:',TPTN_LOCAL_NAME); ?></h4>
	<p>
      <label>
      <?php _e('HTML to display before the list of posts: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="before_list" id="before_list" value="<?php echo attribute_escape(stripslashes($tptn_settings[before_list])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display before each list item: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo attribute_escape(stripslashes($tptn_settings[before_list_item])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display after each list item: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo attribute_escape(stripslashes($tptn_settings[after_list_item])); ?>">
      </label>
	</p>
	<p>
      <label>
      <?php _e('HTML to display after the list of posts: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="after_list" id="after_list" value="<?php echo attribute_escape(stripslashes($tptn_settings[after_list])); ?>">
      </label>
	</p>
	<h4><?php _e('Post thumbnail options:',TPTN_LOCAL_NAME); ?></h4>
	<p>
		<label>
		<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ($tptn_settings['post_thumb_op']=='inline') echo 'checked="checked"' ?> />
		<?php _e('Display thumbnails inline with posts',TPTN_LOCAL_NAME); ?></label>
		<br />
		<label>
		<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_1" <?php if ($tptn_settings['post_thumb_op']=='thumbs_only') echo 'checked="checked"' ?> />
		<?php _e('Display only thumbnails, no text',TPTN_LOCAL_NAME); ?></label>
		<br />
		<label>
		<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_2" <?php if ($tptn_settings['post_thumb_op']=='text_only') echo 'checked="checked"' ?> />
		<?php _e('Do not display thumbnails, only text.',TPTN_LOCAL_NAME); ?></label>
		<br />
	</p>
    <p>
      <label>
      <?php _e('Post thumbnail meta field (the meta should point to the image source): ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo attribute_escape(stripslashes($tptn_settings[thumb_meta])); ?>">
      </label>
    </p>
    <p>
      <label>
      <input type="checkbox" name="scan_images" id="scan_images" <?php if ($tptn_settings[scan_images]) echo 'checked="checked"' ?> />
      <?php _e('If the postmeta is not set, then should the plugin extract the first image from the post. This can slow down the loading of your post if the first image in the related posts is large in file-size',TPTN_LOCAL_NAME); ?>
      </label>
    </p>
    <p><strong><?php _e('Thumbnail dimensions:',TPTN_LOCAL_NAME); ?></strong><br />
      <label>
      <?php _e('Max width: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo attribute_escape(stripslashes($tptn_settings[thumb_width])); ?>" style="width:30px">px
      </label>
	  <br />
      <label>
      <?php _e('Max height: ',TPTN_LOCAL_NAME); ?>
      <input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo attribute_escape(stripslashes($tptn_settings[thumb_height])); ?>" style="width:30px">px
      </label>
    </p>
	<p><?php _e('The plugin will first check if the post contains a thumbnail. If it doesn\'t then it will check the meta field. If this is not available, then it will show the default image as specified below:',TPTN_LOCAL_NAME); ?>
	<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo attribute_escape(stripslashes($tptn_settings[thumb_default])); ?>" style="width:500px">
	</p>
    <p>
      <input type="submit" name="tptn_save" id="tptn_save" value="Save Options" style="border:#0c0 1px solid" />
      <input name="tptn_default" type="submit" id="tptn_default" value="Default Options" style="border:#f00 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default?',TPTN_LOCAL_NAME); ?>')) return false;" />
    </p>
    <h4>
      <?php _e('Reset count',TPTN_LOCAL_NAME); ?>
    </h4>
    <p>
      <?php _e('This cannot be reversed. Make sure that your database has been backed up before proceeding',TPTN_LOCAL_NAME); ?>
    </p>
    <p>
      <input name="tptn_trunc_all" type="submit" id="tptn_trunc_all" value="Reset Popular Posts" style="border:#900 1px solid" onclick="if (!confirm('<?php _e('Are you sure you want to reset the popular posts?',TPTN_LOCAL_NAME); ?>')) return false;" />
      <input name="tptn_trunc_daily" type="submit" id="tptn_trunc_daily" value="Reset Daily Popular Posts" style="border:#C00 1px solid" onclick="if (!confirm('<?php _e('Are you sure you want to reset the daily popular posts?',TPTN_LOCAL_NAME); ?>')) return false;" />
      <input name="tptn_clean_duplicates" type="submit" id="tptn_clean_duplicates" value="Clear duplicates" style="border:#600 1px solid" onclick="if (!confirm('<?php _e('This will delete the duplicate entries in the tables. Proceed?',TPTN_LOCAL_NAME); ?>')) return false;" />
    </p>
    </fieldset>
  </form>
</div>
  <div id="side">
	<div class="side-widget">
	<span class="title"><?php _e('Quick links') ?></span>				
	<ul>
		<li><a href="http://ajaydsouza.com/wordpress/plugins/top-10/"><?php _e('Top 10 ');_e('plugin page',TPTN_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e('Other plugins',TPTN_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/"><?php _e('Ajay\'s blog',TPTN_LOCAL_NAME) ?></a></li>
		<li><a href="http://ajaydsouza.com/support"><?php _e('Support',TPTN_LOCAL_NAME) ?></a></li>
		<li><a href="http://twitter.com/ajaydsouza"><?php _e('Follow @ajaydsouza on Twitter',TPTN_LOCAL_NAME) ?></a></li>
	</ul>
	</div>
	<div class="side-widget">
	<span class="title"><?php _e('Recent developments',TPTN_LOCAL_NAME) ?></span>				
	<?php require_once(ABSPATH . WPINC . '/rss.php'); wp_widget_rss_output('http://ajaydsouza.com/archives/category/wordpress/plugins/feed/', array('items' => 5, 'show_author' => 0, 'show_date' => 1));
	?>
	</div>
	<div class="side-widget">
		<span class="title"><?php _e('Support the development',TPTN_LOCAL_NAME) ?></span>
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="KGVN7LJLLZCMY">
			<input type="hidden" name="lc" value="IN">
			<input type="hidden" name="item_name" value="Donation for Top 10">
			<input type="hidden" name="item_number" value="tptn">
			<strong><?php _e('Enter amount in USD: ',TPTN_LOCAL_NAME) ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e('Send your donation to the author of',TPTN_LOCAL_NAME) ?> Top 10?">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
  </div>
  
</div>

<?php

}

function tptn_manage() {
	$paged = intval($_GET['paged']);
	$limit = intval($_GET['limit']);
	$daily = $_GET['daily'];

	echo '<div class="wrap">';
	echo '<h2>';
	if (!$daily) _e('Popular Posts',TPTN_LOCAL_NAME); else _e('Daily Popular Posts',TPTN_LOCAL_NAME);
	echo '</h2>';
	echo '<div style="border: #ccc 1px solid; padding: 10px">';
	echo tptn_pop_display($daily,$paged,$limit);
	echo '</div></div>';
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
		$plugin_page = add_options_page(__("Top 10", TPTN_LOCAL_NAME), __("Top 10", TPTN_LOCAL_NAME), 9, 'tptn_options', 'tptn_options');
		add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );
		add_posts_page(__("Popular Posts", TPTN_LOCAL_NAME), __("Top 10", TPTN_LOCAL_NAME), 9, 'tptn_manage', 'tptn_manage');
	}
}
add_action('admin_menu', 'tptn_adminmenu');

function tptn_adminhead() {
	global $tptn_url;

?>
<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/wick/wick.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/admin-styles.css" />
<script type="text/javascript" language="JavaScript">
function checkForm() {
answer = true;
if (siw && siw.selectingSomething)
	answer = false;
return answer;
}//
</script>
<script type="text/javascript" src="<?php echo $tptn_url ?>/wick/sample_data.js.php"></script>
<script type="text/javascript" src="<?php echo $tptn_url ?>/wick/wick.js"></script>
<?php }

// Function to delete all rows in the posts table
function tptn_clean_duplicates($daily = false) {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";
	$count = 0;

	$wpdb->query("CREATE TEMPORARY TABLE ".$table_name."_temp AS SELECT * FROM ".$table_name." GROUP BY postnumber");
	$wpdb->query("TRUNCATE TABLE $table_name");
	$wpdb->query("INSERT INTO ".$table_name." SELECT * FROM ".$table_name."_temp");
}

/* Create a Dashboard Widget */
function tptn_pop_display($daily = false, $page = 0, $limit = 10) {
	global $wpdb, $siteurl, $tableposts, $id;

	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";	// If we're viewing daily posts, set this to true
	
	$tptn_settings = tptn_read_options();
	if (!($limit)) $limit = $tptn_settings['limit'];
	if (!($page)) $page = 0; // Default page value.

	if(!$daily) {
		$sql = "SELECT postnumber, cntaccess as sumCount, ID, post_type ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' ";
		$sql .= "ORDER BY sumCount DESC";
	} else {
		$daily_range = $tptn_settings[daily_range]-1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC";
	}

	$results = $wpdb->get_results($sql);
	$numrows = 0;
	if ($results) {
		foreach ($results as $result) {
			$numrows++;
		}
	}
	
	$pages = intval($numrows/$limit); // Number of results pages.

	// $pages now contains int of pages, unless there is a remainder from division.

	if ($numrows % $limit) {$pages++;} // has remainder so add one page

	$current = ($page/$limit) + 1; // Current page number.

	if (($pages < 1) || ($pages == 0)) {$total = 1;} // If $pages is less than one or equal to 0, total pages is 1.
	else {	$total = $pages;} // Else total pages is $pages value.

	$first = $page + 1; // The first result.

	if (!((($page + $limit) / $limit) >= $pages) && $pages != 1) {$last = $page + $limit;} //If not last results page, last result equals $page plus $limit.
	else{$last = $numrows;} // If last results page, last result equals total number of results.
	
	if(!$daily) {
		$sql = "SELECT postnumber, cntaccess , ID, post_type ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' ";
		$sql .= "ORDER BY cntaccess DESC LIMIT $page, $limit";
	} else {
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $page, $limit";
	}

	$results = $wpdb->get_results($sql);

	$output = '<div id="tptn_popular_posts">';
	$output .= '<table width="100%" border="0">
	 <tr>
	  <td width="50%" align="left">';
	$output .= __('Results',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$first.'</strong> - <strong>'.$last.'</strong> ';
	$output .= __('of',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$numrows.'</strong>
	  </td>
	  <td width="50%" align="right">';
	$output .= __('Page',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$current.'</strong> ';
	$output .= __('of',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$total.'</strong>
	  </td>
	 </tr>
	 <tr>
	  <td colspan="2" align="right">&nbsp;</td>
	 </tr>
	 <tr>
	  <td align="left">';
	
	if(!$daily) {
		$output .= '<a href="./edit.php?page=tptn_manage&daily=1">';
		$output .= __('View Daily Popular Posts',TPTN_LOCAL_NAME);
		$output .= '</a></td>';
	} else {
		$output .= '<a href="./edit.php?page=tptn_manage&daily=0">';
		$output .= __('View Overall Popular Posts',TPTN_LOCAL_NAME);
		$output .= '</a></td>';
	}
	$output .= '<td align="right">';
	$output .= __('Results per-page:',TPTN_LOCAL_NAME);
	$output .= ' <a href="./edit.php?page=tptn_manage&daily='.$daily.'&limit=10">10</a> | <a href="./edit.php?page=tptn_manage&daily='.$daily.'&limit=20">20</a> | <a href="./edit.php?page=tptn_manage&daily='.$daily.'&limit=50">50</a> | <a href="./edit.php?page=tptn_manage&daily='.$daily.'&limit=100">100</a> 
	  </td>
	 </tr>
	 <tr>
	  <td colspan="2" align="right"><hr /></td>
	 </tr>
	</table>';


	$output .=   '<ul>';
	if ($results) {
		foreach ($results as $result) {
			$output .= '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
			if ($daily) $output .= ' ('.$result->sumCount.')'; else $output .= ' ('.$result->cntaccess.')';
			$output .= '</li>';
		}
	}
	$output .=   '</ul>';
	
	$output .=   '<p align="center">';
	if ($page != 0) { // Don't show back link if current page is first page.
		$back_page = $page - $limit;
		$output .=  "<a href=\"./edit.php?page=tptn_manage&paged=$back_page&daily=$daily&limit=$limit\">&laquo; ";
		$output .=  __('Previous',TPTN_LOCAL_NAME);
		$output .=  "</a>\n";
	}

	for ($i=1; $i <= $pages; $i++) // loop through each page and give link to it.
	{
		$ppage = $limit*($i - 1);
		if ($ppage == $page){
			$output .=  "<b>$i</b>\n";
		} // If current page don't give link, just text.
		else{
			$output .=  "<a href=\"./edit.php?page=tptn_manage&paged=$ppage&daily=$daily&limit=$limit\">$i</a> \n";
		}
	}

	if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) { // If last page don't give next link.
		$next_page = $page + $limit;
		$output .=  "<a href=\"./edit.php?page=tptn_manage&paged=$next_page&daily=$daily&limit=$limit\">";
		$output .=  __('Next',TPTN_LOCAL_NAME);
		$output .=  " &raquo;</a>";
	}
	$output .=   '</p>';
	$output .=   '<p style="text-align:center;border-top: #000 1px solid">Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></p>';
	$output .= '</div>';

	return $output;

}
 
// Dashboard for Popular Posts
function tptn_pop_dashboard() {
	echo tptn_pop_display(false,0,10);
}
// Dashboard for Daily Popular Posts
function tptn_pop_daily_dashboard() {
	echo tptn_pop_display(true,0,10);
}
 
function tptn_pop_dashboard_setup() {
	if (function_exists('wp_add_dashboard_widget')) {
		wp_add_dashboard_widget( 'tptn_pop_dashboard', __( 'Popular Posts',TPTN_LOCAL_NAME ), 'tptn_pop_dashboard' );
		wp_add_dashboard_widget( 'tptn_pop_daily_dashboard', __( 'Daily Popular',TPTN_LOCAL_NAME ), 'tptn_pop_daily_dashboard' );
	}
}
add_action('wp_dashboard_setup', 'tptn_pop_dashboard_setup');


/* Display page views on the Edit Posts / Pages screen */
// Add an extra column
function tptn_column($cols) {
	$tptn_settings = tptn_read_options();
	
	if ($tptn_settings[pv_in_admin])	$cols['tptn'] = __('Total / Today\'s Views',TPTN_LOCAL_NAME);
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
		
		// Now process daily count
		$table_name = $wpdb->prefix . "top_ten_daily";

		$daily_range = $tptn_settings[daily_range]-1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$resultscount = $wpdb->get_row("SELECT postnumber, SUM(cntaccess) as sumCount FROM $table_name WHERE postnumber = $id AND dp_date >= '$current_date' GROUP BY postnumber ");
		$cntaccess .= number_format((($resultscount) ? $resultscount->sumCount : 0));
		
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