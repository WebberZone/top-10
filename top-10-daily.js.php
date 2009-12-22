<?php
//"top-10-daily.js.php" Display Daily Popular Lists.
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Display counter using Ajax
function tptn_daily_lists() {
	global $wpdb, $siteurl, $tableposts, $id;
	$table_name = $wpdb->prefix . "top_ten_daily";
	
	$is_widget = intval($_GET['widget']);
	
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	$daily_range = $tptn_settings[daily_range]. ' DAY';
	$current_date = $wpdb->get_var("SELECT DATE_ADD(DATE_SUB(CURDATE(), INTERVAL $daily_range), INTERVAL 1 DAY) ");
	
	$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
	$sql .= "GROUP BY postnumber ";
	$sql .= "ORDER BY sumCount DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	$output = '<div id="tptn_related_daily">';
	if(!$is_widget) $output .= $tptn_settings['title_daily'];
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

	echo "document.write('".$output."')";
}
tptn_daily_lists();
?>
