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

		new Hook_Loader();

		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				new Admin\Network\Admin();
			}
		}
	}
}
