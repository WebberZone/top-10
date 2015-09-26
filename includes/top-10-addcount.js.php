<?php
/**
 * Update counts to database.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 */
Header( 'content-type: application/x-javascript' );

// Force a short-init since we just need core WP, not the entire framework stack
define( 'SHORTINIT', true );

// Build the wp-config.php path from a plugin/theme
$wp_config_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
$wp_config_filename = '/wp-load.php';

// Check if the file exists in the root or one level up
if ( ! file_exists( $wp_config_path . $wp_config_filename ) ) {
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
	$table_name = $wpdb->base_prefix . 'top_ten';
	$top_ten_daily = $wpdb->base_prefix . 'top_ten_daily';
	$str = '';

	$id = intval( $_GET['top_ten_id'] );
	$blog_id = intval( $_GET['top_ten_blog_id'] );
	$activate_counter = intval( $_GET['activate_counter'] );

	if ( $id > 0 ) {
		if ( ( 1 == $activate_counter ) || ( 11 == $activate_counter ) ) {
			$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '1', '%d') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) );
			$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
		}
		if ( ( 10 == $activate_counter ) || ( 11 == $activate_counter ) ) {
			$current_date = gmdate( 'Y-m-d H', current_time( 'timestamp', 1 ) );
			$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$top_ten_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES('%d', '1', '%s', '%d' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) );
			$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
		}
	}
	echo '<!-- ' . $str . ' -->';
}

