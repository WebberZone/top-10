<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package    Top 10
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Creates the admin submenu pages under the Downloads menu and assigns their
 * links to global variables
 *
 * @since 2.5.0
 *
 * @global string $tptn_settings_page Settings page hook.
 * @global string $tptn_settings_popular_posts Popular posts page hook.
 * @global string $tptn_settings_popular_posts_daily Daily popular page hook.
 * @global string $tptn_settings_tools_help Tools page hook.
 * @global string $tptn_settings_exim_help Export/Import page hook.
 * @return void
 */
function tptn_add_admin_pages_links() {
	global $tptn_settings_page, $tptn_settings_tools_help, $tptn_settings_popular_posts, $tptn_settings_popular_posts_daily, $tptn_settings_exim_help;

	$tptn_settings_page = add_menu_page( esc_html__( 'Top 10 Settings', 'top-10' ), esc_html__( 'Top 10', 'top-10' ), 'manage_options', 'tptn_options_page', 'tptn_options_page', 'dashicons-editor-ol' );
	add_action( "load-$tptn_settings_page", 'tptn_settings_help' ); // Load the settings contextual help.

	$plugin_page = add_submenu_page( 'tptn_options_page', esc_html__( 'Top 10 Settings', 'top-10' ), esc_html__( 'Settings', 'top-10' ), 'manage_options', 'tptn_options_page', 'tptn_options_page' );

	// Initialise Top 10 Statistics pages.
	$tptn_stats_screen = new Top_Ten_Statistics();

	$tptn_settings_popular_posts = add_submenu_page( 'tptn_options_page', __( 'Top 10 Popular Posts', 'top-10' ), __( 'Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$tptn_settings_popular_posts", array( $tptn_stats_screen, 'screen_option' ) );

	$tptn_settings_popular_posts_daily = add_submenu_page( 'tptn_options_page', __( 'Top 10 Daily Popular Posts', 'top-10' ), __( 'Daily Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts&orderby=daily_count&order=desc', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$tptn_settings_popular_posts_daily", array( $tptn_stats_screen, 'screen_option' ) );

	// Add links to Tools pages.
	$tptn_settings_tools_help = add_submenu_page( 'tptn_options_page', esc_html__( 'Top 10 Tools', 'top-10' ), esc_html__( 'Tools', 'top-10' ), 'manage_options', 'tptn_tools_page', 'tptn_tools_page' );
	add_action( "load-$tptn_settings_tools_help", 'tptn_settings_tools_help' );

	$tptn_settings_exim_help = add_submenu_page( 'tptn_options_page', esc_html__( 'Top 10 Import Export Tables', 'top-10' ), esc_html__( 'Import/Export', 'top-10' ), 'manage_options', 'tptn_exim_page', 'tptn_exim_page' );
	add_action( "load-$tptn_settings_exim_help", 'tptn_settings_exim_help' );
}
add_action( 'admin_menu', 'tptn_add_admin_pages_links' );


/**
 * Customise the taxonomy columns.
 *
 * @since 2.5.0
 * @param  array $columns Columns in the admin view.
 * @return array Updated columns.
 */
function tptn_tax_columns( $columns ) {

	// Remove the description column.
	unset( $columns['description'] );

	$new_columns = array(
		'tax_id' => 'ID',
	);

	return array_merge( $columns, $new_columns );
}
add_filter( 'manage_edit-tptn_category_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_category_sortable_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_tag_columns', 'tptn_tax_columns' );
add_filter( 'manage_edit-tptn_tag_sortable_columns', 'tptn_tax_columns' );


/**
 * Add taxonomy ID to the admin column.
 *
 * @since 2.5.0
 *
 * @param  string     $value Deprecated.
 * @param  string     $name  Name of the column.
 * @param  int|string $id    Category ID.
 * @return int|string
 */
function tptn_tax_id( $value, $name, $id ) {
	return 'tax_id' === $name ? $id : $value;
}
add_filter( 'manage_tptn_category_custom_column', 'tptn_tax_id', 10, 3 );
add_filter( 'manage_tptn_tag_custom_column', 'tptn_tax_id', 10, 3 );


/**
 * Add rating links to the admin dashboard
 *
 * @since 2.5.0
 *
 * @param string $footer_text The existing footer text.
 * @return string Updated Footer text
 */
function tptn_admin_footer( $footer_text ) {

	if ( get_current_screen()->parent_base === 'tptn_options_page' ) {

		$text = sprintf(
			/* translators: 1: Top 10 website, 2: Plugin reviews link. */
			__( 'Thank you for using <a href="%1$s" target="_blank">Top 10</a>! Please <a href="%2$s" target="_blank">rate us</a> on <a href="%2$s" target="_blank">WordPress.org</a>', 'top-10' ),
			'https://webberzone.com/top-10',
			'https://wordpress.org/support/plugin/top-10/reviews/#new-post'
		);

		return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';

	} else {

		return $footer_text;

	}
}
add_filter( 'admin_footer_text', 'tptn_admin_footer' );


/**
 * Add CSS to Admin head
 *
 * @since 2.5.0
 *
 * return void
 */
function tptn_admin_head() {
	?>
	<style type="text/css" media="screen">
		#dashboard_right_now .tptn-article-count:before {
			content: "\f331";
		}
	</style>
	<?php
}
add_filter( 'admin_head', 'tptn_admin_head' );

/**
 * Adding WordPress plugin action links.
 *
 * @version 1.9.2
 *
 * @param   array $links Action links.
 * @return  array   Links array with our settings link added.
 */
function tptn_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=tptn_options_page' ) . '">' . __( 'Settings', 'top-10' ) . '</a>',
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( TOP_TEN_PLUGIN_FILE ), 'tptn_plugin_actions_links' );


/**
 * Add links to the plugin action row.
 *
 * @since   1.5
 *
 * @param   array $links Action links.
 * @param   array $file Plugin file name.
 * @return  array   Links array with our links added
 */
function tptn_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( TOP_TEN_PLUGIN_FILE );

	if ( $file === $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/top-10/">' . __( 'Support', 'top-10' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'top-10' ) . '</a>';
		$links[] = '<a href="https://github.com/WebberZone/top-10">' . __( 'Contribute', 'top-10' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'tptn_plugin_actions', 10, 2 );


/**
 * Add a menu entry to the Network Admin
 *
 * @since 2.8.0
 */
function tptn_network_admin_menu_links() {
	global $tptn_network_pop_posts_page;

	// Initialise Top 10 Statistics pages.
	$tptn_stats_screen = new Top_Ten_Network_Statistics();

	$tptn_network_pop_posts_page = add_menu_page( esc_html__( 'Top 10 - Network Popular Posts', 'top-10' ), esc_html__( 'Top 10', 'top-10' ), 'manage_network_options', 'tptn_network_pop_posts_page', array( $tptn_stats_screen, 'plugin_settings_page' ), 'dashicons-editor-ol' );
	add_action( "load-$tptn_network_pop_posts_page", array( $tptn_stats_screen, 'screen_option' ) );

}
add_action( 'network_admin_menu', 'tptn_network_admin_menu_links' );

/**
 * Enqueue Admin JS
 *
 * @since 2.9.0
 *
 * @param string $hook The current admin page.
 */
function tptn_load_admin_scripts( $hook ) {

	global $tptn_settings_page, $tptn_settings_tools_help, $tptn_settings_popular_posts, $tptn_settings_popular_posts_daily, $tptn_settings_exim_help, $tptn_network_pop_posts_page;

	wp_register_script( 'top-ten-admin-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/admin-scripts.min.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker' ), '1.0', true );
	wp_register_script( 'top-ten-suggest-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/top-10-suggest.min.js', array( 'jquery', 'jquery-ui-autocomplete' ), '1.0', true );

	wp_register_style(
		'tptn-admin-customizer-css',
		TOP_TEN_PLUGIN_URL . 'includes/admin/css/top-10-customizer.min.css',
		false,
		'1.0',
		false
	);

	if ( in_array( $hook, array( $tptn_settings_page, $tptn_settings_tools_help, $tptn_settings_popular_posts, $tptn_settings_popular_posts_daily, $tptn_settings_exim_help, $tptn_network_pop_posts_page . '-network' ), true ) ) {

		wp_enqueue_script( 'top-ten-admin-js' );
		wp_enqueue_script( 'top-ten-suggest-js' );
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();

		wp_enqueue_code_editor(
			array(
				'type'       => 'text/html',
				'codemirror' => array(
					'indentUnit' => 2,
					'tabSize'    => 2,
				),
			)
		);

	}

	// Only enqueue the styles if this is a popular posts page.
	if ( in_array( $hook, array( $tptn_settings_popular_posts, $tptn_settings_popular_posts_daily, $tptn_network_pop_posts_page . '-network' ), true ) ) {
		wp_enqueue_style(
			'tptn-admin-ui-css',
			TOP_TEN_PLUGIN_URL . 'includes/admin/css/top-10-admin.min.css',
			false,
			'1.0',
			false
		);
	}
}
add_action( 'admin_enqueue_scripts', 'tptn_load_admin_scripts' );


/**
 * This function enqueues scripts and styles in the Customizer.
 *
 * @since 2.9.0
 */
function tptn_customize_controls_enqueue_scripts() {
	wp_enqueue_script( 'customize-controls' );
	wp_enqueue_script( 'top-ten-suggest-js' );

	wp_enqueue_style( 'tptn-admin-customizer-css' );

}
add_action( 'customize_controls_enqueue_scripts', 'tptn_customize_controls_enqueue_scripts', 99 );


/**
 * This function enqueues scripts and styles on widgets.php.
 *
 * @since 2.9.0
 *
 * @param string $hook The current admin page.
 */
function tptn_enqueue_scripts_widgets( $hook ) {
	if ( 'widgets.php' !== $hook ) {
		return;
	}
	wp_enqueue_script( 'top-ten-suggest-js' );
	wp_enqueue_style( 'tptn-admin-customizer-css' );
}
add_action( 'admin_enqueue_scripts', 'tptn_enqueue_scripts_widgets', 99 );

