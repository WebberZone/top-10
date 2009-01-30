<?php
//"top-10-counter.js.php" Display number of page views
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
function tptn_disp_count() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . "top_ten";
	$tptn_settings = tptn_read_options();
	$before_count = $tptn_settings['before_count'];
	$after_count = $tptn_settings['after_count'];
	
	$id = intval($_GET['top_ten_id']);
	if($id > 0) {

		$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $id");
		$cntaccess = (($resultscount) ? $resultscount->cntaccess : 0);
		
		echo 'document.write("'.$before_count.$cntaccess.$after_count.'")';
	}
}
tptn_disp_count();
?>
