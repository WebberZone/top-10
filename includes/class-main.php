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
	private static ?self $instance = null;

	/**
	 * Admin.
	 *
	 * @since 3.3.0
	 *
	 * @var Admin\Admin
	 */
	public ?Admin\Admin $admin = null;

	/**
	 * Network Admin.
	 *
	 * @since 4.2.0
	 *
	 * @var Admin\Network\Admin
	 */
	public ?Admin\Network\Admin $network_admin = null;

	/**
	 * Shortcodes.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Shortcodes
	 */
	public Frontend\Shortcodes $shortcodes;

	/**
	 * Blocks.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Blocks\Blocks
	 */
	public Frontend\Blocks\Blocks $blocks;

	/**
	 * Counter.
	 *
	 * @since 3.3.0
	 *
	 * @var Counter
	 */
	public Counter $counter;

	/**
	 * Tracker.
	 *
	 * @since 3.3.0
	 *
	 * @var Tracker
	 */
	public Tracker $tracker;

	/**
	 * Feed.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Feed
	 */
	public Frontend\Feed $feed;

	/**
	 * Styles.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Styles_Handler
	 */
	public Frontend\Styles_Handler $styles;

	/**
	 * Language Handler.
	 *
	 * @since 3.3.0
	 *
	 * @var Frontend\Language_Handler
	 */
	public Frontend\Language_Handler $language;

	/**
	 * Cron class.
	 *
	 * @since 3.3.0
	 *
	 * @var Cron
	 */
	public Cron $cron;

	/**
	 * Pro class.
	 *
	 * @since 4.0.0
	 *
	 * @var Pro\Pro
	 */
	public ?Pro\Pro $pro = null;

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

		add_action( 'init', array( $this, 'init_admin' ) );
	}

	/**
	 * Initialize admin components.
	 *
	 * @since 4.2.0
	 */
	public function init_admin(): void {
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
			if ( is_multisite() ) {
				$this->network_admin = new Admin\Network\Admin();
			}
		}
	}
}
