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
		$results = $wpdb->get_results("SELECT postnumber, cntaccess FROM $table_name WHERE postnumber = '$id'");
		$test = 0;
		if ($results) {
			foreach ($results as $result) {
				$wpdb->query("UPDATE $table_name SET cntaccess = cntaccess + 1 WHERE postnumber = $result->postnumber");
				$test = 1;
			}
		}
		if ($test == 0) {
			$wpdb->query("INSERT INTO $table_name (postnumber, cntaccess) VALUES('$id', '1')");
		}
		// Now update daily count
		$current_date = $wpdb->get_var("SELECT CURDATE() ");

		$results = $wpdb->get_results("SELECT postnumber, cntaccess, dp_date FROM $top_ten_daily WHERE postnumber = '$id' AND dp_date = '$current_date' ");
		$test = 0;
		if ($results) {
			foreach ($results as $result) {
				$wpdb->query("UPDATE $top_ten_daily SET cntaccess = cntaccess + 1 WHERE postnumber = $result->postnumber AND dp_date = '$current_date' ");
				$test = 1;
			}
		}
		if ($test == 0) {
			$wpdb->query("INSERT INTO $top_ten_daily (postnumber, cntaccess, dp_date) VALUES('$id', '1', '$current_date' )");
		}
	}
}

?>
