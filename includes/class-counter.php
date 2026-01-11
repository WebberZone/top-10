<?php
/**
 * Counter class.
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
class Counter {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		Hook_Registry::add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
		Hook_Registry::add_filter( 'the_excerpt_rss', array( __CLASS__, 'rss_filter' ) );
		Hook_Registry::add_filter( 'the_content_feed', array( __CLASS__, 'rss_filter' ) );
		Hook_Registry::add_action( 'wp_ajax_tptn_edit_count_ajax', array( __CLASS__, 'edit_count_ajax' ) );
	}

	/**
	 * Adds the viewed count to the post content. Filters `the_content`.
	 *
	 * @since 3.3.0
	 *
	 * @param   string $content Post content.
	 * @return  string Filtered post content.
	 */
	public static function the_content( $content ) {
		global $post, $wp_filters;

		// Track the number of times this function is called.
		static $filter_calls = 0;
		++$filter_calls;

		// Check if this is the last call of 'the_content' and only process for the main query.
		if ( ! ( in_the_loop() && is_main_query() && (int) get_queried_object_id() === (int) $post->ID ) || ( doing_filter( 'the_content' ) && isset( $wp_filters['the_content'] ) && (int) $wp_filters['the_content'] !== $filter_calls ) ) {
			return $content;
		}

		// Exclude posts that should not display the viewed count.
		$exclude_on_post_ids = explode( ',', \tptn_get_option( 'exclude_on_post_ids' ) );
		if ( isset( $post ) && in_array( $post->ID, $exclude_on_post_ids, true ) ) {
			return $content;
		}

		// Determine where to add the viewed count.
		$add_to = wp_parse_list( \tptn_get_option( 'add_to', array() ) );

		$conditions = array(
			'single'            => is_single(),
			'page'              => is_page(),
			'home'              => is_home(),
			'category_archives' => is_category(),
			'tag_archives'      => is_tag(),
			'other_archives'    => is_tax() || is_author() || is_date(),
		);

		foreach ( $conditions as $key => $condition ) {
			if ( $condition && in_array( $key, $add_to, true ) ) {
				return $content . self::echo_post_count( 0 );
			}
		}

		return $content;
	}


	/**
	 * Filter to display the post count when viewing feeds.
	 *
	 * @since   1.9.8
	 *
	 * @param   string $content    Post content.
	 * @return  string  Filtered post content
	 */
	public static function rss_filter( $content ) {
		global $post;

		$id = intval( $post->ID );

		$add_to = \tptn_get_option( 'add_to', false );

		if ( ! empty( $add_to['feed'] ) ) {
			return $content . '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . self::get_post_count( $id ) . '</div>';
		} else {
			return $content;
		}
	}

	/**
	 * Function to manually display count.
	 *
	 * @since   1.0
	 * @param   int|boolean $echo_output Flag to echo the output.
	 * @return  string|void  Formatted string if $echo_output is set to 0|false
	 */
	public static function echo_post_count( $echo_output = 1 ) {
		global $post;

		$home_url = home_url( '/' );

		/**
		 * Filter the script URL of the counter.
		 *
		 * @since   2.0
		 */
		$home_url = apply_filters( 'tptn_view_counter_script_url', $home_url );

		// Strip any query strings since we don't need them.
		$home_url = strtok( $home_url, '?' );

		$id = intval( $post->ID );

		$nonce_action = 'tptn-nonce-' . $id;
		$nonce        = wp_create_nonce( $nonce_action );

		if ( \tptn_get_option( 'dynamic_post_count' ) ) {
			$output = sprintf(
				'<div class="tptn_counter" id="tptn_counter_%1$d"><script type="text/javascript" data-cfasync="false" src="%2$s?top_ten_id=%1$d&view_counter=1&_wpnonce=%3$s"></script></div>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				$id,
				$home_url,
				$nonce
			);
		} else {
			$output = '<div class="tptn_counter" id="tptn_counter_' . $id . '">' . self::get_post_count( $id ) . '</div>';
		}

		/**
		 * Filter the viewed count script
		 *
		 * @since   2.0.0
		 *
		 * @param   string  $output Counter viewed count code
		 */
		$output = apply_filters( 'tptn_view_post_count', $output );

		if ( $echo_output ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $output;
		}
	}


	/**
	 * Return the formatted post count for the supplied ID.
	 *
	 * @since  3.3.0
	 * @since  4.0.0 Added $args parameter.
	 *
	 * @param   int|string|\WP_Post $post       Post ID or WP_Post object.
	 * @param   int|string          $blog_id    Blog ID.
	 * @param   array               $args       Additional arguments.
	 * @return  string  Formatted post count
	 */
	public static function get_post_count( $post = 0, $blog_id = 0, $args = array() ) {
		if ( $post instanceof \WP_Post ) {
			$id = $post->ID;
		} else {
			$id = absint( $post );
		}

		if ( $id <= 0 ) {
			return '';
		}

		$count_disp_form      = isset( $args['count_disp_form'] ) ? $args['count_disp_form'] : stripslashes( \tptn_get_option( 'count_disp_form' ) );
		$count_disp_form_zero = isset( $args['count_disp_form_zero'] ) ? $args['count_disp_form_zero'] : stripslashes( \tptn_get_option( 'count_disp_form_zero' ) );
		$total_count          = self::get_post_count_only( $id, 'total', $blog_id );
		$is_singular          = is_singular();
		$is_zero_total_count  = ( 0 === (int) $total_count );

		if ( $is_zero_total_count && ! $is_singular ) {
			$count_disp_form_zero = str_replace(
				'%totalcount%',
				(string) $total_count,
				$count_disp_form_zero
			);
		} else {
			$count_disp_form = str_replace(
				'%totalcount%',
				Helpers::number_format_i18n( $is_zero_total_count ? $total_count + 1 : $total_count ),
				$count_disp_form
			);
		}

		foreach ( array( 'daily', 'overall' ) as $type ) {
			if ( false !== strpos( $count_disp_form, "%{$type}count%" ) || false !== strpos( $count_disp_form_zero, "%{$type}count%" ) ) {
				$count = self::get_post_count_only( $id, $type );
				if ( $is_zero_total_count && ! $is_singular ) {
					$count_disp_form_zero = str_replace(
						"%{$type}count%",
						(string) $count,
						$count_disp_form_zero
					);
				} else {
					$is_zero_cntaccess = ( 0 === (int) $count );
					$count_disp_form   = str_replace(
						"%{$type}count%",
						Helpers::number_format_i18n( $is_zero_cntaccess ? $count + 1 : $count ),
						$count_disp_form
					);
				}
			}
		}

		return apply_filters( 'tptn_post_count', ( $is_zero_total_count && ! $is_singular ) ? $count_disp_form_zero : $count_disp_form );
	}

	/**
	 * Returns the post count.
	 *
	 * @since 3.3.0
	 *
	 * @param   int|\WP_Post $post    Post ID or WP_Post object.
	 * @param   string       $counter Which count to return? total, daily or overall.
	 * @param   int          $blog_id Blog ID.
	 * @param   array        $args    Additional arguments.
	 * @return  int|string Post count
	 */
	public static function get_post_count_only( $post = 0, $counter = 'total', $blog_id = 0, $args = array() ) {
		global $wpdb;

		if ( $post instanceof \WP_Post ) {
			$id = $post->ID;
		} else {
			$id = absint( $post );
		}

		$defaults = array(
			'format_number' => false,
			'from_date'     => '',
			'to_date'       => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		// Sanitize the attributes.
		$args = Helpers::sanitize_args( $args );

		if ( empty( $blog_id ) ) {
			$blog_id = get_current_blog_id();
		}

		if ( $id > 0 || 'overall' === $counter ) {
			$post_count = 0;
			switch ( $counter ) {
				case 'total':
					$post_count = Database::get_count( $id, $blog_id, false );
					break;
				case 'daily':
					$date_range = array();
					if ( ! empty( $args['from_date'] ) ) {
						$date_range['from_date'] = Helpers::get_from_date( $args['from_date'], 0, 0 );
					}
					if ( ! empty( $args['to_date'] ) ) {
						$date_range['to_date'] = Helpers::get_from_date( $args['to_date'], 0, 0 );
					}
					if ( empty( $date_range ) ) {
						$date_range['from_date'] = Helpers::get_from_date();
					}
					$post_count = Database::get_count( $id, $blog_id, true, $date_range );
					break;
				case 'overall':
					$post_count = Database::get_total_count( $blog_id, false );
					break;
			}
			if ( $args['format_number'] ) {
				$post_count = number_format_i18n( $post_count );
			}

			/**
			 * Returns the post count.
			 *
			 * @since   2.6.0
			 *
			 * @param   int|string  $post_count Post count.
			 * @param   mixed       $id         Post ID.
			 * @param   string      $counter    Which count to return? total, daily or overall.
			 * @param   int         $blog_id    Blog ID.
			 */
			return apply_filters( 'tptn_post_count_only', $post_count, $id, $counter, $blog_id );
		} else {
			return 0;
		}
	}


	/**
	 * Delete the counts from the selected table.
	 *
	 * @since 3.3.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type bool          $daily    Set to true for daily table. False for overall.
	 *     @type array|string  $post_id  An array or comma-separated string of post IDs. Empty string or array for all IDs. Default is blank.
	 *     @type array|string  $blog_id  An array or comma-separated string of blog IDs. Empty string or array for all IDs. Default is current blog ID.
	 *     @type string        $db_date  The date before which to delete data. Default is empty string which means all dates. Applies to daily table only.
	 * }
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function delete_counts( $args = array() ) {
		$defaults = array(
			'daily'   => true,
			'post_id' => '',
			'blog_id' => get_current_blog_id(),
			'dp_date' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		// Convert the arguments to match Database class format.
		$db_args = array(
			'daily'    => $args['daily'],
			'post_ids' => wp_parse_id_list( $args['post_id'] ),
			'to_date'  => $args['dp_date'],
		);

		// Handle blog_id conversion.
		if ( ! empty( $args['blog_id'] ) && is_array( $args['blog_id'] ) ) {
			// If multiple blog IDs, we need to delete for each one.
			$result = 0;
			foreach ( $args['blog_id'] as $blog_id ) {
				$db_args['blog_id'] = $blog_id;
				$result            += Database::delete_counts( $db_args );
			}
			return $result;
		} else {
			$db_args['blog_id'] = is_array( $args['blog_id'] ) ? reset( $args['blog_id'] ) : $args['blog_id'];
			return Database::delete_counts( $db_args );
		}
	}


	/**
	 * Delete post count.
	 *
	 * @since 3.3.0
	 *
	 * @param int  $post_id Post ID.
	 * @param int  $blog_id Blog ID.
	 * @param bool $daily   Daily flag.
	 * @return bool|int Number of rows affected or false if error.
	 */
	public static function delete_count( $post_id, $blog_id, $daily = false ) {
		global $wpdb;

		$post_id = intval( $post_id );
		$blog_id = intval( $blog_id );

		if ( empty( $post_id ) || empty( $blog_id ) ) {
			return false;
		}

		$results = self::delete_counts(
			array(
				'post_id' => $post_id,
				'blog_id' => $blog_id,
				'daily'   => $daily,
			)
		);

		return $results;
	}


	/**
	 * Function to update the Top 10 count with ajax.
	 *
	 * @since 3.3.0
	 */
	public static function edit_count_ajax() {
		// Security check.
		check_ajax_referer( 'top_ten_admin_nonce', 'top_ten_admin_nonce' );

		if ( ! isset( $_REQUEST['total_count'] ) || ! isset( $_REQUEST['post_id'] ) || ! isset( $_REQUEST['total_count_original'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_die();
		}

		$results = 0;

		$post_id              = absint( $_REQUEST['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$blog_id              = isset( $_REQUEST['blog_id'] ) ? absint( $_REQUEST['blog_id'] ) : get_current_blog_id(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$total_count          = absint( filter_var( $_REQUEST['total_count'], FILTER_SANITIZE_NUMBER_INT ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$total_count_original = absint( filter_var( $_REQUEST['total_count_original'], FILTER_SANITIZE_NUMBER_INT ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		// Check permissions.
		if ( isset( $_REQUEST['blog_id'] ) && absint( $_REQUEST['blog_id'] ) !== get_current_blog_id() ) {
			// In network mode (editing a different blog's data), require manage_network capability.
			if ( ! current_user_can( 'manage_network' ) ) {
				wp_die();
			}
		} else {
			// Switch to the target blog to check edit permissions.
			$target_blog_id = isset( $_REQUEST['blog_id'] ) ? absint( $_REQUEST['blog_id'] ) : get_current_blog_id();

			if ( get_current_blog_id() !== $target_blog_id ) {
				switch_to_blog( $target_blog_id );
			}

			$can_edit = current_user_can( 'edit_post', $post_id );

			// Switch back if we switched.
			if ( get_current_blog_id() !== $target_blog_id ) {
				restore_current_blog();
			}

			if ( ! $can_edit ) {
				wp_die();
			}
		}

		if ( $total_count_original !== $total_count ) {
			$results = self::edit_count( $post_id, $blog_id, $total_count );
		}
		echo wp_json_encode( $results );
		wp_die();
	}

	/**
	 * Edit post count.
	 *
	 * @since 3.3.0
	 *
	 * @param int $post_id Post ID.
	 * @param int $blog_id Blog ID.
	 * @param int $total_count Total count.
	 * @return bool|int Number of rows affected or false if error.
	 */
	public static function edit_count( $post_id, $blog_id, $total_count ) {

		$post_id     = intval( $post_id );
		$blog_id     = intval( $blog_id );
		$total_count = intval( $total_count );

		if ( empty( $post_id ) || empty( $blog_id ) || empty( $total_count ) ) {
			return false;
		}

		return Database::set_count( $post_id, $total_count, $blog_id, false );
	}
}
