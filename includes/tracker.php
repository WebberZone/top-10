<?php
/**
 * Functions controlling the tracker
 *
 * @package Top_Ten
 */

/**
 * Function to update the post views for the current post. Filters `the_content`.
 *
 * @since	1.0
 *
 * @deprecated 2.4.0
 *
 * @param	string $content Post content.
 * @return	string	Filtered content
 */
function tptn_add_viewed_count( $content = '' ) {
	global $post, $tptn_settings;

	$home_url = home_url( '/' );

	/**
	 * Filter the script URL of the counter.
	 *
	 * @since	2.0
	 */
	$home_url = apply_filters( 'tptn_add_counter_script_url', $home_url );

	// Strip any query strings since we don't need them.
	$home_url = strtok( $home_url, '?' );

	if ( is_singular() && 'draft' !== $post->post_status ) {

		$current_user = wp_get_current_user();	// Let's get the current user
		$post_author = ( $current_user->ID == $post->post_author ) ? true : false;	// Is the current user the post author?
		$current_user_admin = ( current_user_can( 'manage_options' ) ) ? true : false;	// Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;	// Is the current user an editor?

		$include_code = true;
		if ( ( $post_author ) && ( ! $tptn_settings['track_authors'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_admin ) && ( ! $tptn_settings['track_admins'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_editor ) && ( ! $tptn_settings['track_editors'] ) ) {
			$include_code = false;
		}

		if ( $include_code ) {

			$output = '';
			$id = intval( $post->ID );
			$blog_id = get_current_blog_id();
			$activate_counter = $tptn_settings['activate_overall'] ? 1 : 0;		// It's 1 if we're updating the overall count.
			$activate_counter = $activate_counter + ( $tptn_settings['activate_daily'] ? 10 : 0 );	// It's 10 if we're updating the daily count.

			if ( $activate_counter > 0 ) {
				if ( $tptn_settings['cache_fix'] ) {
					$output = '
					<script type="text/javascript"> jQuery(document).ready(function() {
						jQuery.ajax({
							url: "' . $home_url . '",
							data: {
								top_ten_id: ' . $id . ',
								top_ten_blog_id: ' . $blog_id . ',
								activate_counter: ' . $activate_counter . ',
								top10_rnd: (new Date()).getTime() + "-" + Math.floor(Math.random() * 100000)
							}
						});
					});</script>';

				} else {
					$output = '<script type="text/javascript" async src="' . $home_url . '?top_ten_id=' . $id . '&amp;top_ten_blog_id=' . $blog_id . '&amp;activate_counter=' . $activate_counter . '"></script>';
				}
			}

			/**
			 * Filter the counter script
			 *
			 * @since	1.9.8.5
			 *
			 * @param	string	$output	Counter script code
			 */
			$output = apply_filters( 'tptn_viewed_count', $output );

			echo $output;
		}
	}
}


/**
 * Enqueues the scripts needed by Top 10.
 *
 * @since 1.9.7
 * @return void
 */
function tptn_enqueue_scripts() {
	global $post, $tptn_settings;

	if ( $tptn_settings['cache_fix'] ) {
		wp_enqueue_script( 'jquery' );
	}

	if ( is_singular() && 'draft' !== $post->post_status ) {

		$current_user = wp_get_current_user();	// Let's get the current user
		$post_author = ( $current_user->ID == $post->post_author ) ? true : false;	// Is the current user the post author?
		$current_user_admin = ( current_user_can( 'manage_options' ) ) ? true : false;	// Is the current user an admin?
		$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;	// Is the current user an editor?

		$include_code = true;
		if ( ( $post_author ) && ( ! $tptn_settings['track_authors'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_admin ) && ( ! $tptn_settings['track_admins'] ) ) {
			$include_code = false;
		}
		if ( ( $current_user_editor ) && ( ! $tptn_settings['track_editors'] ) ) {
			$include_code = false;
		}

		if ( $include_code ) {

			$id = intval( $post->ID );
			$blog_id = get_current_blog_id();
			$activate_counter = $tptn_settings['activate_overall'] ? 1 : 0;		// It's 1 if we're updating the overall count.
			$activate_counter = $activate_counter + ( $tptn_settings['activate_daily'] ? 10 : 0 );	// It's 10 if we're updating the daily count.

			$home_url = home_url( '/' );

			/**
			 * Filter the script URL of the counter.
			 *
			 * @since	2.0
			 */
			$home_url = apply_filters( 'tptn_add_counter_script_url', $home_url );

			// Strip any query strings since we don't need them.
			$home_url = strtok( $home_url, '?' );

			// $home_url = admin_url( 'admin-ajax.php' );
			wp_enqueue_script( 'tptn_tracker', plugins_url( 'includes/js/top-10-tracker.js', TOP_TEN_PLUGIN_FILE ), array( 'jquery' ) );

			wp_localize_script( 'tptn_tracker', 'ajax_tptn_tracker', array(
					'ajax_url' => $home_url,
					'top_ten_nonce' => wp_create_nonce( 'tptn-tracker-nonce' ),
					'top_ten_id' => $id,
					'top_ten_blog_id' => $blog_id,
					'activate_counter' => $activate_counter,
					'tptn_rnd' => wp_rand( 1, time() ),
				)
			);

		}
	}

}
add_action( 'wp_enqueue_scripts', 'tptn_enqueue_scripts' );


/**
 * Function to add additional queries to query_vars.
 *
 * @since	2.0.0
 *
 * @param	array $vars   Query variables array.
 * @return	array	$Query variables array with Top 10 parameters appended
 */
function tptn_query_vars( $vars ) {
	// Add these to the list of queryvars that WP gathers.
	$vars[] = 'top_ten_id';
	$vars[] = 'top_ten_blog_id';
	$vars[] = 'activate_counter';
	$vars[] = 'view_counter';
	$vars[] = 'top_ten_debug';
	return $vars;
}
add_filter( 'query_vars', 'tptn_query_vars' );


/**
 * Parses the WordPress object to update/display the count.
 *
 * @since	2.0.0
 *
 * @param	object $wp WordPress object.
 */
function tptn_parse_request( $wp ) {
	global $wpdb;

	if ( empty( $wp ) ) {
		global $wp;
	}

	if ( ! isset( $wp->query_vars ) || ! is_array( $wp->query_vars ) ) {
		return;
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	$top_ten_daily = $wpdb->base_prefix . 'top_ten_daily';
	$str = '';

	if ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'activate_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );
		$blog_id = intval( $wp->query_vars['top_ten_blog_id'] );
		$activate_counter = intval( $wp->query_vars['activate_counter'] );

		if ( $id > 0 ) {

			if ( ( 1 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '1', '%d') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) );

				$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
			}

			if ( ( 10 == $activate_counter ) || ( 11 == $activate_counter ) ) {

				$current_date = gmdate( 'Y-m-d H', current_time( 'timestamp', 0 ) );

				$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$top_ten_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES('%d', '1', '%s', '%d' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) );

				$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
			}
		}

		// If the debug parameter is set then we output $str else we send a No Content header.
		if ( array_key_exists( 'top_ten_debug', $wp->query_vars ) && 1 === intval( $wp->query_vars['top_ten_debug'] ) ) {
			header( 'content-type: application/x-javascript' );
			echo esc_html( $str );
		} else {
			header( 'HTTP/1.0 204 No Content' );
			header( 'Cache-Control: max-age=15, s-maxage=0' );
		}

		// Stop anything else from loading as it is not needed.
		exit;

	} elseif ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'view_counter', $wp->query_vars ) && $wp->query_vars['top_ten_id'] != '' ) {

		$id = intval( $wp->query_vars['top_ten_id'] );

		if ( $id > 0 ) {

			$output = get_tptn_post_count( $id );

			header( 'content-type: application/x-javascript' );
			echo 'document.write("' . $output . '")';

			// stop anything else from loading as it is not needed.
			exit;
		}
	} else {
		return;
	}
}
add_action( 'parse_request', 'tptn_parse_request' );


/**
 * Parse the ajax response.
 *
 * @since 2.4.0
 */
function tptn_tracker_parser() {

	global $wpdb, $tptn_settings;

	// Check for the nonce and exit if failed.
	if ( isset( $_POST['top_ten_nonce'] ) && ! wp_verify_nonce( sanitize_key( $_POST['top_ten_nonce'] ), 'tptn-tracker-nonce' ) ) {
		wp_die( esc_html__( 'Top 10: Security check failed', 'top-10' ) );
	}

	$table_name = $wpdb->base_prefix . 'top_ten';
	$top_ten_daily = $wpdb->base_prefix . 'top_ten_daily';
	$str = '';

	$id = isset( $_POST['top_ten_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['top_ten_id'] ) ) ) : 0;
	$blog_id = isset( $_POST['top_ten_blog_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['top_ten_blog_id'] ) ) ) : 0;
	$activate_counter = isset( $_POST['activate_counter'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['activate_counter'] ) ) ) : 0;

	if ( $id > 0 ) {

		if ( ( 1 == $activate_counter ) || ( 11 == $activate_counter ) ) {

			$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES('%d', '1', '%d') ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) );

			$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
		}

		if ( ( 10 == $activate_counter ) || ( 11 == $activate_counter ) ) {

			$current_date = gmdate( 'Y-m-d H', current_time( 'timestamp', 0 ) );

			$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$top_ten_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES('%d', '1', '%s', '%d' ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) );

			$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
		}
	}

	echo esc_html( $str );

	wp_die();
}
add_action( 'wp_ajax_nopriv_tptn_tracker', 'tptn_tracker_parser' );
add_action( 'wp_ajax_tptn_tracker', 'tptn_tracker_parser' );


/**
 * Add tracker code.
 *
 * @since 2.3.0
 *
 * @deprecated 2.4.0
 *
 * @param bool $echo Echo the code or return it.
 * @return string|void
 */
function tptn_add_tracker( $echo = true ) {

	if ( $echo ) {
		echo tptn_add_viewed_count( '' );
	} else {
		return tptn_add_viewed_count( '' );
	}
}

