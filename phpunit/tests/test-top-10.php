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
