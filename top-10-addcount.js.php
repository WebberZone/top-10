<?php
//"top-10-addcount.js.php" Add count to database
Header("content-type: application/x-javascript");

// Force a short-init since we just need core WP, not the entire framework stack
define( 'SHORTINIT', true );

// bootstrap WordPress
require_once('wp-bootstrap.php');

// Include the now instantiated global $wpdb Class for use
global $wpdb;

// Ajax Increment Counter
tptn_inc_count();
function tptn_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	$top_ten_daily = $wpdb->prefix . "top_ten_daily";
	
	$id = intval($_GET['top_ten_id']);
	$activate_counter = intval($_GET['activate_counter']);
	if($id > 0) {
		if ( ($activate_counter == 1) || ($activate_counter == 11) ) $wpdb->query("INSERT INTO $table_name (postnumber, cntaccess) VALUES('$id', '1') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ");
		$current_date = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		if ( ($activate_counter == 1) || ($activate_counter == 11) ) $wpdb->query("INSERT INTO $top_ten_daily (postnumber, cntaccess, dp_date) VALUES('$id', '1', '$current_date' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ");
	}
}

?>