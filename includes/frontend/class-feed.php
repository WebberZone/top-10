<?php
/**
 * Functions to fetch and display the posts.
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Frontend;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Feed {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'pop_posts_feed' ) );
	}

	/**
	 * Add custom feeds for the overall and daily popular posts.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function pop_posts_feed() {

		$popular_posts_overall = \tptn_get_option( 'feed_permalink_overall' );
		$popular_posts_daily   = \tptn_get_option( 'feed_permalink_daily' );

		if ( ! empty( $popular_posts_overall ) ) {
			add_feed( $popular_posts_overall, array( $this, 'pop_posts_feed_overall' ) );
		}
		if ( ! empty( $popular_posts_daily ) ) {
			add_feed( $popular_posts_daily, array( $this, 'pop_posts_feed_daily' ) );
		}
	}

	/**
	 * Callback for overall popular posts.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function pop_posts_feed_overall() {
		$this->pop_posts_feed_callback( false );
	}

	/**
	 * Callback for daily popular posts.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function pop_posts_feed_daily() {
		$this->pop_posts_feed_callback( true );
	}

	/**
	 * Callback function for add_feed to locate the correct template.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $daily Daily posts flag.
	 *
	 * @return void
	 */
	public function pop_posts_feed_callback( $daily = false ) {

		set_query_var( 'daily', $daily );

		$template = locate_template( 'feed-rss2-popular-posts.php' );

		if ( ! $template ) {
			$template = TOP_TEN_PLUGIN_DIR . 'includes/frontend/feed-rss2-popular-posts.php';
		}

		load_template( $template );
	}
}
