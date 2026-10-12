<?php

use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Feed_Counts;
use WebberZone\Top_Ten\Util\Csv_Helper;

class FeedCountsTest extends WP_UnitTestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();
		Activator::create_tables();
	}

	public function set_up() {
		parent::set_up();
		Feed_Counts::maybe_upgrade();
	}

	private function counts( int $post_id, bool $daily = false ): array {
		global $wpdb;
		$table = Database::get_table( $daily );
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(cntaccess) AS total, SUM(feedcnt) AS feed FROM {$table} WHERE postnumber = %d AND blog_id = %d", $post_id, get_current_blog_id() ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return array( (int) $row['total'], (int) $row['feed'] );
	}

	public function test_feed_counts_are_enabled_with_both_columns() {
		$this->assertTrue( Feed_Counts::is_enabled() );
		$this->assertFalse( Feed_Counts::is_pending() );
		$this->assertTrue( Feed_Counts::table_has_column( Database::get_table( false ), true ) );
		$this->assertTrue( Feed_Counts::table_has_column( Database::get_table( true ), true ) );
	}

	public function test_aggregation_counts_feed_views_separately() {
		$id = self::factory()->post->create();
		Database::append_to_funnel( $id, get_current_blog_id(), 11, 1 );
		Database::append_to_funnel( $id, get_current_blog_id(), 11, 1 );
		Database::append_to_funnel( $id, get_current_blog_id(), 11, 0 );
		$this->assertTrue( Database::aggregate_visit_log( 10000, get_current_blog_id() ) );
		$this->assertSame( array( 3, 2 ), $this->counts( $id ) );
		$this->assertSame( array( 3, 2 ), $this->counts( $id, true ) );
	}

	public function test_legacy_direct_writes_count_feed_views() {
		$id = self::factory()->post->create();
		Database::update_counts_direct( $id, get_current_blog_id(), 11, 1 );
		Database::update_counts_direct( $id, get_current_blog_id(), 11, 0 );
		$this->assertSame( array( 2, 1 ), $this->counts( $id ) );
		$this->assertSame( array( 2, 1 ), $this->counts( $id, true ) );
	}

	public function test_set_count_clamps_feed_views_to_the_new_total() {
		$id = self::factory()->post->create();
		Database::update_counts_direct( $id, get_current_blog_id(), 1, 1 );
		Database::update_counts_direct( $id, get_current_blog_id(), 1, 1 );
		Database::set_count( $id, 1 );
		$this->assertSame( array( 1, 1 ), $this->counts( $id ) );
		Database::set_count( $id, 0 );
		$this->assertSame( array( 0, 0 ), $this->counts( $id ) );
	}

	public function test_bulk_upsert_sets_or_clamps_feed_views() {
		$id   = self::factory()->post->create();
		$blog = get_current_blog_id();
		Database::bulk_upsert( array( array( 'postnumber' => $id, 'cntaccess' => 10, 'blog_id' => $blog, 'feedcnt' => 4 ) ) );
		$this->assertSame( array( 10, 4 ), $this->counts( $id ) );
		Database::bulk_upsert( array( array( 'postnumber' => $id, 'cntaccess' => 3, 'blog_id' => $blog ) ) );
		$this->assertSame( array( 3, 3 ), $this->counts( $id ) );
		Database::bulk_upsert( array( array( 'postnumber' => $id, 'cntaccess' => 5, 'blog_id' => $blog, 'feedcnt' => 9 ) ) );
		$this->assertSame( array( 5, 5 ), $this->counts( $id ) );
	}

	public function test_csv_round_trip_keeps_feed_visits_and_reads_old_files() {
		$file = tempnam( sys_get_temp_dir(), 'tptn-feed' );
		$fh   = fopen( $file, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		Csv_Helper::write_export_csv(
			$fh,
			array(
				array( 'postnumber' => 7, 'cntaccess' => 12, 'blog_id' => 1, 'feedcnt' => 5 ),
			),
			false
		);
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$parsed = Csv_Helper::parse_import_file( $file );
		$this->assertSame( 5, $parsed['rows'][0]['feedcnt'] );
		$this->assertSame( 12, $parsed['rows'][0]['cntaccess'] );

		$fh = fopen( $file, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		Csv_Helper::write_export_csv( $fh, array( array( 'postnumber' => 7, 'cntaccess' => 12, 'blog_id' => 1 ) ), false );
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$parsed = Csv_Helper::parse_import_file( $file );
		$this->assertArrayNotHasKey( 'feedcnt', $parsed['rows'][0] );
		unlink( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	public function test_schema_omits_column_only_for_existing_tables_without_it() {
		$this->assertTrue( Feed_Counts::schema_includes_column( 'wp_tptn_table_that_does_not_exist' ) );
		$this->assertStringContainsString( 'feedcnt', Database::create_full_table_sql( true ) );
		$this->assertStringNotContainsString( 'feedcnt', Database::create_daily_table_sql( false ) );
	}
}
