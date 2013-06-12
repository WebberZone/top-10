<?php

/*
    This file finds the proper path back to the main WordPress directory and
    includes the wp-load.php file so WordPress functions are accessible.
*/

// Build the wp-config.php path from a plugin/theme
$wp_load_file = preg_replace( '/\/wp-content\/.*$/i', '/wp-load.php', __FILE__ );

// Require the wp-load.php file
require_once( $wp_load_file );
