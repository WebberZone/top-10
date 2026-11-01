<?php

use WebberZone\Top_Ten\Abilities;
use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Database;

class TopTenAbilitiesTest extends WP_UnitTestCase {

	private $written_counts = array();

	public function record_count( $post_id, $count, $blog_id ) {
		$this->written_counts[] = array( $post_id, $blog_id );
	}

	public function set_up() {
		parent::set_up();
		if ( ! function_exists( 'wp_get_ability' ) ) {
			$this->markTestSkipped( 'Requires the WordPress Abilities API.' );
		}
		Activator::create_tables();
		add_action( 'tptn_set_count', array( $this, 'record_count' ), 10, 3 );
		update_option( 'tptn_settings', \WebberZone\Top_Ten\Admin\Settings::get_defaults() );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
	}

	public function tear_down() {
		remove_action( 'tptn_set_count', array( $this, 'record_count' ), 10 );
		foreach ( $this->written_counts as $entry ) {
			foreach ( array( false, true ) as $daily ) {
				Database::delete_counts( array( 'post_ids' => array( $entry[0] ), 'blog_id' => $entry[1], 'daily' => $daily ) );
			}
		}
		$this->written_counts = array();
		wp_set_current_user( 0 );
		parent::tear_down();
	}
	public function test_popular_posts_delegate_and_filter_unreadable_posts() {
		$visible = get_post( self::factory()->post->create( array( 'post_title' => 'A &amp; B' ) ) );
		$visible->visits = 42;
		$private = get_post( self::factory()->post->create( array( 'post_status' => 'private', 'post_author' => self::factory()->user->create( array( 'role' => 'administrator' ) ) ) ) );
		$received = array();
		$filter = static function ( $posts, $args ) use ( $visible, $private, &$received ) {
			$received = $args;
			return array( $visible, $private );
		};
		add_filter( 'get_tptn_posts', $filter, 99, 2 );
		try {
			$result = wp_get_ability( 'top-10/get-popular-posts' )->execute( array( 'count' => 3, 'period' => 'daily', 'daily_range' => 7, 'post_types' => array( 'post' ) ) );
		} finally {
			remove_filter( 'get_tptn_posts', $filter, 99 );
		}
		$this->assertSame( array( array( 'id' => $visible->ID, 'title' => 'A & B', 'url' => get_permalink( $visible ), 'views' => 42 ) ), $result );
		$this->assertSame( 3, $received['limit'] );
		$this->assertTrue( $received['daily'] );
		$this->assertSame( 7, $received['daily_range'] );
		$this->assertSame( 0, $received['hour_range'] );
		$this->assertSame( get_current_blog_id(), $received['blog_id'] );
		$this->assertSame( array( 'post' ), $received['post_types'] );
	}
	public function test_post_counts_match_public_helpers_and_deny_unreadable_posts() {
		$post_id = self::factory()->post->create();
		Database::set_count( $post_id, 100 );
		Database::set_count( $post_id, 7, null, true );
		$this->assertSame( array( 'total' => (int) get_tptn_total_count( $post_id ), 'daily' => (int) get_tptn_daily_count( $post_id ) ), wp_get_ability( 'top-10/get-post-views' )->execute( array( 'post_id' => $post_id ) ) );
		$private = self::factory()->post->create( array( 'post_status' => 'private', 'post_author' => self::factory()->user->create( array( 'role' => 'administrator' ) ) ) );
		$this->assertWPError( wp_get_ability( 'top-10/get-post-views' )->execute( array( 'post_id' => $private ) ) );
		wp_set_current_user( 0 );
		$this->assertWPError( wp_get_ability( 'top-10/get-post-views' )->execute( array( 'post_id' => $post_id ) ) );
		$this->assertWPError( wp_get_ability( 'top-10/get-popular-posts' )->execute( array() ) );
	}

	public function test_real_query_returns_period_counts_and_public_types() {
		$post_id = self::factory()->post->create();
		Database::set_count( $post_id, 101 );
		Database::set_count( $post_id, 17, null, true );
		$ability = wp_get_ability( 'top-10/get-popular-posts' );
		$overall = $ability->execute( array( 'post_types' => array( 'post' ) ) );
		$daily = $ability->execute( array( 'post_types' => array( 'post' ), 'period' => 'daily', 'daily_range' => 1 ) );
		$this->assertSame( $post_id, $overall[0]['id'] );
		$this->assertSame( 101, $overall[0]['views'] );
		$this->assertSame( $post_id, $daily[0]['id'] );
		$this->assertSame( 17, $daily[0]['views'] );
	}

	public function test_rest_visibility_execution_validation_and_permissions() {
		$post_id = self::factory()->post->create();
		Database::set_count( $post_id, 12 );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities' ) );
		$this->assertSame( 200, $response->get_status() );
		$this->assertContains( 'top-10/get-post-views', wp_list_pluck( $response->get_data(), 'name' ) );
		$request = new WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/top-10/get-post-views/run' );
		$request->set_query_params( array( 'input' => array( 'post_id' => (string) $post_id ) ) );
		$response = rest_do_request( $request );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 12, $response->get_data()['total'] );
		$request->set_query_params( array( 'input' => array( 'post_id' => -1 ) ) );
		$this->assertSame( 400, rest_do_request( $request )->get_status() );
		wp_set_current_user( 0 );
		$request->set_query_params( array( 'input' => array( 'post_id' => $post_id ) ) );
		$this->assertSame( 401, rest_do_request( $request )->get_status() );
	}
}
