<?php
/**
 * Display number of page views.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 */
Header( 'content-type: application/x-javascript' );

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

$id = intval( $_GET['top_ten_id'] );

// Display counter using Ajax
function tptn_disp_count() {
	global $wpdb;

	$id = intval( $_GET['top_ten_id'] );
	if ( $id > 0 ) {

		$output = get_tptn_post_count( $id );

		echo 'document.write("' . $output . '")';
	}
}
tptn_disp_count();

