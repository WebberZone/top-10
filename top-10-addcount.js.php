<?php
//"top-10-addcount.js.php" Add count to database
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Ajax Increment Counter
tptn_inc_count();
function tptn_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	$top_ten_daily = $wpdb->prefix . "top_ten_daily";
	
	$id = intval($_GET['top_ten_id']);
	if($id > 0) {
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
		// Now update daily count
		$results = $wpdb->get_results("select postnumber, cntaccess from $top_ten_daily where postnumber = '$id'");
		$test = 0;
		if ($results) {
			foreach ($results as $result) {
				$wpdb->query("update $top_ten_daily set cntaccess = cntaccess + 1 where postnumber = $result->postnumber");
				$test = 1;
			}
		}
		if ($test == 0) {
			$wpdb->query("insert into $top_ten_daily (postnumber, cntaccess) values('$id', '1')");
		}
	}
}

?>
