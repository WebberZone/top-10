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
 * @copyright 2008-2026 Ajay D'Souza
 *
 * @wordpress-plugin
 * Plugin Name: Top 10
 * Plugin URI:  https://webberzone.com/plugins/top-10/
 * Description: Count daily and total visits per post and display the most popular posts based on the number of views
 * Version:     4.2.0-RC1
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
	define( 'TOP_TEN_VERSION', '4.2.0' );
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

if ( ! function_exists( __NAMESPACE__ . '\\tptn_deactivate_other_instances' ) ) {
	/**
	 * Deactivate other instances of Top 10 when this plugin is activated.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide Whether the plugin is being activated network-wide.
	 */
	function tptn_deactivate_other_instances( $plugin, $network_wide = false ) {
		$free_plugin = 'top-10/top-10.php';
		$pro_plugin  = 'top-10-pro/top-10.php';

		// Only proceed if one of our plugins is being activated.
		if ( ! in_array( $plugin, array( $free_plugin, $pro_plugin ), true ) ) {
			return;
		}

		$plugins_to_deactivate = array();
		$deactivated_plugin    = '';

		// If pro is being activated, deactivate free.
		if ( $pro_plugin === $plugin ) {
			if ( is_plugin_active( $free_plugin ) || ( $network_wide && is_plugin_active_for_network( $free_plugin ) ) ) {
				$plugins_to_deactivate[] = $free_plugin;
				$deactivated_plugin      = 'Top 10';
			}
		}

		// If free is being activated, deactivate pro.
		if ( $free_plugin === $plugin ) {
			if ( is_plugin_active( $pro_plugin ) || ( $network_wide && is_plugin_active_for_network( $pro_plugin ) ) ) {
				$plugins_to_deactivate[] = $pro_plugin;
				$deactivated_plugin      = 'Top 10 Pro';
			}
		}

		if ( ! empty( $plugins_to_deactivate ) ) {
			deactivate_plugins( $plugins_to_deactivate, false, $network_wide );
			set_transient( 'tptn_deactivated_notice', $deactivated_plugin, 1 * HOUR_IN_SECONDS );
		}
	}
	add_action( 'activated_plugin', __NAMESPACE__ . '\\tptn_deactivate_other_instances', 10, 2 );
}

// Show admin notice about automatic deactivation.
if ( ! has_action( 'admin_notices', __NAMESPACE__ . '\\tptn_show_deactivation_notice' ) ) {
	add_action(
		'admin_notices',
		function () {
			$deactivated_plugin = get_transient( 'tptn_deactivated_notice' );
			if ( $deactivated_plugin ) {
				/* translators: %s: Name of the deactivated plugin */
				$message = sprintf( __( "Top 10 and Top 10 PRO should not be active at the same time. We've automatically deactivated %s.", 'top-10' ), $deactivated_plugin );
				?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
				<?php
				delete_transient( 'tptn_deactivated_notice' );
			}
		}
	);
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

if ( ! function_exists( __NAMESPACE__ . '\\tptn_freemius' ) ) {
	// Load Freemius.
	require_once plugin_dir_path( __FILE__ ) . 'load-freemius.php';
}

// Load the autoloader.
if ( ! function_exists( __NAMESPACE__ . '\\autoload' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/autoloader.php';
}


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
if ( ! function_exists( 'tptn_get_settings' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/options-api.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/wz-pluggables.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-top-ten-query.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
}

/**
 * Global variable holding the current settings for Top 10
 *
 * @since 1.9.3
 *
 * @var array
 */
global $tptn_settings;
$tptn_settings = tptn_get_settings();
