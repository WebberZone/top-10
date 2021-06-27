<?php
/**
 * Query API: Top_Ten_Query class
 *
 * @package Top_Ten
 * @subpackage Top_Ten_Query
 * @since 3.0.0
 */

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
		 * Query vars, before parsing.
		 *
		 * @since 3.0.2
		 * @var int[]
		 */
		public $blog_id;

		/**
		 * Cache set flag.
		 *
		 * @since 3.0.0
		 * @var bool
		 */
		public $in_cache = false;

		/**
		 * Daily flag.
		 *
		 * @since 3.0.0
		 * @var bool
		 */
		public $is_daily = false;

		/**
		 * Query vars, before parsing.
		 *
		 * @since 3.0.2
		 * @var array
		 */
		public $input_query_args = array();

		/**
		 * Query vars, after parsing.
		 *
		 * @since 3.0.0
		 * @var array
		 */
		public $query_args = array();

		/**
		 * Top Ten table name being queried.
		 *
		 * @since 3.0.0
		 * @var string
		 */
		public $table_name;

		/**
		 * Main constructor.
		 *
		 * @since 3.0.0
		 *
		 * @param array|string $args The Query variables. Accepts an array or a query string.
		 */
		public function __construct( $args = array() ) {
			$this->prepare_query_args( $args );

			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ), 10, 2 );
			add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );

			parent::__construct( $this->query_args );

			// Remove filters after use.
			remove_filter( 'posts_fields', array( $this, 'posts_fields' ) );
			remove_filter( 'posts_join', array( $this, 'posts_join' ) );
			remove_filter( 'posts_where', array( $this, 'posts_where' ) );
			remove_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
			remove_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
			remove_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ) );
			remove_filter( 'the_posts', array( $this, 'the_posts' ) );
		}

		/**
		 * Prepare the query variables.
		 *
		 * @since 3.0.0
		 * @see WP_Query::parse_query()
		 * @see tptn_get_registered_settings()
		 *
		 * @param string|array $args {
		 *     Optional. Array or string of Query parameters.
		 *
		 *     @type array|string  $blog_id          An array or comma-separated string of blog IDs.
		 *     @type bool          $daily            Set to true to get the daily/custom period posts. False for overall.
		 *     @type array|string  $include_cat_ids  An array or comma-separated string of category/custom taxonomy term_taxonomy_ids.
		 *     @type array|string  $include_post_ids An array or comma-separated string of post IDs.
		 *     @type bool          $offset           Offset the related posts returned by this number.
		 *     @type bool          $strict_limit     If this is set to false, then it will fetch 3x posts.
		 * }
		 */
		public function prepare_query_args( $args = array() ) {
			global $wpdb;
			$tptn_settings = tptn_get_settings();

			$defaults = array(
				'blog_id'          => get_current_blog_id(),
				'daily'            => false,
				'include_cat_ids'  => 0,
				'include_post_ids' => 0,
				'offset'           => 0,
				'strict_limit'     => true,
			);
			$defaults = array_merge( $defaults, $tptn_settings );
			$args     = wp_parse_args( $args, $defaults );

			// Set necessary variables.
			$args['top_ten_query']       = true;
			$args['suppress_filters']    = false;
			$args['ignore_sticky_posts'] = true;
			$args['no_found_rows']       = true;

			// Store query args before we manipulate them.
			$this->input_query_args = $args;

			if ( $args['daily'] ) {
				$this->table_name = $wpdb->base_prefix . 'top_ten_daily';
				$this->is_daily   = true;
			} else {
				$this->table_name = $wpdb->base_prefix . 'top_ten';
				$this->is_daily   = false;
			}

			// Set the number of posts to be retrieved.
			$args['posts_per_page'] = ( $args['strict_limit'] ) ? $args['limit'] : ( $args['limit'] * 3 );

			// If post_types is empty or contains a query string then use parse_str else consider it comma-separated.
			if ( ! empty( $args['post_types'] ) && is_array( $args['post_types'] ) ) {
				$post_types = $args['post_types'];
			} elseif ( ! empty( $args['post_types'] ) && false === strpos( $args['post_types'], '=' ) ) {
				$post_types = explode( ',', $args['post_types'] );
			} else {
				parse_str( $args['post_types'], $post_types );  // Save post types in $post_types variable.
			}

			// If post_types is empty or if we want all the post types.
			if ( empty( $post_types ) || 'all' === $args['post_types'] ) {
				$post_types = get_post_types(
					array(
						'public' => true,
					)
				);
			}

			/**
			 * Filter the post_types passed to the query.
			 *
			 * @since 2.2.0
			 * @since 3.0.0 Changed second argument from post ID to WP_Post object.
			 *
			 * @param array   $post_types  Array of post types to filter by.
			 * @param array   $args        Arguments array.
			 */
			$args['post_type'] = apply_filters( 'tptn_posts_post_types', $post_types, $args );

			// Parse the blog_id argument to get an array of IDs.
			$this->blog_id = wp_parse_id_list( $args['blog_id'] );

			// Tax Query.
			if ( ! empty( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) {
				$tax_query = $args['tax_query'];
			} else {
				$tax_query = array();
			}

			if ( ! empty( $args['include_cat_ids'] ) ) {
				$tax_query[] = array(
					'field'            => 'term_taxonomy_id',
					'terms'            => wp_parse_id_list( $args['include_cat_ids'] ),
					'include_children' => false,
				);
			}

			if ( ! empty( $args['exclude_categories'] ) ) {
				$tax_query[] = array(
					'field'            => 'term_taxonomy_id',
					'terms'            => wp_parse_id_list( $args['exclude_categories'] ),
					'operator'         => 'NOT IN',
					'include_children' => false,
				);
			}

			/**
			 * Filter the tax_query passed to the query.
			 *
			 * @since 3.0.0
			 *
			 * @param array   $tax_query   Array of tax_query parameters.
			 * @param array   $args        Arguments array.
			 */
			$tax_query = apply_filters( 'top_ten_query_tax_query', $tax_query, $args );

			// Add a relation key if more than one $tax_query.
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}

			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

			// Set date_query.
			$args['date_query'] = array(
				array(
					'after'     => $args['how_old'] ? tptn_get_from_date( null, $args['how_old'] + 1, 0 ) : '',
					'before'    => current_time( 'mysql' ),
					'inclusive' => true,
				),
			);

			// Set post_status.
			$args['post_status'] = empty( $args['post_status'] ) ? array( 'publish', 'inherit' ) : $args['post_status'];

			// Set post__not_in for WP_Query using exclude_post_ids.
			$exclude_post_ids = empty( $args['exclude_post_ids'] ) ? array() : wp_parse_id_list( $args['exclude_post_ids'] );

			/**
			 * Filter exclude post IDs array.
			 *
			 * @since 2.2.0
			 * @since 3.0.0 Added $args
			 *
			 * @param int[] $exclude_post_ids Array of post IDs.
			 * @param array $args             Arguments array.
			 */
			$exclude_post_ids = apply_filters( 'tptn_exclude_post_ids', $exclude_post_ids, $args );

			$args['post__not_in'] = $exclude_post_ids;

			// Unset what we don't need.
			unset( $args['title'] );
			unset( $args['title_daily'] );
			unset( $args['blank_output'] );
			unset( $args['blank_output_text'] );
			unset( $args['show_excerpt'] );
			unset( $args['excerpt_length'] );
			unset( $args['show_date'] );
			unset( $args['show_author'] );
			unset( $args['disp_list_count'] );
			unset( $args['title_length'] );
			unset( $args['link_new_window'] );
			unset( $args['link_nofollow'] );
			unset( $args['before_list'] );
			unset( $args['after_list'] );
			unset( $args['before_list_item'] );
			unset( $args['after_list_item'] );

			/**
			 * Filters the arguments of the query.
			 *
			 * @since 3.0.0
			 *
			 * @param array     $args The arguments of the query.
			 * @param Top_Ten_Query $this The Top_Ten_Query instance (passed by reference).
			 */
			$this->query_args = apply_filters_ref_array( 'top_ten_query_args', array( $args, &$this ) );
		}

		/**
		 * Modify the SELECT clause - posts_fields.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $fields  The SELECT clause of the query.
		 * @param WP_Query $query The WP_Query instance.
		 * @return string  Updated Fields
		 */
		public function posts_fields( $fields, $query ) {
			global $wpdb;

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $fields;
			}

			$_fields[] = "{$this->table_name}.postnumber";
			$_fields[] = $this->is_daily ? "SUM({$this->table_name}.cntaccess) as visits" : "{$this->table_name}.cntaccess as visits";

			$_fields = implode( ', ', $_fields );

			$fields .= ',' . $_fields;

			return $fields;
		}

		/**
		 * Modify the posts_join clause.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $join  The JOIN clause of the query.
		 * @param WP_Query $query The WP_Query instance.
		 * @return string  Updated JOIN
		 */
		public function posts_join( $join, $query ) {
			global $wpdb;

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $join;
			}

			$join .= " INNER JOIN {$this->table_name} ON {$this->table_name}.postnumber={$wpdb->posts}.ID ";

			return $join;
		}

		/**
		 * Modify the posts_where clause.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $where The WHERE clause of the query.
		 * @param WP_Query $query The WP_Query instance.
		 * @return string  Updated WHERE
		 */
		public function posts_where( $where, $query ) {
			global $wpdb;

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $where;
			}

			$where .= " AND {$this->table_name}.blog_id IN ('" . join( "', '", $this->blog_id ) . "') "; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( $this->is_daily ) {
				if ( isset( $this->query_args['from_date'] ) ) {
					$from_date = tptn_get_from_date( $this->query_args['from_date'], 0, 0 );
				} else {
					$from_date = tptn_get_from_date( null, $this->query_args['daily_range'], $this->query_args['hour_range'] );
				}
				$where .= $wpdb->prepare( " AND {$this->table_name}.dp_date >= %s ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				if ( isset( $this->query_args['to_date'] ) ) {
					$to_date = tptn_get_from_date( $this->query_args['to_date'], 0, 0 );
					$where  .= $wpdb->prepare( " AND {$this->table_name}.dp_date <= %s ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				}
			}

			return $where;
		}

		/**
		 * Modify the posts_orderby clause.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $orderby  The ORDER BY clause of the query.
		 * @param WP_Query $query    The WP_Query instance.
		 * @return string  Updated ORDER BY
		 */
		public function posts_orderby( $orderby, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $orderby;
			}

			// If orderby is set, then this was done intentionally and we don't make any modifications.
			if ( ! empty( $query->get( 'orderby' ) ) ) {
				return $orderby;
			}

			$orderby = ' visits DESC ';

			return $orderby;
		}

		/**
		 * Modify the posts_groupby clause.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $groupby  The GROUP BY clause of the query.
		 * @param WP_Query $query    The WP_Query instance.
		 * @return string  Updated GROUP BY
		 */
		public function posts_groupby( $groupby, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $groupby;
			}

			if ( $this->is_daily ) {
				$groupby = " {$this->table_name}.postnumber ";
			}

			return $groupby;
		}

		/**
		 * Filter posts_pre_query to allow caching to work.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $posts Array of post data.
		 * @param WP_Query $query The WP_Query instance.
		 * @return string  Updated Array of post objects.
		 */
		public function posts_pre_query( $posts, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $posts;
			}

			// Check the cache if there are any posts saved.
			if ( ! empty( $this->query_args['cache_posts'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {

				$cache_name = tptn_cache_get_key( $this->input_query_args );

				$post_ids = get_transient( $cache_name );

				if ( ! empty( $post_ids ) ) {
					$posts                = get_posts(
						array(
							'post__in'  => $post_ids,
							'fields'    => $query->get( 'fields' ),
							'orderby'   => 'post__in',
							'post_type' => $query->get( 'post_type' ),
						)
					);
					$query->found_posts   = count( $posts );
					$query->max_num_pages = ceil( $query->found_posts / $query->get( 'posts_per_page' ) );
					$this->in_cache       = true;
				}
			}

			return $posts;
		}

		/**
		 * Modify the array of retrieved posts.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Post[] $posts Array of post objects.
		 * @param WP_Query  $query The WP_Query instance (passed by reference).
		 * @return string  Updated Array of post objects.
		 */
		public function the_posts( $posts, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $posts;
			}

			// Support caching to speed up retrieval.
			if ( ! empty( $this->query_args['cache_posts'] ) && ! $this->in_cache && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
				/** This filter is defined in display-posts.php */
				$cache_time = apply_filters( 'tptn_cache_time', $this->query_args['cache_time'], $this->query_args );
				$cache_name = tptn_cache_get_key( $this->input_query_args );
				$post_ids   = wp_list_pluck( $query->posts, 'ID' );

				set_transient( $cache_name, $post_ids, $cache_time );
			}

			if ( ! empty( $this->query_args['include_post_ids'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$include_post_ids = wp_parse_id_list( $this->query_args['include_post_ids'] );
			}
			if ( ! empty( $include_post_ids ) ) {
				$extra_posts = get_posts(
					array(
						'post__in'  => $include_post_ids,
						'fields'    => $query->get( 'fields' ),
						'orderby'   => 'post__in',
						'post_type' => $query->get( 'post_type' ),
					)
				);
				$posts       = array_merge( $extra_posts, $posts );
			}

			// Shuffle posts if random order is set.
			if ( $this->random_order ) {
				shuffle( $posts );
			}

			/**
			 * Filter array of WP_Post objects before it is returned to the Top_Ten_Query instance.
			 *
			 * @since 3.0.0
			 *
			 * @param WP_Post[] $posts Array of post objects.
			 * @param array     $args  Arguments array.
			 */
			return apply_filters( 'top_ten_query_the_posts', $posts, $this->query_args );
		}
	}
endif;
