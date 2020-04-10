<?php
/**
 * Default settings.
 *
 * Functions to register the default settings of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.6.0
 *
 * @package Top 10
 * @subpackage Admin/Register_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Retrieve the array of plugin settings
 *
 * @since 2.5.0
 *
 * @return array Settings array
 */
function tptn_get_registered_settings() {

	$tptn_settings = array(
		'general'     => tptn_settings_general(),
		'counter'     => tptn_settings_counter(),
		'list'        => tptn_settings_list(),
		'thumbnail'   => tptn_settings_thumbnail(),
		'styles'      => tptn_settings_styles(),
		'maintenance' => tptn_settings_maintenance(),
		'feed'        => tptn_settings_feed(),
	);

	/**
	 * Filters the settings array
	 *
	 * @since 2.5.0
	 *
	 * @param array   $tptn_settings Settings array
	 */
	return apply_filters( 'tptn_registered_settings', $tptn_settings );

}


/**
 * Retrieve the array of General settings
 *
 * @since 2.6.0
 *
 * @return array General settings array
 */
function tptn_settings_general() {

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
	 * @since 2.6.0
	 *
	 * @param array $settings General settings array
	 */
	return apply_filters( 'tptn_settings_general', $settings );
}


/**
 * Retrieve the array of Counter settings
 *
 * @since 2.6.0
 *
 * @return array Counter settings array
 */
function tptn_settings_counter() {

	$settings = array(
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
			'desc'    => sprintf( esc_html__( 'Use %1$s to display the total count, %2$s for daily count and %3$s for overall counts across all posts. Default display is %4$s', 'top-10' ), '<code>%totalcount%</code>', '<code>%dailycount%</code>', '<code>%overallcount%</code>', '<code>(Visited %totalcount% times, %dailycount% visits today)</code>' ),
			'type'    => 'textarea',
			'options' => '(Visited %totalcount% times, %dailycount% visits today)',
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
		'tracker_type'          => array(
			'id'      => 'tracker_type',
			'name'    => esc_html__( 'Tracker type', 'top-10' ),
			'desc'    => '',
			'type'    => 'radiodesc',
			'default' => 'query_based',
			'options' => tptn_get_tracker_types(),
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
	 * @since 2.6.0
	 *
	 * @param array $settings Counter settings array
	 */
	return apply_filters( 'tptn_settings_counter', $settings );
}


/**
 * Retrieve the array of List settings
 *
 * @since 2.6.0
 *
 * @return array List settings array
 */
function tptn_settings_list() {

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
	 * @since 2.6.0
	 *
	 * @param array $settings List settings array
	 */
	return apply_filters( 'tptn_settings_list', $settings );
}


/**
 * Retrieve the array of Thumbnail settings
 *
 * @since 2.6.0
 *
 * @return array Thumbnail settings array
 */
function tptn_settings_thumbnail() {

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
			/* translators: 1: OTF Regenerate plugin link, 2: Force regenerate plugin link. */
			'desc'    => esc_html__( 'You can choose from existing image sizes above or create a custom size. If you have chosen Custom size above, then enter the width, height and crop settings below. For best results, use a cropped image. If you change the width and/or height below, existing images will not be automatically resized.' ) . '<br />' . sprintf( esc_html__( 'I recommend using %1$s or %2$s to regenerate all image sizes.', 'top-10' ), '<a href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=otf-regenerate-thumbnails&amp;TB_iframe=true&amp;width=600&amp;height=550' ) ) . '" class="thickbox">OTF Regenerate Thumbnails</a>', '<a href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=600&amp;height=550' ) ) . '" class="thickbox">Regenerate Thumbnails</a>' ),
			'type'    => 'thumbsizes',
			'default' => 'tptn_thumbnail',
			'options' => tptn_get_all_image_sizes(),
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
			'type'    => 'text',
			'options' => TOP_TEN_PLUGIN_URL . 'default.png',
			'size'    => 'large',
		),
	);

	/**
	 * Filters the Thumbnail settings array
	 *
	 * @since 2.6.0
	 *
	 * @param array $settings Thumbnail settings array
	 */
	return apply_filters( 'tptn_settings_thumbnail', $settings );
}


/**
 * Retrieve the array of Styles settings
 *
 * @since 2.6.0
 *
 * @return array Styles settings array
 */
function tptn_settings_styles() {

	$settings = array(
		'tptn_styles' => array(
			'id'      => 'tptn_styles',
			'name'    => esc_html__( 'Popular posts style', 'top-10' ),
			'desc'    => '',
			'type'    => 'radiodesc',
			'default' => 'no_style',
			'options' => tptn_get_styles(),
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
	 * @since 2.6.0
	 *
	 * @param array $settings Styles settings array
	 */
	return apply_filters( 'tptn_settings_styles', $settings );
}


/**
 * Retrieve the array of Maintenance settings
 *
 * @since 2.6.0
 *
 * @return array Maintenance settings array
 */
function tptn_settings_maintenance() {

	$settings = array(
		'cron_on'         => array(
			'id'      => 'cron_on',
			'name'    => esc_html__( 'Enable scheduled maintenance', 'top-10' ),
			'desc'    => esc_html__( 'Cleaning the database at regular intervals could improve performance, especially on high traffic blogs. Enabling maintenance will automatically delete entries older than 90 days in the daily tables.', 'top-10' ),
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
	 * @since 2.6.0
	 *
	 * @param array $settings Maintenance settings array
	 */
	return apply_filters( 'tptn_settings_maintenance', $settings );
}


/**
 * Retrieve the array of Feed settings
 *
 * @since 2.8.0
 *
 * @return array Feed settings array
 */
function tptn_settings_feed() {

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
	return apply_filters( 'tptn_settings_feed', $settings );
}


/**
 * Upgrade pre v2.5.0 settings.
 *
 * @since v2.5.0
 * @return array Settings array
 */
function tptn_upgrade_settings() {
	$old_settings = get_option( 'ald_tptn_settings' );

	if ( empty( $old_settings ) ) {
		return false;
	}

	// Start will assigning all the old settings to the new settings and we will unset later on.
	$settings = $old_settings;

	// Convert the add_to_{x} to the new settings format.
	$add_to = array(
		'single'            => 'add_to_content',
		'page'              => 'count_on_pages',
		'feed'              => 'add_to_feed',
		'home'              => 'add_to_home',
		'category_archives' => 'add_to_category_archives',
		'tag_archives'      => 'add_to_tag_archives',
		'other_archives'    => 'add_to_archives',
	);

	foreach ( $add_to as $newkey => $oldkey ) {
		if ( $old_settings[ $oldkey ] ) {
			$settings['add_to'][ $newkey ] = $newkey;
		}
		unset( $settings[ $oldkey ] );
	}

	// Convert the activate_overall and activate_daily to the new settings format.
	$trackers = array(
		'overall' => 'activate_overall',
		'daily'   => 'activate_daily',
	);

	foreach ( $trackers as $newkey => $oldkey ) {
		if ( $old_settings[ $oldkey ] ) {
			$settings['trackers'][ $newkey ] = $newkey;
		}
		unset( $settings[ $oldkey ] );
	}

	// Convert the track_{x} to the new settings format.
	$track_users = array(
		'authors' => 'track_authors',
		'editors' => 'track_editors',
		'admins'  => 'track_admins',
	);

	foreach ( $track_users as $newkey => $oldkey ) {
		if ( $old_settings[ $oldkey ] ) {
			$settings['track_users'][ $newkey ] = $newkey;
		}
		unset( $settings[ $oldkey ] );
	}

	// Convert 'blank_output' to the new format: true = 'blank' and false = 'custom_text'.
	$settings['blank_output'] = ! empty( $old_settings['blank_output'] ) ? 'blank' : 'custom_text';

	$settings['custom_css'] = $old_settings['custom_CSS'];

	return $settings;

}


/**
 * Get the various styles.
 *
 * @since 2.5.0
 * @return array Style options.
 */
function tptn_get_styles() {

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
			'description' => '<br /><img src="' . esc_url( plugins_url( 'includes/admin/images/tptn-left-thumbs.png', TOP_TEN_PLUGIN_FILE ) ) . '" width="350" /> <br />' . esc_html__( 'Enabling this option will set the post thumbnail to be before text. Disabling this option will not revert any settings.', 'top-10' ),
		),
	);

	/**
	 * Filter the array containing the types of trackers to add your own.
	 *
	 * @since 2.5.0
	 *
	 * @param string $trackers Different trackers.
	 */
	return apply_filters( 'tptn_get_styles', $styles );
}
