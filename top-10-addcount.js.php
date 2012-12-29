<?php
//"top-10-addcount.js.php" Add count to database
Header("content-type: application/x-javascript");

// Force a short-init since we just need core WP, not the entire framework stack
define( 'SHORTINIT', true );

// Build the wp-config.php path from a plugin/theme
$wp_config_path = dirname( dirname( dirname( __FILE__ ) ) );
$wp_config_filename = '/wp-config.php';

// Check if the file exists in the root or one level up
if( !file_exists( $wp_config_path . $wp_config_filename ) ) {
    // Just in case the user may have placed wp-config.php one more level up from the root
    $wp_config_filename = dirname( $wp_config_path ) . $wp_config_filename;
}
// Require the wp-config.php file
require( $wp_config_filename );

// Include the now instantiated global $wpdb Class for use
global $wpdb;


// Ajax Increment Counter
tptn_inc_count();
function tptn_inc_count() {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	$top_ten_daily = $wpdb->prefix . "top_ten_daily";
	
	$id = intval($_GET['top_ten_id']);
	if($id > 0) {
		$wpdb->query("INSERT INTO $table_name (postnumber, cntaccess) VALUES('$id', '1') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ");
		$current_date = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$wpdb->query("INSERT INTO $top_ten_daily (postnumber, cntaccess, dp_date) VALUES('$id', '1', '$current_date' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ");
	}
}

?>