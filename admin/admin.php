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
 * @copyright 2008-2016 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Function generates the plugin settings page.
 *
 * @since	1.0
 */
function tptn_options() {

	global $wpdb;

	$tptn_settings = tptn_read_options();

	/* Temporary check to remove the deprecated hook */
	if ( wp_next_scheduled( 'ald_tptn_hook' ) ) {
		wp_clear_scheduled_hook( 'ald_tptn_hook' );

		tptn_enable_run( $tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence'] );
	}

	/*
    Temporary check if default styles are off and left thumbnails are selected - will be eventually deprecated
    This is a mismatch, so we force it to no style
    */
	if ( ( false === $tptn_settings['include_default_style'] ) && ( 'left_thumbs' === $tptn_settings['tptn_styles'] ) ) {
		$tptn_settings['tptn_styles'] = 'no_style';
		update_option( 'ald_tptn_settings', $tptn_settings );
	}
	if ( ( true === $tptn_settings['include_default_style'] ) && ( 'left_thumbs' !== $tptn_settings['tptn_styles'] ) ) {
		$tptn_settings['tptn_styles'] = 'left_thumbs';
		update_option( 'ald_tptn_settings', $tptn_settings );
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
		$tptn_settings['activate_overall'] = isset( $_POST['activate_overall'] ) ? true : false;
		$tptn_settings['activate_daily'] = isset( $_POST['activate_daily'] ) ? true : false;
		$tptn_settings['cache'] = isset( $_POST['cache'] ) ? true : false;
		$tptn_settings['daily_midnight'] = isset( $_POST['daily_midnight'] ) ? true : false;
		$tptn_settings['daily_range'] = absint( $_POST['daily_range'] );
		$tptn_settings['hour_range'] = absint( $_POST['hour_range'] );
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

		$tptn_settings['count_disp_form'] = wp_kses_post( wp_unslash( $_POST['count_disp_form'] ) );
		$tptn_settings['count_disp_form_zero'] = wp_kses_post( wp_unslash( $_POST['count_disp_form_zero'] ) );
		$tptn_settings['dynamic_post_count'] = isset( $_POST['dynamic_post_count'] ) ? true : false;

		$tptn_settings['tracker_type'] = sanitize_text_field( $_POST['tracker_type'] );
		$tptn_settings['track_authors'] = isset( $_POST['track_authors'] ) ? true : false;
		$tptn_settings['track_admins'] = isset( $_POST['track_admins'] ) ? true : false;
		$tptn_settings['track_editors'] = isset( $_POST['track_editors'] ) ? true : false;

		$tptn_settings['pv_in_admin'] = isset( $_POST['pv_in_admin'] ) ? true : false;
		$tptn_settings['show_count_non_admins'] = isset( $_POST['show_count_non_admins'] ) ? true : false;

		/* Popular post list options */
		$tptn_settings['limit'] = absint( $_POST['limit'] );
		$tptn_settings['how_old'] = absint( $_POST['how_old'] );

		// Process post types to be selected.
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$post_types_arr = ( isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) ) : array( 'post' => 'post' );
		$post_types = array_intersect( $wp_post_types, $post_types_arr );
		$tptn_settings['post_types'] = http_build_query( $post_types, '', '&' );

		$tptn_settings['exclude_post_ids'] = empty( $_POST['exclude_post_ids'] ) ? '' : implode( ',', array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['exclude_post_ids'] ) ) ) ) );

		/**** Exclude categories ****/
		$exclude_categories_slugs = array_map( 'trim', explode( ',', sanitize_text_field( $_POST['exclude_cat_slugs'] ) ) );

		foreach ( $exclude_categories_slugs as $exclude_categories_slug ) {
			$category_obj = get_term_by( 'name', $exclude_categories_slug, 'category' );

			// Fall back to slugs since that was the default format before v2.4.0.
			if ( false === $category_obj ) {
				$category_obj = get_term_by( 'slug', $exclude_categories_slug, 'category' );
			}
			if ( isset( $category_obj->term_taxonomy_id ) ) {
				$exclude_categories[] = $category_obj->term_taxonomy_id;
				$exclude_cat_slugs[] = $category_obj->name;
			}
		}
		$tptn_settings['exclude_categories'] = ( isset( $exclude_categories ) ) ? join( ',', $exclude_categories ) : '';
		$tptn_settings['exclude_cat_slugs'] = ( isset( $exclude_cat_slugs ) ) ? join( ',', $exclude_cat_slugs ) : '';

		$tptn_settings['title'] = wp_kses_post( $_POST['title'] );
		$tptn_settings['title_daily'] = wp_kses_post( $_POST['title_daily'] );

		$tptn_settings['blank_output'] = ( 'blank' === sanitize_text_field( $_POST['blank_output'] ) ) ? true : false;
		$tptn_settings['blank_output_text'] = wp_kses_post( $_POST['blank_output_text'] );

		$tptn_settings['show_excerpt'] = isset( $_POST['show_excerpt'] ) ? true : false;
		$tptn_settings['excerpt_length'] = absint( $_POST['excerpt_length'] );
		$tptn_settings['show_date'] = isset( $_POST['show_date'] ) ? true : false;
		$tptn_settings['show_author'] = isset( $_POST['show_author'] ) ? true : false;
		$tptn_settings['title_length'] = absint( $_POST['title_length'] );
		$tptn_settings['disp_list_count'] = isset( $_POST['disp_list_count'] ) ? true : false;

		$tptn_settings['link_new_window'] = isset( $_POST['link_new_window'] ) ? true : false;
		$tptn_settings['link_nofollow'] = isset( $_POST['link_nofollow'] ) ? true : false;
		$tptn_settings['exclude_on_post_ids'] = empty( $_POST['exclude_on_post_ids'] ) ? '' : implode( ',', array_map( 'absint', explode( ',', sanitize_text_field( $_POST['exclude_on_post_ids'] ) ) ) );

		// List HTML options
		$tptn_settings['before_list'] = wp_kses_post( $_POST['before_list'] );
		$tptn_settings['after_list'] = wp_kses_post( $_POST['after_list'] );
		$tptn_settings['before_list_item'] = wp_kses_post( $_POST['before_list_item'] );
		$tptn_settings['after_list_item'] = wp_kses_post( $_POST['after_list_item'] );

		/* Thumbnail options */
		$tptn_settings['post_thumb_op'] = sanitize_text_field( $_POST['post_thumb_op'] );
		$tptn_settings['thumb_size'] = sanitize_text_field( $_POST['thumb_size'] );
		$tptn_settings['thumb_width'] = absint( $_POST['thumb_width'] );
		$tptn_settings['thumb_height'] = absint( $_POST['thumb_height'] );
		$tptn_settings['thumb_crop'] = ( isset( $_POST['thumb_crop'] ) ? true : false );
		$tptn_settings['thumb_html'] = sanitize_text_field( $_POST['thumb_html'] );

		$tptn_settings['thumb_meta'] = empty( $_POST['thumb_meta'] ) ? 'post-image' : sanitize_text_field( $_POST['thumb_meta'] );
		$tptn_settings['scan_images'] = isset( $_POST['scan_images'] ) ? true : false;
		$tptn_settings['thumb_default_show'] = isset( $_POST['thumb_default_show'] ) ? true : false;
		$tptn_settings['thumb_default'] = ( ( '' === esc_url_raw( $_POST['thumb_default'] ) ) || ( '/default.png' === esc_url_raw( $_POST['thumb_default'] ) ) ) ? TOP_TEN_PLUGIN_URL . '/default.png' : esc_url_raw( $_POST['thumb_default'] );

		/* Styles */
		$tptn_settings['custom_CSS'] = wp_kses_post( $_POST['custom_CSS'] );

		$tptn_settings['tptn_styles'] = sanitize_text_field( $_POST['tptn_styles'] );

		if ( 'left_thumbs' === $tptn_settings['tptn_styles'] ) {
			$tptn_settings['include_default_style'] = true;
			$tptn_settings['post_thumb_op'] = 'inline';
		} elseif ( 'text_only' === $tptn_settings['tptn_styles'] ) {
			$tptn_settings['include_default_style'] = false;
			$tptn_settings['post_thumb_op'] = 'text_only';
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

		// Delete the cache.
		tptn_cache_delete();

		/* Echo a success message */
		$str = '<div id="message" class="updated fade"><p>' . __( 'Options saved successfully. If enabled, the cache has been cleared.', 'top-10' ) . '</p>';

		if ( 'left_thumbs' === $tptn_settings['tptn_styles'] ) {
			$str .= '<p>' . __( 'Left Thumbnails style selected. Post thumbnail location set to Inline before text.', 'top-10' ) . '</p>';
		}
		if ( 'text_only' === $tptn_settings['tptn_styles'] ) {
			$str .= '<p>' . __( 'Text Only style selected. Thumbnails will not be displayed.', 'top-10' ) . '</p>';
		}
		if ( 'tptn_thumbnail' !== $tptn_settings['thumb_size'] ) {
			$thumb_size = tptn_get_all_image_sizes( $tptn_settings['thumb_size'] );
			$str .= '<p>' . sprintf( __( 'Pre-built thumbnail size selected. Thumbnail set to %1$d x %2$d.', 'top-10' ), $thumb_size['width'], $thumb_size['height'] ) . '</p>';
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

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options set to Default.', 'top-10' ) . '</p></div>';
		echo $str;
	}

	/* Truncate overall posts table */
	if ( ( isset( $_POST['tptn_trunc_all'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Top 10 popular posts reset', 'top-10' ) . '</p></div>';
		echo $str;
	}

	/* Truncate daily posts table */
	if ( ( isset( $_POST['tptn_trunc_daily'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_trunc_count( true );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Top 10 daily popular posts reset', 'top-10' ) . '</p></div>';
		echo $str;
	}

	/* Clean duplicates */
	if ( ( isset( $_POST['tptn_clean_duplicates'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_clean_duplicates( true );
		tptn_clean_duplicates( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Duplicate rows cleaned from tables', 'top-10' ) . '</p></div>';
		echo $str;
	}

	/* Merge blog IDs */
	if ( ( isset( $_POST['tptn_merge_blogids'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		tptn_merge_blogids( true );
		tptn_merge_blogids( false );
		$str = '<div id="message" class="updated fade"><p>' . __( 'Post counts across blog IDs 0 and 1 have been merged', 'top-10' ) . '</p></div>';
		echo $str;
	}

	/* Save maintenance options */
	if ( ( isset( $_POST['tptn_mnts_save'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		$tptn_settings['cron_hour'] = min( 23, absint( $_POST['cron_hour'] ) );
		$tptn_settings['cron_min'] = min( 59, absint( $_POST['cron_min'] ) );
		$tptn_settings['cron_recurrence'] = sanitize_text_field( $_POST['cron_recurrence'] );

		if ( isset( $_POST['cron_on'] ) ) {
			$tptn_settings['cron_on'] = true;
			tptn_enable_run( $tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence'] );
			$str = '<div id="message" class="updated fade"><p>' . __( 'Scheduled maintenance enabled / modified', 'top-10' ) . '</p></div>';
		} else {
			$tptn_settings['cron_on'] = false;
			tptn_disable_run();
			$str = '<div id="message" class="updated fade"><p>' . __( 'Scheduled maintenance disabled', 'top-10' ) . '</p></div>';
		}
		update_option( 'ald_tptn_settings', $tptn_settings );
		$tptn_settings = tptn_read_options();

		echo $str;
	}

	if ( ( isset( $_POST['tptn_import'] ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {

		$top_ten_all_mu_tables = isset( $_POST['top_ten_all_mu_tables'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['top_ten_all_mu_tables'] ) ) : array();
		$top_ten_mu_tables_blog_ids = explode( ',', sanitize_text_field( $_POST['top_ten_mu_tables_blog_ids'] ) );
		$top_ten_mu_tables_sel_blog_ids = array_values( $top_ten_all_mu_tables );

		foreach ( $top_ten_mu_tables_sel_blog_ids as $top_ten_mu_tables_sel_blog_id ) {
			$sql = '
                    INSERT INTO ' . $wpdb->base_prefix . "top_ten (postnumber, cntaccess, blog_id)
                      SELECT postnumber, cntaccess, '%d' FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten
                      ON DUPLICATE KEY UPDATE ' . $wpdb->base_prefix . 'top_ten.cntaccess = ' . $wpdb->base_prefix . 'top_ten.cntaccess + (
                        SELECT ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten.cntaccess FROM ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten WHERE ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten.postnumber = ' . $wpdb->base_prefix . 'top_ten.postnumber
                      )
                ';

			$wpdb->query( $wpdb->prepare( $sql, $top_ten_mu_tables_sel_blog_id ) );

			$sql = '
                    INSERT INTO ' . $wpdb->base_prefix . "top_ten_daily (postnumber, cntaccess, dp_date, blog_id)
                      SELECT postnumber, cntaccess, dp_date, '%d' FROM " . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten_daily
                      ON DUPLICATE KEY UPDATE ' . $wpdb->base_prefix . 'top_ten_daily.cntaccess = ' . $wpdb->base_prefix . 'top_ten_daily.cntaccess + (
                        SELECT ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten_daily.cntaccess FROM ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten_daily WHERE ' . $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten_daily.postnumber = ' . $wpdb->base_prefix . 'top_ten_daily.postnumber
                      )
                ';

			$wpdb->query( $wpdb->prepare( $sql, $top_ten_mu_tables_sel_blog_id ) );
		}

		update_site_option( 'top_ten_mu_tables_sel_blog_ids', array_unique( array_merge( $top_ten_mu_tables_sel_blog_ids, get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() ) ) ) );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Counts from selected sites have been imported.', 'top-10' ) . '</p></div>';
		echo $str;
	}

	if ( ( ( isset( $_POST['tptn_delete_selected_tables'] ) ) || ( isset( $_POST['tptn_delete_imported_tables'] ) ) ) && ( check_admin_referer( 'tptn-plugin-settings' ) ) ) {
		$top_ten_all_mu_tables = isset( $_POST['top_ten_all_mu_tables'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['top_ten_all_mu_tables'] ) ) : array();
		$top_ten_mu_tables_blog_ids = explode( ',', sanitize_text_field( $_POST['top_ten_mu_tables_blog_ids'] ) );
		$top_ten_mu_tables_sel_blog_ids = array_values( $top_ten_all_mu_tables );

		if ( isset( $_POST['tptn_delete_selected_tables'] ) ) {
			$top_ten_mu_tables_sel_blog_ids = array_intersect( $top_ten_mu_tables_sel_blog_ids, get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() ) );
		} else {
			$top_ten_mu_tables_sel_blog_ids = get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() );
		}

		if ( ! empty( $top_ten_mu_tables_sel_blog_ids ) ) {

			$sql = 'DROP TABLE ';
			foreach ( $top_ten_mu_tables_sel_blog_ids as $top_ten_mu_tables_sel_blog_id ) {
				$sql .= $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten, ';
				$sql .= $wpdb->base_prefix . $top_ten_mu_tables_sel_blog_id . '_top_ten_daily, ';
			}
			$sql = substr( $sql, 0, -2 );

			$wpdb->query( $sql );
			$str = '<div id="message" class="updated fade"><p>' . __( 'Selected tables have been deleted. Note that only imported tables have been deleted.', 'top-10' ) . '</p></div>';
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
	<div id="donatediv" class="postbox">
		<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'top-10' ); ?>">
			<br />
		</div>
		<h3 class='hndle'><span><?php esc_html_e( 'Support the development', 'top-10' ); ?></span></h3>
		<div class="inside">
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
					<input type="hidden" name="cmd" value="_xclick">
					<input type="hidden" name="business" value="donate@ajaydsouza.com">
					<input type="hidden" name="lc" value="IN">
					<input type="hidden" name="item_name" value="<?php esc_attr_e( 'Donation for Top 10', 'top-10' ); ?>">
					<input type="hidden" name="item_number" value="tptn_admin">
					<strong><?php esc_html_e( 'Enter amount in USD:', 'top-10' ); ?></strong>
					<input name="amount" value="10.00" size="6" type="text">
					<br />
					<input type="hidden" name="currency_code" value="USD">
					<input type="hidden" name="button_subtype" value="services">
					<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
					<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php esc_attr_e( 'Send your donation to the author of Top 10', 'top-10' ); ?>">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>
	</div>
	<div id="qlinksdiv" class="postbox">
		<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'top-10' ); ?>">
			<br />
		</div>
		<h3 class='hndle'><span><?php esc_html_e( 'Quick links', 'top-10' ); ?></span></h3>
		<div class="inside">
			<div id="quick-links">
				<ul>
					<li>
						<a href="https://webberzone.com/" target="_blank">
							WebberZone
						</a>
					</li>
					<li>
						<a href="https://webberzone.com/plugins/top-10/" target="_blank">
							<?php esc_html_e( 'Top 10 plugin page', 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://github.com/ajaydsouza/top-10" target="_blank">
							<?php esc_html_e( 'Top 10 Github page', 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://wordpress.org/plugins/top-10/faq/" target="_blank">
							<?php esc_html_e( 'FAQ', 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://wordpress.org/support/plugin/top-10" target="_blank">
							<?php esc_html_e( 'Support', 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://wordpress.org/support/view/plugin-reviews/top-10" target="_blank">
							<?php esc_html_e( 'Reviews', 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://ajaydsouza.com/" target="_blank">
							<?php esc_html_e( "Ajay's blog", 'top-10' ); ?>
						</a>
					</li>
					<li>
						<a href="https://webberzone.com/plugins/" target="_blank">
							<?php esc_html_e( 'Other plugins', 'top-10' ); ?>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="followdiv" class="postbox">
		<div class="handlediv" title="<?php esc_html_e( 'Click to toggle', 'top-10' ); ?>">
			<br />
		</div>
		<h3 class='hndle'><span><?php esc_html_e( 'Follow us', 'top-10' ); ?></span></h3>
		<div class="inside">
			<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( TOP_TEN_PLUGIN_URL . '/admin/images/fb.png' ); ?>" width="100" height="100" /></a>
			<a href="https://twitter.com/webberzonewp/" target="_blank"><img src="<?php echo esc_url( TOP_TEN_PLUGIN_URL . '/admin/images/twitter.jpg' ); ?>" width="100" height="100" /></a>
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

	$plugin_page = add_menu_page( __( 'Top 10 Settings', 'top-10' ), __( 'Top 10', 'top-10' ), 'manage_options', 'tptn_options', 'tptn_options', 'dashicons-editor-ol' );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( 'Top 10 Settings', 'top-10' ), __( 'Top 10 Settings', 'top-10' ), 'manage_options', 'tptn_options', 'tptn_options' );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

	$tptn_stats_screen = new Top_Ten_Statistics;

	$plugin_page = add_submenu_page( 'tptn_options', __( 'View Popular Posts', 'top-10' ), __( 'View Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$plugin_page", array( $tptn_stats_screen, 'screen_option' ) );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

	$plugin_page = add_submenu_page( 'tptn_options', __( 'Daily Popular Posts', 'top-10' ), __( 'Daily Popular Posts', 'top-10' ), 'manage_options', 'tptn_popular_posts&orderby=daily_count&order=desc', array( $tptn_stats_screen, 'plugin_settings_page' ) );
	add_action( "load-$plugin_page", array( $tptn_stats_screen, 'screen_option' ) );
	add_action( 'admin_head-' . $plugin_page, 'tptn_adminhead' );

}
add_action( 'admin_menu', 'tptn_adminmenu' );


/**
 * Add JS and CSS to admin header.
 *
 * @since	1.6
 */
function tptn_adminhead() {

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'plugin-install' );
	wp_enqueue_script( 'suggest' );

	add_thickbox();

?>
		<style type="text/css">
			.postbox .handlediv:before {
				right: 12px;
				font: 400 20px/1 dashicons;
				speak: none;
				display: inline-block;
				top: 0;
				position: relative;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				text-decoration: none!important;
				content: '\f142';
				padding: 8px 10px;
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
			jQuery(document).ready(function ($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('tptn_options');
			});

		    // Function to add auto suggest.
		    function setSuggest( id, taxonomy ) {
		        jQuery('#' + id).suggest("<?php echo admin_url( 'admin-ajax.php?action=ajax-tag-search&tax=' ); ?>" + taxonomy, {multiple:true, multipleSep: ","});
		    }

		    // Function check the form submission.
			function checkForm() {
				answer = true;
				if (siw && siw.selectingSomething)
					answer = false;
				return answer;
			} //

		    // Function to clear the cache.
			function clearCache() {
				/**** since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php ****/
				jQuery.post(ajaxurl, {
					action: 'tptn_clear_cache'
				}, function (response, textStatus, jqXHR) {
					alert(response.message);
				}, 'json');
			}
			//]]>
		</script>

		<?php
}


/**
 * Adding WordPress plugin action links.
 *
 * @version	1.9.2
 *
 * @param	array $links Action links.
 * @return	array	Links array with our settings link added.
 */
function tptn_plugin_actions_links( $links ) {

	return array_merge(
		array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=tptn_options' ) . '">' . __( 'Settings', 'top-10' ) . '</a>',
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
 * @param	array $links Action links.
 * @param	array $file Plugin file name.
 * @return	array	Links array with our links added
 */
function tptn_plugin_actions( $links, $file ) {
	$plugin = plugin_basename( TOP_TEN_PLUGIN_FILE );

	if ( $file == $plugin ) {
		$links[] = '<a href="https://wordpress.org/support/plugin/top-10/">' . __( 'Support', 'top-10' ) . '</a>';
		$links[] = '<a href="https://ajaydsouza.com/donate/">' . __( 'Donate', 'top-10' ) . '</a>';
		$links[] = '<a href="https://github.com/WebberZone/top-10">' . __( 'Contribute', 'contextual-related-posts' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'tptn_plugin_actions', 10, 2 );


/**
 * Function to delete all duplicate rows in the posts table.
 *
 * @since	1.6.2
 *
 * @param	bool $daily  Daily flag.
 */
function tptn_clean_duplicates( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
	}

	$wpdb->query( 'CREATE TEMPORARY TABLE ' . $table_name . '_temp AS SELECT * FROM ' . $table_name . ' GROUP BY postnumber' );
	$wpdb->query( "TRUNCATE TABLE $table_name" );
	$wpdb->query( 'INSERT INTO ' . $table_name . ' SELECT * FROM ' . $table_name . '_temp' );
}


/**
 * Function to merge counts with post numbers of blog ID 0 and 1 respectively.
 *
 * @since	2.0.4
 *
 * @param	bool $daily  Daily flag
 */
function tptn_merge_blogids( $daily = false ) {
	global $wpdb;

	$table_name = $wpdb->base_prefix . 'top_ten';
	if ( $daily ) {
		$table_name .= '_daily';
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
