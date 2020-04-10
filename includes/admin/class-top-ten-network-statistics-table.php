<?php
/**
 * Top 10 Display Network statistics table.
 *
 * @package   Top_Ten
 * @subpackage  Top_Ten_Network_Statistics_Table
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2020 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Top_Ten_Network_Table class.
 *
 * @extends WP_List_Table
 */
class Top_Ten_Network_Statistics_Table extends WP_List_Table {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'popular_post', 'top-10' ), // Singular name of the listed records.
				'plural'   => __( 'popular_posts', 'top-10' ), // plural name of the listed records.
			)
		);
	}

	/**
	 * Retrieve the Top 10 posts
	 *
	 * @param int   $per_page Posts per page.
	 * @param int   $page_number Page number.
	 * @param array $args Array of arguments.
	 *
	 * @return  array   Array of popular posts
	 */
	public function get_popular_posts( $per_page = 20, $page_number = 1, $args = null ) {

		global $wpdb;

		// Initialise some variables.
		$fields  = array();
		$where   = '';
		$join    = '';
		$groupby = '';
		$orderby = '';
		$limits  = '';
		$sql     = '';

		$from_date = tptn_get_from_date(
			isset( $args['post-date-filter-from'] ) ? $args['post-date-filter-from'] : null,
			1,
			0
		);
		$to_date   = tptn_get_from_date(
			isset( $args['post-date-filter-to'] ) ? $args['post-date-filter-to'] : null,
			1,
			0
		);

		/* Start creating the SQL */
		$table_name_daily = $wpdb->base_prefix . 'top_ten_daily AS ttd';
		$table_name       = $wpdb->base_prefix . 'top_ten AS ttt';

		// Fields to return.
		$fields[] = 'ttt.postnumber as ID';
		$fields[] = 'ttt.cntaccess as total_count';
		$fields[] = 'SUM(ttd.cntaccess) as daily_count';
		$fields[] = 'ttt.blog_id as blog_id';

		$fields = implode( ', ', $fields );

		// Create the JOIN clause.
		$join = $wpdb->prepare(
			" LEFT JOIN (
			SELECT * FROM {$table_name_daily} " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'WHERE DATE(ttd.dp_date) >= DATE(%s) AND DATE(ttd.dp_date) <= DATE(%s)
			) AS ttd
			ON ttt.postnumber=ttd.postnumber
			',
			$from_date,
			$to_date
		);

		// Create the base GROUP BY clause.
		$groupby = ' ID, blog_id ';

		// Create the base ORDER BY clause.
		$orderby = ' total_count DESC ';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! in_array( $orderby, array( 'daily_count', 'total_count' ) ) ) { //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
				$orderby = ' total_count ';
			}

			if ( ! empty( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( in_array( $order, array( 'asc', 'ASC', 'desc', 'DESC' ) ) ) { //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					$orderby .= ' ' . $order;
				} else {
					$orderby .= ' DESC';
				}
			}
		}

		// Create the base LIMITS clause.
		$limits = $wpdb->prepare( ' LIMIT %d, %d ', ( $page_number - 1 ) * $per_page, $per_page );

		if ( ! empty( $groupby ) ) {
			$groupby = " GROUP BY {$groupby} ";
		}
		if ( ! empty( $orderby ) ) {
			$orderby = " ORDER BY {$orderby} ";
		}

		$sql = "SELECT $fields FROM {$table_name} $join WHERE 1=1 $where $groupby $orderby $limits";

		$result = $wpdb->get_results( $sql, 'ARRAY_A' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $result;

	}


	/**
	 * Delete the post count for this post.
	 *
	 * @param int $id post ID.
	 */
	public static function delete_post_count( $id ) {
		global $wpdb;

		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->base_prefix}top_ten",
			array(
				'postnumber' => $id,
			),
			array( '%d' )
		);
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"{$wpdb->base_prefix}top_ten_daily",
			array(
				'postnumber' => $id,
			),
			array( '%d' )
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @param  string $args Array of arguments.
	 * @return null|string null|string
	 */
	public function record_count( $args = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass

		global $wpdb;

		$sql = $wpdb->prepare(
			"
			SELECT COUNT(*) FROM {$wpdb->base_prefix}top_ten as ttt
			INNER JOIN {$wpdb->posts} ON ttt.postnumber=ID
			WHERE blog_id=%d
			AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit')
		",
			get_current_blog_id()
		);

		return $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Text displayed when no post data is available
	 */
	public function no_items() {
		esc_html_e( 'No popular posts available.', 'top-10' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item Current item.
	 * @param string $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'total_count':
			case 'daily_count':
				return tptn_number_format_i18n( absint( $item[ $column_name ] ) );
			default:
				// Show the whole array for troubleshooting purposes.
				return print_r( $item, true );  //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'bulk-delete',
			$item['ID']
		);
	}

	/**
	 * Render the title column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_title( $item ) {

		$blog_post = get_blog_post( $item['blog_id'], $item['ID'] );

		// Return the title contents.
		return sprintf(
			'<a href="%3$s" target="_blank">%1$s</a> <span style="color:silver">(id:%2$s)</span>',
			$blog_post->post_title,
			$item['ID'],
			get_blog_permalink( $item['blog_id'], $item['ID'] )
		);

	}


	/**
	 * Handles the post date column output.
	 *
	 * @param array $item Current item.
	 * @return void
	 */
	public function column_date( $item ) {

		$blog_post = get_blog_post( $item['blog_id'], $item['ID'] );

		$m_time = $blog_post->post_date;
		$h_time = mysql2date( __( 'Y/m/d' ), $m_time );

		echo '<abbr title="' . esc_attr( $h_time ) . '">' . esc_attr( $h_time ) . '</abbr>';
	}

	/**
	 * Handles the blog id column output.
	 *
	 * @param array $item Current item.
	 * @return void
	 */
	public function column_blog_id( $item ) {

		$blog_details = get_blog_details( $item['blog_id'] );

		printf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url(
				add_query_arg(
					array(
						'page' => 'tptn_popular_posts',
					),
					get_admin_url( $item['blog_id'] ) . 'admin.php'
				)
			),
			esc_html( $blog_details->blogname )
		);
	}

	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Title', 'top-10' ),
			'blog_id'     => __( 'Blog', 'top-10' ),
			'date'        => __( 'Date', 'top-10' ),
			'total_count' => __( 'Total visits', 'top-10' ),
			'daily_count' => __( 'Daily visits', 'top-10' ),
		);

		/**
		 * Filter the columns displayed in the Posts list table.
		 *
		 * @since 1.5.0
		 *
		 * @param   array   $columns    An array of column names.
		 */
		return apply_filters( 'manage_pop_posts_columns', $columns );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'total_count' => array( 'total_count', false ),
			'daily_count' => array( 'daily_count', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete Count', 'top-10' ),
		);
		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 *
	 * @param array $args Array of arguments.
	 */
	public function prepare_items( $args = null ) {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'pop_posts_per_page', 20 );

		$current_page = $this->get_pagenum();

		$total_items = self::record_count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			)
		);

		$this->items = self::get_popular_posts( $per_page, $current_page, $args );
	}

	/**
	 * Handles any bulk actions
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$postid = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'tptn_delete_entry' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				self::delete_post_count( $postid );
			} else {
				die( esc_html__( 'Are you sure you want to do this', 'top-10' ) );
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === $_REQUEST['action'] )
			|| ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === $_REQUEST['action2'] )
		) {
			$delete_ids = isset( $_REQUEST['bulk-delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bulk-delete'] ) ) : array();

			// Loop over the array of record IDs and delete them.
			foreach ( $delete_ids as $id ) {
				self::delete_post_count( $id );
			}
		}
	}

	/**
	 * Adds extra navigation elements to the table.
	 *
	 * @param string $which Which part of the table are we.
	 */
	public function extra_tablenav( $which ) {
		?>
		<div class="alignleft actions">
		<?php
		if ( 'top' === $which ) {
			ob_start();

			// Add date selector.
			$current_date = current_time( 'd M Y' );

			$post_date_from = isset( $_REQUEST['post-date-filter-from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-from'] ) ) : $current_date; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="text" id="datepicker-from" name="post-date-filter-from" value="' . esc_attr( $post_date_from ) . '" size="11" />';

			$post_date_to = isset( $_REQUEST['post-date-filter-to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-to'] ) ) : $current_date; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<input type="text" id="datepicker-to" name="post-date-filter-to" value="' . esc_attr( $post_date_to ) . '" size="11" />';

			$output = ob_get_clean();

			if ( ! empty( $output ) ) {
				echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				submit_button( __( 'Filter' ), '', 'filter_action', false, array( 'id' => 'top-10-query-submit' ) );
			}
		}
		?>
		</div>
		<?php
	}
}

