<?php
/**
 * Top 10 Admin interface.
 *
 * This page is accessible via Top 10 Settings menu item
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Function generates the plugin settings page.
 *
 * @since	1.0
 *
 */
function tptn_options() {

	global $wpdb, $network_wide, $tptn_url;

	$tptn_settings = tptn_read_options();

	/* Temporary check to remove the deprecated hook */
	if ( wp_next_scheduled( 'ald_tptn_hook' ) ) {
		wp_clear_scheduled_hook( 'ald_tptn_hook' );

		tptn_enable_run( $tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence'] );
	}

	/* Parse post types */
	parse_str( $tptn_settings['post_types'], $post_types );
	$wp_post_types	= get_post_types( array(
		'public'	=> true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

	/* Save options has been triggered */
	if ( ( isset( $_POST['tptn_save'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {

		/* General options */
		$tptn_settings['activate_overall'] = isset( $_POST['activate_overall']) ? true : false;
		$tptn_settings['activate_daily'] = isset( $_POST['activate_daily']) ? true : false;
		$tptn_settings['cache_fix'] = isset( $_POST['cache_fix'] ) ? true : false;
		$tptn_settings['daily_midnight'] = isset( $_POST['daily_midnight'] ) ? true : false;
		$tptn_settings['daily_range'] = intval( $_POST['daily_range'] );
		$tptn_settings['hour_range'] = intval( $_POST['hour_range'] );
		$tptn_settings['uninstall_clean_options'] = isset( $_POST['uninstall_clean_options'] ) ? true : false;
		$tptn_settings['uninstall_clean_tables'] = isset( $_POST['uninstall_clean_tables'] ) ? true : false;
		$tptn_settings['show_metabox'] = ( isset( $_POST['show_metabox'] ) ? true : false );
		$tptn_settings['show_metabox_admins'] = ( isset( $_POST['show_metabox_admins'] ) ? true : false );
		$tptn_settings['show_credit'] = isset( $_POST['show_credit'] ) ? true : false;

		/* Counter and tracker options */
		$tptn_settings['add_to_content'] = isset( $_POST['add_to_content'] ) ? true : false;
		$tptn_settings['count_on_pages'] = isset( $_POST['count_on_pages'] ) ? true : false;
		$tptn_settings['add_to_feed'] = isset( $_POST['add_to_feed'] ) ? true : false;
		$tptn_settings['add_to_home'] = isset( $_POST['add_to_home'] ) ? true : false;
		$tptn_settings['add_to_category_archives'] = isset( $_POST['add_to_category_archives'] ) ? true : false;
		$tptn_settings['add_to_tag_archives'] = isset( $_POST['add_to_tag_archives'] ) ? true : false;
		$tptn_settings['add_to_archives'] = isset( $_POST['add_to_archives'] ) ? true : false;

		$tptn_settings['count_disp_form'] = $_POST['count_disp_form'];
		$tptn_settings['count_disp_form_zero'] = $_POST['count_disp_form_zero'];
		$tptn_settings['dynamic_post_count'] = isset( $_POST['dynamic_post_count'] ) ? true : false;

		$tptn_settings['track_authors'] = isset( $_POST['track_authors']) ? true : false;
		$tptn_settings['track_admins'] = isset( $_POST['track_admins']) ? true : false;
		$tptn_settings['track_editors'] = isset( $_POST['track_editors']) ? true : false;

		$tptn_settings['pv_in_admin'] = isset( $_POST['pv_in_admin']) ? true : false;
		$tptn_settings['show_count_non_admins'] = isset( $_POST['show_count_non_admins'] ) ? true : false;

		/* Popular post list options */
		$tptn_settings['limit'] = intval( $_POST['limit'] );
		$tptn_settings['how_old'] = intval( $_POST['how_old'] );

		// Process post types to be selected
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$post_types_arr = ( isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) ? $_POST['post_types'] : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$tptn_settings['post_types'] = http_build_query( $post_types, '', '&' );

		$tptn_settings['exclude_post_ids'] = $_POST['exclude_post_ids'] == '' ? '' : implode( ',', array_map( 'intval', explode( ",", $_POST['exclude_post_ids'] ) ) );

		// Exclude categories
		$tptn_settings['exclude_cat_slugs'] = $_POST['exclude_cat_slugs'];

		$exclude_categories_slugs = explode( ", ", $tptn_settings['exclude_cat_slugs'] );

		foreach ( $exclude_categories_slugs as $exclude_categories_slug ) {
			$catObj = get_category_by_slug( $exclude_categories_slug );
			if ( isset( $catObj->term_id ) ) {
				$exclude_categories[] = $catObj->term_id;
			}
		}
		$tptn_settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';

		$tptn_settings['title'] = wp_kses_post( $_POST['title'] );
		$tptn_settings['title_daily'] = wp_kses_post( $_POST['title_daily'] );

		$tptn_settings['blank_output'] = ( $_POST['blank_output'] == 'blank' ) ? true : false;
		$tptn_settings['blank_output_text'] = wp_kses_post( $_POST['blank_output_text'] );

		$tptn_settings['show_excerpt'] = isset( $_POST['show_excerpt'] ) ? true : false;
		$tptn_settings['excerpt_length'] = intval( $_POST['excerpt_length'] );
		$tptn_settings['show_date'] = isset( $_POST['show_date'] ) ? true : false;
		$tptn_settings['show_author'] = isset( $_POST['show_author'] ) ? true : false;
		$tptn_settings['title_length'] = intval( $_POST['title_length'] );
		$tptn_settings['disp_list_count'] = isset( $_POST['disp_list_count'] ) ? true : false;

		$tptn_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$tptn_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;
		$tptn_settings['exclude_on_post_ids'] = $_POST['exclude_on_post_ids'] == '' ? '' : implode( ',', array_map( 'intval', explode( ",", $_POST['exclude_on_post_ids'] ) ) );

		// List HTML options
		$tptn_settings['before_list'] = $_POST['before_list'];
		$tptn_settings['after_list'] = $_POST['after_list'];
		$tptn_settings['before_list_item'] = $_POST['before_list_item'];
		$tptn_settings['after_list_item'] = $_POST['after_list_item'];

		/* Thumbnail options */
		$tptn_settings['post_thumb_op'] = $_POST['post_thumb_op'];
		$tptn_settings['thumb_size'] = $_POST['thumb_size'];
		$tptn_settings['thumb_width'] = intval( $_POST['thumb_width'] );
		$tptn_settings['thumb_height'] = intval( $_POST['thumb_height'] );
		$tptn_settings['thumb_crop'] = ( isset( $_POST['thumb_crop'] ) ? true : false );
		$tptn_settings['thumb_html'] = $_POST['thumb_html'];

		$tptn_settings['thumb_meta'] = '' == $_POST['thumb_meta'] ? 'post-image' : $_POST['thumb_meta'];
		$tptn_settings['scan_images'] = isset( $_POST['scan_images'] ) ? true : false;
		$tptn_settings['thumb_default_show'] = isset( $_POST['thumb_default_show'] ) ? true : false;
		$tptn_settings['thumb_default'] = ( ( '' == $_POST['thumb_default'] ) || ( "/default.png" == $_POST['thumb_default'] ) ) ? $tptn_url . '/default.png' : $_POST['thumb_default'];

		/* Custom styles */
		$tptn_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

		/* If default style is selected, enforce fixed width, height of thumbnail. Disable author, excerpt and date display */
		if ( isset( $_POST['include_default_style'] ) ) {
			$tptn_settings['include_default_style'] = true;
			$tptn_settings['post_thumb_op'] = 'inline';
			$tptn_settings['thumb_height'] = 65;
			$tptn_settings['thumb_width'] = 65;
			$tptn_settings['thumb_crop'] = true;
			$tptn_settings['show_excerpt'] = false;
			$tptn_settings['show_author'] = false;
			$tptn_settings['show_date'] = false;
		} else {
			$tptn_settings['include_default_style'] = false;
		}

		/**
		 * Filter the settings array just before saving them to the database
		 *
		 * @since	2.0.4
		 *
		 * @param	array	$tptn_settings	Settings array
		 */
		$tptn_settings = apply_filters( 'tptn_save_options', $tptn_settings );


		/* Update the options */
		update_option( 'ald_tptn_settings', $tptn_settings );

		/* Let's get the options again after we update them */
		$tptn_settings = tptn_read_options();
		parse_str( $tptn_settings['post_types'], $post_types );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		/* Echo a success message */
		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully.', 'tptn' ) . '</p>';

		if ( isset( $_POST['include_default_style'] ) ) {
			$str .= '<p>'. __( 'Default styles selected. Thumbnail width, height and crop settings have been fixed. Author, Excerpt and Date will not be displayed.', 'tptn' ) . '</p>';

		}

		$str .= '</div>';

		echo $str;
	}

	/* Default options has been triggered */
	if ( ( isset( $_POST['tptn_default'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		delete_option( 'ald_tptn_settings' );
		$tptn_settings = tptn_default_options();
		update_option( 'ald_tptn_settings', $tptn_settings );
		tptn_disable_run();

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options set to Default.', 'tptn' ) .'</p></div>';
		echo $str;
	}

	/* Truncate overall posts table */
	if ( ( isset( $_POST['tptn_trunc_all'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Top 10 popular posts reset', 'tptn' ) .'</p></div>';
		echo $str;
	}

	/* Truncate daily posts table */
	if ( ( isset( $_POST['tptn_trunc_daily'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Top 10 daily popular posts reset', 'tptn' ) .'</p></div>';
		echo $str;
	}

	/* Clean duplicates */
	if ( ( isset( $_POST['tptn_clean_duplicates'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_clean_duplicates( true );
		tptn_clean_duplicates( false );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Duplicate rows cleaned from tables', 'tptn' ) .'</p></div>';
		echo $str;
	}

	/* Merge blog IDs */
	if ( ( isset( $_POST['tptn_merge_blogids'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_merge_blogids( true );
		tptn_merge_blogids( false );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Post counts across blog IDs 0 and 1 have been merged', 'tptn' ) .'</p></div>';
		echo $str;
	}

	/* Save maintenance options */
	if ( ( isset( $_POST['tptn_mnts_save'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		$tptn_settings['cron_hour'] = min( 23, intval( $_POST['cron_hour'] ) );
		$tptn_settings['cron_min'] = min( 59, intval( $_POST['cron_min'] ) );
		$tptn_settings['cron_recurrence'] = $_POST['cron_recurrence'];

		if ( isset( $_POST['cron_on'] ) ) {
			$tptn_settings['cron_on'] = true;
			tptn_enable_run( $tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence'] );
			$str = '<div id="message" class="updated fade"><p>' . __( 'Scheduled maintenance enabled / modified', 'tptn' ) .'</p></div>';
		} else {
			$tptn_settings['cron_on'] = false;
			tptn_disable_run();
			$str = '<div id="message" class="updated fade"><p>'. __( 'Scheduled maintenance disabled', 'tptn' ) .'</p></div>';
		}
		update_option( 'ald_tptn_settings', $tptn_settings );
		$tptn_settings = tptn_read_options();

		echo $str;
	}

	if ( ( isset( $_POST['tptn_import'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {

		$top_ten_all_mu_tables = isset( $_POST['top_ten_all_mu_tables'] ) ? $_POST['top_ten_all_mu_tables'] : array();
		$top_ten_mu_tables_blog_ids = explode( ",", $_POST['top_ten_mu_tables_blog_ids'] );
		$top_ten_mu_tables_sel_blog_ids = array_values( $top_ten_all_mu_tables );

		foreach ( $top_ten_mu_tables_sel_blog_ids as $top_ten_mu_tables_sel_blog_id ) {
			$sql = "
					INSERT INTO " . $wpdb->base_prefix . "top_ten (postnumber, cntaccess, blog_id)
					  SELECT postnumber, cntaccess, '%d' FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten
					  ON DUPLICATE KEY UPDATE " . $wpdb->base_prefix . "top_ten.cntaccess = " . $wpdb->base_prefix . "top_ten.cntaccess + (
					    SELECT " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten.cntaccess FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten WHERE " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten.postnumber = " . $wpdb->base_prefix . "top_ten.postnumber
					  )
				";

			$wpdb->query( $wpdb->prepare( $sql, $top_ten_mu_tables_sel_blog_id ) );

			$sql = "
					INSERT INTO " . $wpdb->base_prefix . "top_ten_daily (postnumber, cntaccess, dp_date, blog_id)
					  SELECT postnumber, cntaccess, dp_date, '%d' FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten_daily
					  ON DUPLICATE KEY UPDATE " . $wpdb->base_prefix . "top_ten_daily.cntaccess = " . $wpdb->base_prefix . "top_ten_daily.cntaccess + (
					    SELECT " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten_daily.cntaccess FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten_daily WHERE " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten_daily.postnumber = " . $wpdb->base_prefix . "top_ten_daily.postnumber
					  )
				";

			$wpdb->query( $wpdb->prepare( $sql, $top_ten_mu_tables_sel_blog_id ) );
		}

		update_site_option( 'top_ten_mu_tables_sel_blog_ids', array_unique( array_merge( $top_ten_mu_tables_sel_blog_ids, get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() ) ) ) );


		$str = '<div id="message" class="updated fade"><p>'. __( 'Counts from selected sites have been imported.', 'tptn' ) .'</p></div>';
		echo $str;
	}

	if ( ( ( isset( $_POST['tptn_delete_selected_tables'] ) ) || ( isset( $_POST['tptn_delete_imported_tables'] ) ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		$top_ten_all_mu_tables = isset( $_POST['top_ten_all_mu_tables'] ) ? $_POST['top_ten_all_mu_tables'] : array();
		$top_ten_mu_tables_blog_ids = explode( ",", $_POST['top_ten_mu_tables_blog_ids'] );
		$top_ten_mu_tables_sel_blog_ids = array_values( $top_ten_all_mu_tables );

		if ( isset( $_POST['tptn_delete_selected_tables'] ) ) {
			$top_ten_mu_tables_sel_blog_ids = array_intersect( $top_ten_mu_tables_sel_blog_ids, get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() ) );
		} else {
			$top_ten_mu_tables_sel_blog_ids = get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() );
		}

		if ( ! empty( $top_ten_mu_tables_sel_blog_ids ) ) {

			$sql = "DROP TABLE ";
			foreach( $top_ten_mu_tables_sel_blog_ids as $top_ten_mu_tables_sel_blog_id ) {
				$sql .= $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten, ";
				$sql .= $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . "_top_ten_daily, ";
			}
			$sql = substr( $sql, 0, -2 );

			$wpdb->query( $sql );
			$str = '<div id="message" class="updated fade"><p>'. __( 'Selected tables have been deleted. Note that only imported tables have been deleted.', 'tptn' ) .'</p></div>';
			echo $str;
		}
	}

	/**** Include the views page ****/
	include_once( 'main-view.php' );
}


/**
 * Function to generate the right sidebar of the Settings and Admin popular posts pages.
 *
 * @since	1.8.1
 */
function tptn_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'tptn' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', 'tptn' ); ?></span></h3>
      <div class="inside">
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="donate@ajaydsouza.com">
			<input type="hidden" name="lc" value="IN">
			<input type="hidden" name="item_name" value="<?php _e( 'Donation for Top 10', 'tptn' ); ?>">
			<input type="hidden" name="item_number" value="tptn_admin">
			<strong><?php _e( 'Enter amount in USD: ', 'tptn' ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Top 10', 'tptn' ); ?>">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'tptn' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', 'tptn' ); ?></span></h3>
      <div class="inside">
		<div id="follow-us">
			<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
			<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'tptn' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', 'tptn' ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
			<ul>
				<li><a href="https://webberzone.com/plugins/top-10/" target="_blank"><?php _e( 'Top 10 plugin page', 'tptn' ); ?></a></li>
				<li><a href="https://github.com/ajaydsouza/top-10" target="_blank"><?php _e( 'Top 10 Github page', 'tptn' ); ?></a></li>
				<li><a href="https://webberzone.com/plugins/" target="_blank"><?php _e( 'Other plugins', 'tptn' ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/top-10/faq/" target="_blank"><?php _e( 'FAQ', 'tptn' ); ?></a></li>
				<li><a href="https://wordpress.org/support/plugin/top-10" target="_blank"><?php _e( 'Support', 'tptn' ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/top-10" target="_blank"><?php _e( 'Reviews', 'tptn' ); ?></a></li>
				<li><a href="https://ajaydsouza.com/" target="_blank"><?php _e( "Ajay's blog", 'tptn' ); ?></a></li>
			</ul>
        </div>
      </div>
    </div>

<?php
}


/**
 * Add Top 10 menu in WP-Admin.
 *
 * @since	1.0
 */
function tptn_adminmenu() {

	$plugin_page = add_menu_page( __( "Top 10 Settings", 'tptn' ), __( "Top 10", 'tptn' ), 'manage_options', 'tptn_options', 'tptn_options', 'dashicons-editor-ol' );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( "Top 10 Settings", 'tptn' ), __( "Top 10 Settings", 'tptn' ), 'manage_options', 'tptn_options', 'tptn_options' );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	$tptn_stats_screen = new Top_Ten_Statistics;

	$plugin_page = add_submenu_page( 'tptn_options', __( "View Popular Posts", 'tptn' ), __( "View Popular Posts", 'tptn' ), 'manage_options', 'tptn_popular_posts', [ $tptn_stats_screen, 'plugin_settings_page' ] );
	add_action( "load-$plugin_page", [ $tptn_stats_screen, 'screen_option' ] );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

}
add_action( 'admin_menu', 'tptn_adminmenu' );


/**
 * Add JS and CSS to admin header.
 *
 * @since	1.6
 */
function tptn_adminhead() {
	global $tptn_url;

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'plugin-install' );

	add_thickbox();

?>
	<style type="text/css">
		.postbox .handlediv:before {
			right:12px;
			font:400 20px/1 dashicons;
			speak:none;
			display:inline-block;
			top:0;
			position:relative;
			-webkit-font-smoothing:antialiased;
			-moz-osx-font-smoothing:grayscale;
			text-decoration:none!important;
			content:'\f142';
			padding:8px 10px;
		}
		.postbox.closed .handlediv:before {
			content: '\f140';
		}
		.wrap h2:before {
		    content: "\f204";
		    display: inline-block;
		    -webkit-font-smoothing: antialiased;
		    font: normal 29px/1 'dashicons';
		    vertical-align: middle;
		    margin-right: 0.3em;
		}
	</style>

	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('tptn_options');
		});
		//]]>
	</script>

	<script type="text/javascript" language="JavaScript">
		//<![CDATA[
		function checkForm() {
			answer = true;
			if (siw && siw.selectingSomething)
				answer = false;
			return answer;
		}//
		//]]>
	</script>

	<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/admin/wick/wick.css" />
	<script type="text/javascript" language="JavaScript">
		//<![CDATA[
		<?php
		function wick_data() {
			global $wpdb;

			$categories = get_categories( 'hide_empty=0' );
			$str = 'collection = [';
			foreach ( $categories as $cat ) {
				$str .= "'" . $cat->slug . "',";
			}
			$str = substr( $str, 0, -1 );	// Remove trailing comma
			$str .= '];';

			echo $str;
		}
		wick_data();
		?>
		//]]>
	</script>

	<script type="text/javascript" src="<?php echo $tptn_url ?>/admin/wick/wick.js"></script>

<?php
}


/**
 * Adding WordPress plugin action links.
 *
 * @version	1.9.2
 *
 * @param	array	$links
 * @return	array	Links array with our settings link added
 */
function tptn_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=tptn_options' ) . '">' . __( 'Settings', 'tptn' ) . '</a>'
		),
		$links
	);

}
add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( __DIR__ ) . 'top-10.php' ), 'tptn_plugin_actions_links' );


/**
 * Add links to the plugin action row.
 *
 * @since	1.5
 *
 * @param	array	$links
 * @param	array	$file
 * @return	array	Links array with our links added
 */
function tptn_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( __FILE__ );

	// create link
	if ( $file == $plugin ) {
		$links[] = '<a href="https://webberzone.com/support/">' . __( 'Support', 'tptn' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'tptn' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'tptn_plugin_actions', 10, 2 );


/**
 * Function to delete all duplicate rows in the posts table.
 *
 * @since	1.6.2
 *
 * @param	bool 	$daily	Daily flag
 */
function tptn_clean_duplicates( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . "top_ten";
	if ( $daily ) {
		$table_name .= "_daily";
	}
	$count = 0;

	$wpdb->query( "CREATE TEMPORARY TABLE " . $table_name . "_temp AS SELECT * FROM " . $table_name . " GROUP BY postnumber" );
	$wpdb->query( "TRUNCATE TABLE $table_name" );
	$wpdb->query( "INSERT INTO " . $table_name . " SELECT * FROM " . $table_name . "_temp" );
}


/**
 * Function to merge counts with post numbers of blog ID 0 and 1 respectively.
 *
 * @since	2.0.4
 *
 * @param	bool 	$daily	Daily flag
 */
function tptn_merge_blogids( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . "top_ten";
	if ( $daily ) {
		$table_name .= "_daily";
	}

	if ( $daily ) {
		$wpdb->query( "
			INSERT INTO `$table_name` (postnumber, cntaccess, dp_date, blog_id) (
				SELECT
					postnumber,
					SUM(cntaccess) as sumCount,
					dp_date,
					1
				FROM `$table_name`
				WHERE blog_ID IN (0,1)
				GROUP BY postnumber, dp_date
			) ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess);
		" );
	} else {
		$wpdb->query( "
			INSERT INTO `$table_name` (postnumber, cntaccess, blog_id) (
				SELECT
					postnumber,
					SUM(cntaccess) as sumCount,
					1
				FROM `$table_name`
				WHERE blog_ID IN (0,1)
				GROUP BY postnumber
			) ON DUPLICATE KEY UPDATE cntaccess = VALUES(cntaccess);
		" );
	}

	$wpdb->query( "DELETE FROM $table_name WHERE blog_id = 0" );
}

