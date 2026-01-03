#!/usr/bin/env php
<?php
/**
 * CLI entry point for benchmarking Top 10 trackers.
 *
 * @package WebberZone\Top_Ten\Tools
 */

if ( php_sapi_name() !== 'cli' ) {
	echo "This script can only run via the command line.\n";
	exit( 1 );
}

tptn_tools_bootstrap_wordpress();

require_once __DIR__ . '/class-tracker-benchmark.php';

$benchmark = new WebberZone\Top_Ten\Tools\Tracker_Benchmark();
$benchmark->run();

/**
 * Locate and load WordPress.
 *
 * @return void
 */
function tptn_tools_bootstrap_wordpress() {
	$path = __DIR__;

	for ( $i = 0; $i < 8; $i++ ) {
		if ( file_exists( $path . '/wp-load.php' ) ) {
			require_once $path . '/wp-load.php';
			return;
		}
		$path = dirname( $path );
	}

	echo "Could not locate wp-load.php. Please run this script from inside the plugin directory.\n";
	exit( 1 );
}
