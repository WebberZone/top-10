<?php
/**
 * Settings class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Admin\Settings\Settings_API;
use WebberZone\Top_Ten\Admin\Settings\Settings_Sanitize;
use WebberZone\Top_Ten\Frontend\Display;
use WebberZone\Top_Ten\Frontend\Media_Handler;
use WebberZone\Top_Ten\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @version 1.0
 * @since   3.3.0
 */
class Settings {

	/**
	 * Settings API.
	 *
	 * @since 3.3.0
	 *
	 * @var Settings_API Settings API.
	 */
	public $settings_api;

	/**
	 * Statistics table.
	 *
	 * @since 3.3.0
	 *
	 * @var object Statistics table.
	 */
	public $statistics;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 3.3.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix = 'tptn';

	/**
	 * Settings Key.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key = 'tptn_settings';

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 3.3.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug = 'tptn_options_page';

	/**
	 * Main constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		Hook_Registry::add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		Hook_Registry::add_filter( 'plugin_action_links_' . plugin_basename( TOP_TEN_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
		Hook_Registry::add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
		Hook_Registry::add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
		Hook_Registry::add_filter( self::$prefix . '_after_setting_output', array( $this, 'display_admin_thumbnail' ), 10, 2 );
		Hook_Registry::add_filter( self::$prefix . '_setting_field_description', array( $this, 'reset_default_thumb_setting' ), 10, 2 );
		Hook_Registry::add_filter( self::$prefix . '_settings_page_header', array( $this, 'settings_page_header' ) );
		Hook_Registry::add_filter( self::$prefix . '_after_setting_output', array( $this, 'after_setting_output' ), 10, 2 );
		Hook_Registry::add_action( self::$prefix . '_settings_form_buttons', array( $this, 'add_wizard_button' ) );

		Hook_Registry::add_action( 'wp_ajax_nopriv_' . self::$prefix . '_taxonomy_search_tom_select', array( __CLASS__, 'taxonomy_search_tom_select' ) );
		Hook_Registry::add_action( 'wp_ajax_' . self::$prefix . '_taxonomy_search_tom_select', array( __CLASS__, 'taxonomy_search_tom_select' ) );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 3.3.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 1.8.0
	 *
	 * @return array Settings array
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_header'          => esc_html__( 'Top 10 Settings', 'top-10' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'top-10' ),
			'success_message'      => esc_html__( 'Settings updated.', 'top-10' ),
			'save_changes'         => esc_html__( 'Save Changes', 'top-10' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'top-10' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'top-10' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'top-10' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 1.8.0
		 *
		 * @param array $strings Translation strings.
		 */
		return apply_filters( self::$prefix . '_translation_strings', $strings );
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		// Settings menu.
		$menus[] = array(
			'settings_page' => true,
			'type'          => 'submenu',
			'parent_slug'   => 'tptn_dashboard',
			'page_title'    => esc_html__( 'Top 10 Settings', 'top-10' ),
			'menu_title'    => esc_html__( 'Settings', 'top-10' ),
			'menu_slug'     => $this->menu_slug,
		);

		return $menus;
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general'     => __( 'General', 'top-10' ),
			'counter'     => __( 'Counter/Tracker', 'top-10' ),
			'list'        => __( 'Posts list', 'top-10' ),
			'thumbnail'   => __( 'Thumbnail', 'top-10' ),
			'styles'      => __( 'Styles', 'top-10' ),
			'maintenance' => __( 'Maintenance', 'top-10' ),
			'feed'        => __( 'Feed', 'top-10' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 1.2.0
		 *
		 * @param array $settings_sections Settings array
		 */
		return apply_filters( self::$prefix . '_settings_sections', $settings_sections );
	}


	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Settings array
	 */
	public static function get_registered_settings() {
		$settings = array();
		$sections = self::get_settings_sections();

		foreach ( $sections as $section => $value ) {
			$method_name = 'settings_' . $section;
			if ( method_exists( __CLASS__, $method_name ) ) {
				$settings[ $section ] = self::$method_name();
			}
		}

		/**
		 * Filters the settings array
		 *
		 * @since 1.2.0
		 *
		 * @param array $tptn_setings Settings array
		 */
		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * Retrieve the array of General settings
	 *
	 * @since 3.3.0
	 *
	 * @return array General settings array
	 */
	public static function settings_general() {
		$settings = array(
			'cache'                   => array(
				'id'      => 'cache',
				'name'    => esc_html__( 'Enable cache', 'top-10' ),
				'desc'    => esc_html__( 'If activated, Top 10 will use the Transients API to cache the popular posts output for the time specified below.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'cache_time'              => array(
				'id'      => 'cache_time',
				'name'    => esc_html__( 'Time to cache', 'top-10' ),
				'desc'    => esc_html__( 'Enter the number of seconds to cache the output.', 'top-10' ),
				'type'    => 'text',
				'default' => HOUR_IN_SECONDS,
			),
			'uninstall_clean_options' => array(
				'id'      => 'uninstall_clean_options',
				'name'    => esc_html__( 'Delete options on uninstall', 'top-10' ),
				'desc'    => esc_html__( 'If this is checked, all settings related to Top 10 are removed from the database if you choose to uninstall/delete the plugin.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'uninstall_clean_tables'  => array(
				'id'      => 'uninstall_clean_tables',
				'name'    => esc_html__( 'Delete counter data on uninstall', 'top-10' ),
				'desc'    => esc_html__( 'If this is checked, the tables containing the counter statistics are removed from the database if you choose to uninstall/delete the plugin. Keep this unchecked if you choose to reinstall the plugin and do not want to lose your counter data.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'show_credit'             => array(
				'id'      => 'show_credit',
				'name'    => esc_html__( 'Link to Top 10 plugin page', 'top-10' ),
				'desc'    => esc_html__( 'A no-follow link to the plugin homepage will be added as the last item of the popular posts.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'admin_header'            => array(
				'id'   => 'admin_header',
				'name' => '<h3>' . esc_html__( 'Admin settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'show_metabox'            => array(
				'id'      => 'show_metabox',
				'name'    => esc_html__( 'Show metabox', 'top-10' ),
				'desc'    => esc_html__( 'This will add the Top 10 metabox on Edit Posts or Add New Posts screens. Also applies to Pages and Custom Post Types.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'show_metabox_admins'     => array(
				'id'      => 'show_metabox_admins',
				'name'    => esc_html__( 'Limit meta box to Admins', 'top-10' ),
				'desc'    => esc_html__( 'If selected, the meta box will be hidden from anyone who is not an Admin. By default, Contributors and above will be able to see the meta box. Applies only if the above option is selected.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'pv_in_admin'             => array(
				'id'      => 'pv_in_admin',
				'name'    => esc_html__( 'Display admin columns', 'top-10' ),
				'desc'    => esc_html__( "Adds three columns called Total Views, Today's Views and Views. You can selectively disable these by pulling down the Screen Options from the top right of the respective screens.", 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'admin_column_post_types' => array(
				'id'      => 'admin_column_post_types',
				'name'    => esc_html__( 'Display columns on post types', 'top-10' ),
				'desc'    => esc_html__( 'Select which post types to display the admin columns. Unselect the above option if you would like to disable the columns.', 'top-10' ),
				'type'    => 'posttypes',
				'default' => 'post,page',
				'pro'     => true,
			),
			'show_count_non_admins'   => array(
				'id'      => 'show_count_non_admins',
				'name'    => esc_html__( 'Show views to non-admins', 'top-10' ),
				'desc'    => esc_html__( "If you disable this then non-admins won't see the above columns or view the independent pages with the top posts.", 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'show_dashboard_to_roles' => array(
				'id'      => 'show_dashboard_to_roles',
				'name'    => esc_html__( 'Also show dashboard to', 'top-10' ),
				'desc'    => esc_html__( 'Choose the user roles that should have access to the Top 10 dashboard, which showcases popular posts over time. These roles are linked to specific capabilities, and selecting a lower role will automatically grant access to higher roles.', 'top-10' ),
				'type'    => 'multicheck',
				'default' => 'administrator',
				'options' => self::get_user_roles( array( 'administrator' ) ),
				'pro'     => true,
			),
			'show_admin_bar'          => array(
				'id'      => 'show_admin_bar',
				'name'    => esc_html__( 'Show Admin Bar menu', 'top-10' ),
				'desc'    => esc_html__( 'Display the Top 10 menu in the WordPress admin bar with quick access to popular posts stats and tools.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
				'pro'     => true,
			),
			'query_optimization'      => array(
				'id'   => 'query_optimization',
				'name' => '<h3>' . esc_html__( 'Query Optimization', 'top-10' ) . '</h3>',
				'desc' => esc_html__( 'Settings for optimizing database queries', 'top-10' ),
				'type' => 'header',
			),
			'max_execution_time'      => array(
				'id'      => 'max_execution_time',
				'name'    => esc_html__( 'Max Execution Time', 'top-10' ),
				'desc'    => esc_html__( 'Maximum execution time for MySQL queries in milliseconds. Set to 0 to disable. Default is 3000 (3 seconds).', 'top-10' ),
				'type'    => 'number',
				'default' => 3000,
				'min'     => 0,
				'step'    => 100,
				'pro'     => true,
			),
		);

		/**
		 * Filters the General settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings General settings array
		 */
		return apply_filters( self::$prefix . '_settings_general', $settings );
	}


	/**
	 * Retrieve the array of Counter settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Counter settings array
	 */
	public static function settings_counter() {
		$settings = array(
			'counter_header'       => array(
				'id'   => 'counter_header',
				'name' => '<h3>' . esc_html__( 'Counter settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'add_to'               => array(
				'id'      => 'add_to',
				'name'    => esc_html__( 'Display number of views on', 'top-10' ) . ':',
				/* translators: 1: Code. */
				'desc'    => sprintf( esc_html__( 'If you choose to disable this, please add the following code to your template file where you want it displayed: %1$s', 'top-10' ), "<code>&lt;?php if ( function_exists( 'echo_tptn_post_count' ) ) { echo_tptn_post_count(); } ?&gt;</code>" ),
				'type'    => 'multicheck',
				'default' => 'single,page',
				'options' => array(
					'single'            => esc_html__( 'Posts', 'top-10' ),
					'page'              => esc_html__( 'Pages', 'top-10' ),
					'home'              => esc_html__( 'Home page', 'top-10' ),
					'feed'              => esc_html__( 'Feeds', 'top-10' ),
					'category_archives' => esc_html__( 'Category archives', 'top-10' ),
					'tag_archives'      => esc_html__( 'Tag archives', 'top-10' ),
					'other_archives'    => esc_html__( 'Other archives', 'top-10' ),
				),
			),
			'count_disp_form'      => array(
				'id'      => 'count_disp_form',
				'name'    => esc_html__( 'Format to display the post views', 'top-10' ),
				/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
				'desc'    => sprintf( esc_html__( 'Use %1$s to display the total count, %2$s for daily count and %3$s for overall counts across all posts. Default display is %4$s', 'top-10' ), '<code>%totalcount%</code>', '<code>%dailycount%</code>', '<code>%overallcount%</code>', '<code>Visited %totalcount% times, %dailycount% visit(s) today</code>' ),
				'type'    => 'textarea',
				'default' => 'Visited %totalcount% times, %dailycount% visit(s) today',
			),
			'count_disp_form_zero' => array(
				'id'      => 'count_disp_form_zero',
				'name'    => esc_html__( 'No visits text', 'top-10' ),
				'desc'    => esc_html__( "This text is displayed when there are no hits for the post and it isn't a single page. For example, if you display post views on the Homepage or Archives, this text will be used. To override this, simply enter the same text as the above option.", 'top-10' ),
				'type'    => 'textarea',
				'default' => 'No visits yet',
			),
			'number_format_count'  => array(
				'id'      => 'number_format_count',
				'name'    => esc_html__( 'Number format post count', 'top-10' ),
				'desc'    => esc_html__( 'Activating this option will convert the post counts into a number format based on the locale', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'daily_midnight'       => array(
				'id'      => 'daily_midnight',
				'name'    => esc_html__( 'Start daily counts at midnight', 'top-10' ),
				'desc'    => esc_html__( 'The daily counter displays visits starting at midnight. This option is enabled by default, similar to most standard counters. If you disable this option, you can use the hourly setting in the next option.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'range_desc'           => array(
				'id'   => 'range_desc',
				'name' => esc_html__( 'Default custom period range', 'top-10' ),
				'desc' => esc_html__( 'The following two options let you set the default range for the custom period. This can be overridden in the widget settings.', 'top-10' ),
				'type' => 'descriptive_text',
			),
			'daily_range'          => array(
				'id'      => 'daily_range',
				'name'    => esc_html__( 'Day(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '1',
				'min'     => '0',
				'size'    => 'small',
			),
			'hour_range'           => array(
				'id'      => 'hour_range',
				'name'    => esc_html__( 'Hour(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '0',
				'min'     => '0',
				'max'     => '23',
				'size'    => 'small',
			),
			'dynamic_post_count'   => array(
				'id'      => 'dynamic_post_count',
				'name'    => esc_html__( 'Always display latest post count', 'top-10' ),
				'desc'    => esc_html__( 'This option uses JavaScript and will increase your page load time. Turn this off if you are not using caching plugins or are OK with displaying older cached counts.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'exclude_on_post_ids'  => array(
				'id'      => 'exclude_on_post_ids',
				'name'    => esc_html__( 'Exclude display on these post IDs', 'top-10' ),
				'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude displaying the top posts on. e.g. 188,320,500', 'top-10' ),
				'type'    => 'numbercsv',
				'default' => '',
			),
			'tracker_header'       => array(
				'id'   => 'tracker_header',
				'name' => '<h3>' . esc_html__( 'Tracker settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'trackers'             => array(
				'id'      => 'trackers',
				'name'    => esc_html__( 'Enable trackers', 'top-10' ),
				/* translators: 1: Code. */
				'desc'    => esc_html__( 'Top 10 tracks hits in two tables in the database. The overall table only tracks the total hits per post. The daily table tracks hits per post on an hourly basis.', 'top-10' ),
				'type'    => 'multicheck',
				'default' => 'overall,daily',
				'options' => array(
					'overall' => esc_html__( 'Overall', 'top-10' ),
					'daily'   => esc_html__( 'Daily range', 'top-10' ),
				),
			),
			'tracker_type'         => array(
				'id'      => 'tracker_type',
				'name'    => esc_html__( 'Tracker type', 'top-10' ),
				'desc'    => '',
				'type'    => 'radiodesc',
				'default' => 'query_based',
				'options' => self::get_tracker_types(),
			),
			'tracker_all_pages'    => array(
				'id'      => 'tracker_all_pages',
				'name'    => esc_html__( 'Load tracker on all pages', 'top-10' ),
				'desc'    => esc_html__( 'This will load the tracker js on all pages. Helpful if you are running minification/concatenation plugins.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'track_users'          => array(
				'id'      => 'track_users',
				'name'    => esc_html__( 'Track user groups', 'top-10' ) . ':',
				'desc'    => esc_html__( 'Uncheck above to disable tracking if the current user falls into any one of these groups.', 'top-10' ),
				'type'    => 'multicheck',
				'default' => 'authors,editors,admins',
				'options' => array(
					'authors' => esc_html__( 'Authors', 'top-10' ),
					'editors' => esc_html__( 'Editors', 'top-10' ),
					'admins'  => esc_html__( 'Admins', 'top-10' ),
				),
			),
			'logged_in'            => array(
				'id'      => 'logged_in',
				'name'    => esc_html__( 'Track logged-in users', 'top-10' ),
				'desc'    => esc_html__( 'Uncheck to stop tracking logged in users. Only logged out visitors will be tracked if this is disabled. Unchecking this will override the above setting.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'no_bots'              => array(
				'id'      => 'no_bots',
				'name'    => esc_html__( 'Do not track bots', 'top-10' ),
				'desc'    => esc_html__( 'Enable this if you want Top 10 to attempt to stop tracking bots. The plugin includes a comprehensive set of known bot user agents but in some cases this might not be enough to stop tracking bots.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'debug_mode'           => array(
				'id'      => 'debug_mode',
				'name'    => esc_html__( 'Debug mode', 'top-10' ),
				'desc'    => esc_html__( 'Setting this to true will force the tracker to display an output in the browser. This is useful if you are having issues and are seeking support.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
		);

		/**
		 * Filters the Counter settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings Counter settings array
		 */
		return apply_filters( self::$prefix . '_settings_counter', $settings );
	}


	/**
	 * Retrieve the array of List settings
	 *
	 * @since 3.3.0
	 *
	 * @return array List settings array
	 */
	public static function settings_list() {
		$settings = array(
			'use_global_settings'           => array(
				'id'      => 'use_global_settings',
				'name'    => esc_html__( 'Use global settings in block', 'top-10' ),
				'desc'    => esc_html__( 'If activated, the settings from this page are automatically inserted in the Popular Posts block. This also applies to existing blocks which do not have any attributes set if the post is edited.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
				'pro'     => true,
			),
			'limit'                         => array(
				'id'      => 'limit',
				'name'    => esc_html__( 'Number of posts to display', 'top-10' ),
				'desc'    => esc_html__( 'Maximum number of posts that will be displayed in the list. This option is used if you do not specify the number of posts in the widget or shortcodes', 'top-10' ),
				'type'    => 'number',
				'default' => '10',
				'size'    => 'small',
			),
			'how_old'                       => array(
				'id'      => 'how_old',
				'name'    => esc_html__( 'Published age of posts', 'top-10' ),
				'desc'    => esc_html__( 'This options allows you to only show posts that have been published within the above day range. Applies to both overall posts and daily posts lists. e.g. 365 days will only show posts published in the last year in the popular posts lists. Enter 0 for no restriction.', 'top-10' ),
				'type'    => 'number',
				'default' => '0',
				'size'    => 'small',
			),
			'post_types'                    => array(
				'id'      => 'post_types',
				'name'    => esc_html__( 'Post types to include', 'top-10' ),
				'desc'    => esc_html__( 'At least one option should be selected above. Select which post types you want to include in the list of posts. This field can be overridden using a comma separated list of post types when using the manual display.', 'top-10' ),
				'type'    => 'posttypes',
				'default' => 'post',
			),
			'exclusion_header'              => array(
				'id'   => 'exclusion_header',
				'name' => '<h3>' . esc_html__( 'Exclusion settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'exclude_front'                 => array(
				'id'      => 'exclude_front',
				'name'    => esc_html__( 'Exclude Front page and Posts page', 'top-10' ),
				'desc'    => esc_html__( 'If you have designated specific pages for your Front page and Posts page via Settings > Reading, they will be tracked like any other page. Enable this option to exclude them from appearing in the popular posts lists. Note that tracking will still occur.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'exclude_current_post'          => array(
				'id'      => 'exclude_current_post',
				'name'    => esc_html__( 'Exclude current post', 'top-10' ),
				'desc'    => esc_html__( 'Enabling this will exclude the current post being browsed from being displayed in the popular posts list.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'exclude_post_ids'              => array(
				'id'      => 'exclude_post_ids',
				'name'    => esc_html__( 'Post/page IDs to exclude', 'top-10' ),
				'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude from the list. e.g. 188,320,500', 'top-10' ),
				'type'    => 'numbercsv',
				'default' => '',
				'size'    => 'large',
			),
			'exclude_cat_slugs'             => array(
				'id'               => 'exclude_cat_slugs',
				'name'             => esc_html__( 'Exclude Categories', 'top-10' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'top-10' ),
				'type'             => 'csv',
				'default'          => '',
				'size'             => 'large',
				'field_class'      => 'ts_autocomplete',
				'field_attributes' => self::get_taxonomy_search_field_attributes( 'category' ),
			),
			'exclude_categories'            => array(
				'id'       => 'exclude_categories',
				'name'     => esc_html__( 'Exclude category IDs', 'top-10' ),
				'desc'     => esc_html__( 'This is a readonly field that is automatically populated based on the above input when the settings are saved. These might differ from the IDs visible in the Categories page which use the term_id. Top 10 uses the term_taxonomy_id which is unique to this taxonomy.', 'top-10' ),
				'type'     => 'text',
				'default'  => '',
				'size'     => 'large',
				'readonly' => true,
			),
			'exclude_terms_include_parents' => array(
				'id'      => 'exclude_terms_include_parents',
				'name'    => esc_html__( 'Include parent categories', 'top-10' ),
				'desc'    => esc_html__( 'Exclude popular posts from parent categories or all ancestors for nested categories.', 'top-10' ),
				'type'    => 'radio',
				'default' => 'none',
				'options' => array(
					'none'   => esc_html__( 'None', 'top-10' ),
					'parent' => esc_html__( 'Only parent categories', 'top-10' ),
					'all'    => esc_html__( 'All ancestors', 'top-10' ),
				),
				'pro'     => true,
			),
			'exclude_on_cat_slugs'          => array(
				'id'               => 'exclude_on_cat_slugs',
				'name'             => esc_html__( 'Exclude on Categories', 'top-10' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'top-10' ),
				'type'             => 'csv',
				'default'          => '',
				'size'             => 'large',
				'field_class'      => 'ts_autocomplete',
				'field_attributes' => self::get_taxonomy_search_field_attributes( 'category' ),
			),
			'customize_output_header'       => array(
				'id'   => 'customize_output_header',
				'name' => '<h3>' . esc_html__( 'Customize the output', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'title'                         => array(
				'id'      => 'title',
				'name'    => esc_html__( 'Heading of posts', 'top-10' ),
				'desc'    => esc_html__( 'Displayed before the list of the posts as a the master heading', 'top-10' ),
				'type'    => 'text',
				'default' => '<h3>' . esc_html__( 'Popular posts:', 'top-10' ) . '</h3>',
				'size'    => 'large',
			),
			'title_daily'                   => array(
				'id'      => 'title_daily',
				'name'    => esc_html__( 'Heading of posts for daily/custom period lists', 'top-10' ),
				'desc'    => esc_html__( 'Displayed before the list of the posts as a the master heading', 'top-10' ),
				'type'    => 'text',
				'default' => '<h3>' . esc_html__( 'Currently trending:', 'top-10' ) . '</h3>',
				'size'    => 'large',
			),
			'blank_output'                  => array(
				'id'      => 'blank_output',
				'name'    => esc_html__( 'Show when no posts are found', 'top-10' ),
				/* translators: 1: Code. */
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'blank',
				'options' => array(
					'blank'       => esc_html__( 'Blank output', 'top-10' ),
					'custom_text' => esc_html__( 'Display custom text', 'top-10' ),
				),
			),
			'blank_output_text'             => array(
				'id'      => 'blank_output_text',
				'name'    => esc_html__( 'Custom text', 'top-10' ),
				'desc'    => esc_html__( 'Enter the custom text that will be displayed if the second option is selected above', 'top-10' ),
				'type'    => 'textarea',
				'default' => esc_html__( 'No top posts yet', 'top-10' ),
			),
			'show_excerpt'                  => array(
				'id'      => 'show_excerpt',
				'name'    => esc_html__( 'Show post excerpt', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'excerpt_length'                => array(
				'id'      => 'excerpt_length',
				'name'    => esc_html__( 'Length of excerpt (in words)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '10',
				'size'    => 'small',
			),
			'show_date'                     => array(
				'id'      => 'show_date',
				'name'    => esc_html__( 'Show date', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'show_author'                   => array(
				'id'      => 'show_author',
				'name'    => esc_html__( 'Show author', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'disp_list_count'               => array(
				'id'      => 'disp_list_count',
				'name'    => esc_html__( 'Show number of views', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'title_length'                  => array(
				'id'      => 'title_length',
				'name'    => esc_html__( 'Limit post title length (in characters)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '60',
				'size'    => 'small',
			),
			'link_new_window'               => array(
				'id'      => 'link_new_window',
				'name'    => esc_html__( 'Open links in new window', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'link_nofollow'                 => array(
				'id'      => 'link_nofollow',
				'name'    => esc_html__( 'Add nofollow to links', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
			'html_wrapper_header'           => array(
				'id'   => 'html_wrapper_header',
				'name' => '<h3>' . esc_html__( 'HTML to display', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'before_list'                   => array(
				'id'      => 'before_list',
				'name'    => esc_html__( 'Before the list of posts', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => '<ul>',
				'size'    => 'large',
			),
			'after_list'                    => array(
				'id'      => 'after_list',
				'name'    => esc_html__( 'After the list of posts', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => '</ul>',
				'size'    => 'large',
			),
			'before_list_item'              => array(
				'id'      => 'before_list_item',
				'name'    => esc_html__( 'Before each list item', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => '<li>',
				'size'    => 'large',
			),
			'after_list_item'               => array(
				'id'      => 'after_list_item',
				'name'    => esc_html__( 'After each list item', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'default' => '</li>',
				'size'    => 'large',
			),
		);

		/**
		 * Filters the List settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings List settings array
		 */
		return apply_filters( self::$prefix . '_settings_list', $settings );
	}


	/**
	 * Retrieve the array of Thumbnail settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Thumbnail settings array
	 */
	public static function settings_thumbnail() {
		$settings = array(
			'post_thumb_op'      => array(
				'id'      => 'post_thumb_op',
				'name'    => esc_html__( 'Location of the post thumbnail', 'top-10' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'text_only',
				'options' => array(
					'inline'      => esc_html__( 'Display thumbnails inline with posts, before title', 'top-10' ),
					'after'       => esc_html__( 'Display thumbnails inline with posts, after title', 'top-10' ),
					'thumbs_only' => esc_html__( 'Display only thumbnails, no text', 'top-10' ),
					'text_only'   => esc_html__( 'Do not display thumbnails, only text', 'top-10' ),
				),
			),
			'thumb_size'         => array(
				'id'      => 'thumb_size',
				'name'    => esc_html__( 'Thumbnail size', 'top-10' ),
				'desc'    => esc_html__( 'You can choose from existing image sizes above or create a custom size (tptn_thumbnail). If you select a custom size, enter the width, height, and crop settings below. For best results, use a cropped image. Note that changing the width and/or height below will not automatically resize existing images. You will need to regenerate the images using a plugin or WP CLI: wp media regenerate.', 'top-10' ),
				'type'    => 'thumbsizes',
				'default' => 'tptn_thumbnail',
				'options' => Media_Handler::get_all_image_sizes(),
			),
			'thumb_width'        => array(
				'id'      => 'thumb_width',
				'name'    => esc_html__( 'Thumbnail width', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '250',
				'size'    => 'small',
			),
			'thumb_height'       => array(
				'id'      => 'thumb_height',
				'name'    => esc_html__( 'Thumbnail height', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '250',
				'size'    => 'small',
			),
			'thumb_crop'         => array(
				'id'      => 'thumb_crop',
				'name'    => esc_html__( 'Hard crop thumbnails', 'top-10' ),
				'desc'    => esc_html__( 'Check this box to hard crop the thumbnails. i.e. force the width and height above vs. maintaining proportions.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'thumb_create_sizes' => array(
				'id'      => 'thumb_create_sizes',
				'name'    => esc_html__( 'Generate thumbnail sizes', 'top-10' ),
				'desc'    => esc_html__( 'If you select this option and tptn_thumbnail is chosen above, the plugin will register the image size with WordPress to create new thumbnails. Note that this does not update old images, as explained above.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'thumb_html'         => array(
				'id'      => 'thumb_html',
				'name'    => esc_html__( 'Thumbnail size attributes', 'top-10' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'html',
				'options' => array(
					/* translators: %s: Code. */
					'css'  => sprintf( esc_html__( 'Use CSS to set the width and height: e.g. %s', 'top-10' ), '<code>style="max-width:250px;max-height:250px"</code>' ),
					/* translators: %s: Code. */
					'html' => sprintf( esc_html__( 'Use HTML attributes to set the width and height: e.g. %s', 'top-10' ), '<code>width="250" height="250"</code>' ),
					'none' => esc_html__( 'No width or height set. You will need to use external styles to force any width or height of your choice.', 'top-10' ),
				),
			),
			'thumb_meta'         => array(
				'id'      => 'thumb_meta',
				'name'    => esc_html__( 'Thumbnail meta field name', 'top-10' ),
				'desc'    => esc_html__( 'The value of this field should contain the URL of the image and can be set in the metabox in the Edit Post screen', 'top-10' ),
				'type'    => 'text',
				'default' => 'post-image',
			),
			'scan_images'        => array(
				'id'      => 'scan_images',
				'name'    => esc_html__( 'Get first image', 'top-10' ),
				'desc'    => esc_html__( 'If enabled, the plugin will fetch the first image in the post content. Note that this might slow down your page loading time if the first image in the posts is large in file size.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'thumb_default_show' => array(
				'id'      => 'thumb_default_show',
				'name'    => esc_html__( 'Use default thumbnail', 'top-10' ),
				'desc'    => esc_html__( 'Check this box to show the default thumbnail from the next option if no thumbnail is found from the post.', 'top-10' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'thumb_default'      => array(
				'id'      => 'thumb_default',
				'name'    => esc_html__( 'Default thumbnail', 'top-10' ),
				'desc'    => esc_html__( 'Enter the full URL of the image that you wish to display if no thumbnail is found. This image will be displayed below.', 'top-10' ),
				'type'    => 'file',
				'default' => Display::get_default_thumbnail(),
				'size'    => 'large',
			),
		);

		/**
		 * Filters the Thumbnail settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings Thumbnail settings array
		 */
		return apply_filters( self::$prefix . '_settings_thumbnail', $settings );
	}


	/**
	 * Retrieve the array of Styles settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Styles settings array
	 */
	public static function settings_styles() {
		$settings = array(
			'tptn_styles' => array(
				'id'      => 'tptn_styles',
				'name'    => esc_html__( 'Popular posts style', 'top-10' ),
				'desc'    => '',
				'type'    => 'radiodesc',
				'default' => 'no_style',
				'options' => self::get_styles(),
			),
			'custom_css'  => array(
				'id'          => 'custom_css',
				'name'        => esc_html__( 'Custom CSS', 'top-10' ),
				'desc'        => sprintf(
					/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
					esc_html__( 'Do not include %3$sstyle%4$s tags. Check out %1$sthis article%2$s for available CSS classes to style.', 'top-10' ),
					'<a href="' . esc_url( 'https://webberzone.com/support/knowledgebase/using-and-customising-top-10/' ) . '" target="_blank">',
					'</a>',
					'<code>',
					'</code>'
				),
				'type'        => 'css',
				'default'     => '',
				'field_class' => 'codemirror_css',
			),
		);

		/**
		 * Filters the Styles settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings Styles settings array
		 */
		return apply_filters( self::$prefix . '_settings_styles', $settings );
	}


	/**
	 * Retrieve the array of Maintenance settings
	 *
	 * @since 3.3.0
	 *
	 * @return array Maintenance settings array
	 */
	public static function settings_maintenance() {
		$settings = array(
			'cron_on'          => array(
				'id'      => 'cron_on',
				'name'    => esc_html__( 'Enable scheduled maintenance', 'top-10' ),
				'desc'    => sprintf(
					/* translators: 1: Constant holding number of days data is stored. */
					esc_html__( 'Regularly cleaning the database can enhance performance, especially for high-traffic blogs. Enabling maintenance will automatically delete entries older than %s days from the daily tables.', 'top-10' ),
					'<strong>' . (int) apply_filters( 'tptn_maintenance_days', TOP_TEN_STORE_DATA ) . '</strong>'
				),
				'type'    => 'checkbox',
				'default' => false,
			),
			'maintenance_days' => array(
				'id'      => 'maintenance_days',
				'name'    => esc_html__( 'Data retention period', 'top-10' ),
				'desc'    => sprintf(
					/* translators: 1: Constant holding number of days data is stored. */
					esc_html__( 'Enter the number of days to retain data in the daily tables before scheduled maintenance removes older entries. Enter 0 to use the default value of %s days.', 'top-10' ),
					'<strong>' . TOP_TEN_STORE_DATA . '</strong>'
				),
				'type'    => 'number',
				'default' => 0,
				'min'     => 0,
				'size'    => 'small',
				'pro'     => true,
			),
			'cron_range_desc'  => array(
				'id'   => 'cron_range_desc',
				'name' => esc_html__( 'Time to run maintenance', 'top-10' ),
				'desc' => esc_html__( 'The next two options allow you to set the time to run the cron.', 'top-10' ),
				'type' => 'descriptive_text',
			),
			'cron_hour'        => array(
				'id'      => 'cron_hour',
				'name'    => esc_html__( 'Hour', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '0',
				'min'     => '0',
				'max'     => '23',
				'size'    => 'small',
			),
			'cron_min'         => array(
				'id'      => 'cron_min',
				'name'    => esc_html__( 'Minute', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '0',
				'min'     => '0',
				'max'     => '59',
				'size'    => 'small',
			),
			'cron_recurrence'  => array(
				'id'      => 'cron_recurrence',
				'name'    => esc_html__( 'Run maintenance', 'top-10' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'weekly',
				'options' => array(
					'daily'       => esc_html__( 'Daily', 'top-10' ),
					'weekly'      => esc_html__( 'Weekly', 'top-10' ),
					'fortnightly' => esc_html__( 'Fortnightly', 'top-10' ),
					'monthly'     => esc_html__( 'Monthly', 'top-10' ),
				),
			),
		);

		/**
		 * Filters the Maintenance settings array
		 *
		 * @since 3.3.0
		 *
		 * @param array $settings Maintenance settings array
		 */
		return apply_filters( self::$prefix . '_settings_maintenance', $settings );
	}


	/**
	 * Retrieve the array of Feed settings
	 *
	 * @since 2.8.0
	 *
	 * @return array Feed settings array
	 */
	public static function settings_feed() {
		$settings = array(
			'feed_permalink_overall' => array(
				'id'      => 'feed_permalink_overall',
				'name'    => esc_html__( 'Permalink - Overall', 'top-10' ),
				/* translators: 1: Opening link tag, 2: Closing link tag. */
				'desc'    => sprintf( esc_html__( 'This will set the path of the custom feed generated by the plugin for overall popular posts. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'top-10' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
				'type'    => 'text',
				'default' => 'popular-posts',
				'size'    => 'large',
			),
			'feed_permalink_daily'   => array(
				'id'      => 'feed_permalink_daily',
				'name'    => esc_html__( 'Permalink - Daily', 'top-10' ),
				/* translators: 1: Opening link tag, 2: Closing link tag. */
				'desc'    => sprintf( esc_html__( 'This will set the path of the custom feed generated by the plugin for daily/custom period popular posts. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'top-10' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
				'type'    => 'text',
				'default' => 'popular-posts-daily',
				'size'    => 'large',
			),
			'feed_limit'             => array(
				'id'      => 'feed_limit',
				'name'    => esc_html__( 'Number of posts to display', 'top-10' ),
				'desc'    => esc_html__( 'Maximum number of posts that will be displayed in the custom feed.', 'top-10' ),
				'type'    => 'number',
				'default' => '10',
				'size'    => 'small',
			),
			'feed_daily_range'       => array(
				'id'      => 'feed_daily_range',
				'name'    => esc_html__( 'Custom period in day(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'default' => '1',
				'min'     => '0',
				'size'    => 'small',
			),
			'feed_category_slugs'    => array(
				'id'               => 'feed_category_slugs',
				'name'             => esc_html__( 'Filter Categories', 'top-10' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs to include in the feed. The field has an autocomplete so simply start typing the starting letters and it will prompt you with options. Leave blank to include all categories.', 'top-10' ),
				'type'             => 'csv',
				'default'          => '',
				'size'             => 'large',
				'field_class'      => 'ts_autocomplete',
				'field_attributes' => self::get_taxonomy_search_field_attributes( 'category' ),
				'pro'              => true,
			),
		);

		/**
		 * Filters the Feed settings array
		 *
		 * @since 2.8.0
		 *
		 * @param array $settings Feed settings array
		 */
		return apply_filters( self::$prefix . '_settings_feed', $settings );
	}

	/**
	 * Get the various styles.
	 *
	 * @since 2.5.0
	 * @return array Style options.
	 */
	public static function get_styles() {
		$styles = array(
			array(
				'id'          => 'no_style',
				'name'        => esc_html__( 'No styles', 'top-10' ),
				'description' => esc_html__( 'Select this option if you plan to add your own styles', 'top-10' ) . '<br />',
			),
			array(
				'id'          => 'text_only',
				'name'        => esc_html__( 'Text only', 'top-10' ),
				'description' => esc_html__( 'Disable thumbnails and no longer include the default style sheet included in the plugin', 'top-10' ) . '<br />',
			),
			array(
				'id'          => 'left_thumbs',
				'name'        => esc_html__( 'Left thumbnails', 'top-10' ),
				'description' => esc_html__( 'Enabling this option will set the post thumbnail to be before text. Disabling this option will not revert any settings.', 'top-10' ),
			),
		);

		/**
		 * Filter the array containing the types of styles to add your own.
		 *
		 * @since 2.5.0
		 *
		 * @param array $styles Different styles.
		 */
		return apply_filters( self::$prefix . '_get_styles', $styles );
	}

	/**
	 * Get User Roles.
	 *
	 * @since 4.0.0
	 *
	 * @param array $remove_roles Roles to remove.
	 * @return array User roles in the format 'role' => 'name'.
	 */
	public static function get_user_roles( $remove_roles = array() ) {
		// Get the global $wp_roles object using the wp_roles() function.
		$wp_roles = wp_roles();

		// Initialize the array to store roles in the desired format.
		$roles_array = array();

		if ( ! empty( $wp_roles->roles ) ) {
			// Loop through all roles and store them in 'role' => 'name' format.
			foreach ( $wp_roles->roles as $role_key => $role_details ) {
				if ( in_array( $role_key, $remove_roles, true ) ) {
					continue;
				}
				$roles_array[ $role_key ] = esc_html( $role_details['name'] );
			}
		} else {
			// Fallback to default WordPress roles.
			$default_roles = array(
				'administrator' => esc_html__( 'Administrator' ),
				'editor'        => esc_html__( 'Editor' ),
				'author'        => esc_html__( 'Author' ),
				'contributor'   => esc_html__( 'Contributor' ),
				'subscriber'    => esc_html__( 'Subscriber' ),
			);

			foreach ( $default_roles as $role_key => $role_name ) {
				if ( ! in_array( $role_key, $remove_roles, true ) ) {
					$roles_array[ $role_key ] = $role_name;
				}
			}
		}

		return $roles_array;
	}

	/**
	 * Adding WordPress plugin action links.
	 *
	 * @since 3.3.0
	 *
	 * @param array $links Array of links.
	 * @return array
	 */
	public function plugin_actions_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'top-10' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add meta links on Plugins page.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $links Array of Links.
	 * @param string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( false !== strpos( $file, 'top-10.php' ) ) {
			$new_links = array(
				'support'    => '<a href = "https://wordpress.org/support/plugin/top-10">' . esc_html__( 'Support', 'top-10' ) . '</a>',
				'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'top-10' ) . '</a>',
				'contribute' => '<a href = "https://github.com/WebberZone/top-10">' . esc_html__( 'Contribute', 'top-10' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Get the help sidebar content to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_sidebar() {
		$help_sidebar =
			/* translators: 1: Plugin support site link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'top-10' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: WordPress.org support forums link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'top-10' ), esc_url( 'https://wordpress.org/support/plugin/top-10' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github issues link, 2: Github plugin page link. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'top-10' ),
				esc_url( 'https://github.com/ajaydsouza/top-10/issues' ),
				esc_url( 'https://github.com/ajaydsouza/top-10' )
			) . '</p>';

		/**
		 * Filter to modify the help sidebar content.
		 *
		 * @since 1.8.0
		 *
		 * @param string $help_sidebar Help sidebar content.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_sidebar );
	}

	/**
	 * Get the help tabs to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'tptn-settings-general-help',
				'title'   => esc_html__( 'General', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab provides global settings for how Top 10 works across your site.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Configure caching, uninstall behaviour, admin columns, the dashboard, and other high-level options. You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-counter-help',
				'title'   => esc_html__( 'Counter / Tracker', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab controls how Top 10 tracks and displays view counts.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Choose where to display view counts, how they are formatted, which visits are tracked, and how the tracker works (REST API, query variables, or Ajax).', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-list-help',
				'title'   => esc_html__( 'Posts list', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab controls the popular posts list output.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Configure the number of posts, age of posts, post types, exclusions, and what information is shown (title, date, author, views, excerpts, and HTML wrappers).', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-thumbnail-help',
				'title'   => esc_html__( 'Thumbnail', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab manages thumbnail settings for the popular posts list.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Choose whether thumbnails are displayed, select the image size, configure custom dimensions, cropping, the default thumbnail, and fallback behaviour when no thumbnail is found.', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-styles-help',
				'title'   => esc_html__( 'Styles', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab controls how the popular posts list is styled.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Select a built-in style or disable styles if you want to fully control the look using your own CSS.', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-maintenance-help',
				'title'   => esc_html__( 'Maintenance', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab handles tools and maintenance-related options.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Use this screen to reset settings, clear cached popular posts, and manage database-related options such as pruning or rebuilding counts.', 'top-10' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-feed-help',
				'title'   => esc_html__( 'Feed', 'top-10' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This tab controls how Top 10 content appears in your site feed.', 'top-10' ) . '</strong></p>' .
					'<p>' . esc_html__( 'Configure copyright text and additional HTML that is added before or after the content in your feeds.', 'top-10' ) . '</p>',
			),
		);

		/**
		 * Filter to add more help tabs.
		 *
		 * @since 1.8.0
		 *
		 * @param array $help_tabs Associative array of help tabs.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_tabs );
	}

	/**
	 * Function returns the different types of trackers.
	 *
	 * @since 3.3.0
	 * @return array Tracker types.
	 */
	public static function get_tracker_types() {

		$trackers = array(
			array(
				'id'          => 'rest_based',
				'name'        => __( 'REST API based', 'top-10' ),
				'description' => __( 'Uses the REST API to record visits', 'top-10' ),
			),
			array(
				'id'          => 'query_based',
				'name'        => __( 'Query variable based', 'top-10' ),
				'description' => __( 'Uses query variables to record visits', 'top-10' ),
			),
			array(
				'id'          => 'ajaxurl',
				'name'        => __( 'Ajaxurl based', 'top-10' ),
				'description' => __( 'Uses admin-ajax.php which is inbuilt within WordPress to process the tracker', 'top-10' ),
			),
		);

		/**
		 * Filter the array containing the types of trackers to add your own.
		 *
		 * @since 2.4.0
		 *
		 * @param array $trackers Different trackers.
		 */
		return apply_filters( self::$prefix . '_get_tracker_types', $trackers );
	}




	/**
	 * Add footer text on the plugin page.
	 *
	 * @since 2.0.0
	 */
	public static function get_admin_footer_text() {
		return sprintf(
			/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
			__( 'Thank you for using %1$sWebberZone Top 10%2$s! Please %3$srate us%2$s on WordPress.org', 'top-10' ),
			'<a href="https://webberzone.com/plugins/top-10/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/plugin/top-10/reviews/#new-post" target="_blank">'
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 3.3.0
	 *
	 * @param string $hook Current hook.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( ! isset( $this->settings_api->settings_page ) || $this->settings_api->settings_page !== $hook ) {
			return;
		}

		wp_enqueue_script( 'top-ten-admin-js' );
		wp_enqueue_style( 'top-ten-admin-css' );
		wp_enqueue_style( 'wp-spinner' );
		wp_localize_script(
			'top-ten-admin-js',
			'tptn_admin',
			array(
				'thumb_default' => Display::get_default_thumbnail(),
			)
		);
		wp_localize_script(
			'top-ten-admin-js',
			'top_ten_admin_data',
			array(
				'security' => wp_create_nonce( 'tptn-admin' ),
				'strings'  => array(
					'confirm_message'      => esc_html__( 'Are you sure you want to clear the cache?', 'top-10' ),
					'clearing_text'        => esc_html__( 'Clearing...', 'top-10' ),
					'success_message'      => esc_html__( 'Cache cleared successfully!', 'top-10' ),
					'fail_message'         => esc_html__( 'Failed to clear cache. Please try again.', 'top-10' ),
					'request_fail_message' => esc_html__( 'Request failed: ', 'top-10' ),
				),
			)
		);
	}

	/**
	 * Modify settings when they are being saved.
	 *
	 * @since 3.3.0
	 *
	 * @param  array $settings Settings array.
	 * @return array Sanitized settings array.
	 */
	public function change_settings_on_save( $settings ) {

		Settings_Sanitize::sanitize_tax_slugs( $settings, 'exclude_cat_slugs', 'exclude_categories' );
		Settings_Sanitize::sanitize_tax_slugs( $settings, 'exclude_on_cat_slugs', 'exclude_on_categories' );
		Settings_Sanitize::sanitize_tax_slugs( $settings, 'feed_category_slugs', 'feed_categories' );

		// Save cron settings.
		$settings['cron_hour'] = min( 23, absint( $settings['cron_hour'] ) );
		$settings['cron_min']  = min( 59, absint( $settings['cron_min'] ) );

		if ( ! empty( $settings['cron_on'] ) ) {
			\WebberZone\Top_Ten\Admin\Cron::enable_run( $settings['cron_hour'], $settings['cron_min'], $settings['cron_recurrence'] );
		} else {
			\WebberZone\Top_Ten\Admin\Cron::disable_run();
		}

		// Force thumb_width and thumb_height if either are zero.
		if ( empty( $settings['thumb_width'] ) || empty( $settings['thumb_height'] ) ) {
			list( $settings['thumb_width'], $settings['thumb_height'] ) = Media_Handler::get_thumb_size( $settings['thumb_size'] );
		}

		// Delete the cache.
		\WebberZone\Top_Ten\Util\Cache::delete();

		return $settings;
	}

	/**
	 * Display the default thumbnail below the setting.
	 *
	 * @since 3.3.0
	 *
	 * @param  string $html Current HTML.
	 * @param  array  $args Argument array of the setting.
	 * @return string
	 */
	public function display_admin_thumbnail( $html, $args ) {

		$thumb_default = \tptn_get_option( 'thumb_default' );

		if ( 'thumb_default' === $args['id'] && '' !== $thumb_default ) {
			$html .= '<br />';
			$html .= sprintf( '<img src="%1$s" style="max-width:200px" title="%2$s" alt="%2$s" />', esc_attr( $thumb_default ), esc_html__( 'Default thumbnail', 'top-10' ) );
		}

		return $html;
	}

	/**
	 * Display the default thumbnail below the setting.
	 *
	 * @since 3.3.0
	 *
	 * @param  string $html Current HTML.
	 * @param  array  $args Argument array of the setting.
	 * @return string
	 */
	public function reset_default_thumb_setting( $html, $args ) {

		$thumb_default = \tptn_get_option( 'thumb_default' );

		if ( 'thumb_default' === $args['id'] && Display::get_default_thumbnail() !== $thumb_default ) {
			$html = '<span class="dashicons dashicons-undo reset-default-thumb" style="cursor: pointer;" title="' . __( 'Reset' ) . '"></span> <br />' . $html;
		}

		return $html;
	}

	/**
	 * Add a link to the Tools page from the settings page.
	 *
	 * @since 3.5.0
	 */
	public static function settings_page_header() {
		global $tptn_freemius;
		?>
		<p>
			<?php if ( ! $tptn_freemius->is_paying() ) { ?>
			<a class="tptn_button tptn_button_gold" href="<?php echo esc_url( $tptn_freemius->get_upgrade_url() ); ?>">
				<?php esc_html_e( 'Upgrade to Pro', 'top-10' ); ?>
			</a>
			<?php } ?>
		</p>

		<?php
	}

	/**
	 * Updated the settings fields to display a pro version link.
	 *
	 * @param string $output Settings field HTML.
	 * @param array  $args   Settings field arguments.
	 * @return string Updated HTML.
	 */
	public static function after_setting_output( $output, $args ) {
		if ( isset( $args['pro'] ) && $args['pro'] ) {
			$output .= sprintf(
				'<a class="tptn_button tptn_button_gold" target="_blank" href="%s" title="%s">%s</a>',
				esc_url( \WebberZone\Top_Ten\tptn_freemius()->get_upgrade_url() ),
				esc_attr__( 'Upgrade to Pro', 'top-10' ),
				esc_html__( 'Upgrade to Pro', 'top-10' )
			);
		}

		if ( isset( $args['id'] ) && 'tptn_styles' === $args['id'] ) {
			$post_thumb_op = \tptn_get_option( 'post_thumb_op' );

			if ( 'text_only' === $post_thumb_op ) {
				$output .= sprintf(
					'<p class="description" style="color:#9B0800;">%s</p>',
					esc_html__( 'Note: Thumbnail position set to “Do not display thumbnails, only text”. The plugin will force the output style to “Text only” regardless of what you choose here.', 'top-10' )
				);
			}
		}

		return $output;
	}

	/**
	 * Add a button to the settings page to start the settings wizard.
	 *
	 * @since 4.2.0
	 */
	public function add_wizard_button() {
		printf(
			'<br /><a aria-label="%s" class="button button-secondary" href="%s" title="%s" style="margin-top: 10px;">%s</a>',
			esc_attr__( 'Start Settings Wizard', 'top-10' ),
			esc_url( admin_url( 'admin.php?page=tptn_wizard' ) ),
			esc_attr__( 'Start Settings Wizard', 'top-10' ),
			esc_html__( 'Start Settings Wizard', 'top-10' )
		);
	}

	/**
	 * Handle Tom Select taxonomy search AJAX requests.
	 *
	 * @since 4.2.0
	 *
	 * @return void
	 */
	public static function taxonomy_search_tom_select() {
		// Verify nonce.
		if ( ! isset( $_REQUEST['nonce'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing nonce' ) );
		}

		$nonce_valid = wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), self::$prefix . '_taxonomy_search_tom_select' );

		if ( ! $nonce_valid ) {
			wp_send_json_error(
				array(
					'message'         => 'Invalid nonce',
					'received_nonce'  => sanitize_key( $_REQUEST['nonce'] ),
					'expected_action' => self::$prefix . '_taxonomy_search_tom_select',
				)
			);
		}

		if ( ! isset( $_REQUEST['endpoint'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_send_json_error( 'Missing endpoint' );
		}

		$endpoint = sanitize_key( $_REQUEST['endpoint'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$search_term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$comma = _x( ',', 'tag delimiter', 'top-10' );
		if ( ',' !== $comma ) {
			$search_term = str_replace( $comma, ',', $search_term );
		}
		if ( false !== strpos( $search_term, ',' ) ) {
			$search_term = explode( ',', $search_term );
			$search_term = $search_term[ count( $search_term ) - 1 ];
		}
		$search_term = trim( $search_term );

		if ( 'public_taxonomies' === $endpoint ) {
			$taxonomies = (array) get_taxonomies( array( 'public' => true ), 'objects' );
			$taxonomy   = array();
			$tax        = null;

			foreach ( $taxonomies as $taxonomy_name => $taxonomy_object ) {
				if ( ! is_string( $taxonomy_name ) || '' === $taxonomy_name ) {
					continue;
				}

				if ( empty( $taxonomy_object->cap->assign_terms ) ) {
					continue;
				}

				if ( ! current_user_can( $taxonomy_object->cap->assign_terms ) ) {
					continue;
				}

				$taxonomy[] = $taxonomy_name;
			}

			if ( empty( $taxonomy ) ) {
				wp_send_json_success( array() );
			}

			$tax = get_taxonomy( $taxonomy[0] );
		} else {
			$taxonomy = $endpoint;
			$tax      = get_taxonomy( $taxonomy );

			if ( ! $tax ) {
				wp_send_json_error( 'Invalid taxonomy' );
			}

			if ( ! current_user_can( $tax->cap->assign_terms ) ) {
				wp_send_json_error( 'Insufficient permissions' );
			}
		}

		/** This filter has been defined in /wp-admin/includes/ajax-actions.php */
		$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $search_term );

		/*
		 * Require $term_search_min_chars chars for matching (default: 2)
		 * ensure it's a non-negative, non-zero integer.
		 */
		if ( ( 0 === $term_search_min_chars ) || ( strlen( $search_term ) < $term_search_min_chars ) ) {
			wp_send_json_success( array() );
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'name__like' => $search_term,
				'hide_empty' => false,
			)
		);

		$results = array();
		foreach ( (array) $terms as $term ) {
			$formatted_string = "{$term->name} ({$term->taxonomy}:{$term->term_taxonomy_id})";
			$results[]        = array(
				'value' => $formatted_string,
				'text'  => $term->name,
			);
		}

		wp_send_json_success( $results );
	}

	/**
	 * Get field attributes for Tom Select taxonomy search fields.
	 *
	 * @since 4.2.0
	 *
	 * @param string $taxonomy  The taxonomy to search.
	 * @param array  $ts_config Optional Tom Select configuration.
	 * @return array Field attributes array.
	 */
	private static function get_taxonomy_search_field_attributes( $taxonomy, $ts_config = array() ) {
		$attributes = array(
			'data-wp-prefix'   => strtoupper( (string) self::$prefix ),
			'data-wp-action'   => self::$prefix . '_taxonomy_search_tom_select',
			'data-wp-nonce'    => wp_create_nonce( self::$prefix . '_taxonomy_search_tom_select' ),
			'data-wp-endpoint' => $taxonomy,
		);

		if ( ! empty( $ts_config ) ) {
			$attributes['data-ts-config'] = wp_json_encode( $ts_config );
		}

		return $attributes;
	}

	/**
	 * Get field attributes for Tom Select meta key search fields.
	 *
	 * @since 4.2.0
	 *
	 * @param array $ts_config Optional Tom Select configuration.
	 * @return array Field attributes array.
	 */
	private static function get_meta_keys_search_field_attributes( $ts_config = array() ) {
		$attributes = array(
			'data-wp-prefix'   => strtoupper( (string) self::$prefix ),
			'data-wp-action'   => self::$prefix . '_taxonomy_search_tom_select',
			'data-wp-nonce'    => wp_create_nonce( self::$prefix . '_taxonomy_search_tom_select' ),
			'data-wp-endpoint' => 'meta_keys',
		);

		if ( ! empty( $ts_config ) ) {
			$attributes['data-ts-config'] = wp_json_encode( $ts_config );
		}

		return $attributes;
	}
}
