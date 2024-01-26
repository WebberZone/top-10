<?php
/**
 * Functions controlling the tracker
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Util\Helpers;

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
		add_action( 'parse_request', array( $this, 'parse_request' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_nopriv_tptn_tracker', array( $this, 'tracker_parser' ) );
		add_action( 'wp_ajax_tptn_tracker', array( $this, 'tracker_parser' ) );
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

		$track_users = \tptn_get_option( 'track_users' );
		$trackers    = \tptn_get_option( 'trackers' );

		if ( is_singular() || \tptn_get_option( 'tracker_all_pages' ) ) {

			$current_user        = wp_get_current_user();  // Let's get the current user.
			$post_author         = ( (int) $current_user->ID === (int) $post->post_author ) ? true : false; // Is the current user the post author?
			$current_user_admin  = ( current_user_can( 'manage_options' ) ) ? true : false;  // Is the current user an admin?
			$current_user_editor = ( ( current_user_can( 'edit_others_posts' ) ) && ( ! current_user_can( 'manage_options' ) ) ) ? true : false;    // Is the current user an editor?
			$is_bot              = Helpers::is_bot();

			$include_code = true;
			if ( ( $post_author ) && ( empty( $track_users['authors'] ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_admin ) && ( empty( $track_users['admins'] ) ) ) {
				$include_code = false;
			}
			if ( ( $current_user_editor ) && ( empty( $track_users['editors'] ) ) ) {
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
				$activate_counter = ! empty( $trackers['overall'] ) ? 1 : 0;     // It's 1 if we're updating the overall count.
				$activate_counter = $activate_counter + ( ! empty( $trackers['daily'] ) ? 10 : 0 );  // It's 10 if we're updating the daily count.
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

		if ( empty( $wp ) ) {
			global $wp;
		}

		if ( empty( $wp->query_vars['top_ten_id'] ) ) {
			return;
		}

		if ( array_key_exists( 'top_ten_id', $wp->query_vars ) && array_key_exists( 'activate_counter', $wp->query_vars ) ) {

			$id               = absint( $wp->query_vars['top_ten_id'] );
			$blog_id          = absint( $wp->query_vars['top_ten_blog_id'] );
			$activate_counter = absint( $wp->query_vars['activate_counter'] );

			$str = self::update_count( $id, $blog_id, $activate_counter );

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
	 * Parse the ajax response.
	 *
	 * @since 2.4.0
	 */
	public static function tracker_parser() {

		$id               = isset( $_POST['top_ten_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$blog_id          = isset( $_POST['top_ten_blog_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_blog_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$activate_counter = isset( $_POST['activate_counter'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['activate_counter'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$top_ten_debug    = isset( $_POST['top_ten_debug'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['top_ten_debug'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$str = self::update_count( $id, $blog_id, $activate_counter );

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
	 *
	 * @return string Response on database update.
	 */
	public static function update_count( $id, $blog_id, $activate_counter ) {

		global $wpdb;

		$table_name       = Helpers::get_tptn_table( false );
		$table_name_daily = Helpers::get_tptn_table( true );
		$str              = '';

		/**
		 * Filter the flag to confirm that counts should be updated in the database.
		 *
		 * @since 3.4.0
		 *
		 * @param bool $flag Flag to confirm that counts should be updated in the database.
		 * @param int $id      Post ID.
		 * @param int $blog_id Blog ID.
		 * @param int $activate_counter Activate counter flag.
		 */
		$before_update_count = apply_filters( 'tptn_before_update_count', true, $id, $blog_id, $activate_counter );

		if ( $id > 0 && $before_update_count ) {

			if ( ( 1 === $activate_counter ) || ( 11 === $activate_counter ) ) {

				$tt = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, '1',  %d ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				$str .= ( false === $tt ) ? 'tte' : 'tt' . $tt;
			}

			if ( ( 10 === $activate_counter ) || ( 11 === $activate_counter ) ) {

				$current_date = current_time( 'Y-m-d H' );

				$ttd = $wpdb->query( $wpdb->prepare( "INSERT INTO {$table_name_daily} (postnumber, cntaccess, dp_date, blog_id) VALUES( %d, '1',  %s,  %d ) ON DUPLICATE KEY UPDATE cntaccess= cntaccess+1 ", $id, $current_date, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				$str .= ( false === $ttd ) ? ' ttde' : ' ttd' . $ttd;
			}
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
		 */
		return apply_filters( 'tptn_update_count', $str, $id, $blog_id, $activate_counter );
	}
}
