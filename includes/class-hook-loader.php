<?php
/**
 * Hook Loader class.
 *
 * Handles all hook registrations and callbacks for the plugin.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Util\Hook_Registry;
use WebberZone\Top_Ten\Top_Ten_Core_Query;
use WebberZone\Top_Ten\Frontend\Media_Handler;
use WebberZone\Top_Ten\Frontend\REST_API;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Hook Loader class.
 *
 * Centralizes all hook registrations and their callback implementations.
 *
 * @since 4.0.0
 */
final class Hook_Loader {

	/**
	 * Constructor.
	 *
	 * @since 4.0.0
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register all plugin hooks.
	 *
	 * @since 4.0.0
	 */
	private function register_hooks(): void {
		$this->register_init_hooks();
		$this->register_plugin_hooks();
	}

	/**
	 * Register initialization hooks.
	 *
	 * @since 4.0.0
	 */
	private function register_init_hooks(): void {
		Hook_Registry::add_action( 'init', array( $this, 'initiate_plugin' ) );
		Hook_Registry::add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		Hook_Registry::add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		Hook_Registry::add_action( 'parse_query', array( $this, 'parse_query' ) );
	}

	/**
	 * Register plugin management hooks.
	 *
	 * @since 4.0.0
	 */
	private function register_plugin_hooks(): void {
		Hook_Registry::add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
		Hook_Registry::add_action( 'pre_current_active_plugins', array( $this, 'plugin_deactivated_notice' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.3.0
	 */
	public function initiate_plugin(): void {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Top 10 widgets.
	 *
	 * @since 3.3.0
	 */
	public function register_widgets(): void {
		register_widget( '\WebberZone\Top_Ten\Frontend\Widgets\Posts_Widget' );
		register_widget( '\WebberZone\Top_Ten\Frontend\Widgets\Count_Widget' );
	}

	/**
	 * Function to register our new routes from the controller.
	 *
	 * @since 3.0.0
	 */
	public function register_rest_routes(): void {
		$controller = new Frontend\REST_API();
		$controller->register_routes();
	}

	/**
	 * Hook into WP_Query to check if tptn_query is set and is true. If so, we load the Top 10 query.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_Query $query The WP_Query object.
	 */
	public function parse_query( $query ): void {
		if ( true === $query->get( 'top_ten_query' ) ) {
			new Top_Ten_Core_Query( $query->query_vars );
		}
	}

	/**
	 * Checks if another version of Top 10/Top 10 Pro is active and deactivates it.
	 * Hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
	 *
	 * @since 3.5.0
	 *
	 * @param string $plugin        The plugin being activated.
	 * @param bool   $network_wide  Whether the plugin is being activated network-wide.
	 */
	public function activated_plugin( $plugin, $network_wide ): void {
		if ( ! in_array( $plugin, array( 'top-10/top-10.php', 'top-10-pro/top-10.php' ), true ) ) {
			return;
		}

		Activator::activation_hook( $network_wide );

		$plugin_to_deactivate  = 'top-10/top-10.php';
		$deactivated_notice_id = '1';

		// If we just activated the free version, deactivate the pro version.
		if ( $plugin === $plugin_to_deactivate ) {
			$plugin_to_deactivate  = 'top-10-pro/top-10.php';
			$deactivated_notice_id = '2';
		}

		if ( is_multisite() && is_network_admin() ) {
			$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_keys( $active_plugins );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );
		}

		foreach ( $active_plugins as $plugin_basename ) {
			if ( $plugin_to_deactivate === $plugin_basename ) {
				set_transient( 'tptn_deactivated_notice_id', $deactivated_notice_id, 1 * HOUR_IN_SECONDS );
				deactivate_plugins( $plugin_basename );
				return;
			}
		}
	}

	/**
	 * Displays a notice when either Top 10 or Top 10 Pro is automatically deactivated.
	 *
	 * @since 3.5.0
	 */
	public function plugin_deactivated_notice(): void {
		$deactivated_notice_id = (int) get_transient( 'tptn_deactivated_notice_id' );
		if ( ! in_array( $deactivated_notice_id, array( 1, 2 ), true ) ) {
			return;
		}

		$message = __( "Top 10 and Top 10 Pro should not be active at the same time. We've automatically deactivated Top 10.", 'top-10' );
		if ( 2 === $deactivated_notice_id ) {
			$message = __( "Top 10 and Top 10 Pro should not be active at the same time. We've automatically deactivated Top 10 Pro.", 'top-10' );
		}

		?>
			<div class="updated" style="border-left: 4px solid #ffba00;">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php

			delete_transient( 'tptn_deactivated_notice_id' );
	}
}
