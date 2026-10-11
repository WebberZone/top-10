<?php

use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Database;

class DailyQueryTest extends WP_UnitTestCase {

	private $fixture_ids = array();
	private static $fixture_blog_id = 0;

	public function tear_down() {
		global $wpdb;
		foreach ( $this->fixture_ids as $id ) {
			$wpdb->delete( Database::get_table( true ), array( 'postnumber' => $id ), array( '%d' ) );
		}
		parent::tear_down();
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();
		Activator::create_tables();
		if ( is_multisite() ) {
			self::$fixture_blog_id = self::factory()->blog->create();
		}
	}

	public static function tear_down_after_class() {
		if ( self::$fixture_blog_id ) {
			wpmu_delete_blog( self::$fixture_blog_id, true );
		}
		parent::tear_down_after_class();
	}

	private function query( array $args = array() ): Top_Ten_Query {
		return new Top_Ten_Query( array_merge( tptn_settings_defaults(), array( 'daily' => true, 'daily_range' => 90, 'cache_posts' => false, 'cache_results' => false, 'exclude_current_post' => false, 'limit' => 10, 'strict_limit' => true ), $args ) );
	}

	private function seed(): array {
		global $wpdb;
		$ids = self::factory()->post->create_many( 3 );
		$this->fixture_ids = $ids;
		$today = current_time( 'Y-m-d' ) . ' 00:00:00';
		foreach ( array( array( $ids[0], 10, $today ), array( $ids[0], 15, '2020-01-02 00:00:00' ), array( $ids[1], 25, $today ), array( $ids[2], 25, $today ) ) as $row ) {
			$wpdb->insert( Database::get_table( true ), array( 'postnumber' => $row[0], 'cntaccess' => $row[1], 'dp_date' => $row[2], 'blog_id' => get_current_blog_id() ) );
		}
		$wpdb->insert( Database::get_table( true ), array( 'postnumber' => $ids[0], 'cntaccess' => 1000, 'dp_date' => $today, 'blog_id' => get_current_blog_id() + 100 ) );
		return $ids;
	}

	public function test_daily_aggregation_preserves_counts_order_and_blog_scope() {
		$ids = $this->seed();
		$q = $this->query( array( 'post__in' => $ids ) );
		$this->assertStringContainsString( 'INNER JOIN (SELECT postnumber', $q->request );
		$this->assertStringContainsString( 'postnumber IN (' . implode( ',', $ids ) . ')', $q->request );
		$this->assertSame( array( $ids[2], $ids[1], $ids[0] ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertSame( array( '25', '25', '10' ), wp_list_pluck( $GLOBALS['wpdb']->get_results( $q->request ), 'visits' ) );
		$this->assertNotEmpty( $q->posts[0]->post_title );
	}

	public function test_explicit_date_bounds_and_offset() {
		$ids = $this->seed();
		$q = $this->query( array( 'post__in' => $ids, 'from_date' => '2020-01-01', 'to_date' => '2020-01-03' ) );
		$this->assertSame( array( $ids[0] ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertSame( '15', $GLOBALS['wpdb']->get_results( $q->request )[0]->visits );
		$q = $this->query( array( 'post__in' => $ids, 'offset' => 1, 'limit' => 1 ) );
		$this->assertSame( array( $ids[1] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_custom_daily_sql_keeps_original_table_available() {
		$ids = $this->seed();
		$filter = static function ( $where, $query ) {
			return $where . " AND {$query->table_name}.cntaccess >= 20";
		};
		add_filter( 'top_ten_query_posts_where', $filter, 10, 2 );
		try {
			$q = $this->query( array( 'post__in' => $ids ) );
		} finally {
			remove_filter( 'top_ten_query_posts_where', $filter );
		}
		$this->assertStringNotContainsString( 'INNER JOIN (SELECT postnumber', $q->request );
		$this->assertSame( array( $ids[2], $ids[1] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_meta_join_keeps_ranking_semantics() {
		$ids = $this->seed();
		update_post_meta( $ids[0], 'daily_fixture', 'yes' );
		update_post_meta( $ids[2], 'daily_fixture', 'yes' );
		$q = $this->query( array( 'post__in' => $ids, 'meta_key' => 'daily_fixture', 'meta_value' => 'yes' ) );
		$this->assertStringContainsString( 'INNER JOIN (SELECT postnumber', $q->request );
		$this->assertSame( array( $ids[2], $ids[0] ), wp_list_pluck( $q->posts, 'ID' ) );
	}
	public function test_optimized_and_legacy_sql_return_identical_rankings() {
		global $wpdb;
		$ids = $this->seed();
		update_post_meta( $ids[0], 'daily_fixture', 'yes' );
		add_post_meta( $ids[0], 'daily_fixture', 'yes' );
		foreach ( array( array(), array( 'from_date' => '2020-01-01' ), array( 'order' => 'ASC' ), array( 'limit' => 1, 'offset' => 1 ), array( 'post__not_in' => array( $ids[2] ) ), array( 'meta_query' => array( array( 'key' => 'daily_fixture', 'compare' => 'EXISTS' ) ) ) ) as $args ) {
			$args['post__in'] = $ids;
			$optimized = $this->query( $args );
			$rows = $wpdb->get_results( $optimized->request, ARRAY_A );
			$filter = static function ( $clauses ) { return $clauses; };
			add_filter( 'top_ten_query_posts_clauses', $filter );
			try {
				$legacy = $this->query( $args );
			} finally {
				remove_filter( 'top_ten_query_posts_clauses', $filter );
			}
			$this->assertStringNotContainsString( 'INNER JOIN (SELECT postnumber', $legacy->request );
			$this->assertSame( $rows, $wpdb->get_results( $legacy->request, ARRAY_A ) );
		}
	}

	public function test_taxonomy_join_preserves_counts_and_exclusions() {
		global $wpdb;
		$ids = $this->seed();
		$term = self::factory()->term->create( array( 'taxonomy' => 'category' ) );
		wp_set_post_terms( $ids[0], array( $term ), 'category' );
		wp_set_post_terms( $ids[2], array( $term ), 'category' );
		$q = $this->query( array( 'category__in' => array( $term ) ) );
		$this->assertStringContainsString( 'INNER JOIN (SELECT postnumber', $q->request );
		$this->assertSame( array( $ids[2], $ids[0] ), wp_list_pluck( $q->posts, 'ID' ) );
		$this->assertSame( array( '25', '10' ), wp_list_pluck( $wpdb->get_results( $q->request ), 'visits' ) );
	}

	public function test_network_queries_are_limited_to_current_site_unless_allowed() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite only.' );
		}
		$args = array( 'blog_id' => array( get_current_blog_id(), self::$fixture_blog_id ) );
		add_filter( 'tptn_allow_network_queries', '__return_false' );
		$q = new \WebberZone\Top_Ten\Top_Ten_Core_Query( $args );
		$this->assertFalse( $q->multiple_blogs );
		$this->assertSame( array( get_current_blog_id() ), $q->blog_id );
		remove_filter( 'tptn_allow_network_queries', '__return_false' );
		add_filter( 'tptn_allow_network_queries', '__return_true' );
		$q = new \WebberZone\Top_Ten\Top_Ten_Core_Query( $args );
		$this->assertTrue( $q->multiple_blogs );
	}

	public function test_multisite_aggregation_preserves_blog_scope() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite only.' );
		}
		add_filter( 'tptn_allow_network_queries', '__return_true' );
		global $wpdb;
		$ids = $this->seed();
		$blog_id = self::$fixture_blog_id;
		switch_to_blog( $blog_id );
		$other_id = self::factory()->post->create();
		$wpdb->insert( Database::get_table( true ), array( 'postnumber' => $other_id, 'cntaccess' => 30, 'dp_date' => current_time( 'Y-m-d' ) . ' 00:00:00', 'blog_id' => $blog_id ) );
		restore_current_blog();
		$this->fixture_ids[] = $other_id;
		$q = $this->query( array( 'blog_id' => array( get_current_blog_id(), $blog_id ) ) );
		$this->assertStringContainsString( 'INNER JOIN (SELECT postnumber', $q->request );
		$this->assertSame( '', $wpdb->last_error );
		$rows = $wpdb->get_results( $q->request, ARRAY_A );
		$this->assertSame( '30', $rows[0]['visits'] );
		$this->assertSame( (string) $blog_id, $rows[0]['blog_id'] );
		$this->assertSame( array( '30', '25', '25', '10' ), array_column( $rows, 'visits' ) );
		$repeat = $this->query( array( 'blog_id' => array( get_current_blog_id(), $blog_id ) ) );
		$this->assertSame( $rows, $wpdb->get_results( $repeat->request, ARRAY_A ) );
		$this->assertSame( '', $wpdb->last_error );
		update_post_meta( $ids[0], 'cross_site_fixture', 'yes' );
		switch_to_blog( $blog_id );
		update_post_meta( $other_id, 'cross_site_fixture', 'yes' );
		restore_current_blog();
		$filtered = $this->query( array( 'blog_id' => array( get_current_blog_id(), $blog_id ), 'meta_key' => 'cross_site_fixture', 'meta_value' => 'yes' ) );
		$this->assertSame( array( '30', '10' ), wp_list_pluck( $wpdb->get_results( $filtered->request ), 'visits' ) );
		$this->assertSame( '', $wpdb->last_error );
	}

	public function test_post_id_that_overrides_post_in_is_not_restricted() {
		$ids = $this->seed();
		$q = $this->query( array( 'p' => $ids[2], 'post__in' => array( $ids[0] ) ) );
		$this->assertStringNotContainsString( 'postnumber IN (', $q->request );
		$this->assertStringContainsString( 'postnumber = ' . $ids[2], $q->request );
		$this->assertSame( array( $ids[2] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

	public function test_filtered_cache_hit_skips_the_eligible_post_lookup() {
		$ids = $this->seed();
		update_post_meta( $ids[0], 'daily_fixture', 'yes' );
		$args = array( 'cache_posts' => true, 'meta_key' => 'daily_fixture', 'meta_value' => 'yes' );
		$cold = $this->query( $args );
		$sql = array();
		$filter = static function ( $query ) use ( &$sql ) { $sql[] = $query; return $query; };
		add_filter( 'query', $filter );
		try {
			$warm = $this->query( $args );
		} finally {
			remove_filter( 'query', $filter );
		}
		$this->assertSame( wp_list_pluck( $cold->posts, 'ID' ), wp_list_pluck( $warm->posts, 'ID' ) );
		$this->assertSame( array(), array_values( preg_grep( '/SELECT DISTINCT.*LIMIT 501/', $sql ) ) );
	}

	public function test_filtered_query_with_no_matching_posts_stays_empty() {
		$this->seed();
		$q = $this->query( array( 'meta_key' => 'missing_daily_fixture', 'meta_value' => 'yes' ) );
		$this->assertSame( array(), $q->posts );
		$this->assertSame( '', $GLOBALS['wpdb']->last_error );
	}

	public function test_post_becoming_eligible_after_probe_is_still_included() {
		$ids = $this->seed();
		update_post_meta( $ids[0], 'daily_fixture', 'yes' );
		$updated = false;
		$filter = static function ( $sql ) use ( &$updated, $ids ) {
			if ( ! $updated && false !== strpos( $sql, 'INNER JOIN (SELECT postnumber' ) ) {
				$updated = true;
				update_post_meta( $ids[2], 'daily_fixture', 'yes' );
			}
			return $sql;
		};
		add_filter( 'query', $filter );
		try {
			$q = $this->query( array( 'meta_key' => 'daily_fixture', 'meta_value' => 'yes' ) );
		} finally {
			remove_filter( 'query', $filter );
		}
		$this->assertTrue( $updated );
		$this->assertSame( array( $ids[2], $ids[0] ), wp_list_pluck( $q->posts, 'ID' ) );
	}

}
