<?php
/**
 * Main plugin class.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

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

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				$this->admin = new Admin\Network\Admin();
			}
		}

		if ( tptn_freemius()->is__premium_only() ) {
			if ( tptn_freemius()->can_use_premium_code() ) {
				$this->pro = new Pro\Pro();
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

	/**
	 * Hook into WP_Query to check if crp_query is set and is true. If so, we load the CRP query.
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
}
