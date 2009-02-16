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
	$count_disp_form = htmlspecialchars(stripslashes($tptn_settings[count_disp_form]));
	
	$id = intval($_GET['top_ten_id']);
	if($id > 0) {

		$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $id");
		$cntaccess = number_format((($resultscount) ? $resultscount->cntaccess : 0));
		
		$count_disp_form = str_replace("%totalcount%", $cntaccess, $count_disp_form);
		
		echo 'document.write("'.$count_disp_form.'")';
	}
}
tptn_disp_count();
?>
