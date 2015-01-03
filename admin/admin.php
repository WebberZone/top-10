<?php
/**
 * Top 10 Admin interface.
 *
 * This page is accessible via Top 10 Settings menu item
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
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
    $poststable = $wpdb->posts;

	$tptn_settings = tptn_read_options();
	parse_str( $tptn_settings['post_types'],$post_types );
	$wp_post_types	= get_post_types( array(
		'public'	=> true,
	) );
	$posts_types_inc = array_intersect( $wp_post_types, $post_types );

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

		$tptn_settings['d_use_js'] = isset( $_POST['d_use_js'] ) ? true : false;	// This needs to be deprecated

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

		$tptn_settings['thumb_timthumb'] = isset( $_POST['thumb_timthumb'] ) ? true : false;	// To be deprecated
		$tptn_settings['thumb_timthumb_q'] = intval( $_POST['thumb_timthumb_q'] );				// To be deprecated

		$tptn_settings['thumb_meta'] = '' == $_POST['thumb_meta'] ? 'post-image' : $_POST['thumb_meta'];
		$tptn_settings['scan_images'] = isset( $_POST['scan_images'] ) ? true : false;
		$tptn_settings['thumb_default_show'] = isset( $_POST['thumb_default_show'] ) ? true : false;
		$tptn_settings['thumb_default'] = ( ( '' == $_POST['thumb_default'] ) || ( '\/default.png' == $_POST['thumb_default'] ) ) ? $_POST['thumb_default'] : $tptn_url . '/default.png';

		/* Custom styles */
		$tptn_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

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

		/* Update the options */
		update_option( 'ald_tptn_settings', $tptn_settings );

		/* Let's get the options again after we update them */
		$tptn_settings = tptn_read_options();
		parse_str( $tptn_settings['post_types'], $post_types );
		$posts_types_inc = array_intersect( $wp_post_types, $post_types );

		/* Echo a success message */
		$str = '<div id="message" class="updated fade"><p>'. __( 'Options saved successfully.', TPTN_LOCAL_NAME ) . '</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['tptn_default'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		delete_option( 'ald_tptn_settings' );
		$tptn_settings = tptn_default_options();
		update_option( 'ald_tptn_settings', $tptn_settings );
		tptn_disable_run();

		$str = '<div id="message" class="updated fade"><p>'. __( 'Options set to Default.', TPTN_LOCAL_NAME ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['tptn_trunc_all'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Top 10 popular posts reset', TPTN_LOCAL_NAME ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['tptn_trunc_daily'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Top 10 daily popular posts reset', TPTN_LOCAL_NAME ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['tptn_clean_duplicates'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_clean_duplicates( true );
		tptn_clean_duplicates( false );
		$str = '<div id="message" class="updated fade"><p>'. __( 'Duplicate rows cleaned from tables', TPTN_LOCAL_NAME ) .'</p></div>';
		echo $str;
	}

	if ( ( isset( $_POST['tptn_mnts_save'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		$tptn_settings['cron_hour'] = min( 23, intval( $_POST['cron_hour'] ) );
		$tptn_settings['cron_min'] = min( 59, intval( $_POST['cron_min'] ) );
		$tptn_settings['cron_recurrence'] = $_POST['cron_recurrence'];

		if ( isset( $_POST['cron_on'] ) ) {
			$tptn_settings['cron_on'] = true;
			tptn_enable_run( $tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence'] );
			$str = '<div id="message" class="updated fade"><p>' . __( 'Scheduled maintenance enabled / modified', TPTN_LOCAL_NAME ) .'</p></div>';
		} else {
			$tptn_settings['cron_on'] = false;
			tptn_disable_run();
			$str = '<div id="message" class="updated fade"><p>'. __( 'Scheduled maintenance disabled', TPTN_LOCAL_NAME ) .'</p></div>';
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


		$str = '<div id="message" class="updated fade"><p>'. __( 'Counts from selected sites have been imported.', TPTN_LOCAL_NAME ) .'</p></div>';
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
			$str = '<div id="message" class="updated fade"><p>'. __( 'Selected tables have been deleted. Note that only imported tables have been deleted.', TPTN_LOCAL_NAME ) .'</p></div>';
			echo $str;
		}
	}

?>

<div class="wrap">
	<h2><?php _e( 'Top 10 Settings', TPTN_LOCAL_NAME ); ?></h2>

	<ul class="subsubsub">
		<?php
			/**
			 * Fires before the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'tptn_admin_nav_bar_before' )
		?>

	  	<li><a href="#genopdiv"><?php _e( 'General options', TPTN_LOCAL_NAME ); ?></a> | </li>
	  	<li><a href="#counteropdiv"><?php _e( 'Counter and tracker options', TPTN_LOCAL_NAME ); ?></a> | </li>
	  	<li><a href="#pplopdiv"><?php _e( 'Popular post list options', TPTN_LOCAL_NAME ); ?></a> | </li>
	  	<li><a href="#thumbopdiv"><?php _e( 'Thumbnail options', TPTN_LOCAL_NAME ); ?></a> | </li>
	  	<li><a href="#customcssdiv"><?php _e( 'Custom styles', TPTN_LOCAL_NAME ); ?></a> | </li>
	  	<li><a href="#tptn_maintenance_op"><?php _e( 'Maintenance', TPTN_LOCAL_NAME ); ?></a></li>

		<?php
			/**
			 * Fires after the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'tptn_admin_nav_bar_after' )
		?>
	</ul>

	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="tptn_options" name="tptn_options" onsubmit="return checkForm()">
	    <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
			<h3 class='hndle'><span><?php _e( 'General options', TPTN_LOCAL_NAME ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before General options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_general_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><label for="activate_overall"><?php _e( 'Enable Overall stats', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="activate_overall" id="activate_overall" <?php if ( $tptn_settings['activate_overall'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="activate_daily"><?php _e( 'Enable Daily stats', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="activate_daily" id="activate_daily" <?php if ( $tptn_settings['activate_daily'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cache_fix"><?php _e( 'Cache fix:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="cache_fix" id="cache_fix" <?php if ( $tptn_settings['cache_fix'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'This will try to prevent W3 Total Cache and other caching plugins from caching the tracker script of the plugin. Try toggling this option in case you find that your posts are not tracked.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="daily_midnight"><?php _e( 'Start daily counts from midnight:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="daily_midnight" id="daily_midnight" <?php if ( $tptn_settings['daily_midnight'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'Daily counter will display number of visits from midnight. This option is checked by default and mimics the way most normal counters work. Turning this off will allow you to use the hourly setting in the next option.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="daily_range"><?php _e( 'Daily popular contains top posts over:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes( $tptn_settings['daily_range'] ); ?>"> <?php _e( 'day(s)', TPTN_LOCAL_NAME ); ?>
							<input type="textbox" name="hour_range" id="hour_range" size="3" value="<?php echo stripslashes( $tptn_settings['hour_range'] ); ?>"> <?php _e( 'hour(s)', TPTN_LOCAL_NAME ); ?>
							<p class="description"><?php _e( "Think of Daily Popular has a custom date range applied as a global setting. Instead of displaying popular posts from the past day, this setting lets you display posts for as many days or as few hours as you want. This can be overridden in the widget.", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uninstall_clean_options"><?php _e( 'Delete options on uninstall', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					    	<input type="checkbox" name="uninstall_clean_options" id="uninstall_clean_options" <?php if ( $tptn_settings['uninstall_clean_options'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'If this is checked, all settings related to Top 10 are removed from the database if you choose to uninstall/delete the plugin.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uninstall_clean_tables"><?php _e( 'Delete counter data on uninstall', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					    	<input type="checkbox" name="uninstall_clean_tables" id="uninstall_clean_tables" <?php if ( $tptn_settings['uninstall_clean_tables'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'If this is checked, the tables containing the counter statistics are removed from the database if you choose to uninstall/delete the plugin.', TPTN_LOCAL_NAME ); ?></p>
							<p class="description"><?php _e( "Keep this unchecked if you choose to reinstall the plugin and don't want to lose your counter data.", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_credit"><?php _e( 'Link to Top 10 plugin page', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					    	<input type="checkbox" name="show_credit" id="show_credit" <?php if ( $tptn_settings['show_credit'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'A link to the plugin is added as an extra list item to the list of popular posts', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_genop_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after General options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_general_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="counteropdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Counter and tracker options', TPTN_LOCAL_NAME ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before Counter options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_counter_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><?php _e( 'Display number of views on:', TPTN_LOCAL_NAME ); ?></th>
						<td>
							<label><input type="checkbox" name="add_to_content" id="add_to_content" <?php if ( $tptn_settings['add_to_content'] ) echo 'checked="checked"' ?> /> <?php _e( 'Posts', TPTN_LOCAL_NAME ); ?></label><br />
							<label><input type="checkbox" name="count_on_pages" id="count_on_pages" <?php if ( $tptn_settings['count_on_pages'] ) echo 'checked="checked"' ?> /> <?php _e( 'Pages', TPTN_LOCAL_NAME ); ?></label><br />
							<label><input type="checkbox" name="add_to_home" id="add_to_home" <?php if ( $tptn_settings['add_to_home'] ) echo 'checked="checked"' ?> /> <?php _e( 'Home page', TPTN_LOCAL_NAME ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ( $tptn_settings['add_to_feed'] ) echo 'checked="checked"' ?> /> <?php _e( 'Feeds', TPTN_LOCAL_NAME ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_category_archives" id="add_to_category_archives" <?php if ( $tptn_settings['add_to_category_archives'] ) echo 'checked="checked"' ?> /> <?php _e( 'Category archives', TPTN_LOCAL_NAME ); ?></label><br />
							<label><input type="checkbox" name="add_to_tag_archives" id="add_to_tag_archives" <?php if ( $tptn_settings['add_to_tag_archives'] ) echo 'checked="checked"' ?> /> <?php _e( 'Tag archives', TPTN_LOCAL_NAME ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_archives" id="add_to_archives" <?php if ( $tptn_settings['add_to_archives'] ) echo 'checked="checked"' ?> /> <?php _e( 'Other archives', TPTN_LOCAL_NAME ); ?></label></label>
							<p class="description"><?php _e( "If you choose to disable this, please add <code>&lt;?php if ( function_exists ( 'echo_tptn_post_count' ) ) echo_tptn_post_count(); ?&gt;</code> to your template file where you want it displayed", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'Format to display the post views:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<textarea name="count_disp_form" id="count_disp_form" cols="50" rows="3" style="width:100%"><?php echo htmlspecialchars( stripslashes( $tptn_settings['count_disp_form'] ) ); ?></textarea>
							<p class="description"><?php _e( 'Use <code>%totalcount%</code> to display the total count, <code>%dailycount%</code> to display the daily count and <code>%overallcount%</code> to display the overall count across all posts on the blog. e.g. the default options displays <code>[Visited 123 times, 23 visits today]</code>', TPTN_LOCAL_NAME ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'What do display when there are no visits?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<textarea name="count_disp_form_zero" id="count_disp_form_zero" cols="50" rows="3" style="width:100%"><?php echo htmlspecialchars( stripslashes( $tptn_settings['count_disp_form_zero'] ) ); ?></textarea>
					    	<p class="description"><?php _e( "This text applies only when there are 0 hits for the post and it isn't a single page. e.g. if you display post views on the homepage or archives then this text will be used. To override this, just enter the same text as above option.", TPTN_LOCAL_NAME ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dynamic_post_count"><?php _e( 'Always display latest post count', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="dynamic_post_count" id="dynamic_post_count" <?php if ( $tptn_settings['dynamic_post_count'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'This option uses JavaScript and will increase your page load time. Turn this off if you are not using caching plugins or are OK with displaying older cached counts.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_authors"><?php _e( 'Track visits of authors on their own posts?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="track_authors" id="track_authors" <?php if ( $tptn_settings['track_authors'] ) echo 'checked="checked"' ?> />
					    	<p class="description"><?php _e( 'Disabling this option will stop authors visits tracked on their own posts', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_admins"><?php _e( 'Track visits of admins?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="track_admins" id="track_admins" <?php if ( $tptn_settings['track_admins'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'Disabling this option will stop admin visits being tracked.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_editors"><?php _e( 'Track visits of Editors?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="track_editors" id="track_editors" <?php if ( $tptn_settings['track_editors'] ) echo 'checked="checked"' ?> />
					    	<p class="description"><?php _e( 'Disabling this option will stop editor visits being tracked.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pv_in_admin"><?php _e( 'Display page views on Posts and Pages in Admin', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					    	<input type="checkbox" name="pv_in_admin" id="pv_in_admin" <?php if ( $tptn_settings['pv_in_admin'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( "Adds three columns called Total Views, Today's Views and Views to All Posts and All Pages", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_count_non_admins"><?php _e( 'Show number of views to non-admins', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					    	<input type="checkbox" name="show_count_non_admins" id="show_count_non_admins" <?php if ( $tptn_settings['show_count_non_admins'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( "If you disable this then non-admins won't see the above columns or view the independent pages with the top posts", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_counterop_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after Counter options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_counter_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="pplopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Popular post list options', TPTN_LOCAL_NAME ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before Popular post list options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_list_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><label for="limit"><?php _e( 'Number of popular posts to display: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="limit" id="limit" value="<?php echo esc_attr( stripslashes( $tptn_settings['limit'] ) ); ?>">
							<p class="description"><?php _e( "Maximum number of posts that will be displayed in the list. This option is used if you don't specify the number of posts in the widget or shortcodes", TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Post types to include in results (including custom post types)', TPTN_LOCAL_NAME ); ?></th>
						<td>
							<?php foreach ( $wp_post_types as $wp_post_type ) {
								$post_type_op = '<input type="checkbox" name="post_types[]" value="'.$wp_post_type.'" ';
								if ( in_array( $wp_post_type, $posts_types_inc ) ) $post_type_op .= ' checked="checked" ';
								$post_type_op .= ' />'.$wp_post_type.'&nbsp;&nbsp;';
								echo $post_type_op;
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_post_ids"><?php _e( 'List of post or page IDs to exclude from the results: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td><input type="textbox" name="exclude_post_ids" id="exclude_post_ids" value="<?php echo esc_attr( stripslashes( $tptn_settings['exclude_post_ids'] ) ); ?>"  style="width:250px">
							<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', TPTN_LOCAL_NAME ); ?></p>
							</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_cat_slugs"><?php _e( 'Exclude Categories: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<div style="position:relative;text-align:left">
								<table id="MYCUSTOMFLOATER" class="myCustomFloater" style="position:absolute;top:50px;left:0;background-color:#cecece;display:none;visibility:hidden">
								<tr><td><!--
										please see: http://chrisholland.blogspot.com/2004/09/geekstuff-css-display-inline-block.html
										to explain why i'm using a table here.
										You could replace the table/tr/td with a DIV, but you'd have to specify it's width and height
										-->
									<div class="myCustomFloaterContent">
									you should never be seeing this
									</div>
								</td></tr>
								</table>
								<textarea class="wickEnabled:MYCUSTOMFLOATER" cols="50" rows="3" wrap="virtual" name="exclude_cat_slugs" style="width:100%"><?php echo ( stripslashes( $tptn_settings['exclude_cat_slugs'] ) ); ?></textarea>
								<p class="description"><?php _e( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options', TPTN_LOCAL_NAME ); ?></p>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'Title of popular posts: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="title" id="title" value="<?php echo esc_attr( stripslashes( $tptn_settings['title'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title_daily"><?php _e( 'Title of daily popular posts: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="title_daily" id="title_daily" value="<?php echo esc_attr( stripslashes( $tptn_settings['title_daily'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="blank_output"><?php _e( 'When there are no posts, what should be shown?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<label>
							<input type="radio" name="blank_output" value="blank" id="blank_output_0" <?php if ( $tptn_settings['blank_output'] ) echo 'checked="checked"' ?> />
							<?php _e( 'Blank Output', TPTN_LOCAL_NAME ); ?></label>
							<br />
							<label>
							<input type="radio" name="blank_output" value="customs" id="blank_output_1" <?php if ( ! $tptn_settings['blank_output'] ) echo 'checked="checked"' ?> />
							<?php _e( 'Display:', TPTN_LOCAL_NAME ); ?></label>
							<input type="textbox" name="blank_output_text" id="blank_output_text" value="<?php echo esc_attr( stripslashes( $tptn_settings['blank_output_text'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_excerpt"><?php _e( 'Show post excerpt in list?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ( $tptn_settings['show_excerpt'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="excerpt_length"><?php _e( 'Length of excerpt (in words): ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes( $tptn_settings['excerpt_length'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_author"><?php _e( 'Show post author in list?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="show_author" id="show_author" <?php if ( $tptn_settings['show_author'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_date"><?php _e( 'Show post date in list?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="show_date" id="show_date" <?php if ( $tptn_settings['show_date'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title_length"><?php _e( 'Limit post title length (in characters)', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="title_length" id="title_length" value="<?php echo stripslashes( $tptn_settings['title_length'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="disp_list_count"><?php _e( 'Show view count in list?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="disp_list_count" id="disp_list_count" <?php if ( $tptn_settings['disp_list_count'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="d_use_js"><?php _e( 'Always display latest post count in the daily lists?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="d_use_js" id="d_use_js" <?php if ( $tptn_settings['d_use_js'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'This option uses JavaScript and will increase your page load time. When you enable this option, the daily widget will not use the options set there, but options will need to be set on this screen.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="link_new_window	"><?php _e( 'Open links in new window', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="link_new_window" id="link_new_window" <?php if ( $tptn_settings['link_new_window'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="link_nofollow"><?php _e( 'Add nofollow attribute to links in the list', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ( $tptn_settings['link_nofollow'] ) echo 'checked="checked"' ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_on_post_ids"><?php _e( 'Exclude display of related posts on these posts / pages', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="exclude_on_post_ids" id="exclude_on_post_ids" value="<?php echo esc_attr( stripslashes( $tptn_settings['exclude_on_post_ids'] ) ); ?>"  style="width:250px">
							<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>

					<tr style="background: #eee"><th scope="row" colspan="2"><?php _e( 'Customise the list HTML', TPTN_LOCAL_NAME ); ?></th>
					</tr>
					<tr>
						<th scope="row"><label for="before_list"><?php _e( 'HTML to display before the list of posts: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="before_list" id="before_list" value="<?php echo esc_attr( stripslashes( $tptn_settings['before_list'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="before_list_item"><?php _e( 'HTML to display before each list item: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo esc_attr( stripslashes( $tptn_settings['before_list_item'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="after_list_item"><?php _e( 'HTML to display after each list item: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo esc_attr( stripslashes( $tptn_settings['after_list_item'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="after_list"><?php _e( 'HTML to display after the list of posts: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="after_list" id="after_list" value="<?php echo esc_attr( stripslashes( $tptn_settings['after_list'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_pplop_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after Popular post list options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_list_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="thumbopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Thumbnail options', TPTN_LOCAL_NAME ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before thumbnail options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_thumbnail_options_before', $tptn_settings );
					?>

					<tr><th scope="row"><label for="post_thumb_op"><?php _e( 'Location of post thumbnail:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<label>
								<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ( 'inline' == $tptn_settings['post_thumb_op'] ) echo 'checked="checked"' ?> />
								<?php _e( 'Display thumbnails inline with posts, before title', TPTN_LOCAL_NAME ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="after" id="post_thumb_op_1" <?php if ( 'after' == $tptn_settings['post_thumb_op'] ) echo 'checked="checked"' ?> />
								<?php _e( 'Display thumbnails inline with posts, after title', TPTN_LOCAL_NAME ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_2" <?php if ( 'thumbs_only' == $tptn_settings['post_thumb_op'] ) echo 'checked="checked"' ?> />
								<?php _e( 'Display only thumbnails, no text', TPTN_LOCAL_NAME ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_3" <?php if ( 'text_only' == $tptn_settings['post_thumb_op'] ) echo 'checked="checked"' ?> />
								<?php _e( 'Do not display thumbnails, only text.', TPTN_LOCAL_NAME ); ?>
							</label>
						</td>
					</tr>
					<tr><th scope="row"><?php _e( 'Thumbnail size:', TPTN_LOCAL_NAME ); ?></th>
						<td>
							<?php
								$tptn_get_all_image_sizes = tptn_get_all_image_sizes();
								if ( isset( $tptn_get_all_image_sizes['tptn_thumbnail'] ) ) {
									unset( $tptn_get_all_image_sizes['tptn_thumbnail'] );
								}

								foreach( $tptn_get_all_image_sizes as $size ) :
							?>
								<label>
									<input type="radio" name="thumb_size" value="<?php echo $size['name'] ?>" id="<?php echo $size['name'] ?>" <?php if ( $tptn_settings['thumb_size'] == $size['name'] ) echo 'checked="checked"' ?> />
									<?php echo $size['name']; ?> ( <?php echo $size['width']; ?>x<?php echo $size['height']; ?>
									<?php
										if ( $size['crop'] ) {
											echo "cropped";
										}
									?>
									)
								</label>
								<br />
							<?php endforeach; ?>

								<label>
									<input type="radio" name="thumb_size" value="tptn_thumbnail" id="tptn_thumbnail" <?php if ( $tptn_settings['thumb_size'] == 'tptn_thumbnail' ) echo 'checked="checked"' ?> /> <?php _e( 'Custom size', TPTN_LOCAL_NAME ); ?>
								</label>
								<p class="description">
									<?php _e( 'You can choose from existing image sizes above or create a custom size. If you have chosen Custom size above, then enter the width, height and crop settings below. For best results, use a cropped image.', TPTN_LOCAL_NAME ); ?><br />
									<?php _e( "If you change the width and/or height below, existing images will not be automatically resized.", TPTN_LOCAL_NAME ); ?>
									<?php printf( __( "I recommend using <a href='%s' target='_blank'>Force Regenerate Thumbnails</a> or <a href='%s' target='_blank'>Force Regenerate Thumbnails</a> to regenerate all image sizes.", TPTN_LOCAL_NAME ), 'https://wordpress.org/plugins/force-regenerate-thumbnails/', 'https://wordpress.org/plugins/regenerate-thumbnails/' ); ?>
								</p>
						</td>
					<tr><th scope="row"><label for="thumb_width"><?php _e( 'Width of custom thumbnail:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_width'] ) ); ?>" style="width:50px" />px
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_height"><?php _e( 'Height of custom thumbnail', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_height'] ) ); ?>" style="width:50px" />px
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_crop"><?php _e( 'Crop mode:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="thumb_crop" id="thumb_crop" <?php if ( $tptn_settings['thumb_crop'] ) echo 'checked="checked"' ?> />
							<p class="description">
								<?php _e( "By default, thumbnails will be proportionately cropped. Check this box to hard crop the thumbnails.", TPTN_LOCAL_NAME ); ?>
								<?php printf( __( "<a href='%s' target='_blank'>Difference between soft and hard crop</a>", TPTN_LOCAL_NAME ), esc_url( 'http://www.davidtan.org/wordpress-hard-crop-vs-soft-crop-difference-comparison-example/' ) ); ?>
								<?php if ( $tptn_settings['include_default_style'] ) { ?>
									<p class="description"><?php _e( "Since you're using the default styles set under the Custom Styles section, the width and height is fixed at 65px and crop mode is enabled.", TPTN_LOCAL_NAME ); ?></p>
								<?php } ?>
							</p>
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_html"><?php _e( 'Style attributes / Width and Height HTML attributes:', TPTN_LOCAL_NAME ); ?></label></th>
					  	<td>
							<label>
								<input type="radio" name="thumb_html" value="css" id="thumb_html_0" <?php if ( 'css' == $tptn_settings['thumb_html'] ) echo 'checked="checked"' ?> />
								<?php _e( 'Style attributes are used for width and height.', TPTN_LOCAL_NAME ); ?> <br /><code>style="max-width:<?php echo $tptn_settings['thumb_width'] ?>px;max-height:<?php echo $tptn_settings['thumb_height'] ?>px;"</code>
							</label>
							<br />
							<label>
								<input type="radio" name="thumb_html" value="html" id="thumb_html_1" <?php if ( 'html' == $tptn_settings['thumb_html'] ) echo 'checked="checked"' ?> />
								<?php _e( 'HTML width and height attributes are used for width and height.', TPTN_LOCAL_NAME ); ?> <br /><code>width="<?php echo $tptn_settings['thumb_width'] ?>" height="<?php echo $tptn_settings['thumb_height'] ?>"</code>
							</label>
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_timthumb"><?php _e( 'Use timthumb to generate thumbnails? ', TPTN_LOCAL_NAME ); ?></label></th>
					  	<td>
							<input type="checkbox" name="thumb_timthumb" id="thumb_timthumb" <?php if ( $tptn_settings['thumb_timthumb'] ) echo 'checked="checked"' ?> />
							<p class="description"><?php _e( 'If checked, <a href="http://www.binarymoon.co.uk/projects/timthumb/" target="_blank">timthumb</a> will be used to generate thumbnails', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_timthumb_q"><?php _e( 'Quality of thumbnails generated by timthumb:', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="textbox" name="thumb_timthumb_q" id="thumb_timthumb_q" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_timthumb_q'] ) ); ?>" style="width:50px" />
							<p class="description"><?php _e( 'Enter values between 0 and 100 only. 100 is highest quality and the highest file size. Suggested maximum value is 95. Default is 75.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_meta"><?php _e( 'Post thumbnail meta field name: ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					  		<input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_meta'] ) ); ?>">
					  		<p class="description"><?php _e( 'The value of this field should contain the image source and is set in the <em>Add New Post</em> screen', TPTN_LOCAL_NAME ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="scan_images"><?php _e( 'If the postmeta is not set, then should the plugin extract the first image from the post?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
						  	<input type="checkbox" name="scan_images" id="scan_images" <?php if ( $tptn_settings['scan_images'] ) echo 'checked="checked"' ?> />
						  	<p class="description"><?php _e( 'This could slow down the loading of your page if the first image in the related posts is large in file-size', TPTN_LOCAL_NAME ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="thumb_default_show"><?php _e( 'Use default thumbnail? ', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
					  		<input type="checkbox" name="thumb_default_show" id="thumb_default_show" <?php if ( $tptn_settings['thumb_default_show'] ) echo 'checked="checked"' ?> />
					  		<p class="description"><?php _e( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', TPTN_LOCAL_NAME ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="thumb_default"><?php _e( 'Default thumbnail: ', TPTN_LOCAL_NAME ); ?></label></th>
					  	<td>
					  		<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_default'] ) ); ?>" style="width:100%"> <br />
					  		<?php if ( '' != $tptn_settings['thumb_default'] ) echo "<img src='{$tptn_settings['thumb_default']}' style='max-width:200px' />"; ?>
					  		<p class="description"><?php _e( "The plugin will first check if the post contains a thumbnail. If it doesn't then it will check the meta field. If this is not available, then it will show the default image as specified above", TPTN_LOCAL_NAME ); ?></p>
					  	</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_thumbop_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after thumbnail options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_thumbnail_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="customcssdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
			<h3 class='hndle'><span><?php _e( 'Custom CSS', TPTN_LOCAL_NAME ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before custom styles options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_custom_styles_before', $tptn_settings );
					?>

					<tr><th scope="row"><label for="include_default_style"><?php _e( 'Use default style included in the plugin?', TPTN_LOCAL_NAME ); ?></label></th>
						<td>
							<input type="checkbox" name="include_default_style" id="include_default_style" <?php if ( $tptn_settings['include_default_style'] ) echo 'checked="checked"' ?> />
						  	<p class="description"><?php _e( 'Top 10 includes a default style that makes your popular posts list to look beautiful. Check the box above if you want to use this.', TPTN_LOCAL_NAME ); ?></p>
						  	<p class="description"><?php _e( 'Enabling this option will turn on the thumbnails and set their width and height to 65px. It will also turn off the display of the author, excerpt and date if already enabled. Disabling this option will not revert any settings.', TPTN_LOCAL_NAME ); ?></p>
						  	<p class="description"><?php printf( __( 'You can view the default style at <a href="%1$s" target="_blank">%1$s</a>', TPTN_LOCAL_NAME ), esc_url( 'https://github.com/ajaydsouza/top-10/blob/master/css/default-style.css' ) ); ?></p>
						</td>
					</tr>
					<tr><th scope="row" colspan="2"><?php _e( 'Custom CSS to add to header:', TPTN_LOCAL_NAME ); ?></th>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80" style="width:100%"><?php echo stripslashes( $tptn_settings['custom_CSS'] ); ?></textarea>
							<p class="description"><?php _e( 'Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/top-10/faq/" target="_blank">FAQ</a> for available CSS classes to style.', TPTN_LOCAL_NAME ); ?></p>
						</td>
					</tr>

					<?php
						/**
						 * Fires after custom styles options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_custom_styles_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
		<p>
			<input type="submit" name="tptn_save" id="tptn_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
			<input type="submit" name="tptn_default" id="tptn_default" value="<?php _e( 'Default Options', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e( "Do you want to set options to Default?", TPTN_LOCAL_NAME ); ?>')) return false;" />
		</p>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

		<?php
			/**
			 * Fires after all option blocks.
			 *
			 * @since 2.0.0
			 *
			 * @param	array	$tptn_settings	Top 10 settings array
			 */
			do_action( 'tptn_admin_options_after', $tptn_settings );
		?>

	  <hr class="clear" />

	  <form method="post" id="tptn_maintenance_op" name="tptn_reset_options" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Maintenance', TPTN_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
			  <table class="form-table">
				<tr><td scope="row" colspan="2">
				    <p class="description"><?php _e( 'Over time the Daily Top 10 database grows in size, which reduces the performance of the plugin. Cleaning the database at regular intervals could improve performance, especially on high traffic blogs. Enabling maintenance will automatically delete entries older than 90 days.', TPTN_LOCAL_NAME ); ?><br />
				    <strong><?php _e( 'Note: When scheduled maintenance is enabled, WordPress will run the cron job everytime the job is rescheduled (i.e. you change the settings below).', TPTN_LOCAL_NAME ); ?></strong>
				  </td>
				</tr>
				<tr><th scope="row"><label for="cron_on"><?php _e( 'Enable scheduled maintenance of daily tables:', TPTN_LOCAL_NAME ); ?></label></th>
				  <td><input type="checkbox" name="cron_on" id="cron_on" <?php if ( $tptn_settings['cron_on'] ) echo 'checked="checked"' ?> />
				</td>
				</tr>
				<tr><th scope="row"><label for="cron_hour"><?php _e( 'Time to run maintenance', TPTN_LOCAL_NAME ); ?></label></th>
				  <td><input type="textbox" name="cron_hour" id="cron_hour" value="<?php echo esc_attr(stripslashes($tptn_settings['cron_hour'])); ?>" style="width:50px" /> <?php _e( 'hrs', TPTN_LOCAL_NAME ); ?> : <input type="textbox" name="cron_min" id="cron_min" value="<?php echo esc_attr(stripslashes($tptn_settings['cron_min'])); ?>" style="width:50px" /> <?php _e( 'min', TPTN_LOCAL_NAME ); ?></td>
				</tr>
				<tr><th scope="row"><label for="cron_recurrence"><?php _e( 'How often should the maintenance be run:', TPTN_LOCAL_NAME ); ?></label></th>
				  <td>
					<label>
					<input type="radio" name="cron_recurrence" value="daily" id="cron_recurrence0" <?php if ( 'daily' == $tptn_settings['cron_recurrence'] ) echo 'checked="checked"' ?> />
					<?php _e( 'Daily', TPTN_LOCAL_NAME ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="weekly" id="cron_recurrence1" <?php if ( 'weekly' == $tptn_settings['cron_recurrence'] ) echo 'checked="checked"' ?> />
					<?php _e( 'Weekly', TPTN_LOCAL_NAME ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="fortnightly" id="cron_recurrence2" <?php if ( 'fortnightly' == $tptn_settings['cron_recurrence'] ) echo 'checked="checked"' ?> />
					<?php _e( 'Fortnightly', TPTN_LOCAL_NAME ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="monthly" id="cron_recurrence3" <?php if ( 'monthly' == $tptn_settings['cron_recurrence'] ) echo 'checked="checked"' ?> />
					<?php _e( 'Monthly', TPTN_LOCAL_NAME ); ?></label>
					<br />
				  </td>
				</tr>
				<tr><td scope="row" colspan="2">
					<?php
					if ( ( $tptn_settings['cron_on'] ) || wp_next_scheduled( 'ald_tptn_hook' ) ) {
						if ( wp_next_scheduled( 'ald_tptn_hook' ) ) {
							echo '<span style="color:#0c0">';
							_e( 'The cron job has been scheduled. Maintenance will run ', TPTN_LOCAL_NAME );
							echo wp_get_schedule( 'ald_tptn_hook' );
							echo '</span>';
						} else {
							echo '<span style="color:#e00">';
							_e( 'The cron job is missing. Please resave this page to add the job', TPTN_LOCAL_NAME );
							echo '</span>';
						}
					} else {
							echo '<span style="color:#FFA500">';
							_e( 'Maintenance is turned off', TPTN_LOCAL_NAME );
							echo '</span>';
					}
					?>
				</td></tr>
				</table>
			  <input type="submit" name="tptn_mnts_save" id="tptn_mnts_save" value="<?php _e( 'Save Options', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

	  <form method="post" id="tptn_reset_options" name="tptn_reset_options" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Reset count', TPTN_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
		    <p class="description">
		      <?php _e( 'This cannot be reversed. Make sure that your database has been backed up before proceeding', TPTN_LOCAL_NAME ); ?>
		    </p>
		    <p>
		      <input name="tptn_trunc_all" type="submit" id="tptn_trunc_all" value="<?php _e( 'Reset Popular Posts', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php _e( "Are you sure you want to reset the popular posts?", TPTN_LOCAL_NAME ); ?>')) return false;" />
		      <input name="tptn_trunc_daily" type="submit" id="tptn_trunc_daily" value="<?php _e( 'Reset Daily Popular Posts', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php _e( "Are you sure you want to reset the daily popular posts?", TPTN_LOCAL_NAME ); ?>')) return false;" />
		      <input name="tptn_clean_duplicates" type="submit" id="tptn_clean_duplicates" value="<?php _e( 'Clear duplicates', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e( "This will delete the duplicate entries in the tables. Proceed?", TPTN_LOCAL_NAME ); ?>')) return false;" />
		    </p>
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

	  	<?php
			if ( is_multisite() ) {
		?>

	  <form method="post" id="tptn_import_mu" name="tptn_import_mu" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'WordPress Multisite: Migrate Top 10 v1.x counts to 2.x', TPTN_LOCAL_NAME ); ?></span></h3>
	      <div class="inside">
		    <p class="description">
		    	<?php _e( "If you've been using Top 10 v1.x on multisite, you would have needed to activate the plugin independently for each site. This would have resulted in two tables being created for each site in the network.", TPTN_LOCAL_NAME ); ?>
		    	<?php _e( "Top 10 v2.x onwards uses only a single table to record the count, keeping your database clean. You can use this tool to import the recorded counts from v1.x tables to the new v2.x table format.", TPTN_LOCAL_NAME ); ?>
		    </p>
		    <p class="description">
		    	<?php _e( "If you do not see any tables below, then it means that either all data has already been imported or no relevant information has been found.", TPTN_LOCAL_NAME ); ?>
		    </p>
		    <p class="description">
		    	<strong style="color:#C00"><?php _e( "After running the importer, please verify that all the counts have been successfully imported. Only then should you delete any old tables!", TPTN_LOCAL_NAME ); ?></strong>
		    </p>

		    	<?php
			    	$top_ten_mu_tables_sel_blog_ids = get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() );
			    	$top_ten_mu_tables_blog_ids = array();
			    	$top_ten_all_mu_tables = array();

			        // Get all blogs in the network and activate plugin on each one
			        $blog_ids = $wpdb->get_col( "
			        	SELECT blog_id FROM $wpdb->blogs
						WHERE archived = '0' AND spam = '0' AND deleted = '0'
					" );
			        foreach ( $blog_ids as $blog_id ) {
			        	switch_to_blog( $blog_id );
						$top_ten_mu_table = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "top_ten' " );

						if ( ! empty( $top_ten_mu_table ) && ! is_main_site( $blog_id ) ) {
							$top_ten_mu_tables_blog_ids[] = $blog_id;
							$top_ten_all_mu_tables[ $top_ten_mu_table ][0] = $top_ten_mu_table;
							$top_ten_all_mu_tables[ $top_ten_mu_table ][1] = in_array( $blog_id, $top_ten_mu_tables_sel_blog_ids ) ? 1 : 0;
							$top_ten_all_mu_tables[ $top_ten_mu_table ][2] = $blog_id;
						}
			        }

			        // Switch back to the current blog
			        restore_current_blog();

					if ( ! empty( $top_ten_all_mu_tables ) ) {
				?>

			<table class="form-table">
				<tr>
					<th>
						<?php _e( "Blog ID", TPTN_LOCAL_NAME ); ?>
					</th>
					<th>
						<?php _e( "Status", TPTN_LOCAL_NAME ); ?>
					</th>
					<th>
						<?php _e( "Select to import", TPTN_LOCAL_NAME ); ?>
					</th>
				</tr>

				<?php
					foreach ( $top_ten_all_mu_tables as $top_ten_all_mu_table ) {
				?>
					<tr>
						<td>
							<?php
								_e( "Blog #", TPTN_LOCAL_NAME );
								echo $top_ten_all_mu_table[2];
								echo ": ";
								echo get_blog_details( $top_ten_all_mu_table[2] )->blogname;
							?>
						</td>
						<td>
							<?php
								if ( 0 == $top_ten_all_mu_table[1] ) {
									echo '<span style="color:#F00">';
									_e( "Not imported", TPTN_LOCAL_NAME );
									echo '</span>';
								} else {
									echo '<span style="color:#0F0">';
									_e( "Imported", TPTN_LOCAL_NAME );
									echo '</span>';
								}
							?>
						</td>
						<td>
							<?php
								if ( 0 == $top_ten_all_mu_table[1] ) {
									echo '<input type="checkbox" name="top_ten_all_mu_tables[' . $top_ten_all_mu_table[0] . ']" value="' . $top_ten_all_mu_table[2] . '" checked="checked" />';
								} else {
									echo '<input type="checkbox" name="top_ten_all_mu_tables[' . $top_ten_all_mu_table[0] . ']" value="' . $top_ten_all_mu_table[2] . '" />';
								}
							?>
						</td>
					</tr>
				<?php
					}
			    ?>
			</table>
		    <p>
		      <input type="hidden" name="top_ten_mu_tables_blog_ids" value="<?php echo implode( ',', $top_ten_mu_tables_blog_ids ); ?>" />
		      <input name="tptn_import" type="submit" id="tptn_import" value="<?php _e( 'Begin import', TPTN_LOCAL_NAME ); ?>" class="button button-primary" />
		      <input name="tptn_delete_selected_tables" type="submit" id="tptn_delete_selected_tables" value="<?php _e( 'Delete selected tables', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" style="color:#f00" />
		      <input name="tptn_delete_imported_tables" type="submit" id="tptn_delete_imported_tables" value="<?php _e( 'Delete all imported tables', TPTN_LOCAL_NAME ); ?>" class="button button-secondary" style="color:#f00" />
		    </p>
			<?php
				} // End if ( ! empty( $top_ten_all_mu_tables ) )
			?>
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>
			<?php
				}
			?>
	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
		  <?php tptn_admin_side(); ?>
	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

<?php

}


/**
 * Function to generate the top 10 daily popular posts page.
 *
 * @since	1.9.2
 */
function tptn_manage_daily() {
	tptn_manage(1);
}


/**
 * Function to generate the top 10 popular posts page.
 *
 * @since	1.3
 * @param	int	$daily	Overall popular
 */
function tptn_manage( $daily = 0 ) {

	$paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 0;
	$limit = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 0;
	$daily = isset( $_GET['daily'] ) ? intval( $_GET['daily'] ) : $daily;

?>

<div class="wrap">
	<h2>
		<?php if ( ! $daily ) {
			_e( 'Popular Posts', TPTN_LOCAL_NAME );
		} else {
			_e( 'Daily Popular Posts', TPTN_LOCAL_NAME );
		} ?>
	</h2>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
		<?php echo tptn_pop_display( $daily, $paged, $limit, false ); ?>
	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
		  <?php tptn_admin_side(); ?>
	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

<?php
}


/**
 * Function to generate the right sidebar of the Settings and Admin popular posts pages.
 *
 * @since	1.8.1
 */
function tptn_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', TPTN_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="donate@ajaydsouza.com">
			<input type="hidden" name="lc" value="IN">
			<input type="hidden" name="item_name" value="<?php _e( 'Donation for Top 10', TPTN_LOCAL_NAME ); ?>">
			<input type="hidden" name="item_number" value="tptn_admin">
			<strong><?php _e( 'Enter amount in USD: ', TPTN_LOCAL_NAME ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="button_subtype" value="services">
			<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Top 10', TPTN_LOCAL_NAME ); ?>">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', TPTN_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
		<div id="follow-us">
			<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
			<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', TPTN_LOCAL_NAME ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', TPTN_LOCAL_NAME ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
			<ul>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/top-10/" target="_blank"><?php _e( 'Top 10 plugin page', TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="https://github.com/ajaydsouza/top-10" target="_blank"><?php _e( 'Top 10 Github page', TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/" target="_blank"><?php _e( 'Other plugins', TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="http://ajaydsouza.com/" target="_blank"><?php _e( "Ajay's blog", TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/top-10/faq/" target="_blank"><?php _e( 'FAQ', TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="http://wordpress.org/support/plugin/top-10" target="_blank"><?php _e( 'Support', TPTN_LOCAL_NAME ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/top-10" target="_blank"><?php _e( 'Reviews', TPTN_LOCAL_NAME ); ?></a></li>
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

	$plugin_page = add_menu_page( __( "Top 10 Settings", TPTN_LOCAL_NAME ), __( "Top 10", TPTN_LOCAL_NAME ), 'manage_options', 'tptn_options', 'tptn_options', 'dashicons-editor-ol' );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( "Top 10 Settings", TPTN_LOCAL_NAME ), __( "Top 10 Settings", TPTN_LOCAL_NAME ), 'manage_options', 'tptn_options', 'tptn_options' );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( "Overall Popular Posts", TPTN_LOCAL_NAME ), __( "Overall Popular Posts", TPTN_LOCAL_NAME ), 'manage_options', 'tptn_manage', 'tptn_manage' );
	add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( "Daily Popular Posts", TPTN_LOCAL_NAME ), __( "Daily Popular Posts", TPTN_LOCAL_NAME ), 'manage_options', 'tptn_manage_daily', 'tptn_manage_daily' );
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
			'settings' => '<a href="' . admin_url( 'options-general.php?page=tptn_options' ) . '">' . __( 'Settings', TPTN_LOCAL_NAME ) . '</a>'
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
		$links[] = '<a href="http://ajaydsouza.com/support/">' . __( 'Support', TPTN_LOCAL_NAME ) . '</a>';
		$links[] = '<a href="http://ajaydsouza.com/donate/">' . __( 'Donate', TPTN_LOCAL_NAME ) . '</a>';
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


?>