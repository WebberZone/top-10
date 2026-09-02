<?php
/**
 * Class TopTenTest
 *
 * @package Top_Ten
 */

/**
 * Sample test case.
 */
class TopTenTest extends WP_UnitTestCase {

	/**
	 * Test initialization of the plugin
	 */
	public function test_plugin_initialized() {
		$this->assertTrue( class_exists( '\WebberZone\Top_Ten\Main' ) );
	}

	/**
	 * Ensure every 4.5.0 optional feature has a settings toggle.
	 */
	public function test_4_5_features_have_settings_toggles() {
		$features = \WebberZone\Top_Ten\Feature_Manager::get_features();
		$settings = \WebberZone\Top_Ten\Admin\Settings::settings_features();
		$headers  = array(
			'features_display_header',
			'features_tracking_header',
			'features_data_header',
			'features_admin_header',
		);

		foreach ( $headers as $header ) {
			$this->assertArrayHasKey( $header, $settings );
			$this->assertSame( 'header', $settings[ $header ]['type'] );
		}

		$expected = array(
			'page_builders'      => 'enable_page_builders',
			'sitewide_tracking'  => 'enable_sitewide_tracking',
			'daily_table_rollup' => 'enable_daily_table_rollup',
		);

		foreach ( $expected as $feature => $setting ) {
			$this->assertArrayHasKey( $feature, $features );
			$this->assertSame( $setting, $features[ $feature ]['setting'] );
			$this->assertArrayHasKey( $setting, $settings );
			$this->assertTrue( $settings[ $setting ]['pro'] );
		}
	}

	/**
	 * Use one exact metadata query for all required tables and preserve cached checks.
	 */
	public function test_table_status_uses_bulk_metadata_query() {
		global $wpdb;

		\WebberZone\Top_Ten\Admin\Activator::create_tables();
		\WebberZone\Top_Ten\Database::clear_table_installation_cache();

		$queries  = array();
		$listener = function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $listener );

		try {
			$statuses = \WebberZone\Top_Ten\Database::get_table_installation_status( true );
		} finally {
			remove_filter( 'query', $listener );
		}

		$metadata_queries = array_filter(
			$queries,
			function ( $query ) {
				return false !== stripos( $query, 'information_schema.TABLES' ) || false !== stripos( $query, 'sqlite_master' );
			}
		);
		$this->assertCount( 1, $metadata_queries );
		$this->assertStringContainsString( 'IN (', reset( $metadata_queries ) );
		$this->assertCount( 4, $statuses );
		$this->assertNotContains( false, $statuses );

		$queries  = array();
		add_filter( 'query', $listener );
		try {
			\WebberZone\Top_Ten\Database::get_table_installation_status();
		} finally {
			remove_filter( 'query', $listener );
		}
		$metadata_queries = array_filter(
			$queries,
			function ( $query ) {
				return false !== stripos( $query, 'information_schema.TABLES' ) || false !== stripos( $query, 'sqlite_master' );
			}
		);
		$this->assertCount( 0, $metadata_queries );

		$queries  = array();
		$missing_table = $wpdb->prefix . 'top_ten_missing_for_test';
		add_filter( 'query', $listener );
		try {
			$missing = \WebberZone\Top_Ten\Database::is_table_installed( $missing_table, true );
		} finally {
			remove_filter( 'query', $listener );
			\WebberZone\Top_Ten\Database::clear_table_installation_cache();
		}
		$metadata_queries = array_filter(
			$queries,
			function ( $query ) {
				return false !== stripos( $query, 'information_schema.TABLES' ) || false !== stripos( $query, 'sqlite_master' );
			}
		);
		$this->assertFalse( $missing );
		$this->assertCount( 1, $metadata_queries );
	}

	/**
	 * Use estimated metadata for the Tools page without counting or analysing tables.
	 */
	public function test_table_statistics_use_estimated_metadata() {
		\WebberZone\Top_Ten\Admin\Activator::create_tables();
		\WebberZone\Top_Ten\Database::clear_table_installation_cache();
		\WebberZone\Top_Ten\Database::clear_table_statistics_cache();

		$queries  = array();
		$listener = function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $listener );
		try {
			$stats = \WebberZone\Top_Ten\Database::get_table_statistics();
		} finally {
			remove_filter( 'query', $listener );
			\WebberZone\Top_Ten\Database::clear_table_installation_cache();
		}

		$this->assertArrayHasKey( 'top_ten_daily', $stats );
		$this->assertTrue( $stats['top_ten_daily']['estimated'] );
		foreach ( $queries as $query ) {
			$this->assertStringNotContainsString( 'ANALYZE TABLE', $query );
			$this->assertStringNotContainsString( 'COUNT(*) FROM', $query );
		}
	}

	/**
	 * Roll up old daily rows without changing counts or recent hourly rows.
	 */
	public function test_rollup_daily_preserves_counts_and_blog_scope() {
		global $wpdb;

		\WebberZone\Top_Ten\Admin\Activator::create_tables();

		$post_id       = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$other_post_id = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$blog_id       = get_current_blog_id();
		$other_blog_id = $blog_id + 1000;
		$daily_table   = \WebberZone\Top_Ten\Database::get_table( true );
		$overall_table = \WebberZone\Top_Ten\Database::get_table();
		$before_date   = '2000-01-03 00:00:00';

		$rows = array(
			array( $post_id, 1, '2000-01-01 00:00:00', $blog_id ),
			array( $post_id, 2, '2000-01-01 01:00:00', $blog_id ),
			array( $post_id, 3, '2000-01-01 12:00:00', $blog_id ),
			array( $post_id, 5, '2000-01-02 04:00:00', $blog_id ),
			array( $post_id, 11, '2000-01-03 01:00:00', $blog_id ),
			array( $other_post_id, 7, '2000-01-01 04:00:00', $blog_id ),
			array( $post_id, 13, '2000-01-01 08:00:00', $other_blog_id ),
		);

		try {
			foreach ( $rows as $row ) {
				$this->assertNotFalse(
					$wpdb->insert(
						$daily_table,
						array(
							'postnumber' => $row[0],
							'cntaccess' => $row[1],
							'dp_date'   => $row[2],
							'blog_id'   => $row[3],
						),
						array( '%d', '%d', '%s', '%d' )
					)
				);
			}

			$this->assertNotFalse(
				$wpdb->insert(
					$overall_table,
					array(
						'postnumber' => $post_id,
						'cntaccess'  => 23,
						'blog_id'    => $blog_id,
					),
					array( '%d', '%d', '%d' )
				)
			);

			$stats = \WebberZone\Top_Ten\Database::get_daily_rollup_stats( $before_date );
			$this->assertIsArray( $stats );
			$this->assertSame( 5, $stats['rows_before'] );
			$this->assertSame( 3, $stats['rows_after'] );
			$this->assertSame( 2, $stats['rows_reduced'] );
			$this->assertSame( 2, $stats['dates'] );

			$result = \WebberZone\Top_Ten\Database::rollup_daily( $before_date );
			$this->assertIsArray( $result );
			$this->assertSame( 2, $result['dates_processed'] );
			$this->assertSame( 5, $result['rows_before'] );
			$this->assertSame( 3, $result['rows_after'] );
			$this->assertSame( 2, $result['rows_reduced'] );
			$this->assertSame( 23, \WebberZone\Top_Ten\Database::get_count( $post_id, $blog_id ) );

			$rolled_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT cntaccess, dp_date FROM `{$daily_table}` WHERE postnumber = %d AND blog_id = %d ORDER BY dp_date ASC",
					$post_id,
					$blog_id
				),
				ARRAY_A
			);
			$this->assertCount( 3, $rolled_rows );
			$this->assertSame( '6', $rolled_rows[0]['cntaccess'] );
			$this->assertSame( '2000-01-01 00:00:00', $rolled_rows[0]['dp_date'] );
			$this->assertSame( '2000-01-02 00:00:00', $rolled_rows[1]['dp_date'] );
			$this->assertSame( '2000-01-03 01:00:00', $rolled_rows[2]['dp_date'] );

			$other_blog_row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT cntaccess, dp_date FROM `{$daily_table}` WHERE postnumber = %d AND blog_id = %d",
					$post_id,
					$other_blog_id
				),
				ARRAY_A
			);
			$this->assertSame( '13', $other_blog_row['cntaccess'] );
			$this->assertSame( '2000-01-01 08:00:00', $other_blog_row['dp_date'] );
		} finally {
			$wpdb->delete( $daily_table, array( 'postnumber' => $post_id ), array( '%d' ) );
			$wpdb->delete( $daily_table, array( 'postnumber' => $other_post_id ), array( '%d' ) );
			$wpdb->delete( $overall_table, array( 'postnumber' => $post_id ), array( '%d' ) );
		}
	}

	/**
	 * Test network queries use exact blog pairs and include the complete end date.
	 */
	public function test_network_popular_posts_use_half_open_date_range() {
		global $wpdb;

		\WebberZone\Top_Ten\Admin\Activator::create_tables();

		$post_id        = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$blog_id        = get_current_blog_id();
		$other_blog_id  = $blog_id + 1000;
		$daily_table    = \WebberZone\Top_Ten\Database::get_table( true );
		$overall_table  = \WebberZone\Top_Ten\Database::get_table();
		$old_orderby    = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : null;
		$old_order      = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : null;

		try {
			$wpdb->insert( $overall_table, array( 'postnumber' => $post_id, 'cntaccess' => 1000000, 'blog_id' => $blog_id ), array( '%d', '%d', '%d' ) );
			$wpdb->insert( $overall_table, array( 'postnumber' => $post_id, 'cntaccess' => 900000, 'blog_id' => $other_blog_id ), array( '%d', '%d', '%d' ) );

			$daily_rows = array(
				array( $post_id, 500000, '2026-01-01 00:00:00', $blog_id ),
				array( $post_id, 300000, '2026-01-01 23:00:00', $blog_id ),
				array( $post_id, 400000, '2026-01-01 00:00:00', $other_blog_id ),
				array( $post_id, 100, '2026-01-02 00:00:00', $other_blog_id ),
			);
			foreach ( $daily_rows as $row ) {
				$wpdb->insert(
					$daily_table,
					array(
						'postnumber' => $row[0],
						'cntaccess'  => $row[1],
						'dp_date'    => $row[2],
						'blog_id'    => $row[3],
					),
					array( '%d', '%d', '%s', '%d' )
				);
			}

			$range = array(
				'post-date-filter-from' => '01 Jan 2026',
				'post-date-filter-to'   => '01 Jan 2026',
			);
			$_REQUEST['orderby'] = 'total_count';
			$_REQUEST['order']   = 'desc';
			$results             = ( new \WebberZone\Top_Ten\Admin\Statistics_Table( true ) )->get_popular_posts( 10, 1, $range );

			$current_result = null;
			foreach ( $results as $result ) {
				if ( (int) $result['ID'] === $post_id && (int) $result['blog_id'] === $blog_id ) {
					$current_result = $result;
					break;
				}
			}
			$this->assertNotNull( $current_result );
			$this->assertSame( 800000, (int) $current_result['daily_count'] );

			$counts = \WebberZone\Top_Ten\Database::get_counts_with_posts(
				array(
					'daily'     => true,
					'blog_id'   => $blog_id,
					'from_date' => '2026-01-01',
					'to_date'   => '2026-01-01',
					'limit'     => 10,
					'post_ids'  => array( $post_id ),
				)
			);
			$this->assertCount( 1, $counts );
			$this->assertSame( 800000, (int) $counts[0]['cntaccess'] );

			$_REQUEST['orderby'] = 'daily_count';
			$daily_results       = ( new \WebberZone\Top_Ten\Admin\Statistics_Table( true ) )->get_popular_posts( 10, 1, $range );
			$this->assertNotEmpty( $daily_results );
			$daily_current_result = null;
			foreach ( $daily_results as $result ) {
				if ( (int) $result['ID'] === $post_id && (int) $result['blog_id'] === $blog_id ) {
					$daily_current_result = $result;
					break;
				}
			}
			$this->assertNotNull( $daily_current_result );
			$this->assertSame( 800000, (int) $daily_current_result['daily_count'] );
			$this->assertSame( 1000000, (int) $daily_current_result['total_count'] );
		} finally {
			$wpdb->delete( $daily_table, array( 'postnumber' => $post_id ), array( '%d' ) );
			$wpdb->delete( $overall_table, array( 'postnumber' => $post_id ), array( '%d' ) );

			if ( null === $old_orderby ) {
				unset( $_REQUEST['orderby'] );
			} else {
				$_REQUEST['orderby'] = $old_orderby;
			}
			if ( null === $old_order ) {
				unset( $_REQUEST['order'] );
			} else {
				$_REQUEST['order'] = $old_order;
			}
		}
	}

	/**
	 * Use sargable half-open date ranges for dashboard totals, lists and charts.
	 */
	public function test_dashboard_queries_use_half_open_date_ranges() {
		global $wpdb;

		\WebberZone\Top_Ten\Admin\Activator::create_tables();

		$post_id       = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$blog_id       = get_current_blog_id();
		$other_blog_id = $blog_id + 1000;
		$daily_table   = \WebberZone\Top_Ten\Database::get_table( true );
		$rows          = array(
			array( 7, '2026-01-01 23:00:00', $blog_id ),
			array( 11, '2026-01-02 00:00:00', $blog_id ),
			array( 13, '2026-01-02 23:00:00', $blog_id ),
			array( 19, '2026-01-02 12:00:00', $other_blog_id ),
			array( 17, '2026-01-03 00:00:00', $blog_id ),
		);

		foreach ( $rows as $row ) {
			$wpdb->insert(
				$daily_table,
				array(
					'postnumber' => $post_id,
					'cntaccess'  => $row[0],
					'dp_date'    => $row[1],
					'blog_id'    => $row[2],
				),
				array( '%d', '%d', '%s', '%d' )
			);
		}

		$queries  = array();
		$listener = function ( $query ) use ( &$queries ) {
			if ( false !== stripos( $query, 'top_ten_daily' ) ) {
				$queries[] = $query;
			}
			return $query;
		};
		add_filter( 'query', $listener );

		try {
			$dashboard       = new \WebberZone\Top_Ten\Admin\Dashboard();
			$single_args     = array(
				'daily'     => true,
				'from_date' => '01 Jan 2026',
				'to_date'   => '02 Jan 2026',
				'blog_id'   => $blog_id,
				'network'   => false,
			);
			$single_results  = $dashboard->get_popular_posts( $single_args );
			$single_total    = $dashboard->get_period_total_visits( $single_args );
			$network_args    = $single_args;
			$network_args['network'] = true;
			$network_results = $dashboard->get_popular_posts( $network_args );
			$network_total   = $dashboard->get_period_total_visits( $network_args );
			$chart           = $dashboard->fetch_visits_by_date( '2026-01-01', '2026-01-02', true );
		} finally {
			remove_filter( 'query', $listener );
			$wpdb->delete( $daily_table, array( 'postnumber' => $post_id ), array( '%d' ) );
		}

		$this->assertSame( 31, isset( $single_results[0]['visits'] ) ? (int) $single_results[0]['visits'] : null );
		$this->assertSame( 31, $single_total );
		$this->assertCount( 2, $network_results );
		$this->assertSame( 31, (int) $network_results[0]['visits'] );
		$this->assertSame( 19, (int) $network_results[1]['visits'] );
		$this->assertSame( 50, $network_total );

		$chart_totals = array();
		foreach ( $chart as $row ) {
			$chart_totals[ $row->date ] = (int) $row->visits;
		}
		$this->assertSame( 7, $chart_totals['2026-01-01'] ?? null );
		$this->assertSame( 43, $chart_totals['2026-01-02'] ?? null );
		$this->assertArrayNotHasKey( '2026-01-03', $chart_totals );

		foreach ( $queries as $query ) {
			$this->assertStringNotContainsString( 'WHERE DATE(dp_date)', $query );
			$this->assertStringNotContainsString( 'DATE(dp_date) >=', $query );
			$this->assertStringNotContainsString( 'DATE(dp_date) <=', $query );
		}
	}

	/**
	 * Render only the first historical tab and register the lazy-load endpoint.
	 */
	public function test_dashboard_lazy_loads_historical_tabs() {
		$dashboard = new \WebberZone\Top_Ten\Admin\Dashboard();
		$tabs     = $dashboard->get_tabs();
		$tab_count = 0;
		foreach ( $tabs as $tab ) {
			if ( empty( $tab['hide'] ) ) {
				++$tab_count;
			}
		}
		ob_start();
		$dashboard->render_page();
		$html = ob_get_clean();

		$this->assertNotFalse( has_action( 'wp_ajax_tptn_dashboard_tab', array( $dashboard, 'get_dashboard_tab' ) ) );
		$this->assertSame( 1, substr_count( $html, 'data-tptn-loaded="1"' ) );
		$this->assertSame( $tab_count - 1, substr_count( $html, 'data-tptn-loaded="0"' ) );
	}

	/**
	 * Preserve the existing inclusive date ranges for the historical tabs.
	 */
	public function test_dashboard_preserves_historical_tab_date_ranges() {
		$tabs = ( new \WebberZone\Top_Ten\Admin\Dashboard() )->get_tabs();

		$this->assertSame( gmdate( 'd M Y', strtotime( '-1 week' ) ), $tabs['lastweek']['from_date'] );
		$this->assertSame( gmdate( 'd M Y', strtotime( '-30 days' ) ), $tabs['lastmonth']['from_date'] );
	}

	/**
	 * Create a test post and verify author
	 */
	public function test_create_post() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
				'post_status' => 'publish',
			)
		);

		$post = get_post( $post_id );
		$this->assertEquals( $user_id, $post->post_author );
	}
}
