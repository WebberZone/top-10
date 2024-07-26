<?php
/**
 * Query API: Top_Ten_Query class
 *
 * @package Top_Ten
 * @since 3.0.0
 */

use WebberZone\Top_Ten\Top_Ten_Core_Query;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Top_Ten_Query' ) ) :
	/**
	 * Query API: Top_Ten_Query class.
	 *
	 * @since 3.0.0
	 */
	class Top_Ten_Query extends WP_Query {

		/**
		 * Main constructor.
		 *
		 * @since 3.0.0
		 *
		 * @param array|string $args The Query variables. Accepts an array or a query string.
		 */
		public function __construct( $args = array() ) {
			$args       = wp_parse_args( $args, array( 'is_top_ten_query' => true ) );
			$core_query = new Top_Ten_Core_Query( $args );

			add_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ), 10 );
			add_filter( 'posts_fields', array( $core_query, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $core_query, 'posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( $core_query, 'posts_where' ), 10, 2 );
			add_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ), 10, 2 );
			add_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ), 20, 2 );
			add_filter( 'posts_request', array( $core_query, 'posts_request' ), 20, 2 );
			add_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ), 10, 2 );
			add_filter( 'the_posts', array( $core_query, 'the_posts' ), 10, 2 );
			add_action( 'the_post', array( $core_query, 'switch_to_blog_in_loop' ) );
			add_action( 'loop_end', array( $core_query, 'loop_end' ) );

			parent::__construct( $core_query->query_args );

			// Remove filters after use.
			remove_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ) );
			remove_filter( 'posts_fields', array( $core_query, 'posts_fields' ) );
			remove_filter( 'posts_join', array( $core_query, 'posts_join' ) );
			remove_filter( 'posts_where', array( $core_query, 'posts_where' ) );
			remove_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ) );
			remove_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ) );
			remove_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ) );
			remove_filter( 'posts_request', array( $core_query, 'posts_request' ) );
			remove_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ) );
			remove_filter( 'the_posts', array( $core_query, 'the_posts' ) );
			remove_action( 'the_post', array( $core_query, 'switch_to_blog_in_loop' ) );
			remove_action( 'loop_end', array( $core_query, 'loop_end' ) );
		}
	}
endif;
