<?php
/**
 * Query API: Top_Ten_Query class
 *
 * @package Top_Ten
 * @subpackage Top_Ten_Query
 * @since 3.0.0
 */

use WebberZone\Top_Ten\Util\Helpers;

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
		 * Array of blog IDs.
		 *
		 * @since 3.0.2
		 * @var int[]
		 */
		public $blog_id;

		/**
		 * Flag to indicate if multiple blogs are being queried.
		 *
		 * @since 3.2.0
		 * @var bool
		 */
		public $multiple_blogs = false;

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
		 * Stores the SELECT clauses in WordPress multisite.
		 *
		 * @since 3.0.0
		 * @var array
		 */
		public $ms_select;

		/**
		 * Random order flag.
		 *
		 * @since 3.3.0
		 * @var bool
		 */
		public $random_order = false;

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
			add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 20, 2 );
			add_filter( 'posts_request', array( $this, 'posts_request' ), 20, 2 );
			add_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ), 10, 2 );
			add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
			add_action( 'the_post', array( $this, 'switch_to_blog_in_loop' ) );
			add_action( 'loop_end', array( $this, 'loop_end' ) );

			parent::__construct( $this->query_args );

			// Remove filters after use.
			remove_filter( 'posts_fields', array( $this, 'posts_fields' ) );
			remove_filter( 'posts_join', array( $this, 'posts_join' ) );
			remove_filter( 'posts_where', array( $this, 'posts_where' ) );
			remove_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
			remove_filter( 'posts_groupby', array( $this, 'posts_groupby' ) );
			remove_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 20 );
			remove_filter( 'posts_request', array( $this, 'posts_request' ), 20 );
			remove_filter( 'posts_pre_query', array( $this, 'posts_pre_query' ) );
			remove_filter( 'the_posts', array( $this, 'the_posts' ) );
			remove_action( 'the_post', array( $this, 'the_post' ) );
			remove_action( 'loop_end', array( $this, 'loop_end' ) );
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
			$args     = Helpers::parse_wp_query_arguments( $args );

			// Set necessary variables.
			$args['top_ten_query']       = true;
			$args['suppress_filters']    = false;
			$args['ignore_sticky_posts'] = true;
			$args['no_found_rows']       = true;

			// Store query args before we manipulate them.
			$this->input_query_args = $args;

			$this->table_name = Helpers::get_tptn_table( $args['daily'] );
			$this->is_daily   = $args['daily'];

			// Parse the blog_id argument to get an array of IDs.
			$this->blog_id = wp_parse_id_list( $args['blog_id'] );
			if ( is_multisite() ) {
				if ( count( $this->blog_id ) > 1 || ( 1 === count( $this->blog_id ) && get_current_blog_id() !== (int) $this->blog_id[0] ) ) {
					$this->multiple_blogs = true;
				}
			}

			// Set the number of posts to be retrieved.
			if ( empty( $args['posts_per_page'] ) ) {
				$args['posts_per_page'] = ( $args['strict_limit'] ) ? $args['limit'] : ( $args['limit'] * 3 );
			}

			if ( empty( $args['post_type'] ) ) {

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
				 * @since 3.0.0 Changed second argument from post ID to $args.
				 *
				 * @param array   $post_types  Array of post types to filter by.
				 * @param array   $args        Arguments array.
				 */
				$args['post_type'] = apply_filters( 'tptn_posts_post_types', $post_types, $args );

			}

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
				/**
				 * Filter the tax_query relation parameter.
				 *
				 * @since 3.2.0
				 *
				 * @param string  $relation The logical relationship between each inner taxonomy array when there is more than one. Default is 'AND'.
				 * @param array   $args     Arguments array.
				 */
				$tax_query['relation'] = apply_filters( 'top_ten_query_tax_query_relation', 'AND', $args );
			}

			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

			// Set date_query.
			$date_query = array(
				array(
					'after'     => $args['how_old'] ? Helpers::get_from_date( null, $args['how_old'] + 1, 0 ) : '',
					'before'    => current_time( 'mysql' ),
					'inclusive' => true,
				),
			);

			/**
			 * Filter the date_query passed to WP_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param array   $date_query Array of date parameters to be passed to WP_Query.
			 * @param array   $args       Arguments array.
			 */
			$args['date_query'] = apply_filters( 'top_ten_query_date_query', $date_query, $args );

			// Meta Query.
			if ( ! empty( $args['meta_query'] ) && is_array( $args['meta_query'] ) ) {
				$meta_query = $args['meta_query'];
			} else {
				$meta_query = array();
			}

			/**
			 * Filter the meta_query passed to WP_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param array   $meta_query Array of meta_query parameters.
			 * @param array   $args       Arguments array.
			 */
			$meta_query = apply_filters( 'top_ten_query_meta_query', $meta_query, $args ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query

			// Add a relation key if more than one $meta_query.
			if ( count( $meta_query ) > 1 ) {
				/**
				 * Filter the meta_query relation parameter.
				 *
				 * @since 3.2.0
				 *
				 * @param string  $relation The logical relationship between each inner meta_query array when there is more than one. Default is 'AND'.
				 * @param array   $args     Arguments array.
				 */
				$meta_query['relation'] = apply_filters( 'top_ten_query_meta_query_relation', 'AND', $args );
			}

			// Set post_status.
			$args['post_status'] = empty( $args['post_status'] ) ? array( 'publish', 'inherit' ) : $args['post_status'];

			// Set post__not_in for WP_Query using exclude_post_ids.
			$exclude_post_ids = empty( $args['exclude_post_ids'] ) ? array() : wp_parse_id_list( $args['exclude_post_ids'] );

			if ( ! empty( $args['exclude_current_post'] ) ) {
				array_push( $exclude_post_ids, absint( get_the_ID() ) );
			}

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

			$args['post__not_in'] = array_filter( (array) $exclude_post_ids );

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
			 * @param array         $args  The arguments of the query.
			 * @param Top_Ten_Query $query The Top_Ten_Query instance (passed by reference).
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
			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $fields;
			}

			$_fields[] = "{$this->table_name}.postnumber";
			$_fields[] = $this->is_daily ? "SUM({$this->table_name}.cntaccess) as visits" : "{$this->table_name}.cntaccess as visits";
			$_fields[] = "{$this->table_name}.blog_id";

			$_fields = implode( ', ', $_fields );

			$fields .= ',' . $_fields;

			/**
			 * Filters the fields returned by the SELECT clause.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $fields The fields returned by the SELECT clause.
			 * @param Top_Ten_Query $query  The Top_Ten_Query instance (passed by reference).
			 */
			$fields = apply_filters_ref_array( 'top_ten_query_posts_fields', array( $fields, &$this ) );

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

			/**
			 * Filters the JOIN clause of Top_Ten_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $join  The JOIN clause of the Query.
			 * @param Top_Ten_Query $query The Top_Ten_Query instance (passed by reference).
			 */
			$join = apply_filters_ref_array( 'top_ten_query_posts_join', array( $join, &$this ) );

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

			if ( ! $this->multiple_blogs ) {
				$where .= " AND {$this->table_name}.blog_id IN ('" . join( "', '", $this->blog_id ) . "') "; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( $this->is_daily ) {
				if ( isset( $this->query_args['from_date'] ) ) {
					$from_date = Helpers::get_from_date( $this->query_args['from_date'], 0, 0 );
				} else {
					$from_date = Helpers::get_from_date( null, $this->query_args['daily_range'], $this->query_args['hour_range'] );
				}
				$where .= $wpdb->prepare( " AND {$this->table_name}.dp_date >= %s ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				if ( isset( $this->query_args['to_date'] ) ) {
					$to_date = Helpers::get_from_date( $this->query_args['to_date'], 0, 0 );
					$where  .= $wpdb->prepare( " AND {$this->table_name}.dp_date <= %s ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				}
			}

			/**
			 * Filters the WHERE clause of Top_Ten_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $where  The WHERE clause of the Query.
			 * @param Top_Ten_Query $query  The Top_Ten_Query instance (passed by reference).
			 */
			$where = apply_filters_ref_array( 'top_ten_query_posts_where', array( $where, &$this ) );

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
			global $wpdb;

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $orderby;
			}

			// If orderby is set, then this was done intentionally and we don't make any modifications.
			if ( ! empty( $query->get( 'orderby' ) ) ) {
				return $orderby;
			}

			$orderby = ' visits DESC ';
			$orderby = $this->is_daily ? " SUM({$this->table_name}.cntaccess) DESC " : " {$this->table_name}.cntaccess DESC";

			/**
			 * Filters the ORDER BY clause of Top_Ten_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $orderby  The ORDER BY clause of the Query.
			 * @param Top_Ten_Query $query    The Top_Ten_Query instance (passed by reference).
			 */
			$orderby = apply_filters_ref_array( 'top_ten_query_posts_orderby', array( $orderby, &$this ) );

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

			/**
			 * Filters the GROUP BY clause of Top_Ten_Query.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $groupby  The GROUP BY clause of the Query.
			 * @param Top_Ten_Query $query    The Top_Ten_Query instance (passed by reference).
			 */
			$groupby = apply_filters_ref_array( 'top_ten_query_posts_groupby', array( $groupby, &$this ) );

			return $groupby;
		}

		/**
		 * Modify the posts_groupby clause.
		 *
		 * @since 3.2.0
		 *
		 * @param array     $clauses  Query clauses.
		 * @param \WP_Query $query    The WP_Query instance.
		 * @return array  Updated Query Clauses.
		 */
		public function posts_clauses( $clauses, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $clauses;
			}

			$this->ms_select = array();

			if ( $this->multiple_blogs ) {
				global $wpdb;

				$root_site_db_prefix = $wpdb->prefix;

				foreach ( $this->blog_id as $blog_id ) {
					switch_to_blog( $blog_id );

					$ms_select  = "
						SELECT {$clauses['fields']}
						FROM {$root_site_db_prefix}posts {$clauses['join']}
						WHERE 1=1 {$clauses['where']}
					";
					$ms_select .= $wpdb->prepare( " AND {$this->table_name}.blog_id = %d", $blog_id ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

					if ( $this->is_daily ) {
						$ms_select .= " GROUP BY {$this->table_name}.postnumber ";
					}

					$ms_select = str_replace( $root_site_db_prefix, $wpdb->prefix, $ms_select );
					$ms_select = str_replace( "{$wpdb->prefix}top_ten", "{$wpdb->base_prefix}top_ten", $ms_select );

					$this->ms_select[] = $ms_select;

					restore_current_blog();
				}

				// Update the clauses.
				$clauses['fields']  = 'tables.*';
				$clauses['where']   = '';
				$clauses['join']    = '';
				$clauses['groupby'] = '';
			}

			/**
			 * Filters all query clauses at once, for convenience.
			 *
			 * @since 3.2.0
			 *
			 * @param array          $clauses  The GROUP BY clause of the Query.
			 * @param \Top_Ten_Query $query    The Top_Ten_Query instance (passed by reference).
			 */
			$clauses = apply_filters_ref_array( 'top_ten_query_posts_clauses', array( $clauses, &$this ) );

			return $clauses;
		}

		/**
		 * Modify the completed SQL query before sending.
		 *
		 * @since 3.2.0
		 *
		 * @param string   $sql     The complete SQL query.
		 * @param WP_Query $query   The WP_Query instance (passed by reference).
		 * @return string  Updated SQL.
		 */
		public function posts_request( $sql, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $sql;
			}

			if ( $this->multiple_blogs ) {
				global $wpdb;

				// Clean up remanescent WHERE request.
				$sql = str_replace( 'WHERE 1=1', '', $sql );

				// Multisite request.
				$sql = str_replace( "FROM $wpdb->posts", 'FROM ( ' . implode( ' UNION ', $this->ms_select ) . ' ) tables', $sql );

			}

			/**
			 * Filters the completed SQL query before sending.
			 *
			 * @since 3.2.0
			 *
			 * @param string        $sql   The complete SQL query.
			 * @param Top_Ten_Query $query The WP_Query instance (passed by reference).
			 */
			$sql = apply_filters_ref_array( 'top_ten_query_posts_request', array( $sql, &$this ) );

			return $sql;
		}

		/**
		 * Update the_post while WP_Query loops through this.
		 *
		 * @since 3.2.0
		 *
		 * @param \WP_Post $post  The Post object (passed by reference).
		 */
		public function switch_to_blog_in_loop( $post ) {
			global $blog_id;
			if ( $this->multiple_blogs ) {
				if ( isset( $post->blog_id ) && (int) $blog_id !== (int) $post->blog_id ) {
					switch_to_blog( $post->blog_id );
				} else {
					restore_current_blog();
				}
			}
		}

		/**
		 * Restore current blog on loop end.
		 *
		 * @since 3.2.0
		 */
		public function loop_end() {
			if ( $this->multiple_blogs ) {
				restore_current_blog();
			}
		}

		/**
		 * Filter posts_pre_query to allow caching to work.
		 *
		 * @since 3.0.0
		 *
		 * @param string    $posts Array of post data.
		 * @param \WP_Query $query The WP_Query instance.
		 * @return string  Updated Array of post objects.
		 */
		public function posts_pre_query( $posts, $query ) {

			// Return if it is not a Top_Ten_Query.
			if ( true !== $query->get( 'top_ten_query' ) ) {
				return $posts;
			}

			// Check the cache if there are any posts saved.
			if ( ! empty( $this->query_args['cache_posts'] ) && ! ( is_preview() || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {

				$cache_name = \WebberZone\Top_Ten\Frontend\Display::cache_get_key( $this->query_args );

				$post_ids = get_transient( $cache_name );

				if ( ! empty( $post_ids ) ) {
					$posts                = get_posts(
						array(
							'include'     => $post_ids,
							'fields'      => $query->get( 'fields' ),
							'orderby'     => 'post__in',
							'post_type'   => $query->get( 'post_type' ),
							'post_status' => $query->get( 'post_status' ),
						)
					);
					$query->found_posts   = count( $posts );
					$query->max_num_pages = (int) ceil( $query->found_posts / $query->get( 'posts_per_page' ) );
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
		 * @param \WP_Post[] $posts Array of post objects.
		 * @param \WP_Query  $query The WP_Query instance (passed by reference).
		 * @return \WP_Post[] Updated Array of post objects.
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
				$cache_name = \WebberZone\Top_Ten\Frontend\Display::cache_get_key( $this->query_args );
				$post_ids   = wp_list_pluck( $query->posts, 'ID' );

				set_transient( $cache_name, $post_ids, $cache_time );
			}

			if ( ! empty( $this->query_args['include_post_ids'] ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
				$include_post_ids = wp_parse_id_list( $this->query_args['include_post_ids'] );
			}
			if ( ! empty( $include_post_ids ) ) {
				$extra_posts = get_posts(
					array(
						'include'     => $include_post_ids,
						'fields'      => $query->get( 'fields' ),
						'orderby'     => 'post__in',
						'post_type'   => $query->get( 'post_type' ),
						'post_status' => $query->get( 'post_status' ),
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
