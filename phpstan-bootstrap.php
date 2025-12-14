<?php
/**
 * PHPStan bootstrap file for Contextual Related Posts Pro.
 *
 * @package WebberZone\Contextual_Related_Posts
 */

if ( ! defined( 'TOP_TEN_VERSION' ) ) {
	define( 'TOP_TEN_VERSION', '0.0.0' );
}

if ( ! defined( 'TOP_TEN_PLUGIN_FILE' ) ) {
	define( 'TOP_TEN_PLUGIN_FILE', '' );
}

if ( ! defined( 'TOP_TEN_PLUGIN_DIR' ) ) {
	define( 'TOP_TEN_PLUGIN_DIR', '' );
}

if ( ! defined( 'TOP_TEN_PLUGIN_URL' ) ) {
	define( 'TOP_TEN_PLUGIN_URL', '' );
}

if ( ! defined( 'TOP_TEN_STORE_DATA' ) ) {
	define( 'TOP_TEN_STORE_DATA', 180 );
}

if ( ! defined( 'TOP_TEN_DB_VERSION' ) ) {
	define( 'TOP_TEN_DB_VERSION', '0.0.0' );
}

if ( ! function_exists( 'fs_dynamic_init' ) ) {
	/**
	 * Freemius fs_dynamic_init() stub for static analysis.
	 *
	 * This is loaded only by PHPStan and never in normal plugin runtime.
	 *
	 * @param array $args Freemius initialisation arguments.
	 * @return object Object with minimal Freemius-like API.
	 */
	function fs_dynamic_init( array $args ) {
		unset( $args );
		return new class() {
			/**
			 * Stub add_filter method.
			 *
			 * @param string   $hook_name Hook name.
			 * @param callable $callback  Callback.
			 * @return void
			 */
			public function add_filter( $hook_name, $callback ) {
			}
		};
	}
}
