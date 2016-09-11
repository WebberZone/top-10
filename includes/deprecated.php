<?php
/**
 * Deprecated functions and variables from Top 10. You shouldn't
 * use these functions or variables and look for the alternatives instead.
 * The functions will be removed in a later version.
 *
 * @package Top_Ten
 */

/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since	1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_path = plugin_dir_path( TOP_TEN_PLUGIN_FILE );


/**
 * Holds the URL for Top 10
 *
 * @since	1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_url = plugins_url() . '/' . plugin_basename( dirname( TOP_TEN_PLUGIN_FILE ) );


/**
 * Filter to add related posts to feeds.
 *
 * @since	1.9.8
 * @deprecated	2.2.0
 *
 * @param	string $content    Post content.
 * @return	string	Filtered post content
 */
function ald_tptn_rss( $content ) {

	_deprecated_function( __FUNCTION__, '2.2.0', 'tptn_rss_filter()' );

	return tptn_rss_filter( $content );
}


/**
 * Function to update the post views for the current post. Filters `the_content`.
 *
 * @since	1.0
 *
 * @deprecated 2.4.0
 *
 * @param	string $content Post content.
 */
function tptn_add_viewed_count( $content = '' ) {
	global $post, $tptn_settings;

	_deprecated_function( __FUNCTION__, '2.4.0' );

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

	_deprecated_function( __FUNCTION__, '2.4.0' );

	if ( $echo ) {
		echo tptn_add_viewed_count( '' );
	} else {
		return tptn_add_viewed_count( '' );
	}
}


