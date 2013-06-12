<?php
//"top-10-counter.js.php" Display number of page views
Header("content-type: application/x-javascript");

// bootstrap WordPress
require_once('wp-bootstrap.php');

// Include the now instantiated global $wpdb Class for use
global $wpdb;

$nonce=$_REQUEST['_wpnonce'];
$id = intval($_GET['top_ten_id']);
$nonce_action = 'tptn-nonce-'.$id;
if (! wp_verify_nonce($nonce, $nonce_action) ) die("Security check");

// Display counter using Ajax
function tptn_disp_count() {
	global $wpdb;
	
	$id = intval($_GET['top_ten_id']);
	if($id > 0) {

		$output = get_tptn_post_count($id);

		echo 'document.write("'.$output.'")';
	}
}
tptn_disp_count();
?>