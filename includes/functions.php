<?php
/**
 * Top 10 Frontend functions.
 *
 * @since 3.3.0
 *
 * @package WebberZone\Top_Ten
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Function to manually display count.
 *
 * @since  1.0
 * @param   int|boolean $echo_output Flag to echo the output.
 * @return  string  Formatted string if $echo_output is set to 0|false
 */
function echo_tptn_post_count( $echo_output = 1 ) {
	return \WebberZone\Top_Ten\Counter::echo_post_count( $echo_output );
}

/**
 * Function to manually display count.
 *
 * @since   1.9.2
 * @param   int|string|\WP_Post $post       Post ID or WP_Post object.
 * @param   int|string          $blog_id    Blog ID.
 * @return  string  Formatted post count
 */
function get_tptn_post_count( $post = 0, $blog_id = 0 ) {
	return \WebberZone\Top_Ten\Counter::get_post_count( $post, $blog_id );
}

/**
 * Returns the post count.
 *
 * @since   1.9.8.5
 *
 * @param   int|\WP_Post $post    Post ID or WP_Post object.
 * @param   string       $count  Which count to return? total, daily or overall.
 * @param   int          $blog_id Blog ID.
 * @return  int     Post count
 */
function get_tptn_post_count_only( $post = 0, $count = 'total', $blog_id = 0 ) {
	return \WebberZone\Top_Ten\Counter::get_post_count_only( $post, $count, $blog_id );
}

/**
 * Returns the total post count.
 *
 * @since 4.0.0
 * @param int|\WP_Post $post    Post ID or WP_Post object.
 * @param int          $blog_id Blog ID.
 * @return int     Post count
 */
function get_tptn_total_count( $post = 0, $blog_id = 0 ) {
	return get_tptn_post_count_only( $post, 'total', $blog_id );
}

/**
 * Returns the daily post count.
 *
 * @since 4.0.0
 * @param int|\WP_Post $post    Post ID or WP_Post object.
 * @param int          $blog_id Blog ID.
 * @return int     Post count
 */
function get_tptn_daily_count( $post = 0, $blog_id = 0 ) {
	return get_tptn_post_count_only( $post, 'daily', $blog_id );
}

/**
 * Returns the overall post count.
 *
 * @since 4.0.0
 * @param int $blog_id Blog ID.
 * @return int     Post count
 */
function get_tptn_overall_count( $blog_id = 0 ) {
	return get_tptn_post_count_only( 0, 'overall', $blog_id );
}

/**
 * Retrieves an array of the related posts.
 *
 * The defaults are as follows:
 *
 * @since 3.0.0
 *
 * @see Top_Ten_Query::prepare_query_args()
 *
 * @param array $args Optional. Arguments to retrieve posts. See WP_Query::parse_query() for all available arguments.
 * @return WP_Post[]|int[] Array of post objects or post IDs.
 */
function get_tptn_posts( $args = array() ) {
	return \WebberZone\Top_Ten\Frontend\Display::get_posts( $args );
}

/**
 * Function to return formatted list of popular posts.
 *
 * @since 1.5
 *
 * @param  mixed $args   Arguments array.
 * @return string  HTML output of the popular posts.
 */
function tptn_pop_posts( $args ) {
	return \WebberZone\Top_Ten\Frontend\Display::pop_posts( $args );
}

/**
 * Function to echo popular posts.
 *
 * @since   1.0
 *
 * @param   mixed $args   Arguments list.
 */
function tptn_show_pop_posts( $args = array() ) {
	$defaults = array(
		'is_manual' => true,
	);
	$args     = wp_parse_args( $args, $defaults );
	echo tptn_pop_posts( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Function to show daily popular posts.
 *
 * @since   1.2
 *
 * @param   mixed $args   Arguments list.
 */
function tptn_show_daily_pop_posts( $args = array() ) {
	$defaults = array(
		'daily' => true,
	);
	$args     = wp_parse_args( $args, $defaults );

	tptn_show_pop_posts( $args );
}
