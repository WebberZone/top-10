<?php
/**
 * PHPUnit bootstrap file.
 */
if ( class_exists( '\Yoast\PHPUnitPolyfills\Autoload' ) === false ) {
    require_once 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

$versionRequirement = '1.0.1';
if ( defined( '\Yoast\PHPUnitPolyfills\Autoload::VERSION' ) === false
    || version_compare( \Yoast\PHPUnitPolyfills\Autoload::VERSION, $versionRequirement, '<' )
) {
    echo 'Error: Version mismatch detected for the PHPUnit Polyfills.',
        ' Please ensure that PHPUnit Polyfills ', $versionRequirement,
        ' or higher is loaded.', PHP_EOL;
    exit(1);
} else {
    echo 'Error: Please run `composer update -W` before running the tests.' . PHP_EOL;
    echo 'You can still use a PHPUnit phar to run them,',
        ' but the dependencies do need to be installed.', PHP_EOL;
    exit(1);
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Check if we're installed in a src checkout.
$pos = stripos( __FILE__, '/src/wp-content/plugins/' );
if ( ! $_tests_dir && false !== $pos ) {
	$_tests_dir = substr( __FILE__, 0, $pos ) . '/tests/phpunit/';
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php\n";
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/top-10.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

activate_plugin( 'top-10/top-10.php' );

echo "Installing Top 10...\n";

global $tptn_settings, $current_user;

$activator = new \WebberZone\Top_Ten\Admin\Activator();
$activator::activation_hook( false );

$tptn_settings = tptn_get_settings();
