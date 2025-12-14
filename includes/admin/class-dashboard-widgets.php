<?php
/**
 * Dashboard Widgets class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Util\Helpers;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Dashboard_Widgets {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_filter( 'wp_network_dashboard_setup', array( $this, 'wp_network_dashboard_setup' ) );
	}

	/**
	 * Function to add the widgets to the Dashboard.
	 *
	 * @since 3.3.0
	 */
	public static function wp_dashboard_setup() {

		/**
		 * Filter whether to register the dashboard widgets.
		 *
		 * @since 3.3.0
		 *
		 * @param bool $dashboard_setup Whether to register the dashboard widgets.
		 */
		$dashboard_setup = apply_filters( 'tptn_dashboard_setup', true );

		if ( $dashboard_setup && ( current_user_can( 'manage_options' ) || \tptn_get_option( 'show_count_non_admins' ) ) ) {

			// Add the overall popular posts widget.
			wp_add_dashboard_widget(
				'tptn_overall_dashboard',
				__( 'Top 10 - Overall Popular Posts', 'top-10' ),
				array( __CLASS__, 'popular_posts_widget' ),
				array( __CLASS__, 'pop_display' )
			);

			// Add the daily popular posts widget.
			wp_add_dashboard_widget(
				'tptn_daily_dashboard',
				__( 'Top 10 - Daily Popular Posts', 'top-10' ),
				array( __CLASS__, 'popular_posts_widget_daily' ),
				array( __CLASS__, 'pop_display' )
			);

			// Add the mini views overview widget.
			wp_add_dashboard_widget(
				'tptn_views_over_time_dashboard',
				__( 'Top 10 Views Overview', 'top-10' ),
				array( __CLASS__, 'views_over_time_widget' ),
			);
		}
	}

	/**
	 * Function to add the widgets to the Network Dashboard.
	 *
	 * @since 4.2.0
	 */
	public static function wp_network_dashboard_setup() {

		/**
		 * Filter whether to register the network dashboard widgets.
		 *
		 * @since 4.2.0
		 *
		 * @param bool $dashboard_setup Whether to register the network dashboard widgets.
		 */
		$dashboard_setup = apply_filters( 'tptn_network_dashboard_setup', true );

		if ( $dashboard_setup && current_user_can( 'manage_network' ) ) {

			// Add the overall popular posts widget.
			wp_add_dashboard_widget(
				'tptn_network_overall_dashboard',
				__( 'Top 10 - Network Popular Posts', 'top-10' ),
				array( __CLASS__, 'network_popular_posts_widget' ),
				array( __CLASS__, 'pop_display' )
			);

			// Add the daily popular posts widget.
			wp_add_dashboard_widget(
				'tptn_network_daily_dashboard',
				__( 'Top 10 - Network Daily Popular Posts', 'top-10' ),
				array( __CLASS__, 'network_popular_posts_widget_daily' ),
				array( __CLASS__, 'pop_display' )
			);

			// Add the mini views overview widget to network dashboard.
			wp_add_dashboard_widget(
				'tptn_network_views_over_time_dashboard',
				__( 'Top 10 Network Views Overview', 'top-10' ),
				array( __CLASS__, 'views_over_time_widget' ),
			);
		}
	}

	/**
	 *  Create the Dashboard Widget and content of the Popular pages
	 *
	 * @since 3.3.0
	 *
	 * @param   bool $daily  Switch for Daily or Overall popular posts.
	 * @param   int  $page   Which page of the lists are on.
	 * @param   int  $limit  Maximum number of posts per page.
	 * @param   bool $widget Is this a WordPress widget.
	 * @param   bool $network Whether to show network-wide posts.
	 * @return  string Formatted list of popular posts
	 */
	public static function pop_display( $daily = false, $page = 0, $limit = 0, $widget = true, $network = false ) {

		if ( ! $limit ) {
			$limit = \tptn_get_option( 'limit' );
		}

		$args   = array();
		$visits = 'total_count';
		if ( $daily ) {
			$args['orderby'] = 'daily_count';
			$visits          = 'daily_count';
		}
		$statistics_table = new Statistics_Table( $network );
		$results          = $statistics_table->get_popular_posts( $limit, $page + 1, $args );

		$output = '<div id="tptn_popular_posts' . ( $daily ? '_daily' : '' ) . '">';

		if ( $results ) {
			$output .= '<ul>';
			foreach ( $results as $result ) {
				if ( ! absint( $result[ $visits ] ) ) {
					continue;
				}

				// Handle network context differently.
				if ( $network ) {
					// Check if blog exists.
					$blog_details = get_blog_details( $result['blog_id'] );
					if ( ! $blog_details ) {
						continue; // Skip if blog doesn't exist.
					}

					// Get post from specific blog.
					$post = get_blog_post( $result['blog_id'], $result['ID'] );
					if ( ! $post || 'publish' !== $post->post_status ) {
						continue; // Skip if post doesn't exist or isn't published.
					}

					$output .= '<li><a href="' . get_blog_permalink( $result['blog_id'], $result['ID'] ) . '">' . esc_html( $post->post_title ) . '</a>';
					$output .= ' (' . Helpers::number_format_i18n( $result[ $visits ] ) . ')';
					$output .= ' <span class="tptn-blog-name">- ' . esc_html( $blog_details->blogname ) . '</span>';
					$output .= '</li>';
				} else {
					// Single site context.
					if ( ! get_post_status( $result['ID'] ) ) {
						continue;
					}
					$output .= '<li><a href="' . get_permalink( $result['ID'] ) . '">' . get_the_title( $result['ID'] ) . '</a>';
					$output .= ' (' . Helpers::number_format_i18n( $result[ $visits ] ) . ')';
					$output .= '</li>';
				}
			}
			$output .= '</ul>';
		}

		$output .= '<p style="text-align:center">';

		if ( $daily ) {
			if ( $network ) {
				$output .= '<a href="' . network_admin_url( 'admin.php?page=tptn_network_pop_posts_page&orderby=daily_count&order=desc' ) . '">' . __( 'View all network daily popular posts', 'top-10' ) . '</a>';
			} else {
				$output .= '<a href="' . admin_url( 'admin.php?page=tptn_popular_posts&orderby=daily_count&order=desc' ) . '">' . __( 'View all daily popular posts', 'top-10' ) . '</a>';
			}
		} elseif ( $network ) {
				$output .= '<a href="' . network_admin_url( 'admin.php?page=tptn_network_pop_posts_page&orderby=total_count&order=desc' ) . '">' . __( 'View all network popular posts', 'top-10' ) . '</a>';
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
		 * @since   3.3.0
		 *
		 * @param   string   $output  Widget output
		 * @param   bool     $daily   Is this a daily widget
		 * @param   int      $page    Page number
		 * @param   int      $limit   Limit of posts
		 * @param   bool     $widget  Is this a widget
		 * @param   bool     $network Is this network-wide
		 */
		return apply_filters( 'tptn_popular_posts_widget_output', $output, $daily, $page, $limit, $widget, $network );
	}


	/**
	 * Widget for Popular Posts.
	 *
	 * @since 3.3.0
	 */
	public static function popular_posts_widget() {
		echo self::pop_display( false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}


	/**
	 * Widget for Daily Popular Posts.
	 *
	 * @since 3.3.0
	 */
	public static function popular_posts_widget_daily() {
		echo self::pop_display( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Widget for Network Popular Posts.
	 *
	 * @since 4.2.0
	 */
	public static function network_popular_posts_widget() {
		echo self::pop_display( false, 0, 0, true, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Widget for Network Daily Popular Posts.
	 *
	 * @since 4.2.0
	 */
	public static function network_popular_posts_widget_daily() {
		echo self::pop_display( true, 0, 0, true, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Widget for Views Overview (mini chart).
	 *
	 * @since 4.2.0
	 */
	public static function views_over_time_widget() {
		global $tptn_freemius;

		$output = '<div class="tptn-views-over-time-widget">';

		if ( ! $tptn_freemius->is_paying() ) {
			$output .= sprintf(
				'<div class="tptn-views-over-time-chart-placeholder" style="height:150px;margin-bottom:15px;border:1px dashed #ccd0d4;background:#f8f9fa;display:flex;align-items:center;justify-content:center;color:#6c757d;font-style:italic;">%s</div>',
				esc_html__( 'Upgrade to Top 10 Pro to see your views over time chart here.', 'top-10' )
			);
			$output .= sprintf(
				'<p style="text-align:center;"><a class="button button-primary" href="%s">%s</a></p>',
				esc_url( $tptn_freemius->get_upgrade_url() ),
				esc_html__( 'Upgrade to Pro', 'top-10' )
			);
		}

		$output .= '</div>';

		/**
		 * Filters the views over time widget content.
		 *
		 * @since 4.2.0
		 *
		 * @param string $output The widget content.
		 */
		$output = apply_filters( 'tptn_views_over_time_widget_content', $output );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
