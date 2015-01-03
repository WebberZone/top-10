<?php
/**
 * Display the daily popular lists.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
 * @copyright 2008-2015 Ajay D'Souza
 */
Header( "content-type: application/x-javascript" );

if ( ! function_exists('add_action') ) {
	$wp_root = '../../../..';
	if ( file_exists($wp_root.'/wp-load.php') ) {
		require_once( $wp_root . '/wp-load.php' );
	} else {
		require_once( $wp_root . '/wp-config.php' );
	}
}

// Display Top 10 Daily list
function tptn_daily_lists() {
	global $wpdb, $id;

	$is_widget = intval( $_GET['is_widget'] );

	if ( $is_widget ) {
		$output = tptn_pop_posts( 'daily=1&is_widget=1' );
	} else {
		$output = tptn_pop_posts( 'daily=1&is_widget=0' );
	}

	echo "document.write('" . $output . "')";
}
tptn_daily_lists();
?>