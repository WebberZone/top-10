<?php
/**
 * Class TopTenTrackerTest
 *
 * @package Top_Ten
 */

/**
 * Tracker-specific test cases.
 */
class TopTenTrackerTest extends WP_UnitTestCase {

	/**
	 * WP Rocket-style preload user agents to test.
	 *
	 * @var string[]
	 */
	private $bot_user_agents = array(
		'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 (compatible; WP-Rocket-SaaS/1.0; +https://wp-rocket.me/bot/)',
		'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1 WP Rocket/Preload',
	);

	/**
	 * Ensure the tracker is enqueued for bot UAs whether Do Not Track Bots is on or off.
	 *
	 * @dataProvider data_free_tracker_types_and_no_bots
	 */
	public function test_tracker_enqueued_for_preload_bot( $tracker_type, $no_bots ) {
		$post_id = $this->factory->post->create( array( 'post_status' => 'publish' ) );

		$original_ua                = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$_SERVER['HTTP_USER_AGENT'] = $this->bot_user_agents[0];

		$original_settings             = get_option( 'tptn_settings', array() );
		$settings                      = \tptn_get_settings();
		$settings['no_bots']           = $no_bots;
		$settings['tracker_all_pages'] = 1;
		$settings['track_users']       = array( 'authors', 'editors', 'admins' );
		$settings['logged_in']         = 1;
		$settings['tracker_type']      = $tracker_type;
		update_option( 'tptn_settings', $settings );

		wp_dequeue_script( 'tptn_tracker' );
		wp_deregister_script( 'tptn_tracker' );

		$this->go_to( get_permalink( $post_id ) );

		\WebberZone\Top_Ten\Tracker::enqueue_scripts();

		$this->assertTrue( wp_script_is( 'tptn_tracker', 'enqueued' ), "Tracker must be enqueued for preload bot UA with tracker_type={$tracker_type} and no_bots={$no_bots}." );

		$_SERVER['HTTP_USER_AGENT'] = $original_ua;
		update_option( 'tptn_settings', $original_settings );
	}

	/**
	 * Provide the free tracker types and no_bots values to test.
	 *
	 * @return array
	 */
	public function data_free_tracker_types_and_no_bots() {
		return array(
			array( 'query_based', 0 ),
			array( 'query_based', 1 ),
			array( 'ajaxurl', 0 ),
			array( 'ajaxurl', 1 ),
			array( 'rest_based', 0 ),
			array( 'rest_based', 1 ),
		);
	}

	/**
	 * Ensure endpoint-level bot handling respects the no_bots setting.
	 *
	 * @dataProvider data_bot_endpoint_expectations
	 */
	public function test_tracking_request_for_bot_respects_no_bots_setting( $user_agent, $no_bots, $expected ) {
		$original_ua                = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$_SERVER['HTTP_USER_AGENT'] = $user_agent;

		$original_settings   = get_option( 'tptn_settings', array() );
		$settings            = \tptn_get_settings();
		$settings['no_bots'] = $no_bots;
		update_option( 'tptn_settings', $settings );

		if ( $expected ) {
			$this->assertTrue( \WebberZone\Top_Ten\Tracker::is_tracking_request_allowed() );
		} else {
			$this->assertFalse( \WebberZone\Top_Ten\Tracker::is_tracking_request_allowed() );
		}

		$_SERVER['HTTP_USER_AGENT'] = $original_ua;
		update_option( 'tptn_settings', $original_settings );
	}

	/**
	 * Provide bot UA and no_bots combinations with expected endpoint result.
	 *
	 * @return array
	 */
	public function data_bot_endpoint_expectations() {
		$ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 (compatible; WP-Rocket-SaaS/1.0; +https://wp-rocket.me/bot/)';
		return array(
			array( $ua, 1, false ),
			array( $ua, 0, true ),
		);
	}

	/**
	 * Ensure human requests are still allowed when Do Not Track Bots is enabled.
	 */
	public function test_tracking_request_allowed_for_human() {
		$original_ua                = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

		$original_settings   = get_option( 'tptn_settings', array() );
		$settings            = \tptn_get_settings();
		$settings['no_bots'] = 1;
		update_option( 'tptn_settings', $settings );

		$this->assertTrue( \WebberZone\Top_Ten\Tracker::is_tracking_request_allowed() );

		$_SERVER['HTTP_USER_AGENT'] = $original_ua;
		update_option( 'tptn_settings', $original_settings );
	}

	/**
	 * Ensure prefetch and prerender headers are rejected.
	 *
	 * @dataProvider data_prefetch_headers
	 */
	public function test_tracking_request_blocked_for_prefetch_prerender( $header, $value ) {
		$original_value     = $_SERVER[ $header ] ?? '';
		$_SERVER[ $header ] = $value;

		$this->assertFalse( \WebberZone\Top_Ten\Tracker::is_tracking_request_allowed() );

		$_SERVER[ $header ] = $original_value;
	}

	/**
	 * Provide prefetch/prerender header combinations.
	 *
	 * @return array
	 */
	public function data_prefetch_headers() {
		return array(
			array( 'HTTP_SEC_PURPOSE', 'prefetch' ),
			array( 'HTTP_PURPOSE', 'prefetch' ),
			array( 'HTTP_SEC_PURPOSE', 'prerender' ),
		);
	}

	/**
	 * Ensure Sec-Fetch-Mode: navigate is rejected.
	 */
	public function test_tracking_request_blocked_for_navigate_fetch_mode() {
		$original_value                 = $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '';
		$_SERVER['HTTP_SEC_FETCH_MODE'] = 'navigate';

		$this->assertFalse( \WebberZone\Top_Ten\Tracker::is_tracking_request_allowed() );

		$_SERVER['HTTP_SEC_FETCH_MODE'] = $original_value;
	}

	/**
	 * Ensure the REST tracker endpoint returns 204 for bot requests.
	 */
	public function test_rest_tracker_endpoint_blocks_bot() {
		$original_ua                = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$_SERVER['HTTP_USER_AGENT'] = $this->bot_user_agents[0];

		$original_settings   = get_option( 'tptn_settings', array() );
		$settings            = \tptn_get_settings();
		$settings['no_bots'] = 1;
		update_option( 'tptn_settings', $settings );

		$request = new \WP_REST_Request( 'POST', '/top-10/v1/tracker' );
		$request->set_param( 'top_ten_id', 1 );
		$request->set_param( 'activate_counter', 11 );
		$request->set_param( 'top_ten_blog_id', 1 );

		$rest_api = new \WebberZone\Top_Ten\Frontend\REST_API();
		$response = $rest_api->update_post_count( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertSame( 204, $response->get_status() );

		$_SERVER['HTTP_USER_AGENT'] = $original_ua;
		update_option( 'tptn_settings', $original_settings );
	}
}
