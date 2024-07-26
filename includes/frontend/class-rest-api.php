<?php
/**
 * This class adds REST routes to update the count and return the list of popular posts.
 *
 * @package Top_Ten
 */

namespace WebberZone\Top_Ten\Frontend;

use WebberZone\Top_Ten\Counter;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Query API: Top_Ten_Query class.
 *
 * @since 3.0.0
 */
class REST_API extends \WP_REST_Controller {

	/**
	 * Posts Route.
	 *
	 * @since 3.3.0
	 *
	 * @var string Posts Route.
	 */
	public $posts_route;

	/**
	 * Tracker Route.
	 *
	 * @since 3.3.0
	 *
	 * @var string Tracker Route.
	 */
	public $tracker_route;

	/**
	 * Counter Route.
	 *
	 * @since 3.4.0
	 *
	 * @var string Counter Route.
	 */
	public $counter_route;

	/**
	 * Main constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->namespace     = 'top-10/v1';
		$this->posts_route   = 'popular-posts';
		$this->tracker_route = 'tracker';
		$this->counter_route = 'counter';
	}

	/**
	 * Initialises the Top 10 REST API adding the necessary routes.
	 *
	 * @since 3.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->posts_route,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_items_params(),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->posts_route . '/(?P<id>[\d]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Post ID.', 'top-10' ),
						'type'        => 'integer',
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->tracker_route,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_post_count' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_tracker_params(),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->counter_route . '/(?P<id>[\d]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_counter' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_counter_params(),
			)
		);
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_Error|bool
	 */
	public function permissions_check( \WP_REST_Request $request ) {
		return apply_filters( 'top_ten_rest_api_permissions_check', true, $request );
	}

	/**
	 * Get popular posts.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 * @return mixed|\WP_REST_Response Array of post objects or post IDs.
	 */
	public function get_items( $request ) {
		$popular_posts = array();

		$args = $request->get_params();

		/**
		 * Filter the REST API arguments before they passed to get_tptn_posts().
		 *
		 * @since 3.0.0
		 *
		 * @param array            $args    Arguments array.
		 * @param \WP_REST_Request $request WP Rest request.
		 */
		$args = apply_filters( 'top_ten_rest_api_get_tptn_posts_args', $args, $request );

		$results = Display::get_posts( $args );

		if ( is_array( $results ) && ! empty( $results ) ) {
			foreach ( $results as $popular_post ) {
				if ( ! $this->check_read_permission( $popular_post ) ) {
					continue;
				}

				$popular_posts[] = $this->prepare_item( $popular_post, $request );
			}
		}
		return rest_ensure_response( $popular_posts );
	}

	/**
	 * Get a popular post by ID. Also includes the number of views.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 * @return mixed|\WP_REST_Response Array of post objects or post IDs.
	 */
	public function get_item( $request ) {

		$id = $request->get_param( 'id' );

		$error = new \WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.', 'top-10' ),
			array( 'status' => 404 )
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		$post = get_post( (int) $id );
		if ( empty( $post ) || empty( $post->ID ) || ! $this->check_read_permission( $post ) ) {
			return $error;
		}

		$post = $this->prepare_item( $post, $request );

		return rest_ensure_response( $post );
	}

	/**
	 * Get a popular post by ID. Also includes the number of views.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post         $popular_post Popular Post object.
	 * @param \WP_REST_Request $request WP Rest request.
	 * @return array|mixed   The formatted Popular Post object.
	 */
	public function prepare_item( $popular_post, $request ) {

		// Need to prepare items for the rest response.
		$posts_controller = new \WP_REST_Posts_Controller( $popular_post->post_type );
		$data             = $posts_controller->prepare_item_for_response( $popular_post, $request );

		// Add pageviews from popular_post object to response.
		$visits               = isset( $popular_post->visits ) ? $popular_post->visits : Counter::get_post_count_only( $popular_post->ID );
		$data->data['visits'] = absint( $visits );

		return $this->prepare_response_for_collection( $data );
	}

	/**
	 * Update post count.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 * @return mixed|\WP_REST_Response Array of post objects or post IDs.
	 */
	public function update_post_count( $request ) {

		$id               = absint( $request->get_param( 'top_ten_id' ) );
		$blog_id          = absint( $request->get_param( 'top_ten_blog_id' ) );
		$activate_counter = absint( $request->get_param( 'activate_counter' ) );
		$top_ten_debug    = absint( $request->get_param( 'top_ten_debug' ) );

		$str = \WebberZone\Top_Ten\Tracker::update_count( $id, $blog_id, $activate_counter );

		if ( 1 === $top_ten_debug ) {
			return rest_ensure_response( $str );
		} else {
			$response = new \WP_REST_Response( '', 204 );
			$response->header( 'Cache-Control', 'max-age=15, s-maxage=0' );
			return $response;
		}
	}

	/**
	 * Get the counter.
	 *
	 * @since 3.4.0
	 *
	 * @param \WP_REST_Request $request WP Rest request.
	 * @return mixed|\WP_REST_Response Array of post objects or post IDs.
	 */
	public function get_counter( $request ) {
		$id = absint( $request->get_param( 'id' ) );

		$error = new \WP_Error(
			'rest_post_invalid_id',
			__( 'Invalid post ID.', 'top-10' ),
			array( 'status' => 404 )
		);

		if ( (int) $id <= 0 ) {
			return $error;
		}

		$args = array();

		$counter = sanitize_text_field( $request->get_param( 'counter' ) ) ? sanitize_text_field( $request->get_param( 'counter' ) ) : 'total';
		$blog_id = absint( $request->get_param( 'blog_id' ) ) ? absint( $request->get_param( 'blog_id' ) ) : get_current_blog_id();

		$from_date = sanitize_text_field( $request->get_param( 'from_date' ) );
		$to_date   = sanitize_text_field( $request->get_param( 'to_date' ) );

		if ( ! empty( $from_date ) ) {
			$args['from_date'] = $from_date;
		}
		if ( ! empty( $to_date ) ) {
			$args['to_date'] = $to_date;
		}

		$counter = absint( Counter::get_post_count_only( (int) $id, $counter, $blog_id, $args ) );

		return rest_ensure_response( $counter );
	}

	/**
	 * Get the arguments for fetching the popular posts.
	 *
	 * @since 3.0.0
	 *
	 * @return array Top 10 REST API popular posts arguments.
	 */
	public function get_items_params() {
		$args = array(
			'limit'      => array(
				'description'       => esc_html__( 'Number of posts', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'post_types' => array(
				'description' => esc_html__( 'Post types', 'top-10' ),
				'type'        => 'string',
			),
		);

		return apply_filters( 'top_ten_rest_api_get_items_params', $args );
	}

	/**
	 * Get the arguments for tracking posts.
	 *
	 * @since 3.0.0
	 *
	 * @return array Top 10 REST API popular posts arguments.
	 */
	public function get_tracker_params() {
		$args = array(
			'top_ten_id'       => array(
				'description'       => esc_html__( 'ID of the post.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'top_ten_blog_id'  => array(
				'description'       => esc_html__( 'Blog ID of the post.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'activate_counter' => array(
				'description'       => esc_html__( 'Activate counter flag.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'top_ten_debug'    => array(
				'description'       => esc_html__( 'Debug flag.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
		);

		return apply_filters( 'top_ten_rest_api_get_tracker_params', $args );
	}

	/**
	 * Get counter params.
	 *
	 * @since 3.4.0
	 *
	 * @return array Top 10 REST API counter arguments.
	 */
	public function get_counter_params() {
		$args = array(
			'id'        => array(
				'description'       => esc_html__( 'ID of the post.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'counter'   => array(
				'description'       => esc_html__( 'Counter type.', 'top-10' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'blog_id'   => array(
				'description'       => esc_html__( 'Blog ID.', 'top-10' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'from_date' => array(
				'description'       => esc_html__( 'From date.', 'top-10' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'to_date'   => array(
				'description'       => esc_html__( 'To date.', 'top-10' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		return apply_filters( 'top_ten_rest_api_get_counter_params', $args );
	}

	/**
	 * Checks if a given post type can be viewed or managed.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post_Type|string $post_type Post type name or object.
	 * @return bool Whether the post type is allowed in REST.
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if a post can be read.
	 *
	 * Correctly handles posts with the inherit status.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post Post object.
	 * @return bool Whether the post can be read.
	 */
	public function check_read_permission( $post ) {
		$post_type = get_post_type_object( $post->post_type );
		if ( ! $this->check_is_post_type_allowed( $post_type ) ) {
			return false;
		}

		// Is the post readable?
		if ( 'publish' === $post->post_status || current_user_can( 'read_post', $post->ID ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $post->post_status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		// Can we read the parent if we're inheriting?
		if ( 'inherit' === $post->post_status && $post->post_parent > 0 ) {
			$parent = get_post( $post->post_parent );
			if ( $parent ) {
				return $this->check_read_permission( $parent );
			}
		}

		/*
		 * If there isn't a parent, but the status is set to inherit, assume
		 * it's published (as per get_post_status()).
		 */
		if ( 'inherit' === $post->post_status ) {
			return true;
		}

		return false;
	}
}
