<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

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
	 * Cache.
	 *
	 * @since 3.3.0
	 *
	 * @var object Cache.
	 */
	public $cache;

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
	 * Filters.
	 *
	 * @since 3.3.0
	 *
	 * @var object Filters.
	 */
	public $filters;

	/**
	 * Snippets.
	 *
	 * @since 3.3.0
	 *
	 * @var object Snippets.
	 */
	public $snippets;

	/**
	 * Site verification.
	 *
	 * @since 3.3.0
	 *
	 * @var object Site verification.
	 */
	public $site_verification;

	/**
	 * Third party functions.
	 *
	 * @since 3.3.0
	 *
	 * @var object Third party functions.
	 */
	public $third_party;

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
		$this->counter    = new Counter();
		$this->tracker    = new Tracker();
		$this->cache      = new Util\Cache();
		$this->shortcodes = new Frontend\Shortcodes();
		$this->blocks     = new Frontend\Blocks\Blocks();
		$this->filters    = new Frontend\Filters();
		$this->feed       = new Frontend\Feed();

		$this->hooks();

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				$this->admin = new Admin\Network\Admin();
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
	}

	/**
	 * Initialise the plugin translations and media.
	 *
	 * @since 3.3.0
	 */
	public function initiate_plugin() {
		load_plugin_textdomain( 'top-10', false, dirname( plugin_basename( TOP_TEN_PLUGIN_FILE ) ) . '/languages/' );

		Frontend\Media_Handler::add_image_sizes();
	}

	/**
	 * Initialise the Top 10 widgets.
	 *
	 * @since 3.3.0
	 */
	public function register_widgets() {
		register_widget( '\WebberZone\Top_Ten\Frontend\Widgets\Posts_Widget' );
		register_widget( '\WebberZone\Top_Ten\Frontend\Widgets\Count_Widget' );
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
}
