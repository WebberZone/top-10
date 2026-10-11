<?php
/**
 * Registers Top 10 abilities.
 *
 * @package WebberZone\Top_Ten
 */

namespace WebberZone\Top_Ten;

use WebberZone\Top_Ten\Frontend\REST_API;
use WebberZone\Top_Ten\Util\Hook_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared read-only abilities.
 *
 * @since 4.6.0
 */
class Abilities {

	/**
	 * Register hooks, including on WordPress versions without the Abilities API.
	 *
	 * @since 4.6.0
	 */
	public function __construct() {
		if ( ! Feature_Manager::is_enabled( 'abilities_api' ) ) {
			return;
		}
		Hook_Registry::add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		Hook_Registry::add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the shared WebberZone category once.
	 *
	 * @since 4.6.0
	 */
	public function register_category(): void {
		if ( wp_has_ability_category( 'webberzone' ) ) {
			return;
		}
		wp_register_ability_category(
			'webberzone',
			array(
				'label'       => __( 'WebberZone', 'top-10' ),
				'description' => __( 'Abilities provided by WebberZone plugins.', 'top-10' ),
			)
		);
	}

	/**
	 * Register popular posts and individual counts.
	 *
	 * @since 4.6.0
	 */
	public function register_abilities(): void {
		$meta = array(
			'public'       => true,
			'show_in_rest' => true,
			'mcp'          => array( 'public' => true ),
			'annotations'  => array(
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			),
		);
		wp_register_ability(
			'top-10/get-popular-posts',
			array(
				'label'               => __( 'Get Popular Posts', 'top-10' ),
				'description'         => __( 'Returns the most viewed readable posts on the current site, ordered by views. Use overall for lifetime counts or daily for a recent window in days. Results include post ID, plain-text title, URL and views. Counts include aggregated views; buffered visits appear after aggregation.', 'top-10' ),
				'category'            => 'webberzone',
				'input_schema'        => array(
					'type'                 => 'object',
					'default'              => array(),
					'additionalProperties' => false,
					'properties'           => array(
						'count'       => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => 100,
							'default' => 10,
						),
						'period'      => array(
							'type'    => 'string',
							'enum'    => array( 'daily', 'overall' ),
							'default' => 'overall',
						),
						'post_types'  => array(
							'type'     => 'array',
							'minItems' => 1,
							'maxItems' => 100,
							'items'    => array(
								'type'      => 'string',
								'minLength' => 1,
							),
						),
						'daily_range' => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'maximum'     => 36500,
							'description' => __( 'Recent window in days; defaults to the configured daily range.', 'top-10' ),
						),
					),
				),
				'output_schema'       => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'required'             => array( 'id', 'title', 'url', 'views' ),
						'additionalProperties' => true,
						'properties'           => array(
							'id'    => array(
								'type'    => 'integer',
								'minimum' => 1,
							),
							'title' => array( 'type' => 'string' ),
							'url'   => array(
								'type'   => 'string',
								'format' => 'uri',
							),
							'views' => array(
								'type'    => 'integer',
								'minimum' => 0,
							),
						),
					),
				),
				'execute_callback'    => array( $this, 'get_popular_posts' ),
				'permission_callback' => array( $this, 'can_read' ),
				'meta'                => $meta,
			)
		);
		wp_register_ability(
			'top-10/get-post-views',
			array(
				'label'               => __( 'Get Post Views', 'top-10' ),
				'description'         => __( 'Returns lifetime total and recent daily views for a readable post on the current site. The daily window follows the Top 10 daily range setting. Buffered visits appear after aggregation.', 'top-10' ),
				'category'            => 'webberzone',
				'input_schema'        => array(
					'type'                 => 'object',
					'required'             => array( 'post_id' ),
					'additionalProperties' => false,
					'properties'           => array(
						'post_id' => array(
							'type'    => 'integer',
							'minimum' => 1,
							'maximum' => PHP_INT_MAX,
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'required'             => array( 'total', 'daily' ),
					'additionalProperties' => true,
					'properties'           => array(
						'total' => array(
							'type'    => 'integer',
							'minimum' => 0,
						),
						'daily' => array(
							'type'    => 'integer',
							'minimum' => 0,
						),
					),
				),
				'execute_callback'    => array( $this, 'get_post_views' ),
				'permission_callback' => array( $this, 'can_read_post' ),
				'meta'                => $meta,
			)
		);
	}

	/**
	 * Require the read capability for discovery.
	 *
	 * @since 4.6.0
	 * @param mixed $input Ability input.
	 * @return bool Whether the user can read.
	 */
	public function can_read( $input = array() ): bool {
		unset( $input );
		return current_user_can( 'read' );
	}

	/**
	 * Require a readable source post as well as the read capability.
	 *
	 * @since 4.6.0
	 * @param mixed $input Ability input.
	 * @return bool Whether the user can read the post.
	 */
	public function can_read_post( $input ): bool {
		if ( ! $this->can_read() || ! is_array( $input ) || ! isset( $input['post_id'] ) || ! rest_is_integer( $input['post_id'] ) || (float) $input['post_id'] < 1 || (float) $input['post_id'] > PHP_INT_MAX ) {
			return false;
		}
		$post = get_post( (int) $input['post_id'] );
		return $post instanceof \WP_Post && ( new REST_API() )->check_read_permission( $post );
	}

	/**
	 * Retrieve popular posts through the public query helper.
	 *
	 * @since 4.6.0
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error Popular posts or an error.
	 */
	public function get_popular_posts( $input ) {
		if ( ! $this->can_read() ) {
			return new \WP_Error( 'ability_invalid_permissions', __( 'You do not have permission to read popular posts.', 'top-10' ), array( 'status' => 403 ) );
		}
		if ( ! is_array( $input ) || ( isset( $input['count'] ) && ( ! rest_is_integer( $input['count'] ) || $input['count'] < 1 || $input['count'] > 100 ) ) || ( isset( $input['period'] ) && ! in_array( $input['period'], array( 'daily', 'overall' ), true ) ) || ( isset( $input['daily_range'] ) && ( ! rest_is_integer( $input['daily_range'] ) || $input['daily_range'] < 1 || $input['daily_range'] > 36500 ) ) ) {
			return new \WP_Error( 'tptn_invalid_ability_input', __( 'Invalid popular posts arguments.', 'top-10' ), array( 'status' => 400 ) );
		}
		if ( isset( $input['post_types'] ) && ( ! is_array( $input['post_types'] ) || empty( $input['post_types'] ) || count( array_filter( $input['post_types'], 'is_string' ) ) !== count( $input['post_types'] ) ) ) {
			return new \WP_Error( 'tptn_invalid_post_types', __( 'A non-empty array of public post type names is required.', 'top-10' ), array( 'status' => 400 ) );
		}
		$args = array(
			'limit'       => isset( $input['count'] ) ? (int) $input['count'] : 10,
			'daily'       => 'daily' === ( $input['period'] ?? 'overall' ),
			'fields'      => 'all',
			'blog_id'     => get_current_blog_id(),
			'post_status' => 'publish',
			'hour_range'  => 0,
		);
		if ( isset( $input['daily_range'] ) ) {
			$args['daily_range'] = (int) $input['daily_range'];
		}
		if ( isset( $input['post_types'] ) ) {
			$allowed = get_post_types( array( 'public' => true ) );
			if ( array_diff( $input['post_types'], array_keys( $allowed ) ) ) {
				return new \WP_Error( 'tptn_invalid_post_types', __( 'Only public post types may be requested.', 'top-10' ), array( 'status' => 400 ) );
			}
			$args['post_types'] = $input['post_types'];
		}
		$results  = array();
		$rest_api = new REST_API();
		foreach ( (array) \get_tptn_posts( $args ) as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				$post = get_post( $post );
			}
			if ( ! $post instanceof \WP_Post || ! $rest_api->check_read_permission( $post ) ) {
				continue;
			}
			$results[] = array(
				'id'    => (int) $post->ID,
				'title' => trim( html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ), " \t\n\r\0\x0B" ),
				'url'   => (string) get_permalink( $post ),
				'views' => (int) ( $post->visits ?? 0 ),
			);
		}
		return $results;
	}

	/**
	 * Retrieve individual counts through the public helpers.
	 *
	 * @since 4.6.0
	 * @param mixed $input Ability input.
	 * @return array|\WP_Error Counts or an error.
	 */
	public function get_post_views( $input ) {
		if ( ! $this->can_read_post( $input ) ) {
			return new \WP_Error( 'ability_invalid_permissions', __( 'You do not have permission to read this post.', 'top-10' ), array( 'status' => 403 ) );
		}
		return array(
			'total' => (int) \get_tptn_total_count( (int) $input['post_id'] ),
			'daily' => (int) \get_tptn_daily_count( (int) $input['post_id'] ),
		);
	}
}
