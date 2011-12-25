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

	$is_widget = intval($_GET['widget']);
	
	if($is_widget) {
		$output = tptn_pop_posts(true,true);
	} else {
		$output = tptn_pop_posts(true,false);
	}
	
	echo "document.write('".$output."')";
}
tptn_daily_lists();
?>
