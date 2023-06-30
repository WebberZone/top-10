<?php
/**
 * Functions controlling the counter/tracker
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten;

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
		add_filter( 'the_content', array( __CLASS__, 'the_content' ) );
		add_filter( 'the_excerpt_rss', array( __CLASS__, 'rss_filter' ) );
		add_filter( 'the_content_feed', array( __CLASS__, 'rss_filter' ) );
		add_action( 'wp_ajax_tptn_edit_count_ajax', array( __CLASS__, 'edit_count_ajax' ) );
	}

	/**
	 * Function to add the viewed count to the post content. Filters `the_content`.
	 *
	 * @since   1.0
	 * @param   string $content Post content.
	 * @return  string  Filtered post content
	 */
	public static function the_content( $content ) {
		global $post;

		$in_the_loop           = in_the_loop();
		$is_main_query         = is_main_query();
		$get_queried_object_id = get_queried_object_id();

		if ( ! ( in_the_loop() && is_main_query() && (int) get_queried_object_id() === (int) $post->ID ) ) {
			return $content;
		}

		$exclude_on_post_ids = explode( ',', tptn_get_option( 'exclude_on_post_ids' ) );
		$add_to              = tptn_get_option( 'add_to', false );

		if ( isset( $post ) ) {
			if ( in_array( $post->ID, $exclude_on_post_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				return $content;    // Exit without adding related posts.
			}
		}

		if ( ( is_single() ) && ! empty( $add_to['single'] ) ) {
			return $content . self::echo_post_count( 0 );
		} elseif ( ( is_page() ) && ! empty( $add_to['page'] ) ) {
			return $content . self::echo_post_count( 0 );
		} elseif ( ( is_home() ) && ! empty( $add_to['home'] ) ) {
			return $content . self::echo_post_count( 0 );
		} elseif ( ( is_category() ) && ! empty( $add_to['category_archives'] ) ) {
			return $content . self::echo_post_count( 0 );
		} elseif ( ( is_tag() ) && ! empty( $add_to['tag_archives'] ) ) {
			return $content . self::echo_post_count( 0 );
		} elseif ( ( ( is_tax() ) || ( is_author() ) || ( is_date() ) ) && ! empty( $add_to['other_archives'] ) ) {
			return $content . self::echo_post_count( 0 );
		} else {
			return $content;
		}
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

		$add_to = tptn_get_option( 'add_to', false );

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

		if ( tptn_get_option( 'dynamic_post_count' ) ) {
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
	 * @since   3.3.0
	 * @param   int|string $id         Post ID.
	 * @param   int|string $blog_id    Blog ID.
	 * @return  int|string  Formatted post count
	 */
	public static function get_post_count( $id = 0, $blog_id = 0 ) {

		$count_disp_form      = stripslashes( tptn_get_option( 'count_disp_form' ) );
		$count_disp_form_zero = stripslashes( tptn_get_option( 'count_disp_form_zero' ) );
		$totalcntaccess       = self::get_post_count_only( $id, 'total', $blog_id );

		if ( $id > 0 ) {

			// Total count per post.
			if ( ( false !== strpos( $count_disp_form, '%totalcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%totalcount%' ) ) ) {
				if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
					$count_disp_form_zero = str_replace( '%totalcount%', (string) $totalcntaccess, $count_disp_form_zero );
				} else {
					$count_disp_form = str_replace( '%totalcount%', strval( 0 === (int) $totalcntaccess ? $totalcntaccess + 1 : $totalcntaccess ), $count_disp_form );
				}
			}

			// Now process daily count.
			if ( ( false !== strpos( $count_disp_form, '%dailycount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%dailycount%' ) ) ) {
				$cntaccess = self::get_post_count_only( $id, 'daily' );
				if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
					$count_disp_form_zero = str_replace( '%dailycount%', (string) $cntaccess, $count_disp_form_zero );
				} else {
					$count_disp_form = str_replace( '%dailycount%', strval( 0 === (int) $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
				}
			}

			// Now process overall count.
			if ( ( false !== strpos( $count_disp_form, '%overallcount%' ) ) || ( false !== strpos( $count_disp_form_zero, '%overallcount%' ) ) ) {
				$cntaccess = self::get_post_count_only( $id, 'overall' );
				if ( ( 0 === (int) $cntaccess ) && ( ! is_singular() ) ) {
					$count_disp_form_zero = str_replace( '%overallcount%', (string) $cntaccess, $count_disp_form_zero );
				} else {
					$count_disp_form = str_replace( '%overallcount%', strval( 0 === (int) $cntaccess ? $cntaccess + 1 : $cntaccess ), $count_disp_form );
				}
			}

			if ( ( 0 === (int) $totalcntaccess ) && ( ! is_singular() ) ) {
				return apply_filters( 'tptn_post_count', $count_disp_form_zero );
			} else {
				return apply_filters( 'tptn_post_count', $count_disp_form );
			}
		} else {
			return 0;
		}
	}


	/**
	 * Returns the post count.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   int    $id     Post ID.
	 * @param   string $counter Which count to return? total, daily or overall.
	 * @param   int    $blog_id Blog ID.
	 * @return  int Post count
	 */
	public static function get_post_count_only( $id = 0, $counter = 'total', $blog_id = 0 ) {
		global $wpdb;

		$table_name       = Util\Helpers::get_tptn_table( false );
		$table_name_daily = Util\Helpers::get_tptn_table( true );

		if ( empty( $blog_id ) ) {
			$blog_id = get_current_blog_id();
		}

		if ( $id > 0 ) {
			$resultscount = false;
			switch ( $counter ) {
				case 'total':
					$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess as visits FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					break;
				case 'daily':
					$from_date = Util\Helpers::get_from_date();

					$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as visits FROM {$table_name_daily} WHERE postnumber = %d AND blog_id = %d AND dp_date >= %s GROUP BY postnumber ", array( $id, $blog_id, $from_date ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					break;
				case 'overall':
					$resultscount = $wpdb->get_row( 'SELECT SUM(cntaccess) as visits FROM ' . $table_name ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
					break;
			}

			$visits = $resultscount ? $resultscount->visits : 0;

			$post_count = (int) \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( $visits );

			/**
			 * Returns the post count.
			 *
			 * @since   2.6.0
			 *
			 * @param   int    $post_count Formatted post count.
			 * @param   mixed  $id         Post ID.
			 * @param   string $counter    Which count to return? total, daily or overall.
			 * @param   int    $blog_id    Blog ID.
			 */
			return apply_filters( 'tptn_post_count_only', $post_count, $id, $counter, $blog_id );
		} else {
			return 0;
		}
	}


	/**
	 * Delete the counts from the selected table.
	 *
	 * @since 3.2.0
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
		global $wpdb;

		$where = '';

		$defaults = array(
			'daily'   => true,
			'post_id' => '',
			'blog_id' => get_current_blog_id(),
			'dp_date' => '',
		);
		$args     = wp_parse_args( $args, $defaults );

		$table_name = Util\Helpers::get_tptn_table( $args['daily'] );

		// Parse which post_ids data should be deleted.
		$post_ids = wp_parse_id_list( $args['post_id'] );
		if ( ! empty( $post_ids ) ) {
			$where .= " AND {$table_name}.postnumber IN ('" . join( "', '", $post_ids ) . "') "; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Parse which blog_ids data should be deleted.
		$blog_ids = wp_parse_id_list( $args['blog_id'] );
		if ( ! empty( $blog_ids ) ) {
			$where .= " AND {$table_name}.blog_id IN ('" . join( "', '", $blog_ids ) . "') "; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// How old data should we delete?
		if ( $args['daily'] && ! empty( $args['dp_date'] ) ) {
			$from_date = Util\Helpers::get_from_date( $args['dp_date'], 0, 0 );

			$where .= $wpdb->prepare( " AND {$table_name}.dp_date <= %s ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		$result = $wpdb->query( "DELETE FROM {$table_name} WHERE 1=1 $where " ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $result;
	}


	/**
	 * Delete post count.
	 *
	 * @since 2.9.0
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
	 * @since 2.9.0
	 */
	public static function edit_count_ajax() {

		if ( ! isset( $_REQUEST['total_count'] ) || ! isset( $_REQUEST['post_id'] ) || ! isset( $_REQUEST['total_count_original'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_die();
		}

		$results = 0;

		$post_id              = absint( $_REQUEST['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$blog_id              = get_current_blog_id();
		$total_count          = absint( filter_var( $_REQUEST['total_count'], FILTER_SANITIZE_NUMBER_INT ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$total_count_original = absint( filter_var( $_REQUEST['total_count_original'], FILTER_SANITIZE_NUMBER_INT ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		// If our current user can't edit this post, bail.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die();
		}

		if ( $total_count_original !== $total_count ) {
			$results = self::edit_count( $post_id, $blog_id, $total_count );
		}
		echo wp_json_encode( $results );
		wp_die();
	}


	/**
	 * Function to edit the count.
	 *
	 * @since 2.9.0
	 *
	 * @param int $post_id Post ID.
	 * @param int $blog_id Blog ID.
	 * @param int $total_count Total count.
	 * @return bool|int Number of rows affected or false if error.
	 */
	public static function edit_count( $post_id, $blog_id, $total_count ) {

		global $wpdb;

		$post_id     = intval( $post_id );
		$blog_id     = intval( $blog_id );
		$total_count = intval( $total_count );

		if ( empty( $post_id ) || empty( $blog_id ) || empty( $total_count ) ) {
			return false;
		}

		$table_name = Util\Helpers::get_tptn_table( false );

		$results = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO {$table_name} (postnumber, cntaccess, blog_id) VALUES( %d, %d, %d ) ON DUPLICATE KEY UPDATE cntaccess= %d ", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$post_id,
				$total_count,
				$blog_id,
				$total_count
			)
		);

		return $results;
	}
}
