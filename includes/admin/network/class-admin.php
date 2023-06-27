<?php
/**
 * Register Settings.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin\Network;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since   3.3.0
 */
class Admin {

	/**
	 * Statistics table.
	 *
	 * @since 3.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Import Export functions.
	 *
	 * @since 3.3.0
	 *
	 * @var object Import Export functions.
	 */
	public $import_export;

	/**
	 * Tools page.
	 *
	 * @since 3.3.0
	 *
	 * @var object Tools page.
	 */
	public $tools_page;

	/**
	 * Main constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->hooks();

		$this->statistics = new Statistics();
	}

	/**
	 * Run the hooks.
	 *
	 * @since 3.3.0
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 */
	public function admin_enqueue_scripts() {
	}

}
