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
