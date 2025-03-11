<?php
/**
 * Admin class.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Util\Cache;

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
	 * Admin Dashboard.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Dashboard.
	 */
	public $admin_dashboard;

	/**
	 * Settings API.
	 *
	 * @since 3.3.0
	 *
	 * @var object Settings API.
	 */
	public $settings;

	/**
	 * Statistics table.
	 *
	 * @since 3.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Activator class.
	 *
	 * @since 3.3.0
	 *
	 * @var object Activator class.
	 */
	public $activator;

	/**
	 * Admin Columns.
	 *
	 * @since 3.3.0
	 *
	 * @var object Admin Columns.
	 */
	public $admin_columns;

	/**
	 * Metabox functions.
	 *
	 * @since 3.3.0
	 *
	 * @var object Metabox functions.
	 */
	public $metabox;

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
	 * Dashboard widgets.
	 *
	 * @since 3.3.0
	 *
	 * @var object Dashboard widgets.
	 */
	public $dashboard_widgets;

	/**
	 * Cache.
	 *
	 * @since 3.3.0
	 *
	 * @var object Cache.
	 */
	public $cache;

	/**
	 * Admin notices.
	 *
	 * @since 4.1.0
	 *
	 * @var object Admin notices.
	 */
	public $admin_notices;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 3.3.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 3.3.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->hooks();

		// Initialise admin classes.
		$this->admin_dashboard   = new Dashboard();
		$this->settings          = new Settings\Settings();
		$this->statistics        = new Statistics();
		$this->activator         = new Activator();
		$this->admin_columns     = new Columns();
		$this->metabox           = new Metabox();
		$this->import_export     = new Import_Export();
		$this->tools_page        = new Tools_Page();
		$this->dashboard_widgets = new Dashboard_Widgets();
		$this->cache             = new Cache();
		$this->admin_notices     = new Admin_Notices();
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

		$minimize = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script(
			'top-ten-chart-js',
			TOP_TEN_PLUGIN_URL . 'includes/admin/js/chart.min.js',
			array(),
			'4.4.8',
			true
		);
		wp_register_script(
			'top-ten-chart-datalabels-js',
			TOP_TEN_PLUGIN_URL . 'includes/admin/js/chartjs-plugin-datalabels.min.js',
			array( 'top-ten-chart-js' ),
			'2.2.0',
			true
		);
		wp_register_script(
			'top-ten-luxon',
			TOP_TEN_PLUGIN_URL . 'includes/admin/js/luxon.min.js',
			array(),
			'3.5.0',
			true
		);
		wp_register_script(
			'top-ten-chartjs-adapter-luxon-js',
			TOP_TEN_PLUGIN_URL . 'includes/admin/js/chartjs-adapter-luxon.min.js',
			array( 'top-ten-luxon', 'top-ten-chart-js' ),
			'1.3.1',
			true
		);
		wp_register_script(
			'top-ten-chart-data-js',
			TOP_TEN_PLUGIN_URL . "includes/admin/js/chart-data{$minimize}.js",
			array( 'jquery', 'top-ten-chart-js', 'top-ten-chart-datalabels-js', 'top-ten-luxon', 'top-ten-chartjs-adapter-luxon-js' ),
			TOP_TEN_VERSION,
			true
		);
		wp_register_script(
			'top-ten-admin-js',
			TOP_TEN_PLUGIN_URL . "includes/admin/js/admin-scripts{$minimize}.js",
			array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker' ),
			TOP_TEN_VERSION,
			true
		);
		wp_localize_script(
			'top-ten-admin-js',
			'top_ten_admin',
			array(
				'nonce' => wp_create_nonce( 'top_ten_admin_nonce' ),
			)
		);
		wp_register_style(
			'top-ten-admin-css',
			TOP_TEN_PLUGIN_URL . "includes/admin/css/admin-styles{$minimize}.css",
			array(),
			TOP_TEN_VERSION
		);
		wp_register_script(
			'top-ten-wpp-importer-js',
			TOP_TEN_PLUGIN_URL . "includes/admin/js/wpp-importer{$minimize}.js",
			array( 'jquery' ),
			TOP_TEN_VERSION,
			true
		);
	}

	/**
	 * Display admin sidebar.
	 *
	 * @since 3.3.0
	 */
	public static function display_admin_sidebar() {
		require_once TOP_TEN_PLUGIN_DIR . 'includes/admin/settings/sidebar.php';
	}
}
