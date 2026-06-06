<?php
/**
 * Tracker class.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Util\Helpers;
use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Tracker {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'parse_request', array( $this, 'parse_request' ) );
		Hook_Registry::add_filter( 'query_vars', array( $this, 'query_vars' ) );
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_tptn_tracker', array( $this, 'tracker_parser' ) );
		Hook_Registry::add_action( 'wp_ajax_tptn_tracker', array( $this, 'tracker_parser' ) );
	}

	/**
	 * Enqueues the scripts needed by Top 10.
	 *
	 * @since 1.9.7
	 * @return void
	 */
	public static function enqueue_scripts() {
		global $post, $ajax_tptn_tracker;

		if ( ! is_object( $post ) ) {
			return;
		}
		if ( 'draft' === $post->post_status || is_customize_preview() ) {
			return;
		}

		$track_users = wp_parse_list( \tptn_get_option( 'track_users' ) );
		$trackers    = wp_parse_list( \tptn_get_option( 'trackers' ) );

		if ( is_singular() || \tptn_get_option( 'tracker_all_pages' ) ) {

			$current_user        = wp_get_current_user();  // Let's get the current user.
			$post_author         = ( (int) $current_user->ID === (int) $post->post_author ) ? true : false; // Is the current user the post author?
			$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
			$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user an editor?
			$is_bot              = Helpers::is_bot();

			$include_code = true;
			if ( ( $post_author ) && ( ! in_array( 'authors', $track_users, true ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_admin ) && ( ! in_array( 'admins', $track_users, true ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_editor ) && ( ! in_array( 'editors', $track_users, true ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user->exists() ) && ( ! \tptn_get_option( 'logged_in' ) ) ) {
				$include_code = false;
			}
			if ( $is_bot && \tptn_get_option( 'no_bots' ) ) {
				$include_code = false;
			}

			if ( $include_code ) {

				$id               = is_singular() ? absint( $post->ID ) : 0;
				$blog_id          = get_current_blog_id();
				$activate_counter = in_array( 'overall', $trackers, true ) ? 1 : 0;     // It's 1 if we're updating the overall count.
				$activate_counter = $activate_counter + ( in_array( 'daily', $trackers, true ) ? 10 : 0 );  // It's 10 if we're updating the daily count.
				$top_ten_debug    = absint( \tptn_get_option( 'debug_mode' ) );
				$tracker_type     = \tptn_get_option( 'tracker_type' );

				switch ( $tracker_type ) {
					case 'query_based':
						$home_url = home_url( '/' );
						break;

					case 'ajaxurl':
						$home_url = admin_url( 'admin-ajax.php' );
						break;

					case 'rest_based':
						$home_url = rest_url( 'top-10/v1/tracker' );
						break;

					default:
						$home_url = rest_url( 'top-10/v1/tracker' );
						break;
				}

				/**
				 * Filter the URL of the tracker.
				 *
				 * Other tracker types can override the URL processed by the jQuery.post request
				 * The corresponding tracker can use the below variables or append their own to $ajax_tptn_tracker
				 *
				 * @since   2.0
				 *
				 * @param string $home_url URL of the tracker.
				 */
				$home_url = apply_filters( 'tptn_add_counter_script_url', $home_url );

				// Strip any query strings since we don't need them.
				$home_url = strtok( $home_url, '?' );

				$ajax_tptn_tracker = array(
					'ajax_url'         => $home_url,
					'top_ten_id'       => $id,
					'top_ten_blog_id'  => $blog_id,
					'activate_counter' => $activate_counter,
					'top_ten_debug'    => $top_ten_debug,
					'tptn_rnd'         => wp_rand( 1, time() ),
				);

				/**
				 * Filter the localize script arguments for the Top 10 tracker.
				 *
				 * @since 2.4.0
				 */
				$ajax_tptn_tracker = apply_filters( 'tptn_tracker_script_args', $ajax_tptn_tracker );

				wp_enqueue_script(
					'tptn_tracker',
					plugins_url( 'includes/js/top-10-tracker.min.js', TOP_TEN_PLUGIN_FILE ),
					array(),
					TOP_TEN_VERSION,
					true
				);

				wp_localize_script( 'tptn_tracker', 'ajax_tptn_tracker', $ajax_tptn_tracker );

			}
		}
	}

	/**
	 * Function to add additional queries to query_vars.
	 *
	 * @since   2.0.0
	 *
	 * @param   array $vars   Query variables array.
	 * @return  array Query variables array with Top 10 parameters appended
	 */
	public static function query_vars( $vars ) {
		// Add these to the list of queryvars that WP gathers.
		$vars[] = 'top_ten_id';
		$vars[] = 'top_ten_blog_id';
		$vars[] = 'activate_counter';
		$vars[] = 'view_counter';
		$vars[] = 'top_ten_debug';
		$vars[] = 'tptn_feed';

		/**
		 * Function to add additional queries to query_vars.
		 *
		 * @since   2.6.0
		 *
		 * @param array $vars Updated Query variables array with Top 10 queries added.
		 */
		return apply_filters( 'tptn_query_vars', $vars );
	}

	/**
	 * Parses the WordPress object to update/display the count.
	 *
	 * @since   2.0.0
	 *
	 * @param \WP $wp Current WordPress environment instance.
	 */
	public static function parse_request( $wp ) {

		if ( empty( $wp->query_vars['top_ten_id'] ) ) {
			return;
		}

		if ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'activate_counter', $wp->query_vars ) ) {

			$id               = absint( $wp->query_vars['top_ten_id'] );
			$blog_id          = absint( $wp->query_vars['top_ten_blog_id'] );
			$activate_counter = absint( $wp->query_vars['activate_counter'] );

			$is_feed = ! empty( $wp->query_vars['tptn_feed'] );
			$source  = $is_feed ? 1 : 0;

			$str = self::update_count( $id, $blog_id, $activate_counter, $source );

			if ( $is_feed ) {
				self::output_tracking_pixel(); // Sends GIF and exits.
			} else {
				// If the debug parameter is set then we output $str else we send a No Content header.
				if ( array_key_exists( 'top_ten_debug', $wp->query_vars ) && 1 === absint( $wp->query_vars['top_ten_debug'] ) ) {
					header( 'content-type: application/x-javascript' );
					wp_send_json( $str );
				} else {
					header( 'HTTP/1.0 204 No Content' );
					header( 'Cache-Control: max-age=15, s-maxage=0' );
				}

				// Stop anything else from loading as it is not needed.
				exit;
			}
		} elseif ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'view_counter', $wp->query_vars ) ) {

			$id = absint( $wp->query_vars['top_ten_id'] );

			if ( $id > 0 ) {

				$output = Counter::get_post_count( $id );

				header( 'content-type: application/x-javascript' );
				echo 'document.write("' . $output . '");'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				// Stop anything else from loading as it is not needed.
				exit;
			}
		} else {
			return;
		}
	}

	/**
	 * Add a tracking pixel to feed content.
	 *
	 * Appends a 1×1 transparent GIF to each feed item. When a feed reader
	 * loads the image, parse_request() intercepts the request, increments
	 * the view count, and serves the GIF. Views are merged into the same
	 * tables as regular web views.
	 *
	 * Note: feed readers that block remote images by default will not trigger
	 * the pixel. The count only increments when the reader actually loads images.
	 *
	 * @since 4.3.0
	 *
	 * @param string $content Feed content.
	 * @return string Feed content with the tracker image appended.
	 */
	public static function add_feed_tracker( $content ) {
		global $post;

		if (
			! \tptn_get_option( 'track_feed_views' ) ||
			! is_feed() ||
			! is_object( $post ) ||
			empty( $post->ID )
		) {
			return $content;
		}

		/*
		 * In full-text mode WordPress fires both the_excerpt_rss (for <description>)
		 * and the_content_feed (for <content:encoded>) per item. Skip the excerpt hook
		 * in that case so we don't double-count; the_content_feed will add the pixel.
		 * In excerpt-only mode the_content_feed never fires, so the_excerpt_rss handles it.
		 */
		if ( 'the_excerpt_rss' === current_filter() && ! get_option( 'rss_use_excerpt' ) ) {
			return $content;
		}

		$trackers         = wp_parse_list( \tptn_get_option( 'trackers' ) );
		$activate_counter = in_array( 'overall', $trackers, true ) ? 1 : 0;
		$activate_counter = $activate_counter + ( in_array( 'daily', $trackers, true ) ? 10 : 0 );

		if ( 0 === $activate_counter ) {
			return $content;
		}

		$tracker_url = add_query_arg(
			array(
				'top_ten_id'       => absint( $post->ID ),
				'top_ten_blog_id'  => get_current_blog_id(),
				'activate_counter' => $activate_counter,
				'tptn_feed'        => 1,
			),
			home_url( '/' )
		);

		$tracker = sprintf(
			'<img src="%1$s" width="1" height="1" alt="" style="display:none" />',
			esc_url( $tracker_url )
		);

		return $content . $tracker;
	}

	/**
	 * Output a transparent GIF for feed view tracking requests.
	 *
	 * @since 4.3.0
	 *
	 * @return void
	 */
	protected static function output_tracking_pixel() {
		$pixel = 'R0lGODlhAQABAIAAANvf7wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';

		header( 'Content-Type: image/gif' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
		echo base64_decode( $pixel ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode, WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Parse the ajax response.
	 *
	 * @since 2.4.0
	 */
	public static function tracker_parser() {

		$id               = isset( $_POST['top_ten_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$blog_id          = isset( $_POST['top_ten_blog_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_blog_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$activate_counter = isset( $_POST['activate_counter'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['activate_counter'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$top_ten_debug    = isset( $_POST['top_ten_debug'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_debug'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str = self::update_count( $id, $blog_id, $activate_counter, 0 );

		// If the debug parameter is set then we output $str else we send a No Content header.
		if ( 1 === $top_ten_debug ) {
			echo esc_html( $str );
		} else {
			header( 'HTTP/1.0 204 No Content' );
			header( 'Cache-Control: max-age=15, s-maxage=0' );
		}

		wp_die();
	}

	/**
	 * Function to update the count in the database.
	 *
	 * @since 2.6.0
	 *
	 * @param int $id Post ID.
	 * @param int $blog_id Blog ID.
	 * @param int $activate_counter Activate counter flag.
	 * @param int $source Traffic source: 0 = web, 1 = feed.
	 *
	 * @return string Response on database update.
	 */
	public static function update_count( $id, $blog_id, $activate_counter, $source = 0 ) {

		$str = '';

		/**
		 * Filter the flag to confirm that counts should be updated in the database.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $flag Flag to confirm that counts should be updated in the database.
		 * @param int $id      Post ID.
		 * @param int $blog_id Blog ID.
		 * @param int $activate_counter Activate counter flag.
		 * @param int $source  Traffic source: 0 = web, 1 = feed.
		 */
		$before_update_count = apply_filters( 'tptn_before_update_count', true, $id, $blog_id, $activate_counter, $source );

		if ( $id > 0 && $activate_counter > 0 && $before_update_count ) {
			$result = Database::append_to_funnel( $id, $blog_id, $activate_counter, $source );
			$str   .= ( false === $result ) ? 'loge' : 'log' . $result;
		}

		/**
		 * Filter the response on database update.
		 *
		 * @since 2.6.0
		 *
		 * @param string $str Response string.
		 * @param int $id Post ID.
		 * @param int $blog_id Blog ID.
		 * @param int $activate_counter Activate counter flag.
		 * @param int $source Traffic source: 0 = web, 1 = feed.
		 */
		return apply_filters( 'tptn_update_count', $str, $id, $blog_id, $activate_counter, $source );
	}
}
