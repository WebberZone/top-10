<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Admin\Cron;
if ( ! defined( 'WPINC' ) ) {
	exit;
}
/**
 * Main plugin class.
 *
 * @since 3.3.0
 */
final class Main {
	/**
	 * The single instance of the class.
	 *
	 * @var Main
	 */
	private static $instance;

	/**
	 * Admin.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin.
	 */
	public $admin;

	/**
	 * Shortcodes.
	 *
	 * @since 3.3.0
	 *
	 * @var object Shortcodes.
	 */
	public $shortcodes;

	/**
	 * Blocks.
	 *
	 * @since 3.3.0
	 *
	 * @var object Blocks.
	 */
	public $blocks;

	/**
	 * Counter.
	 *
	 * @since 3.3.0
	 *
	 * @var object Counter.
	 */
	public $counter;

	/**
	 * Tracker.
	 *
	 * @since 3.3.0
	 *
	 * @var object Tracker.
	 */
	public $tracker;

	/**
	 * Feed.
	 *
	 * @since 3.3.0
	 *
	 * @var object Feed.
	 */
	public $feed;

	/**
	 * Styles.
	 *
	 * @since 3.3.0
	 *
	 * @var object Styles.
	 */
	public $styles;

	/**
	 * Language Handler.
	 *
	 * @since 3.3.0
	 *
	 * @var object Language Handler.
	 */
	public $language;

	/**
	 * Cron class.
	 *
	 * @since 3.3.0
	 *
	 * @var object Cron class.
	 */
	public $cron;

	/**
	 * Pro class.
	 *
	 * @since 4.0.0
	 *
	 * @var object Pro class.
	 */
	public $pro;

	/**
	 * Gets the instance of the class.
	 *
	 * @since 3.3.0
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * A dummy constructor.
	 *
	 * @since 3.3.0
	 */
	private function __construct() {
		// Do nothing.
	}

	/**
	 * Initializes the plugin.
	 *
	 * @since 3.3.0
	 */
	private function init() {
		$this->language   = new Frontend\Language_Handler();
		$this->styles     = new Frontend\Styles_Handler();
		$this->counter    = new Counter();
		$this->tracker    = new Tracker();
		$this->shortcodes = new Frontend\Shortcodes();
		$this->blocks     = new Frontend\Blocks\Blocks();
		$this->feed       = new Frontend\Feed();
		$this->cron       = new Cron();
		$this->hooks();
		if ( ! function_exists( 'tptn_freemius' ) ) {
			require_once dirname( __DIR__ ) . '/load-freemius.php';
		}
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				new Admin\Network\Admin();
			}
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.3.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'initiate_plugin' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'parse_query', array( $this, 'parse_query' ) );
		add_action(
			'activated_plugin',
			array( $this, 'activated_plugin' ),
			10,
			2
		);
		add_action( 'pre_current_active_plugins', array( $this, 'plugin_deactivated_notice' ) );
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.3.0
	 */
	public function initiate_plugin() {
		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Top 10 widgets.
	 *
	 * @since 3.3.0
	 */
	public function register_widgets() {
		register_widget( '\\WebberZone\\Top_Ten\\Frontend\\Widgets\\Posts_Widget' );
		register_widget( '\\WebberZone\\Top_Ten\\Frontend\\Widgets\\Count_Widget' );
	}

	/**
	 * Function to register our new routes from the controller.
	 *
	 * @since 3.0.0
	 */
	public function register_rest_routes() {
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
	public function parse_query( $query ) {
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
	public function activated_plugin( $plugin, $network_wide ) {
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
	public function plugin_deactivated_notice() {
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
				<p>
				<?php
				echo esc_html( $message );
				?>
		</p>
			</div>
			<?php
			delete_transient( 'tptn_deactivated_notice_id' );
	}
}
