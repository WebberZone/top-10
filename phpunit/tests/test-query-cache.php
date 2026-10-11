<?php

use WebberZone\Top_Ten\Admin\Activator;
use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Top_Ten_Core_Query;
use WebberZone\Top_Ten\Util\Cache;

class QueryCacheTest extends WP_UnitTestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();
		Activator::create_tables();
	}

	private function args( array $args = array() ): array {
		return array_merge( tptn_settings_defaults(), array( 'cache_posts' => true, 'cache_results' => false, 'exclude_current_post' => false, 'limit' => 1, 'strict_limit' => true ), $args );
	}

	private function key( array $args = array() ): string {
		$core = new Top_Ten_Core_Query( $this->args( $args ) );
		$property = new ReflectionProperty( $core, 'cache_name' );
		if ( PHP_VERSION_ID < 80100 ) {
			$property->setAccessible( true );
		}
		return $property->getValue( $core );
	}

	public function test_generated_date_bounds_do_not_change_the_key_each_second() {
		$args = array( 'daily' => true, 'daily_range' => 30, 'how_old' => 90 );
		$key = $this->key( $args );
		usleep( 1100000 );
		$this->assertSame( $key, $this->key( $args ) );
		$this->assertNotSame( $key, $this->key( array_merge( $args, array( 'daily_range' => 7 ) ) ) );
	}

	public function test_explicit_date_bounds_remain_part_of_the_key() {
		$this->assertNotSame(
			$this->key( array( 'date_query' => array( array( 'before' => '2020-01-01' ) ) ) ),
			$this->key( array( 'date_query' => array( array( 'before' => '2021-01-01' ) ) ) )
		);
	}

	public function test_filtered_date_bounds_remain_part_of_the_key() {
		$before = '2020-01-01';
		$filter = static function ( $date_query ) use ( &$before ) {
			$date_query[0]['before'] = $before;
			return $date_query;
		};
		add_filter( 'top_ten_query_date_query', $filter );
		try {
			$key = $this->key();
			$before = '2021-01-01';
			$this->assertNotSame( $key, $this->key() );
		} finally {
			remove_filter( 'top_ten_query_date_query', $filter );
		}
	}

	public function test_cached_query_reuses_results_after_the_clock_changes() {
		$id = self::factory()->post->create();
		Database::set_count( $id, 5 );
		$args = $this->args( array( 'post__in' => array( $id ) ) );
		$cold = new Top_Ten_Query( $args );
		$this->assertSame( array( $id ), wp_list_pluck( $cold->posts, 'ID' ) );
		usleep( 1100000 );
		wp_cache_flush_runtime();
		$queries = array();
		$record = static function ( $sql ) use ( &$queries ) {
			$queries[] = $sql;
			return $sql;
		};
		add_filter( 'query', $record );
		try {
			$warm = new Top_Ten_Query( $args );
		} finally {
			remove_filter( 'query', $record );
		}
		$this->assertSame( array( $id ), wp_list_pluck( $warm->posts, 'ID' ) );
		$this->assertSame( array(), array_values( preg_grep( '/JOIN.*top_ten/i', $queries ) ) );
	}

	public function test_count_edits_invalidate_cached_ranking() {
		$ids = self::factory()->post->create_many( 2 );
		Database::set_count( $ids[0], 5 );
		Database::set_count( $ids[1], 2 );
		$args = $this->args( array( 'post__in' => $ids ) );
		$this->assertSame( array( $ids[0] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
		Database::set_count( $ids[1], 10 );
		$this->assertSame( array( $ids[1] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
	}

	public function test_funnel_aggregation_invalidates_cached_ranking() {
		$ids = self::factory()->post->create_many( 2 );
		Database::set_count( $ids[0], 1 );
		Database::set_count( $ids[1], 0 );
		$args = $this->args( array( 'post__in' => $ids ) );
		$this->assertSame( array( $ids[0] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
		Database::append_to_funnel( $ids[1], get_current_blog_id(), 11 );
		Database::append_to_funnel( $ids[1], get_current_blog_id(), 11 );
		$this->assertTrue( Database::aggregate_visit_log( 10000, get_current_blog_id() ) );
		$this->assertSame( array( $ids[1] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
	}

	public function test_direct_tracking_invalidates_cached_ranking() {
		$ids = self::factory()->post->create_many( 2 );
		Database::set_count( $ids[0], 1 );
		Database::set_count( $ids[1], 0 );
		$args = $this->args( array( 'post__in' => $ids ) );
		$this->assertSame( array( $ids[0] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
		Database::update_counts_direct( $ids[1], get_current_blog_id(), 11 );
		Database::update_counts_direct( $ids[1], get_current_blog_id(), 11 );
		$this->assertSame( array( $ids[1] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
	}

	public function test_bulk_import_invalidates_cached_ranking() {
		$id = self::factory()->post->create();
		$before = $this->key();
		Database::bulk_upsert( array( array( 'postnumber' => $id, 'cntaccess' => 10, 'blog_id' => get_current_blog_id() ) ) );
		$this->assertNotSame( $before, $this->key() );
	}

	public function test_count_deletion_invalidates_cached_ranking() {
		$ids = self::factory()->post->create_many( 2 );
		Database::set_count( $ids[0], 5 );
		Database::set_count( $ids[1], 2 );
		$args = $this->args( array( 'post__in' => $ids ) );
		$this->assertSame( array( $ids[0] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
		Database::delete_counts( array( 'post_ids' => array( $ids[0] ), 'blog_id' => get_current_blog_id() ) );
		$this->assertSame( array( $ids[1] ), wp_list_pluck( ( new Top_Ten_Query( $args ) )->posts, 'ID' ) );
	}

	public function test_date_bounds_changed_by_the_final_args_filter_are_preserved() {
		$before = '2020-01-01';
		$filter = static function ( $args ) use ( &$before ) {
			$args['date_query'][0]['before'] = $before;
			return $args;
		};
		add_filter( 'top_ten_query_args', $filter );
		try {
			$key = $this->key();
			$before = '2021-01-01';
			$this->assertNotSame( $key, $this->key() );
		} finally {
			remove_filter( 'top_ten_query_args', $filter );
		}
	}

	public function test_counts_changed_on_another_blog_invalidate_network_caches() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Requires multisite.' );
		}
		$blog_id = self::factory()->blog->create();
		$key = $this->key( array( 'blog_id' => array( get_current_blog_id(), $blog_id ) ) );
		$local_key = $this->key();
		switch_to_blog( $blog_id );
		try {
			Database::set_count( 1, 5 );
		} finally {
			restore_current_blog();
		}
		$this->assertNotSame( $key, $this->key( array( 'blog_id' => array( get_current_blog_id(), $blog_id ) ) ) );
		$this->assertSame( $local_key, $this->key() );
	}

	public function test_scoped_bulk_import_and_deletion_preserve_unrelated_caches() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Requires multisite.' );
		}
		$other = self::factory()->blog->create();
		$home = get_current_blog_id();
		$local_key = $this->key();
		$remote_args = array( 'blog_id' => array( $home, $other ) );
		$remote_key = $this->key( $remote_args );
		Database::bulk_upsert( array( array( 'postnumber' => 1, 'cntaccess' => 10, 'blog_id' => $other ) ) );
		$this->assertSame( $local_key, $this->key() );
		$this->assertNotSame( $remote_key, $this->key( $remote_args ) );
		$remote_key = $this->key( $remote_args );
		Database::delete_counts( array( 'blog_id' => $other ) );
		$this->assertSame( $local_key, $this->key() );
		$this->assertNotSame( $remote_key, $this->key( $remote_args ) );
	}

	public function test_network_funnel_drain_invalidates_only_sites_with_visits() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Requires multisite.' );
		}
		$other = self::factory()->blog->create();
		$home = get_current_blog_id();
		$local_key = $this->key();
		$args = array( 'blog_id' => array( $home, $other ) );
		$network_key = $this->key( $args );
		Database::append_to_funnel( 1, $other, 11 );
		$this->assertTrue( Database::aggregate_visit_log() );
		$this->assertSame( $local_key, $this->key() );
		$this->assertNotSame( $network_key, $this->key( $args ) );
	}

	public function test_post_edit_preserves_other_sites_and_invalidates_included_site() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Requires multisite.' );
		}
		$other = self::factory()->blog->create();
		$local_key = $this->key();
		$args = array( 'blog_id' => array( get_current_blog_id(), $other ) );
		$network_key = $this->key( $args );
		switch_to_blog( $other );
		try {
			$id = self::factory()->post->create();
			wp_update_post( array( 'ID' => $id, 'post_title' => 'Updated title' ) );
		} finally {
			restore_current_blog();
		}
		$this->assertSame( $local_key, $this->key() );
		$this->assertNotSame( $network_key, $this->key( $args ) );
	}

	public function test_unknown_bulk_scope_still_invalidates_all_sites() {
		$key = $this->key();
		do_action( 'tptn_count_updated', 0, 0, false );
		$this->assertNotSame( $key, $this->key() );
		$key = $this->key();
		Database::delete_counts();
		$this->assertNotSame( $key, $this->key() );
	}

	public function test_scoped_invalidation_refreshes_output_keys_and_explicit_clear_remains_global() {
		$args = array( 'blog_id' => get_current_blog_id() );
		$key = Cache::get_key( $args );
		set_transient( $key, 'old output', 60 );
		Database::set_count( 1, 2 );
		$this->assertNotSame( $key, Cache::get_key( $args ) );
		$this->assertFalse( get_transient( Cache::get_key( $args ) ) );
		$key = Cache::get_key( $args );
		Cache::invalidate();
		$this->assertNotSame( $key, Cache::get_key( $args ) );
	}

	public function test_network_cache_preserves_colliding_post_ids_and_source_sites() {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Requires multisite.' );
		}
		add_filter( 'tptn_allow_network_queries', '__return_true' );
		$home  = get_current_blog_id();
		$other = self::factory()->blog->create();
		$id    = self::factory()->post->create( array( 'post_title' => 'Local ranking' ) );
		Database::set_count( $id, 5, $home );
		switch_to_blog( $other );
		try {
			$remote = wp_insert_post( array( 'import_id' => $id, 'post_title' => 'Remote ranking', 'post_status' => 'publish' ) );
			$this->assertSame( $id, $remote );
			Database::set_count( $remote, 10, $other );
		} finally {
			restore_current_blog();
		}
		$args = $this->args( array( 'blog_id' => array( $home, $other ), 'limit' => 2, 'posts_per_page' => 2, 'post__in' => array( $id ) ) );
		$expected = array( array( $other, $id, 'Remote ranking' ), array( $home, $id, 'Local ranking' ) );
		foreach ( array( true, true, false ) as $cached ) {
			$args['cache_posts'] = $cached;
			$posts = ( new Top_Ten_Query( $args ) )->posts;
			$actual = array_map( static function ( $post ) {
				return array( (int) $post->blog_id, $post->ID, $post->post_title );
			}, $posts );
			$this->assertSame( $expected, $actual );
			$this->assertSame( $home, get_current_blog_id() );
		}
		$args['cache_posts'] = true;
		Database::set_count( $id, 15, $home );
		$posts = ( new Top_Ten_Query( $args ) )->posts;
		$this->assertSame( $home, (int) $posts[0]->blog_id );
		$this->assertSame( 15, (int) $posts[0]->visits );
	}

	public function test_translation_preserves_ranking_metadata_and_handles_missing_translation() {
		$id = self::factory()->post->create();
		$translated = self::factory()->post->create();
		$post = clone get_post( $id );
		$post->blog_id = get_current_blog_id();
		$post->visits = 17;
		$filter = static function () use ( $translated ) { return $translated; };
		add_filter( 'wpml_object_id', $filter );
		try {
			$result = \WebberZone\Top_Ten\Frontend\Language_Handler::object_id_cur_lang( $post );
			$this->assertSame( $translated, $result->ID );
			$this->assertSame( 17, $result->visits );
			$this->assertSame( $post->blog_id, $result->blog_id );
		} finally {
			remove_filter( 'wpml_object_id', $filter );
		}
		$filter = static function () { return null; };
		add_filter( 'wpml_object_id', $filter );
		try {
			$this->assertNull( \WebberZone\Top_Ten\Frontend\Language_Handler::object_id_cur_lang( $post ) );
		} finally {
			remove_filter( 'wpml_object_id', $filter );
		}
	}

	public function test_cache_clear_refreshes_generation_without_transient_enumeration() {
		$args = array( 'cache_clear_regression' => wp_generate_uuid4() );
		$key = Cache::get_key( $args );
		set_transient( $key, 'old output', 60 );
		Cache::delete();
		$this->assertNotSame( $key, Cache::get_key( $args ) );
		$this->assertFalse( get_transient( Cache::get_key( $args ) ) );
	}

}
