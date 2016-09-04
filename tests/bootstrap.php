<?php

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) { $_tests_dir = '/tmp/wordpress-tests-lib'; }

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../top-10.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

activate_plugin( 'top-10/top-10.php' );

echo "Installing Top 10...\n";

global $tptn_db_version, $tptn_settings, $current_user;
$tptn_db_version = '5.0';

tptn_activation_hook( true );

$tptn_settings = tptn_read_options();
