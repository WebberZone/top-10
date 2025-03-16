<?php
/**
 * Autoloads classes from the WebberZone\Top_Ten namespace.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Initialize Freemius SDK.
 */
function tptn_freemius() {
	global $tptn_freemius;
	if ( ! isset( $tptn_freemius ) ) {
		// Activate multisite network integration.
		if ( ! defined( 'WP_FS__PRODUCT_16384_MULTISITE' ) ) {
			define( 'WP_FS__PRODUCT_16384_MULTISITE', true );
		}
		// Include Freemius SDK.
		require_once TOP_TEN_PLUGIN_DIR . 'freemius/start.php';
		$tptn_freemius = \fs_dynamic_init(
			array(
				'id'             => '16384',
				'slug'           => 'top-10',
				'premium_slug'   => 'top-10-pro',
				'type'           => 'plugin',
				'public_key'     => 'pk_bc8489856ce399cf3cc8fd49fc9d3',
				'is_premium'     => false,
				'premium_suffix' => 'Pro',
				'has_addons'     => false,
				'has_paid_plans' => true,
				'menu'           => array(
					'slug'    => 'tptn_dashboard',
					'contact' => false,
					'support' => false,
					'network' => true,
				),
				'is_live'        => true,
			)
		);
	}
	$tptn_freemius->add_filter( 'plugin_icon', __NAMESPACE__ . '\\tptn_freemius_get_plugin_icon' );
	$tptn_freemius->add_filter( 'after_uninstall', __NAMESPACE__ . '\\tptn_freemius_uninstall' );
	return $tptn_freemius;
}

/**
 * Get the plugin icon.
 *
 * @return string
 */
function tptn_freemius_get_plugin_icon() {
	return __DIR__ . '/admin/images/tptn-icon.png';
}

/**
 * Uninstall the plugin.
 */
function tptn_freemius_uninstall() {
	require_once dirname( __DIR__ ) . '/uninstaller.php';
}

// Init Freemius.
tptn_freemius();
// Signal that SDK was initiated.
do_action( 'tptn_freemius_loaded' );
