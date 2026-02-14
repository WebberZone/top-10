<?php
/**
 * Top Ten Query wrapper.
 *
 * @package WebberZone\Top_Ten
 */

use WebberZone\Top_Ten\Top_Ten_Core_Query;
use WebberZone\Top_Ten\Util\Hook_Registry;

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

			Hook_Registry::add_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ), 10 );
			Hook_Registry::add_filter( 'posts_fields', array( $core_query, 'posts_fields' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_join', array( $core_query, 'posts_join' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_where', array( $core_query, 'posts_where' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ), 10, 2 );
			Hook_Registry::add_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ), 20, 2 );
			Hook_Registry::add_filter( 'posts_request', array( $core_query, 'posts_request' ), 20, 2 );
			Hook_Registry::add_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ), 10, 2 );
			Hook_Registry::add_filter( 'the_posts', array( $core_query, 'the_posts' ), 10, 2 );
			Hook_Registry::add_action( 'the_post', array( $core_query, 'switch_to_blog_in_loop' ) );
			Hook_Registry::add_action( 'loop_end', array( $core_query, 'loop_end' ) );

			parent::__construct( $core_query->query_args );

			// Remove filters after use.
			Hook_Registry::remove_filter( 'pre_get_posts', array( $core_query, 'pre_get_posts' ) );
			Hook_Registry::remove_filter( 'posts_fields', array( $core_query, 'posts_fields' ) );
			Hook_Registry::remove_filter( 'posts_join', array( $core_query, 'posts_join' ) );
			Hook_Registry::remove_filter( 'posts_where', array( $core_query, 'posts_where' ) );
			Hook_Registry::remove_filter( 'posts_orderby', array( $core_query, 'posts_orderby' ) );
			Hook_Registry::remove_filter( 'posts_groupby', array( $core_query, 'posts_groupby' ) );
			Hook_Registry::remove_filter( 'posts_clauses', array( $core_query, 'posts_clauses' ) );
			Hook_Registry::remove_filter( 'posts_request', array( $core_query, 'posts_request' ) );
			Hook_Registry::remove_filter( 'posts_pre_query', array( $core_query, 'posts_pre_query' ) );
			Hook_Registry::remove_filter( 'the_posts', array( $core_query, 'the_posts' ) );
			Hook_Registry::remove_action( 'the_post', array( $core_query, 'switch_to_blog_in_loop' ) );
			Hook_Registry::remove_action( 'loop_end', array( $core_query, 'loop_end' ) );
		}
	}
endif;
