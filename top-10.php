<?php
/**
 * Top 10.
 *
 * Count daily and total visits per post and display the most popular posts based on the number of views.
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2016 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Top 10
 * Plugin URI:  https://webberzone.com/plugins/top-10/
 * Description: Count daily and total visits per post and display the most popular posts based on the number of views
 * Version:     2.5.6
 * Author:      Ajay D'Souza
 * Author URI:  https://webberzone.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: top-10
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WebberZone/top-10/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin folder path
 */
if ( ! defined( 'TOP_TEN_PLUGIN_DIR' ) ) {
	define( 'TOP_TEN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin folder URL
 */
if ( ! defined( 'TOP_TEN_PLUGIN_URL' ) ) {
	define( 'TOP_TEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since 2.3.0
 *
 * @var string Plugin Root File
 */
if ( ! defined( 'TOP_TEN_PLUGIN_FILE' ) ) {
	define( 'TOP_TEN_PLUGIN_FILE', __FILE__ );
}


/**
 * Global variable holding the current database version of Top 10
 *
 * @since   1.0
 *
 * @var string
 */
global $tptn_db_version;
$tptn_db_version = '6.0';


/**
 * Global variable holding the current settings for Top 10
 *
 * @since   1.9.3
 *
 * @var array
 */
global $tptn_settings;
$tptn_settings = tptn_get_settings();


/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since  2.5.0
 * @return array Top 10 settings
 */
function tptn_get_settings() {

	$settings = get_option( 'tptn_settings' );

	/**
	 * Settings array
	 *
	 * Retrieves all plugin settings
	 *
	 * @since 1.2.0
	 * @param array $settings Settings array
	 */
	return apply_filters( 'tptn_get_settings', $settings );
}


/**
 * Function to delete all rows in the posts table.
 *
 * @since   1.3
 * @param   bool $daily  Daily flag.
 */
function tptn_trunc_count( $daily = true ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql ); // WPCS: unprepared SQL OK.
}


/*
 *---------------------------------------------------------------------------*
 * Top 10 modules
 *---------------------------------------------------------------------------*
 */

require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/register-settings.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/activate-deactivate.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/public/display-posts.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/public/styles.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/public/output-generator.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/public/media.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/l10n.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/counter.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/tracker.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/cron.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/formatting.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/modules/shortcode.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/modules/exclusions.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/modules/taxonomies.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/modules/class-top-ten-widget.php';
require_once TOP_TEN_PLUGIN_DIR . 'includes/modules/class-top-ten-count-widget.php';


/*
 *---------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *---------------------------------------------------------------------------*
 */

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {

	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/admin.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/settings-page.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/save-settings.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/help-tab.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/tools.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/admin-metabox.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/admin-columns.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/admin-dashboard.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/class-top-ten-statistics-table.php';
	require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/cache.php';

} // End admin.inc

/*
 *---------------------------------------------------------------------------*
 * Deprecated functions
 *---------------------------------------------------------------------------*
 */

require_once TOP_TEN_PLUGIN_DIR . 'includes/deprecated.php';

