<?php
/**
 * Top 10.
 *
 * Count daily and total visits per post and display the most popular posts based on the number of views.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2025 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Top 10
 * Plugin URI:  https://webberzone.com/plugins/top-10/
 * Description: Count daily and total visits per post and display the most popular posts based on the number of views
 * Version:     4.2.0-beta1
 * Author:      WebberZone
 * Author URI:  https://webberzone.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: top-10
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WebberZone/top-10/
 */

namespace WebberZone\Top_Ten;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds the version of Top 10.
 *
 * @since 3.1.0
 */
if ( ! defined( 'TOP_TEN_VERSION' ) ) {
	define( 'TOP_TEN_VERSION', '4.1.1' );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 */
if ( ! defined( 'TOP_TEN_PLUGIN_FILE' ) ) {
	define( 'TOP_TEN_PLUGIN_FILE', __FILE__ );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 */
if ( ! defined( 'TOP_TEN_PLUGIN_DIR' ) ) {
	define( 'TOP_TEN_PLUGIN_DIR', plugin_dir_path( TOP_TEN_PLUGIN_FILE ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 */
if ( ! defined( 'TOP_TEN_PLUGIN_URL' ) ) {
	define( 'TOP_TEN_PLUGIN_URL', plugin_dir_url( TOP_TEN_PLUGIN_FILE ) );
}

/**
 * Holds the default thumbnail URL for Top 10.
 *
 * @since 4.2.0
 */
if ( ! defined( 'TOP_TEN_DEFAULT_THUMBNAIL_URL' ) ) {
	define( 'TOP_TEN_DEFAULT_THUMBNAIL_URL', TOP_TEN_PLUGIN_URL . 'default.png' );
}

/**
 * Number of days of data to be saved in the daily tables.
 *
 * @since 3.0.0
 */
if ( ! defined( 'TOP_TEN_STORE_DATA' ) ) {
	define( 'TOP_TEN_STORE_DATA', 180 );
}

/**
 * Global variable holding the current database version of Top 10
 *
 * @since 1.0
 *
 * @var string
 */
global $tptn_db_version;
$tptn_db_version = '6.0';

// Load Freemius.
require_once TOP_TEN_PLUGIN_DIR . 'load-freemius.php';

// Load the autoloader.
require_once TOP_TEN_PLUGIN_DIR . 'includes/autoloader.php';


if ( ! function_exists( __NAMESPACE__ . '\wz_top_ten' ) ) {
	/**
	 * Returns the main instance of Top 10 to prevent the need to use globals.
	 *
	 * @since 4.0.6
	 *
	 * @return Main Main instance of the plugin.
	 */
	function wz_top_ten(): Main {
		return Main::get_instance();
	}
}

if ( ! function_exists( __NAMESPACE__ . '\load_tptn' ) ) {
	/**
	 * The main function responsible for returning the one true Top 10 instance to functions everywhere.
	 *
	 * @since 3.3.0
	 */
	function load_tptn(): void {
		wz_top_ten();
	}
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load_tptn' );
}

// Register the activation hook.
register_activation_hook( __FILE__, __NAMESPACE__ . '\Admin\Activator::activation_hook' );

/*
 *----------------------------------------------------------------------------
 * Include files
 *----------------------------------------------------------------------------
 */
require_once TOP_TEN_PLUGIN_DIR . 'includes/options-api.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/wz-pluggables.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/class-top-ten-query.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/functions.php';

/**
 * Global variable holding the current settings for Top 10
 *
 * @since 1.9.3
 *
 * @var array
 */
global $tptn_settings;
$tptn_settings = tptn_get_settings();
