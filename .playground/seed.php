<?php
/**
 * Top 10 — WordPress Playground demo seeder.
 *
 * Assumes WordPress is already loaded (the blueprint requires wp-load.php
 * before requiring this file). Run once, after the demo content has been
 * imported and the plugin activated. Safe to re-run: count tables are
 * cleared before reseeding and the demo page is upserted by slug.
 *
 * @package WebberZone\Top_Ten
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

/*
 * ---------------------------------------------------------------------------
 * 1. Settings — start from the plugin defaults, then override a few keys so
 *    the demo shows counts, excerpts and dates out of the box.
 * ---------------------------------------------------------------------------
 */
$settings = function_exists( 'tptn_get_settings' )
	? tptn_get_settings()
	: (array) get_option( 'tptn_settings', array() );

$settings = array_merge(
	$settings,
	array(
		'add_to'             => 'single,page,home',
		'limit'              => 10,
		'disp_list_count'    => true,
		'show_excerpt'       => true,
		'excerpt_length'     => 15,
		'show_date'          => true,
		'title_length'       => 60,
		'thumb_default_show' => true,
	)
);

update_option( 'tptn_settings', $settings );

/*
 * ---------------------------------------------------------------------------
 * 2. View counts — seed the total and daily tables so the popular-posts
 *    queries return data immediately (a fresh import has zero views).
 * ---------------------------------------------------------------------------
 */
$total_table = $wpdb->base_prefix . 'top_ten';
$daily_table = $wpdb->base_prefix . 'top_ten_daily';
$blog_id     = get_current_blog_id();

$post_ids = get_posts(
	array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

if ( empty( $post_ids ) ) {
	return;
}

// Clear any prior demo rows (DELETE — SQLite under Playground has no TRUNCATE).
$wpdb->query( "DELETE FROM {$total_table}" ); // phpcs:ignore WordPress.DB
$wpdb->query( "DELETE FROM {$daily_table}" ); // phpcs:ignore WordPress.DB

mt_srand( 42 ); // Deterministic counts so the demo looks the same every spin-up.

foreach ( $post_ids as $i => $post_id ) {
	// Stagger totals so the ranking looks intentional, with a little jitter.
	$base  = max( 25, 1200 - ( $i * 30 ) );
	$total = $base + mt_rand( 0, 150 );

	$wpdb->insert(
		$total_table,
		array(
			'postnumber' => $post_id,
			'cntaccess'  => $total,
			'blog_id'    => $blog_id,
		),
		array( '%d', '%d', '%d' )
	);

	// Spread part of the total across the last 7 days for the daily / range views.
	for ( $d = 0; $d < 7; $d++ ) {
		$wpdb->insert(
			$daily_table,
			array(
				'postnumber' => $post_id,
				'cntaccess'  => mt_rand( 1, max( 2, (int) ( $total / 20 ) ) ),
				'dp_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-{$d} days" ) ),
				'blog_id'    => $blog_id,
			),
			array( '%d', '%d', '%s', '%d' )
		);
	}
}

/*
 * ---------------------------------------------------------------------------
 * 3. A front-end demo page that lists popular posts via the shortcode, so the
 *    blueprint can land somewhere that visibly shows the plugin working.
 * ---------------------------------------------------------------------------
 */
$existing  = get_page_by_path( 'popular-posts' );
$page_args = array(
	'post_title'   => 'Popular Posts',
	'post_name'    => 'popular-posts',
	'post_status'  => 'publish',
	'post_type'    => 'page',
	'post_content' => "<!-- wp:shortcode -->\n[tptn_list heading=\"0\" limit=\"10\"]\n<!-- /wp:shortcode -->",
);
if ( $existing ) {
	$page_args['ID'] = $existing->ID;
}
wp_insert_post( $page_args );

// Pretty permalinks + flush so /popular-posts/ resolves for the landing page.
update_option( 'permalink_structure', '/%postname%/' );
flush_rewrite_rules( false );
