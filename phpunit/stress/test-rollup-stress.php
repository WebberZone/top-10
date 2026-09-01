<?php
/**
 * Roll-up stress test.
 *
 * @package Top_Ten
 */

/**
 * Exercise the daily roll-up with a sizeable number of hourly rows.
 */
class TopTenRollupStressTest extends WP_UnitTestCase {

	/**
	 * Seed daily rows in batches to keep the test memory usage bounded.
	 *
	 * @param string $table  Daily table name.
	 * @param array  $values Prepared value tuples.
	 */
	private function insert_daily_batch( $table, array $values ) {
		global $wpdb;

		$this->assertNotFalse(
			$wpdb->query(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO `{$table}` (postnumber, cntaccess, dp_date, blog_id) VALUES " . implode( ',', $values )
			),
			$wpdb->last_error
		);
	}

	/**
	 * Roll up a large data set without losing rows or crossing blog/date boundaries.
	 *
	 * @group rollup-stress
	 */
	public function test_large_rollup_preserves_counts_and_is_idempotent() {
		global $wpdb;

		\WebberZone\Top_Ten\Admin\Activator::create_tables();

		$requested_rows = (int) getenv( 'TOP_TEN_ROLLUP_ROWS' );
		$this->assertGreaterThan( 0, $requested_rows, 'TOP_TEN_ROLLUP_ROWS must be a positive integer.' );

		$blog_id       = get_current_blog_id();
		$other_blog_id = $blog_id + 1000;
		$daily_table   = \WebberZone\Top_Ten\Database::get_table( true );
		$overall_table = \WebberZone\Top_Ten\Database::get_table();
		$before_date   = '2000-01-05 00:00:00';
		$base_post_id  = 900000000 + (int) getmypid();
		$post_count    = max( 1, (int) floor( $requested_rows / 96 ) );
		$old_rows      = $post_count * 96;
		$old_sum       = $old_rows;
		$values        = array();

		$baseline = \WebberZone\Top_Ten\Database::get_daily_rollup_stats( $before_date, $blog_id );
		$this->assertIsArray( $baseline );

		try {
			for ( $post_index = 0; $post_index < $post_count; $post_index++ ) {
				$post_id = $base_post_id - $post_index;

				for ( $day = 1; $day <= 4; $day++ ) {
					for ( $hour = 0; $hour < 24; $hour++ ) {
						$values[] = $wpdb->prepare(
							'( %d, %d, %s, %d )',
							$post_id,
							1,
							sprintf( '2000-01-%02d %02d:00:00', $day, $hour ),
							$blog_id
						);

						if ( 500 <= count( $values ) ) {
							$this->insert_daily_batch( $daily_table, $values );
							$values = array();
						}
					}
				}
			}

			if ( ! empty( $values ) ) {
				$this->insert_daily_batch( $daily_table, $values );
			}

			// A recent row must remain hourly and a row on another blog must be untouched.
			$this->insert_daily_batch(
				$daily_table,
				array(
					$wpdb->prepare( '( %d, %d, %s, %d )', $base_post_id, 1, '2000-01-05 01:00:00', $blog_id ),
					$wpdb->prepare( '( %d, %d, %s, %d )', $base_post_id, 1, '2000-01-01 00:00:00', $other_blog_id ),
				)
			);

			$other_blog_values = array();
			for ( $hour = 1; $hour < 24; $hour++ ) {
				$other_blog_values[] = $wpdb->prepare(
					'( %d, %d, %s, %d )',
					$base_post_id,
					1,
					sprintf( '2000-01-01 %02d:00:00', $hour ),
					$other_blog_id
				);
			}
			$this->insert_daily_batch( $daily_table, $other_blog_values );

			$this->assertNotFalse(
				$wpdb->insert(
					$overall_table,
					array(
						'postnumber' => $base_post_id,
						'cntaccess'  => 999,
						'blog_id'    => $blog_id,
					),
					array( '%d', '%d', '%d' )
				)
			);

			$seeded = \WebberZone\Top_Ten\Database::get_daily_rollup_stats( $before_date, $blog_id );
			$this->assertIsArray( $seeded );
			$this->assertSame( $baseline['rows_before'] + $old_rows, $seeded['rows_before'] );
			$this->assertSame( $baseline['rows_after'] + ( $post_count * 4 ), $seeded['rows_after'] );

			$result = \WebberZone\Top_Ten\Database::rollup_daily( $before_date, $blog_id );
			$this->assertIsArray( $result );
			$this->assertSame( 4, $result['dates_processed'] );
			$this->assertSame( $seeded['rows_before'], $result['rows_before'] );
			$this->assertSame( $seeded['rows_before'] - ( $old_rows - ( $post_count * 4 ) ), $result['rows_after'] );
			$this->assertSame( $old_rows - ( $post_count * 4 ), $result['rows_reduced'] );
			$this->assertSame( 999, \WebberZone\Top_Ten\Database::get_count( $base_post_id, $blog_id ) );

			$current_old_rows = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$daily_table}` WHERE postnumber BETWEEN %d AND %d AND blog_id = %d AND dp_date < %s",
					$base_post_id - $post_count + 1,
					$base_post_id,
					$blog_id,
					$before_date
				)
			);
			$current_old_sum = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(cntaccess) FROM `{$daily_table}` WHERE postnumber BETWEEN %d AND %d AND blog_id = %d AND dp_date < %s",
					$base_post_id - $post_count + 1,
					$base_post_id,
					$blog_id,
					$before_date
				)
			);
			$this->assertSame( $post_count * 4, $current_old_rows );
			$this->assertSame( $old_sum, $current_old_sum );

			$this->assertSame(
				1,
				(int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `{$daily_table}` WHERE postnumber = %d AND blog_id = %d AND dp_date >= %s",
						$base_post_id,
						$blog_id,
						$before_date
					)
				)
			);
			$this->assertSame(
				24,
				(int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `{$daily_table}` WHERE postnumber = %d AND blog_id = %d",
						$base_post_id,
						$other_blog_id
					)
				)
			);

			$second_result = \WebberZone\Top_Ten\Database::rollup_daily( $before_date, $blog_id );
			$this->assertIsArray( $second_result );
			$this->assertSame( 0, $second_result['dates_processed'] );
			$this->assertSame( 0, $second_result['rows_reduced'] );
			$this->assertSame( $post_count * 4 + $baseline['rows_before'], $second_result['rows_before'] );
		} finally {
			$min_post_id = $base_post_id - $post_count + 1;
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$daily_table}` WHERE postnumber BETWEEN %d AND %d",
					$min_post_id,
					$base_post_id
				)
			);
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `{$overall_table}` WHERE postnumber BETWEEN %d AND %d",
					$min_post_id,
					$base_post_id
				)
			);
		}
	}
}
