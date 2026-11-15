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

		// rollup_daily() runs its own transactions, which commit this test's rows
		// outside the WP test-suite rollback. Clear leftovers from earlier runs
		// (or the single-site leg) so the counts below only see this test's rows.
		$wpdb->query( $wpdb->prepare( "DELETE FROM `{$daily_table}` WHERE blog_id = %d", $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
							'cntaccess'  => $row[1],
							'dp_date'    => $row[2],
							'blog_id'    => $row[3],
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

			// The WP test suite rolls the test transaction back in tear_down(),
			// which would undo these deletes after rollup_daily() committed; commit
			// them so the next test (or leg) starts from a clean table.
			$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
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

		$dashboard = new \WebberZone\Top_Ten\Admin\Dashboard();
			$single_args             = array(
				'daily'     => true,
				'from_date' => '01 Jan 2026',
				'to_date'   => '02 Jan 2026',
				'blog_id'   => $blog_id,
				'network'   => false,
			);
			$single_results          = $dashboard->get_popular_posts( $single_args );
			$single_total            = $dashboard->get_period_total_visits( $single_args );
			$network_args            = $single_args;
			$network_args['network'] = true;
			$network_results         = $dashboard->get_popular_posts( $network_args );
			$network_total           = $dashboard->get_period_total_visits( $network_args );
			$chart                   = $dashboard->fetch_visits_by_date( '2026-01-01', '2026-01-02', true );

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

		$wpdb->delete( $daily_table, array( 'postnumber' => $post_id ), array( '%d' ) );
	}
}
