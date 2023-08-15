<?php
/**
 * Register Settings.
 *
 * @link  https://webberzone.com
 * @since 3.3.0
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin\Settings;

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
	 * Settings Page in Admin area.
	 *
	 * @since 3.3.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

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
		$this->settings_key = 'tptn_settings';
		self::$prefix       = 'tptn';
		$this->menu_slug    = 'tptn_options_page';

		add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( TOP_TEN_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );
		add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99 );
		add_filter( 'tptn_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
		add_filter( 'tptn_after_setting_output', array( $this, 'display_admin_thumbnail' ), 10, 2 );
		add_filter( 'tptn_setting_field_description', array( $this, 'reset_default_thumb_setting' ), 10, 2 );
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
			'page_header'          => esc_html__( 'Top 10 Settings', 'add-to-all' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'add-to-all' ),
			'success_message'      => esc_html__( 'Settings updated.', 'add-to-all' ),
			'save_changes'         => esc_html__( 'Save Changes', 'add-to-all' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'add-to-all' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'add-to-all' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'add-to-all' ),
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
			'page_title'    => esc_html__( 'Top 10 Settings', 'add-to-all' ),
			'menu_title'    => esc_html__( 'Settings', 'add-to-all' ),
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
			'trackers'                => array(
				'id'      => 'trackers',
				'name'    => esc_html__( 'Enable trackers', 'top-10' ),
				/* translators: 1: Code. */
				'desc'    => '',
				'type'    => 'multicheck',
				'default' => array(
					'overall' => 'overall',
					'daily'   => 'daily',
				),
				'options' => array(
					'overall' => esc_html__( 'Overall', 'top-10' ),
					'daily'   => esc_html__( 'Daily', 'top-10' ),
				),
			),
			'cache'                   => array(
				'id'      => 'cache',
				'name'    => esc_html__( 'Enable cache', 'top-10' ),
				'desc'    => esc_html__( 'If activated, Top 10 will use the Transients API to cache the popular posts output for 1 hour.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'cache_time'              => array(
				'id'      => 'cache_time',
				'name'    => esc_html__( 'Time to cache', 'top-10' ),
				'desc'    => esc_html__( 'Enter the number of seconds to cache the output.', 'top-10' ),
				'type'    => 'text',
				'options' => HOUR_IN_SECONDS,
			),
			'daily_midnight'          => array(
				'id'      => 'daily_midnight',
				'name'    => esc_html__( 'Start daily counts from midnight', 'top-10' ),
				'desc'    => esc_html__( 'Daily counter will display number of visits from midnight. This option is checked by default and mimics the way most normal counters work. Turning this off will allow you to use the hourly setting in the next option.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'range_desc'              => array(
				'id'   => 'range_desc',
				'name' => '<strong>' . esc_html__( 'Default custom period range', 'top-10' ) . '</strong>',
				'desc' => esc_html__( 'The next two options allow you to set the default range for the custom period. This was previously called the daily range. This can be overridden in the widget.', 'top-10' ),
				'type' => 'descriptive_text',
			),
			'daily_range'             => array(
				'id'      => 'daily_range',
				'name'    => esc_html__( 'Day(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '1',
				'min'     => '0',
				'size'    => 'small',
			),
			'hour_range'              => array(
				'id'      => 'hour_range',
				'name'    => esc_html__( 'Hour(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '0',
				'min'     => '0',
				'max'     => '23',
				'size'    => 'small',
			),
			'uninstall_clean_options' => array(
				'id'      => 'uninstall_clean_options',
				'name'    => esc_html__( 'Delete options on uninstall', 'top-10' ),
				'desc'    => esc_html__( 'If this is checked, all settings related to Top 10 are removed from the database if you choose to uninstall/delete the plugin.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'uninstall_clean_tables'  => array(
				'id'      => 'uninstall_clean_tables',
				'name'    => esc_html__( 'Delete counter data on uninstall', 'top-10' ),
				'desc'    => esc_html__( 'If this is checked, the tables containing the counter statistics are removed from the database if you choose to uninstall/delete the plugin. Keep this unchecked if you choose to reinstall the plugin and do not want to lose your counter data.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'show_metabox'            => array(
				'id'      => 'show_metabox',
				'name'    => esc_html__( 'Show metabox', 'top-10' ),
				'desc'    => esc_html__( 'This will add the Top 10 metabox on Edit Posts or Add New Posts screens. Also applies to Pages and Custom Post Types.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'show_metabox_admins'     => array(
				'id'      => 'show_metabox_admins',
				'name'    => esc_html__( 'Limit meta box to Admins only', 'top-10' ),
				'desc'    => esc_html__( 'If selected, the meta box will be hidden from anyone who is not an Admin. By default, Contributors and above will be able to see the meta box. Applies only if the above option is selected.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'show_credit'             => array(
				'id'      => 'show_credit',
				'name'    => esc_html__( 'Link to Top 10 plugin page', 'top-10' ),
				'desc'    => esc_html__( 'A no-follow link to the plugin homepage will be added as the last item of the popular posts.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
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
			'counter_header'        => array(
				'id'   => 'counter_header',
				'name' => '<h3>' . esc_html__( 'Counter settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'add_to'                => array(
				'id'      => 'add_to',
				'name'    => esc_html__( 'Display number of views on', 'top-10' ) . ':',
				/* translators: 1: Code. */
				'desc'    => sprintf( esc_html__( 'If you choose to disable this, please add %1$s to your template file where you want it displayed', 'top-10' ), "<code>&lt;?php if ( function_exists( 'echo_tptn_post_count' ) ) { echo_tptn_post_count(); } ?&gt;</code>" ),
				'type'    => 'multicheck',
				'default' => array(
					'single' => 'single',
					'page'   => 'page',
				),
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
			'count_disp_form'       => array(
				'id'      => 'count_disp_form',
				'name'    => esc_html__( 'Format to display the post views', 'top-10' ),
				/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
				'desc'    => sprintf( esc_html__( 'Use %1$s to display the total count, %2$s for daily count and %3$s for overall counts across all posts. Default display is %4$s', 'top-10' ), '<code>%totalcount%</code>', '<code>%dailycount%</code>', '<code>%overallcount%</code>', '<code>Visited %totalcount% times, %dailycount% visit(s) today</code>' ),
				'type'    => 'textarea',
				'options' => 'Visited %totalcount% times, %dailycount% visit(s) today',
			),
			'count_disp_form_zero'  => array(
				'id'      => 'count_disp_form_zero',
				'name'    => esc_html__( 'What to display when there are no visits?', 'top-10' ),
				/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
				'desc'    => esc_html__( "This text applies only when there are 0 hits for the post and it isn't a single page. e.g. if you display post views on the homepage or archives then this text will be used. To override this, just enter the same text as above option.", 'top-10' ),
				'type'    => 'textarea',
				'options' => 'No visits yet',
			),
			'number_format_count'   => array(
				'id'      => 'number_format_count',
				'name'    => esc_html__( 'Number format post count', 'top-10' ),
				'desc'    => esc_html__( 'Activating this option will convert the post counts into a number format based on the locale', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'dynamic_post_count'    => array(
				'id'      => 'dynamic_post_count',
				'name'    => esc_html__( 'Always display latest post count', 'top-10' ),
				'desc'    => esc_html__( 'This option uses JavaScript and will increase your page load time. Turn this off if you are not using caching plugins or are OK with displaying older cached counts.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'exclude_on_post_ids'   => array(
				'id'      => 'exclude_on_post_ids',
				'name'    => esc_html__( 'Exclude display on these post IDs', 'top-10' ),
				'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude displaying the top posts on. e.g. 188,320,500', 'top-10' ),
				'type'    => 'numbercsv',
				'options' => '',
			),
			'pv_in_admin'           => array(
				'id'      => 'pv_in_admin',
				'name'    => esc_html__( 'Page views in admin', 'top-10' ),
				'desc'    => esc_html__( "Adds three columns called Total Views, Today's Views and Views to All Posts and All Pages. You can selectively disable these by pulling down the Screen Options from the top right of the respective screens.", 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'show_count_non_admins' => array(
				'id'      => 'show_count_non_admins',
				'name'    => esc_html__( 'Show views to non-admins', 'top-10' ),
				'desc'    => esc_html__( "If you disable this then non-admins won't see the above columns or view the independent pages with the top posts.", 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'tracker_header'        => array(
				'id'   => 'tracker_header',
				'name' => '<h3>' . esc_html__( 'Tracker settings', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'tracker_type'          => array(
				'id'      => 'tracker_type',
				'name'    => esc_html__( 'Tracker type', 'top-10' ),
				'desc'    => '',
				'type'    => 'radiodesc',
				'default' => 'query_based',
				'options' => self::get_tracker_types(),
			),
			'tracker_all_pages'     => array(
				'id'      => 'tracker_all_pages',
				'name'    => esc_html__( 'Load tracker on all pages', 'top-10' ),
				'desc'    => esc_html__( 'This will load the tracker js on all pages. Helpful if you are running minification/concatenation plugins.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'track_users'           => array(
				'id'      => 'track_users',
				'name'    => esc_html__( 'Track user groups', 'top-10' ) . ':',
				'desc'    => esc_html__( 'Uncheck above to disable tracking if the current user falls into any one of these groups.', 'top-10' ),
				'type'    => 'multicheck',
				'default' => array(
					'editors' => 'editors',
					'admins'  => 'admins',
				),
				'options' => array(
					'authors' => esc_html__( 'Authors', 'top-10' ),
					'editors' => esc_html__( 'Editors', 'top-10' ),
					'admins'  => esc_html__( 'Admins', 'top-10' ),
				),
			),
			'logged_in'             => array(
				'id'      => 'logged_in',
				'name'    => esc_html__( 'Track logged-in users', 'top-10' ),
				'desc'    => esc_html__( 'Uncheck to stop tracking logged in users. Only logged out visitors will be tracked if this is disabled. Unchecking this will override the above setting.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'no_bots'               => array(
				'id'      => 'no_bots',
				'name'    => esc_html__( 'Do not track bots', 'top-10' ),
				'desc'    => esc_html__( 'Enable this if you want Top 10 to attempt to stop tracking bots. The plugin includes a comprehensive set of known bot user agents but in some cases this might not be enough to stop tracking bots.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'debug_mode'            => array(
				'id'      => 'debug_mode',
				'name'    => esc_html__( 'Debug mode', 'top-10' ),
				'desc'    => esc_html__( 'Setting this to true will force the tracker to display an output in the browser. This is useful if you are having issues and are seeking support.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
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
			'limit'                   => array(
				'id'      => 'limit',
				'name'    => esc_html__( 'Number of posts to display', 'top-10' ),
				'desc'    => esc_html__( 'Maximum number of posts that will be displayed in the list. This option is used if you do not specify the number of posts in the widget or shortcodes', 'top-10' ),
				'type'    => 'number',
				'options' => '10',
				'size'    => 'small',
			),
			'how_old'                 => array(
				'id'      => 'how_old',
				'name'    => esc_html__( 'Published age of posts', 'top-10' ),
				'desc'    => esc_html__( 'This options allows you to only show posts that have been published within the above day range. Applies to both overall posts and daily posts lists. e.g. 365 days will only show posts published in the last year in the popular posts lists. Enter 0 for no restriction.', 'top-10' ),
				'type'    => 'number',
				'options' => '0',
			),
			'post_types'              => array(
				'id'      => 'post_types',
				'name'    => esc_html__( 'Post types to include', 'top-10' ),
				'desc'    => esc_html__( 'At least one option should be selected above. Select which post types you want to include in the list of posts. This field can be overridden using a comma separated list of post types when using the manual display.', 'top-10' ),
				'type'    => 'posttypes',
				'options' => 'post',
			),
			'exclude_front'           => array(
				'id'      => 'exclude_front',
				'name'    => esc_html__( 'Exclude Front page and Posts page', 'top-10' ),
				'desc'    => esc_html__( 'If you have set your Front page and Posts page to be specific pages via Settings > Reading, then these will be tracked similar to other pages. Enable this option to exclude them from showing up in the popular posts lists. The tracking will not be disabled.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'exclude_current_post'    => array(
				'id'      => 'exclude_current_post',
				'name'    => esc_html__( 'Exclude current post', 'top-10' ),
				'desc'    => esc_html__( 'Enabling this will exclude the current post being browsed from being displayed in the popular posts list.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'exclude_post_ids'        => array(
				'id'      => 'exclude_post_ids',
				'name'    => esc_html__( 'Post/page IDs to exclude', 'top-10' ),
				'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude from the list. e.g. 188,320,500', 'top-10' ),
				'type'    => 'numbercsv',
				'options' => '',
			),
			'exclude_cat_slugs'       => array(
				'id'               => 'exclude_cat_slugs',
				'name'             => esc_html__( 'Exclude Categories', 'top-10' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'top-10' ),
				'type'             => 'csv',
				'options'          => '',
				'size'             => 'large',
				'field_class'      => 'category_autocomplete',
				'field_attributes' => array(
					'data-wp-taxonomy' => 'category',
				),
			),
			'exclude_categories'      => array(
				'id'       => 'exclude_categories',
				'name'     => esc_html__( 'Exclude category IDs', 'top-10' ),
				'desc'     => esc_html__( 'This is a readonly field that is automatically populated based on the above input when the settings are saved. These might differ from the IDs visible in the Categories page which use the term_id. Top 10 uses the term_taxonomy_id which is unique to this taxonomy.', 'top-10' ),
				'type'     => 'text',
				'options'  => '',
				'readonly' => true,
			),
			'exclude_on_cat_slugs'    => array(
				'id'               => 'exclude_on_cat_slugs',
				'name'             => esc_html__( 'Exclude on Categories', 'top-10' ),
				'desc'             => esc_html__( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options. Does not support custom taxonomies.', 'top-10' ),
				'type'             => 'csv',
				'options'          => '',
				'size'             => 'large',
				'field_class'      => 'category_autocomplete',
				'field_attributes' => array(
					'data-wp-taxonomy' => 'category',
				),
			),
			'customize_output_header' => array(
				'id'   => 'customize_output_header',
				'name' => '<h3>' . esc_html__( 'Customize the output', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'title'                   => array(
				'id'      => 'title',
				'name'    => esc_html__( 'Heading of posts', 'top-10' ),
				'desc'    => esc_html__( 'Displayed before the list of the posts as a the master heading', 'top-10' ),
				'type'    => 'text',
				'options' => '<h3>' . esc_html__( 'Popular posts:', 'top-10' ) . '</h3>',
				'size'    => 'large',
			),
			'title_daily'             => array(
				'id'      => 'title_daily',
				'name'    => esc_html__( 'Heading of posts for daily/custom period lists', 'top-10' ),
				'desc'    => esc_html__( 'Displayed before the list of the posts as a the master heading', 'top-10' ),
				'type'    => 'text',
				'options' => '<h3>' . esc_html__( 'Currently trending:', 'top-10' ) . '</h3>',
				'size'    => 'large',
			),
			'blank_output'            => array(
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
			'blank_output_text'       => array(
				'id'      => 'blank_output_text',
				'name'    => esc_html__( 'Custom text', 'top-10' ),
				'desc'    => esc_html__( 'Enter the custom text that will be displayed if the second option is selected above', 'top-10' ),
				'type'    => 'textarea',
				'options' => esc_html__( 'No top posts yet', 'top-10' ),
			),
			'show_excerpt'            => array(
				'id'      => 'show_excerpt',
				'name'    => esc_html__( 'Show post excerpt', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'excerpt_length'          => array(
				'id'      => 'excerpt_length',
				'name'    => esc_html__( 'Length of excerpt (in words)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '10',
				'size'    => 'small',
			),
			'show_date'               => array(
				'id'      => 'show_date',
				'name'    => esc_html__( 'Show date', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'show_author'             => array(
				'id'      => 'show_author',
				'name'    => esc_html__( 'Show author', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'disp_list_count'         => array(
				'id'      => 'disp_list_count',
				'name'    => esc_html__( 'Show number of views', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'title_length'            => array(
				'id'      => 'title_length',
				'name'    => esc_html__( 'Limit post title length (in characters)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '60',
				'size'    => 'small',
			),
			'link_new_window'         => array(
				'id'      => 'link_new_window',
				'name'    => esc_html__( 'Open links in new window', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'link_nofollow'           => array(
				'id'      => 'link_nofollow',
				'name'    => esc_html__( 'Add nofollow to links', 'top-10' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'options' => false,
			),
			'html_wrapper_header'     => array(
				'id'   => 'html_wrapper_header',
				'name' => '<h3>' . esc_html__( 'HTML to display', 'top-10' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'before_list'             => array(
				'id'      => 'before_list',
				'name'    => esc_html__( 'Before the list of posts', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'options' => '<ul>',
			),
			'after_list'              => array(
				'id'      => 'after_list',
				'name'    => esc_html__( 'After the list of posts', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'options' => '</ul>',
			),
			'before_list_item'        => array(
				'id'      => 'before_list_item',
				'name'    => esc_html__( 'Before each list item', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'options' => '<li>',
			),
			'after_list_item'         => array(
				'id'      => 'after_list_item',
				'name'    => esc_html__( 'After each list item', 'top-10' ),
				'desc'    => '',
				'type'    => 'text',
				'options' => '</li>',
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
				'desc'    => esc_html__( 'You can choose from existing image sizes above or create a custom size. If you have chosen Custom size above, then enter the width, height and crop settings below. For best results, use a cropped image. If you change the width and/or height below, existing images will not be automatically resized.' ) . '<br />' . sprintf(
					/* translators: 1: Force regenerate plugin link. */
					esc_html__( 'I recommend using %1$s to regenerate all image sizes.', 'top-10' ),
					'<a href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=600&amp;height=550' ) ) . '" class="thickbox">Regenerate Thumbnails</a>'
				),
				'type'    => 'thumbsizes',
				'default' => 'tptn_thumbnail',
				'options' => \WebberZone\Top_Ten\Frontend\Media_Handler::get_all_image_sizes(),
			),
			'thumb_width'        => array(
				'id'      => 'thumb_width',
				'name'    => esc_html__( 'Thumbnail width', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '250',
				'size'    => 'small',
			),
			'thumb_height'       => array(
				'id'      => 'thumb_height',
				'name'    => esc_html__( 'Thumbnail height', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '250',
				'size'    => 'small',
			),
			'thumb_crop'         => array(
				'id'      => 'thumb_crop',
				'name'    => esc_html__( 'Hard crop thumbnails', 'top-10' ),
				'desc'    => esc_html__( 'Check this box to hard crop the thumbnails. i.e. force the width and height above vs. maintaining proportions.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'thumb_create_sizes' => array(
				'id'      => 'thumb_create_sizes',
				'name'    => esc_html__( 'Generate thumbnail sizes', 'top-10' ),
				'desc'    => esc_html__( 'If you select this option and Custom size is selected above, the plugin will register the image size with WordPress to create new thumbnails. Does not update old images as explained above.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
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
				'options' => 'post-image',
			),
			'scan_images'        => array(
				'id'      => 'scan_images',
				'name'    => esc_html__( 'Get first image', 'top-10' ),
				'desc'    => esc_html__( 'The plugin will fetch the first image in the post content if this is enabled. This can slow down the loading of your page if the first image in the followed posts is large in file-size.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'thumb_default_show' => array(
				'id'      => 'thumb_default_show',
				'name'    => esc_html__( 'Use default thumbnail?', 'top-10' ),
				'desc'    => esc_html__( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'top-10' ),
				'type'    => 'checkbox',
				'options' => true,
			),
			'thumb_default'      => array(
				'id'      => 'thumb_default',
				'name'    => esc_html__( 'Default thumbnail', 'top-10' ),
				'desc'    => esc_html__( 'Enter the full URL of the image that you wish to display if no thumbnail is found. This image will be displayed below.', 'top-10' ),
				'type'    => 'file',
				'options' => TOP_TEN_PLUGIN_URL . 'default.png',
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
				/* translators: 1: Opening a tag, 2: Closing a tag, 3: Opening code tage, 4. Closing code tag. */
				'desc'        => sprintf( esc_html__( 'Do not include %3$sstyle%4$s tags. Check out the %1$sFAQ%2$s for available CSS classes to style.', 'top-10' ), '<a href="' . esc_url( 'https://wordpress.org/plugins/top-10/faq/' ) . '" target="_blank">', '</a>', '<code>', '</code>' ),
				'type'        => 'css',
				'options'     => '',
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
			'cron_on'         => array(
				'id'      => 'cron_on',
				'name'    => esc_html__( 'Enable scheduled maintenance', 'top-10' ),
				/* translators: 1: Constant holding number of days data is stored. */
				'desc'    => sprintf( esc_html__( 'Cleaning the database at regular intervals could improve performance, especially on high traffic blogs. Enabling maintenance will automatically delete entries older than %d days in the daily tables.', 'top-10' ), TOP_TEN_STORE_DATA ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'cron_range_desc' => array(
				'id'   => 'cron_range_desc',
				'name' => '<strong>' . esc_html__( 'Time to run maintenance', 'top-10' ) . '</strong>',
				'desc' => esc_html__( 'The next two options allow you to set the time to run the cron.', 'top-10' ),
				'type' => 'descriptive_text',
			),
			'cron_hour'       => array(
				'id'      => 'cron_hour',
				'name'    => esc_html__( 'Hour', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '0',
				'min'     => '0',
				'max'     => '23',
				'size'    => 'small',
			),
			'cron_min'        => array(
				'id'      => 'cron_min',
				'name'    => esc_html__( 'Minute', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '0',
				'min'     => '0',
				'max'     => '59',
				'size'    => 'small',
			),
			'cron_recurrence' => array(
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
				'options' => 'popular-posts',
				'size'    => 'large',
			),
			'feed_permalink_daily'   => array(
				'id'      => 'feed_permalink_daily',
				'name'    => esc_html__( 'Permalink - Daily', 'top-10' ),
				/* translators: 1: Opening link tag, 2: Closing link tag. */
				'desc'    => sprintf( esc_html__( 'This will set the path of the custom feed generated by the plugin for daily/custom period popular posts. You might need to %1$srefresh your permalinks%2$s when changing this option.', 'top-10' ), '<a href="' . admin_url( 'options-permalink.php' ) . '" target="_blank">', '</a>' ),
				'type'    => 'text',
				'options' => 'popular-posts-daily',
				'size'    => 'large',
			),
			'feed_limit'             => array(
				'id'      => 'feed_limit',
				'name'    => esc_html__( 'Number of posts to display', 'top-10' ),
				'desc'    => esc_html__( 'Maximum number of posts that will be displayed in the custom feed.', 'top-10' ),
				'type'    => 'number',
				'options' => '10',
				'size'    => 'small',
			),
			'feed_daily_range'       => array(
				'id'      => 'feed_daily_range',
				'name'    => esc_html__( 'Custom period in day(s)', 'top-10' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '1',
				'min'     => '0',
				'size'    => 'small',
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
				'settings' => '<a href="' . admin_url( 'admin.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'add-to-all' ) . '</a>',
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

		if ( false !== strpos( $file, 'add-to-all.php' ) ) {
			$new_links = array(
				'support'    => '<a href = "https://wordpress.org/support/plugin/add-to-all">' . esc_html__( 'Support', 'add-to-all' ) . '</a>',
				'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'add-to-all' ) . '</a>',
				'contribute' => '<a href = "https://github.com/WebberZone/add-to-all">' . esc_html__( 'Contribute', 'add-to-all' ) . '</a>',
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
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'add-to-all' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: WordPress.org support forums link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'add-to-all' ), esc_url( 'https://wordpress.org/support/plugin/add-to-all' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github issues link, 2: Github plugin page link. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'add-to-all' ),
				esc_url( 'https://github.com/ajaydsouza/add-to-all/issues' ),
				esc_url( 'https://github.com/ajaydsouza/add-to-all' )
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
				'title'   => esc_html__( 'General', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen provides general settings. Enable/disable the Snippets Manager and set the global priority of snippets.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-third-party-help',
				'title'   => esc_html__( 'Third Party', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen provides the settings for configuring the integration with third party scripts.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . sprintf(
						/* translators: 1: Google Analystics help article. */
						esc_html__( 'Google Analytics tracking can be found by visiting this %s', 'add-to-all' ),
						'<a href="https://support.google.com/analytics/topic/9303319" target="_blank">' . esc_html__( 'article', 'add-to-all' ) . '</a>.'
					) .
					'</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-header-help',
				'title'   => esc_html__( 'Header', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the header of your site.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add custom CSS or HTML code. Useful for adding meta tags for site verification, etc.', 'add-to-all' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-body-help',
				'title'   => esc_html__( 'Body', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the content of posts, pages and custom post types.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can set the priority of the filter and choose if you want this to be displayed on either all content (including archives) or just single posts/pages.', 'add-to-all' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-footer-help',
				'title'   => esc_html__( 'Footer', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the footer of your site.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add custom HTML code. Useful for adding tracking code for analytics, etc.', 'add-to-all' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
			),
			array(
				'id'      => 'tptn-settings-feed-help',
				'title'   => esc_html__( 'Feed', 'add-to-all' ),
				'content' =>
				'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the feed of your site.', 'add-to-all' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add copyright text, a link to the title and date of the post, and HTML before and after the content', 'add-to-all' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'add-to-all' ) . '</p>',
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
			__( 'Thank you for using %1$sWebberZone Top_Ten%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'knowledgebase' ),
			'<a href="https://webberzone.com/plugins/add-to-all/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/plugin/add-to-all/reviews/#new-post" target="_blank">'
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 3.3.0
	 */
	public function admin_enqueue_scripts() {

		wp_localize_script(
			'wz-admin-js',
			'tptn_admin',
			array(
				'thumb_default' => TOP_TEN_PLUGIN_URL . 'default.png',
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

		// Sanitize exclude_cat_slugs to save a new entry of exclude_categories.
		if ( isset( $settings['exclude_cat_slugs'] ) ) {

			$exclude_cat_slugs = array_unique( str_getcsv( $settings['exclude_cat_slugs'] ) );

			foreach ( $exclude_cat_slugs as $cat_name ) {
				$cat = get_term_by( 'name', $cat_name, 'category' );

				// Fall back to slugs since that was the default format before v2.4.0.
				if ( false === $cat ) {
					$cat = get_term_by( 'slug', $cat_name, 'category' );
				}
				if ( isset( $cat->term_taxonomy_id ) ) {
					$exclude_categories[]       = $cat->term_taxonomy_id;
					$exclude_categories_slugs[] = $cat->name;
				}
			}
			$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
			$settings['exclude_cat_slugs']  = isset( $exclude_categories_slugs ) ? \WebberZone\Top_Ten\Util\Helpers::str_putcsv( $exclude_categories_slugs ) : '';

		}

		// Sanitize exclude_on_cat_slugs to save a new entry of exclude_on_categories.
		if ( isset( $settings['exclude_on_cat_slugs'] ) ) {

			$exclude_on_cat_slugs = array_unique( str_getcsv( $settings['exclude_on_cat_slugs'] ) );

			foreach ( $exclude_on_cat_slugs as $cat_name ) {
				$cat = get_term_by( 'name', $cat_name, 'category' );

				if ( isset( $cat->term_taxonomy_id ) ) {
					$exclude_on_categories[]       = $cat->term_taxonomy_id;
					$exclude_on_categories_slugs[] = $cat->name;
				}
			}
			$settings['exclude_on_categories'] = isset( $exclude_on_categories ) ? join( ',', $exclude_on_categories ) : '';
			$settings['exclude_on_cat_slugs']  = isset( $exclude_on_categories_slugs ) ? \WebberZone\Top_Ten\Util\Helpers::str_putcsv( $exclude_on_categories_slugs ) : '';

		}

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
			list( $settings['thumb_width'], $settings['thumb_height'] ) = \WebberZone\Top_Ten\Frontend\Media_Handler::get_thumb_size( $settings['thumb_size'] );
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

		if ( 'thumb_default' === $args['id'] && TOP_TEN_PLUGIN_URL . 'default.png' !== $thumb_default ) {
			$html = '<span class="dashicons dashicons-undo reset-default-thumb" style="cursor: pointer;" title="' . __( 'Reset' ) . '"></span> <br />' . $html;
		}

		return $html;
	}
}
