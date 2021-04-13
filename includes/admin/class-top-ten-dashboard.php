<?php
/**
 * Dashboard.
 *
 * @link https://webberzone.com
 * @since 3.0.0
 *
 * @package Top 10
 * @subpackage Admin/Dashboard
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Top_Ten_Dashboard class.
 *
 * @since 3.0.0
 */
class Top_Ten_Dashboard {

	/**
	 * Class instance.
	 *
	 * @since 3.0.0
	 *
	 * @var class Class instance.
	 */
	public static $instance;

	/**
	 * Parent Menu ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string Parent Menu ID.
	 */
	public $parent_id;

	/**
	 * Singleton instance.
	 *
	 * @since 3.0.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor class.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_tptn_chart_data', array( $this, 'get_chart_data' ) );
	}

	/**
	 * Render the settings page.
	 *
	 * @since 3.0.0
	 */
	public function plugin_settings_page() {
		ob_start();

		// Add date selector.
		$chart_to_date   = current_time( 'd M Y' );
		$chart_from_date = gmdate( 'd M Y', strtotime( '-1 week' ) );

		$post_date_from = ( isset( $_REQUEST['post-date-filter-from'] ) && check_admin_referer( 'tptn-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-from'] ) ) : $chart_from_date;

		$post_date_to = ( isset( $_REQUEST['post-date-filter-to'] ) && check_admin_referer( 'tptn-dashboard' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-to'] ) ) : $chart_to_date;

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Top 10 Dashboard', 'top-10' ); ?></h1>

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

						echo '<li style="padding:0; border:0; margin:0;"><a href="#' . esc_attr( $tab_id ) . '" title="' . esc_attr( $tab_name['title'] ) . '" class="nav-tab">';
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
							$url = add_query_arg( $query_args, admin_url( 'admin.php?page=tptn_popular_posts' ) );

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
		$this->parent_id = add_menu_page( esc_html__( 'Top 10 Dashboard', 'top-10' ), esc_html__( 'Top 10', 'top-10' ), 'manage_options', 'tptn_dashboard', array( $this, 'plugin_settings_page' ), 'dashicons-editor-ol' );

		add_submenu_page( 'tptn_dashboard', esc_html__( 'Top 10 Dashboard', 'top-10' ), esc_html__( 'Dashboard', 'top-10' ), 'manage_options', 'tptn_dashboard', array( $this, 'plugin_settings_page' ) );

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

		wp_register_script( 'top-ten-moment-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/moment.min.js', array(), '1.0', true );
		wp_register_script( 'top-ten-chart-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/chart.min.js', array(), '1.0', true );
		wp_register_script( 'top-ten-chart-datalabels-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/chartjs-plugin-datalabels.min.js', array( 'top-ten-chart-js' ), '1.0', true );
		wp_register_script( 'top-ten-chartjs-adapter-moment-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/chartjs-adapter-moment.min.js', array( 'top-ten-moment-js', 'top-ten-chart-js' ), '1.0', true );
		wp_register_script( 'top-ten-chart-data-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/chart-data.min.js', array( 'jquery', 'top-ten-chart-js', 'top-ten-chart-datalabels-js', 'top-ten-moment-js', 'top-ten-chartjs-adapter-moment-js' ), '1.0', true );
		wp_register_script( 'top-ten-admin-js', TOP_TEN_PLUGIN_URL . 'includes/admin/js/admin-scripts.min.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-datepicker' ), '1.0', true );

		if ( $hook === $this->parent_id ) {
			wp_enqueue_script( 'top-ten-chart-js' );
			wp_enqueue_script( 'top-ten-chart-datalabels-js' );
			wp_enqueue_script( 'top-ten-moment-js' );
			wp_enqueue_script( 'top-ten-chartjs-adapter-moment-js' );
			wp_enqueue_script( 'top-ten-chart-data-js' );
			wp_enqueue_script( 'top-ten-admin-js' );
			wp_enqueue_style(
				'tptn-admin-ui-css',
				TOP_TEN_PLUGIN_URL . 'includes/admin/css/top-10-admin.min.css',
				false,
				'1.0',
				false
			);
			wp_localize_script(
				'top-ten-chart-data-js',
				'tptn_chart_data',
				array(
					'security'     => wp_create_nonce( 'tptn-dashboard' ),
					'datasetlabel' => __( 'Visits', 'top-10' ),
					'charttitle'   => __( 'Daily Visits', 'top-10' ),
				)
			);
		}

	}

	/**
	 * Function to add an action to search for tags using Ajax.
	 *
	 * @since 3.0.0
	 */
	public function get_chart_data() {
		global $wpdb;

		check_ajax_referer( 'tptn-dashboard', 'security' );

		$blog_id = get_current_blog_id();

		// Add date selector.
		$to_date   = isset( $_REQUEST['to_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['to_date'] ) ) : current_time( 'd M Y' );
		$from_date = isset( $_REQUEST['from_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['from_date'] ) ) : gmdate( 'd M Y', strtotime( '-1 week' ) );

		$post_date_from = gmdate( 'Y-m-d', strtotime( $from_date ) );
		$post_date_to   = gmdate( 'Y-m-d', strtotime( $to_date ) );

		$sql = $wpdb->prepare(
			" SELECT SUM(cntaccess) AS visits, DATE(dp_date) as date
			FROM {$wpdb->base_prefix}top_ten_daily
			WHERE DATE(dp_date) >= DATE(%s)
			AND DATE(dp_date) <= DATE(%s)
			AND blog_id = %d
			GROUP BY date
			ORDER BY date ASC
			",
			$post_date_from,
			$post_date_to,
			$blog_id
		);

		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$data = array();
		foreach ( $result as $row ) {
			$data[] = $row;
		}

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
			'today'     => array(
				'title'     => __( 'Today', 'top-10' ),
				'from_date' => current_time( 'd M Y' ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'yesterday' => array(
				'title'     => __( 'Yesterday', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 day' ) ),
				'to_date'   => gmdate( 'd M Y', strtotime( '-1 day' ) ),
			),
			'lastweek'  => array(
				'title'     => __( 'Last 7 days', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-1 week' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'lastmonth' => array(
				'title'     => __( 'Last 30 days', 'top-10' ),
				'from_date' => gmdate( 'd M Y', strtotime( '-30 days' ) ),
				'to_date'   => current_time( 'd M Y' ),
			),
			'overall'   => array(
				'title' => __( 'All time', 'top-10' ),
				'daily' => false,
			),
		);

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
		if ( $results ) :
			?>

			<table class="widefat striped">
			<?php
			foreach ( $results as $result ) :
				$visits = tptn_number_format_i18n( $result->visits );
				$result = get_post( $result->ID );
				?>
				<tr>
					<td><a href="<?php echo esc_url( get_permalink( $result ) ); ?>" target="_blank"><?php echo esc_html( get_the_title( $result ) ); ?></td>
					<td><?php echo esc_html( $visits ); ?></td>
				</tr>

			<?php endforeach; ?>
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

		if ( $args['daily'] ) {
			$table_name = $wpdb->base_prefix . 'top_ten_daily';
		} else {
			$table_name = $wpdb->base_prefix . 'top_ten';
		}

		// Fields to return.
		$fields[] = ( $args['daily'] ) ? "SUM({$table_name}.cntaccess) as visits" : "{$table_name}.cntaccess as visits";
		$fields[] = "{$wpdb->posts}.ID";

		$fields = implode( ', ', $fields );

		// Create the JOIN clause.
		$join = " INNER JOIN {$wpdb->posts} ON {$table_name}.postnumber={$wpdb->posts}.ID ";

		// Create the base WHERE clause.
		$where  = $wpdb->prepare( " AND {$table_name}.blog_id = %d ", $args['blog_id'] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$where .= " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit') ";   // Show published posts and attachments.
		$where .= " AND ($wpdb->posts.post_type <> 'revision' ) ";   // No revisions.

		if ( isset( $args['from_date'] ) ) {
			$from_date = gmdate( 'Y-m-d', strtotime( $args['from_date'] ) );
			$where    .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) >= DATE(%s) ", $from_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		if ( isset( $args['to_date'] ) ) {
			$to_date = gmdate( 'Y-m-d', strtotime( $args['to_date'] ) );
			$where  .= $wpdb->prepare( " AND DATE({$table_name}.dp_date) <= DATE(%s) ", $to_date ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Create the base GROUP BY clause.
		if ( $args['daily'] ) {
			$groupby = " {$wpdb->posts}.ID";
		}

		// Create the base ORDER BY clause.
		$orderby = ' visits DESC ';

		// Create the base LIMITS clause.
		$limits = $wpdb->prepare( ' LIMIT %d, %d ', $args['offset'], $args['numberposts'] );

		if ( ! empty( $groupby ) ) {
			$groupby = " GROUP BY {$groupby} ";
		}
		if ( ! empty( $orderby ) ) {
			$orderby = " ORDER BY {$orderby} ";
		}

		$sql = "SELECT DISTINCT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

		$result = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

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

/**
 * Function to initialise stats page.
 *
 * @since 3.0.0
 */
function tptn_load_dashboard() {
	Top_Ten_Dashboard::get_instance();
}
add_action( 'plugins_loaded', 'tptn_load_dashboard' );
