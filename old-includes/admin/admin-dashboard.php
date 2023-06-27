<?php
/**
 * Top 10 Dashboard display.
 *
 * Functions to add the popular lists to the WordPress Admin Dashboard
 *
 * @package   Top_Ten
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 *  Create the Dashboard Widget and content of the Popular pages
 *
 * @since   1.3
 *
 * @param   bool $daily  Switch for Daily or Overall popular posts.
 * @param   int  $page   Which page of the lists are we on.
 * @param   int  $limit  Maximum number of posts per page.
 * @param   bool $widget Is this a WordPress widget.
 * @return  string Formatted list of popular posts
 */
function tptn_pop_display( $daily = false, $page = 0, $limit = false, $widget = true ) {

	if ( ! $limit ) {
		$limit = tptn_get_option( 'limit' );
	}

	$args   = array();
	$visits = 'total_count';
	if ( $daily ) {
		$args['orderby'] = 'daily_count';
		$visits          = 'daily_count';
	}
	$pop_posts_obj = new Top_Ten_Statistics_Table();
	$results       = $pop_posts_obj->get_popular_posts( $limit, $page + 1, $args );

	$output = '<div id="tptn_popular_posts' . ( $daily ? '_daily' : '' ) . '">';

	if ( $results ) {
		$output .= '<ul>';
		foreach ( $results as $result ) {
			if ( ! absint( $result[ $visits ] ) || ! get_post_status( $result['ID'] ) ) {
				continue;
			}
			$output .= '<li><a href="' . get_permalink( $result['ID'] ) . '">' . get_the_title( $result['ID'] ) . '</a>';
			$output .= ' (' . tptn_number_format_i18n( $result[ $visits ] ) . ')';
			$output .= '</li>';
		}
		$output .= '</ul>';
	}

	$output .= '<p style="text-align:center">';

	if ( $daily ) {
		$output .= '<a href="' . admin_url( 'admin.php?page=tptn_popular_posts&orderby=daily_count&order=desc' ) . '">' . __( 'View all daily popular posts', 'top-10' ) . '</a>';
	} else {
		$output .= '<a href="' . admin_url( 'admin.php?page=tptn_popular_posts&orderby=total_count&order=desc' ) . '">' . __( 'View all popular posts', 'top-10' ) . '</a>';
	}

	$output .= '</p>';

	$output .= '<p style="text-align:center;border-top: #000 1px solid">';

	/* translators: 1: Top 10 page link. */
	$output .= sprintf( __( 'Popular posts by <a href="%s" target="_blank">Top 10 plugin</a>', 'top-10' ), esc_url( 'https://webberzone.com/plugins/top-10/' ) );
	$output .= '</p>';
	$output .= '</div>';

	/**
	 *  Filters the dashboard widget output
	 *
	 * @since   1.3
	 *
	 * @param string $output Text output
	 * @param bool $daily  Switch for Daily or Overall popular posts.
	 * @param int  $page   Which page of the lists are we on.
	 * @param int  $limit  Maximum number of posts per page.
	 * @param bool $widget Is this a WordPress widget.
	 */
	return apply_filters( 'tptn_pop_display', $output, $daily, $page, $limit, $widget );
}


/**
 * Widget for Popular Posts.
 *
 * @since   1.1
 */
function tptn_pop_dashboard() {
	echo tptn_pop_display( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Widget for Daily Popular Posts.
 *
 * @since   1.2
 */
function tptn_pop_daily_dashboard() {
	echo tptn_pop_display( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Function to add the widgets to the Dashboard.
 *
 * @since   1.1
 */
function tptn_pop_dashboard_setup() {

	if ( ( current_user_can( 'manage_options' ) ) || ( tptn_get_option( 'show_count_non_admins' ) ) ) {
		wp_add_dashboard_widget(
			'tptn_pop_dashboard',
			__( 'Popular Posts', 'top-10' ),
			'tptn_pop_dashboard'
		);
		wp_add_dashboard_widget(
			'tptn_pop_daily_dashboard',
			__( 'Daily Popular Posts', 'top-10' ),
			'tptn_pop_daily_dashboard'
		);
	}
}
add_action( 'wp_dashboard_setup', 'tptn_pop_dashboard_setup' );
