<?php
/**
 * Dashboard class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Admin\Settings\Settings_API;
use WebberZone\Top_Ten\Database;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Dashboard Class.
 *
 * @since 3.0.0
 */
class Dashboard {

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Constructor class.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_tptn_chart_data', array( $this, 'get_chart_data' ) );
	}

	/**
	 * Render the settings page.
	 *
	 * @since 3.0.0
	 */
	public function render_page() {
		ob_start();

		// Add date selector.
		$chart_to_date   = current_time( 'd M Y' );
		$chart_from_date = gmdate( 'd M Y', strtotime( '-1 month' ) );

		$post_date_from = ( isset( $_REQUEST['post-date-filter-from'] ) && check_admin_referer( 'tptn-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-from'] ) ) : $chart_from_date;

		$post_date_to = ( isset( $_REQUEST['post-date-filter-to'] ) && check_admin_referer( 'tptn-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-to'] ) ) : $chart_to_date;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Top 10 Dashboard', 'top-10' ); ?></h1>
			<?php do_action( 'tptn_settings_page_header' ); ?>

			<?php settings_errors(); ?>

			<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<form method="post" >
					<?php wp_nonce_field( 'tptn-dashboard' ); ?>

					<div>
						<input type="text" id="datepicker-from" name="post-date-filter-from" value="<?php echo esc_attr( $post_date_from ); ?>" size="11" />
						<input type="text" id="datepicker-to" name="post-date-filter-to" value="<?php echo esc_attr( $post_date_to ); ?>" size="11" />
						<?php
						submit_button(
							__( 'Update', 'top-10' ),
							'primary',
							'filter_action',
							false,
							array(
								'id'      => 'top-10-chart-submit',
								'onclick' => 'updateChart(); return false;',
							)
						);
						?>
					</div>
					<div>
						<canvas id="visits" width="400" height="150" aria-label="<?php esc_html_e( 'Top 10 Visits', 'top-10' ); ?>" role="img"></canvas>
					</div>

				</form>

				<h2><?php esc_html_e( 'Historical visits', 'top-10' ); ?></h2>
				<ul class="nav-tab-wrapper" style="padding:0; border-bottom: 1px solid #ccc;">
					<?php
					foreach ( $this->get_tabs() as $tab_id => $tab_name ) {
						$tab_class = 'nav-tab';
						$tab_style = '';

						// Check if this tab should be hidden initially.
						if ( isset( $tab_name['hide'] ) && $tab_name['hide'] ) {
							$tab_style = 'display:none;';
						}

						// Add custom class if specified.
						if ( isset( $tab_name['class'] ) ) {
							$tab_class .= ' ' . $tab_name['class'];
						}

						echo '<li style="padding:0; border:0; margin:0;"><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name['title'] ) . '" class="' . esc_attr( $tab_class ) . '" style="' . esc_attr( $tab_style ) . '">';
							echo esc_html( $tab_name['title'] );
						echo '</a></li>';

					}
					?>
				</ul>

				<form method="post" action="options.php">

					<?php foreach ( $this->get_tabs() as $tab_id => $tab_name ) : ?>

					<div id="<?php echo esc_attr( $tab_id ); ?>">
						<table class="form-table">
						<?php
							$output = $this->display_popular_posts( $tab_name );
							echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						</table>

						<div style="font-weight:bold;padding:5px;">

							<?php
							$query_args = array(
								'order' => 'desc',
							);
							$daily      = ( isset( $tab_name['daily'] ) ) ? $tab_name['daily'] : true;

							if ( $daily ) {
								$query_args['orderby'] = 'daily_count';

								if ( ! empty( $tab_name['from_date'] ) ) {
									$query_args['post-date-filter-from'] = gmdate( 'd+M+Y', strtotime( $tab_name['from_date'] ) );
								}
								if ( ! empty( $tab_name['to_date'] ) ) {
									$query_args['post-date-filter-to'] = gmdate( 'd+M+Y', strtotime( $tab_name['to_date'] ) );
								}
							} else {
								$query_args['orderby'] = 'total_count';
							}
							$base_url = is_network_admin() ? network_admin_url( 'admin.php?page=tptn_network_pop_posts_page' ) : admin_url( 'admin.php?page=tptn_popular_posts' );
							$url      = add_query_arg( $query_args, $base_url );

							?>

							<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'View all popular posts', 'top-10' ); ?> &raquo;</a>

						</div>

					</div><!-- /#tab_id-->

					<?php endforeach; ?>

				</form>

			</div><!-- /#post-body-content -->

			<div id="postbox-container-1" class="postbox-container">

				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php include_once 'sidebar.php'; ?>
				</div><!-- /#side-sortables -->

			</div><!-- /#postbox-container-1 -->
			</div><!-- /#post-body -->
			<br class="clear" />
			</div><!-- /#poststuff -->

		</div><!-- /.wrap -->

		<?php
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Admin Menu.
	 *
	 * @since 3.0.0
	 */
	public function admin_menu() {
		$roles = wp_parse_list( \tptn_get_option( 'show_dashboard_to_roles' ) );

		$this->parent_id = add_menu_page(
			esc_html__( 'Top 10 Dashboard', 'top-10' ),
			esc_html__( 'Top 10', 'top-10' ),
			Settings_API::get_capability_for_menu( $roles ),
			'tptn_dashboard',
			array( $this, 'render_page' ),
			'dashicons-editor-ol'
		);

		add_submenu_page(
			'tptn_dashboard',
			esc_html__( 'Top 10 Dashboard', 'top-10' ),
			esc_html__( 'Dashboard', 'top-10' ),
			Settings_API::get_capability_for_menu( $roles ),
			'tptn_dashboard',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Network Admin Menu.
	 *
	 * @since 4.2.0
	 */
	public function network_admin_menu() {
		$this->parent_id = add_menu_page(
			esc_html__( 'Top 10 Dashboard', 'top-10' ),
			esc_html__( 'Top 10', 'top-10' ),
			'manage_network_options',
			'tptn_dashboard',
			array( $this, 'render_page' ),
			'dashicons-editor-ol'
		);

		add_submenu_page(
			'tptn_dashboard',
			esc_html__( 'Top 10 Dashboard', 'top-10' ),
			esc_html__( 'Dashboard', 'top-10' ),
			'manage_network_options',
			'tptn_dashboard',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $this->parent_id, array( $this, 'help_tabs' ) );
	}

	/**
	 * Enqueue scripts in admin area.
	 *
	 * @since 3.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function admin_enqueue_scripts( $hook ) {

		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'moment' );
			wp_enqueue_script( 'top-ten-chart-js' );
			wp_enqueue_script( 'top-ten-chart-datalabels-js' );
			wp_enqueue_script( 'top-ten-chartjs-adapter-luxon-js' );
			wp_enqueue_script( 'top-ten-chart-data-js' );
			wp_enqueue_script( 'top-ten-admin-js' );
			wp_localize_script(
				'top-ten-chart-data-js',
				'tptn_chart_data',
				array(
					'security'     => wp_create_nonce( 'tptn-dashboard' ),
					'datasetlabel' => __( 'Visits', 'top-10' ),
					'charttitle'   => __( 'Daily Visits', 'top-10' ),
					'network'      => is_network_admin() ? 1 : 0,
				)
			);
			wp_enqueue_style( 'top-ten-admin-css' );
		}
	}

	/**
	 * Fetch chart data for visits over time.
	 *
	 * @since 4.2.0
	 *
	 * @param string $from_date Start date in Y-m-d format.
	 * @param string $to_date   End date in Y-m-d format.
	 * @param bool   $network   Whether to fetch network-wide data.
	 * @return array Chart data with date and visits.
	 */
	public static function fetch_visits_by_date( $from_date, $to_date, $network = false ) {
		global $wpdb;

		if ( $network ) {
			$sql = $wpdb->prepare(
				" SELECT SUM(cntaccess) AS visits, DATE(dp_date) as date
				FROM {$wpdb->base_prefix}top_ten_daily
				WHERE DATE(dp_date) >= DATE(%s)
				AND DATE(dp_date) <= DATE(%s)
				GROUP BY date
				ORDER BY date ASC
				",
				$from_date,
				$to_date
			);
		} else {
			$blog_id = get_current_blog_id();

			$sql = $wpdb->prepare(
				" SELECT SUM(cntaccess) AS visits, DATE(dp_date) as date
				FROM {$wpdb->base_prefix}top_ten_daily
				WHERE DATE(dp_date) >= DATE(%s)
				AND DATE(dp_date) <= DATE(%s)
				AND blog_id = %d
				GROUP BY date
				ORDER BY date ASC
				",
				$from_date,
				$to_date,
				$blog_id
			);
		}

		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$data = array();
		foreach ( $result as $row ) {
			$data[] = $row;
		}

		return $data;
	}

	/**
	 * Function to add an action to search for tags using Ajax.
	 *
	 * @since 3.0.0
	 */
	public function get_chart_data() {
		$roles = wp_parse_list( \tptn_get_option( 'show_dashboard_to_roles' ) );

		if ( ! current_user_can( Settings_API::get_capability_for_menu( $roles ) ) ) {
			wp_die();
		}
		check_ajax_referer( 'tptn-dashboard', 'security' );

		// Add date selector.
		$to_date   = isset( $_REQUEST['to_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['to_date'] ) ) : current_time( 'd M Y' );
		$from_date = isset( $_REQUEST['from_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['from_date'] ) ) : gmdate( 'd M Y', strtotime( '-1 month' ) );

		$post_date_from = gmdate( 'Y-m-d', strtotime( $from_date ) );
		$post_date_to   = gmdate( 'Y-m-d', strtotime( $to_date ) );

		$network_request = is_multisite() && isset( $_REQUEST['network'] ) && 1 === (int) $_REQUEST['network'];

		$data = self::fetch_visits_by_date( $post_date_from, $post_date_to, $network_request );

		echo wp_json_encode( $data );
		wp_die();
	}


	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.0.0
	 *
	 * @return array Settings array
	 */
	public function get_tabs() {
		$tabs = array(
			'today'         => array(
				'title'     => __( 'Today', 'top-10' ),
				'from_date' => current_time( 'd M Y' ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'yesterday'     => array(
				'title'     => __( 'Yesterday', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 day' ) ),
				'to_date'   => gmdate( 'd M Y', strtotime( '-1 day' ) ),
			),
			'lastweek'      => array(
				'title'     => __( 'Last 7 days', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 week' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'lastfortnight' => array(
				'title'     => __( 'Last 14 days', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-2 weeks' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'lastmonth'     => array(
				'title'     => __( 'Last 30 days', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-30 days' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'overall'       => array(
				'title' => __( 'All time', 'top-10' ),
				'daily' => false,
			),
		);

		/**
		 * Filters the tabs for the admin dashboard.
		 *
		 * @since 4.1.0
		 *
		 * @param array $tabs Array of tabs.
		 */
		$tabs = apply_filters( 'tptn_admin_dashboard_tabs', $tabs );

		return $tabs;
	}

	/**
	 * Get popular posts for a date range.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type bool         $daily       Set to true to get the daily/custom period posts. False for overall.
	 *     @type string       $from_date   From date. A date/time string.
	 *     @type int          $numberposts Number of posts to fetch.
	 *     @type string       $to_date     To date. A date/time string.
	 * }
	 * @return string HTML table with popular posts.
	 */
	public function display_popular_posts( $args = array() ) {
		$output = '';

		$defaults = array(
			'daily'       => true,
			'from_date'   => null,
			'numberposts' => 20,
			'to_date'     => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		$results = $this->get_popular_posts( $args );

		ob_start();
		$explicit_network = isset( $args['network'] ) ? (bool) $args['network'] : null;
		$is_network       = ( null !== $explicit_network ) ? $explicit_network : ( is_multisite() && is_network_admin() );
		if ( $results ) :
			?>

			<table class="widefat striped">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Post', 'top-10' ); ?></th>
					<?php if ( $is_network ) : ?>
						<th><?php esc_html_e( 'Site', 'top-10' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Visits', 'top-10' ); ?></th>
				</tr>
				</thead>
				<tbody>
			<?php
			$total_visits = 0;
			foreach ( $results as $result ) :
				$visits_int    = (int) $result['visits'];
				$total_visits += $visits_int;
				$visits        = \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( $visits_int );
				if ( $is_network && isset( $result['blog_id'] ) ) {
					$post      = get_blog_post( (int) $result['blog_id'], (int) $result['ID'] );
					$permalink = is_multisite() ? get_blog_permalink( (int) $result['blog_id'], (int) $result['ID'] ) : get_permalink( (int) $result['ID'] );
				} else {
					$post      = get_post( (int) $result['ID'] );
					$permalink = get_permalink( (int) $result['ID'] );
				}
				if ( ! $post ) {
					continue;
				}
				?>
				<tr>
					<td><a href="<?php echo esc_url( $permalink ); ?>" target="_blank"><?php echo esc_html( get_the_title( $post ) ); ?></a></td>
					<?php if ( $is_network && isset( $result['blog_id'] ) ) : ?>
						<td>
							<?php
							$blog_details = get_blog_details( (int) $result['blog_id'] );
							if ( $blog_details ) {
								$site_url = add_query_arg(
									array(
										'page' => 'tptn_popular_posts',
									),
									get_admin_url( (int) $result['blog_id'], 'admin.php' )
								);
								?>
								<a href="<?php echo esc_url( $site_url ); ?>" target="_blank"><?php echo esc_html( $blog_details->blogname ); ?></a>
								<?php
							} else {
								echo esc_html( sprintf( __( 'Blog ID: %d', 'top-10' ), (int) $result['blog_id'] ) );
							}
							?>
						</td>
					<?php endif; ?>
					<td><?php echo esc_html( $visits ); ?></td>
				</tr>

			<?php endforeach; ?>
				</tbody>
				<?php
				$colspan                = $is_network ? 2 : 1;
				$total_visits_formatted = \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( $total_visits );
				$label                  = $is_network ? __( 'Total network visits for this period', 'top-10' ) : __( 'Total visits for this period', 'top-10' );
				?>
				<tfoot>
				<tr>
					<th colspan="<?php echo esc_attr( (string) $colspan ); ?>" style="text-align:right;"><?php echo esc_html( $label ); ?></th>
					<th><?php echo esc_html( $total_visits_formatted ); ?></th>
				</tr>
				</tfoot>
			</table>

		<?php else : ?>

				<?php esc_html_e( 'Sorry, no popular posts found.', 'top-10' ); ?>

		<?php endif; ?>

		<?php

		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Retrieve the popular posts.
	 *
	 * @since 3.0.0
	 *
	 * @param string|array $args {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type array|string $blog_id     An array or comma-separated string of blog IDs.
	 *     @type bool         $daily       Set to true to get the daily/custom period posts. False for overall.
	 *     @type string       $from_date   From date. A date/time string.
	 *     @type int          $numberposts Number of posts to fetch.
	 *     @type int          $offset      Offset.
	 *     @type string       $to_date     To date. A date/time string.
	 * }
	 * @return array Array of post objects.
	 */
	public function get_popular_posts( $args = array() ) {
		global $wpdb;

		// Initialise some variables.
		$fields  = array();
		$where   = '';
		$join    = '';
		$groupby = '';
		$orderby = '';
		$limits  = '';

		$defaults = array(
			'blog_id'     => get_current_blog_id(),
			'daily'       => true,
			'from_date'   => null,
			'numberposts' => 20,
			'offset'      => 0,
			'to_date'     => null,
		);
		$args     = wp_parse_args( $args, $defaults );

		$explicit_network = isset( $args['network'] ) ? (bool) $args['network'] : null;
		$is_network       = ( null !== $explicit_network ) ? $explicit_network : ( is_multisite() && is_network_admin() );
		$table_name       = Database::get_table( $args['daily'] );

		if ( $is_network ) {
			// Network-wide: aggregate across all blogs.
			$fields[] = ( $args['daily'] ) ? "SUM({$table_name}.cntaccess) as visits" : "{$table_name}.cntaccess as visits";
			$fields[] = "{$table_name}.postnumber as ID";
			$fields[] = "{$table_name}.blog_id as blog_id";

			$fields = implode( ', ', $fields );

			$where = ' 1=1 ';

			if ( isset( $args['from_date'] ) && ! empty( $args['from_date'] ) && $args['daily'] ) {
				$from_date = gmdate( 'Y-m-d', strtotime( $args['from_date'] ) );
				$where    .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) >= DATE(%s) ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( isset( $args['to_date'] ) && ! empty( $args['to_date'] ) && $args['daily'] ) {
				$to_date = gmdate( 'Y-m-d', strtotime( $args['to_date'] ) );
				$where  .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) <= DATE(%s) ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			$groupby = ' ';
			$groupby = " GROUP BY {$table_name}.postnumber, {$table_name}.blog_id ";

			$orderby = ' visits DESC ';
			$orderby = " ORDER BY {$orderby} ";

			$limits = $wpdb->prepare( ' LIMIT %d, %d ', $args['offset'], $args['numberposts'] );

			$sql = "SELECT $fields FROM {$table_name} WHERE $where $groupby $orderby $limits";
		} else {
			// Single site: existing behaviour filtered by current blog.
			$fields[] = ( $args['daily'] ) ? "SUM({$table_name}.cntaccess) as visits" : "{$table_name}.cntaccess as visits";
			$fields[] = "{$wpdb->posts}.ID";

			$fields = implode( ', ', $fields );

			// Create the JOIN clause.
			$join = " INNER JOIN {$wpdb->posts} ON {$table_name}.postnumber={$wpdb->posts}.ID ";

			// Create the base WHERE clause.
			$where  = $wpdb->prepare( " AND {$table_name}.blog_id = %d ", $args['blog_id'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$where .= " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit') ";   // Show published posts and attachments.
			$where .= " AND ($wpdb->posts.post_type <> 'revision' ) ";   // No revisions.

			if ( isset( $args['from_date'] ) && ! empty( $args['from_date'] ) ) {
				$from_date = gmdate( 'Y-m-d', strtotime( $args['from_date'] ) );
				$where    .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) >= DATE(%s) ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( isset( $args['to_date'] ) && ! empty( $args['to_date'] ) ) {
				$to_date = gmdate( 'Y-m-d', strtotime( $args['to_date'] ) );
				$where  .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) <= DATE(%s) ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			// Create the base GROUP BY clause.
			if ( $args['daily'] ) {
				$groupby = " {$wpdb->posts}.ID";
			}

			// Create the base ORDER BY clause.
			$orderby = ' visits DESC ';
			$orderby = " ORDER BY {$orderby} ";

			// Create the base LIMITS clause.
			$limits = $wpdb->prepare( ' LIMIT %d, %d ', $args['offset'], $args['numberposts'] );

			if ( ! empty( $groupby ) ) {
				$groupby = " GROUP BY {$groupby} ";
			}

			$sql = "SELECT DISTINCT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";
		}

		$result = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $result;
	}

	/**
	 * Generates the help tabs.
	 *
	 * @since 3.0.0
	 */
	public function help_tabs() {

		$screen = get_current_screen();

		$screen->set_help_sidebar(
			/* translators: 1: Support link. */
			'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%1$s">WebberZone support site</a>.', 'top-10' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
			/* translators: 1: Forum link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%1$s">WordPress.org support forums</a>.', 'top-10' ), esc_url( 'https://wordpress.org/support/plugin/top-10' ) ) . '</p>' .
			'<p>' . sprintf(
				/* translators: 1: Github Issues link, 2: Github page. */
				__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'top-10' ),
				esc_url( 'https://github.com/WebberZone/top-10/issues' ),
				esc_url( 'https://github.com/WebberZone/top-10' )
			) . '</p>'
		);

		$screen->add_help_tab(
			array(
				'id'      => 'tptn-dashboard-general',
				'title'   => __( 'General', 'top-10' ),
				'content' =>
				'<p>' . __( 'This screen displays the historical traffic on your site.', 'top-10' ) . '</p>' .
					/* translators: 1: Constant holding number of days data is stored. */
					'<p>' . sprintf( __( 'The data is pulled from the daily tables in the database. If you have enabled maintenance then the amount of historical data that is available will be limited to %d days.', 'top-10' ), TOP_TEN_STORE_DATA ) . '</p>' .
					'<p>' . __( 'You can change this by setting the constant TOP_TEN_STORE_DATA to the number of days of your choice in your wp-config.php.', 'top-10' ) . '</p>',
			)
		);
	}
}
