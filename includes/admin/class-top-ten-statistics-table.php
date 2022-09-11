<?php
/**
 * Top 10 Display statistics table.
 *
 * @package   Top_Ten
 * @subpackage  Top_Ten_Statistics_Table
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Top_Ten_Statistics_Table class.
 *
 * @extends WP_List_Table
 */
class Top_Ten_Statistics_Table extends WP_List_Table {

	/**
	 * Holds the post type array elements for translation.
	 *
	 * @var array
	 */
	public $all_post_type;

	/**
	 * Network wide popular posts flag.
	 *
	 * @var bool
	 */
	public $network_wide;

	/**
	 * Class constructor.
	 *
	 * @param bool $network_wide Network wide popular posts.
	 */
	public function __construct( $network_wide = false ) {
		parent::__construct(
			array(
				'singular' => __( 'popular_post', 'top-10' ), // Singular name of the listed records.
				'plural'   => __( 'popular_posts', 'top-10' ), // plural name of the listed records.
			)
		);
		$this->all_post_type = array(
			'all' => __( 'All post types', 'top-10' ),
		);
		$this->network_wide  = $network_wide;
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

		$blog_id = get_current_blog_id();

		$from_date = isset( $args['post-date-filter-from'] ) ? $args['post-date-filter-from'] : current_time( 'd M Y' );
		$from_date = gmdate( 'Y-m-d', strtotime( $from_date ) );
		$to_date   = isset( $args['post-date-filter-to'] ) ? $args['post-date-filter-to'] : current_time( 'd M Y' );
		$to_date   = gmdate( 'Y-m-d', strtotime( $to_date ) );

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
		if ( ! $this->network_wide ) {
			$join .= " LEFT JOIN {$wpdb->posts} ON ttt.postnumber={$wpdb->posts}.ID ";
		}
		$join .= $wpdb->prepare(
			" LEFT JOIN (
			SELECT * FROM {$table_name_daily} " . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'WHERE DATE(ttd.dp_date) >= DATE(%s) AND DATE(ttd.dp_date) <= DATE(%s)
			) AS ttd
			ON ttt.postnumber=ttd.postnumber
			',
			$from_date,
			$to_date
		);

		// Create the base WHERE clause.
		if ( ! $this->network_wide ) {
			$where .= $wpdb->prepare( ' AND ttt.blog_id = %d ', $blog_id ); // Posts need to be from the current blog only.

			// If search argument is set, do a search for it.
			if ( ! empty( $args['search'] ) ) {
				$where .= $wpdb->prepare( " AND $wpdb->posts.post_title LIKE %s ", '%' . $wpdb->esc_like( $args['search'] ) . '%' );
			}

			// If post filter argument is set, do a search for it.
			if ( isset( $args['post-type-filter'] ) && $this->all_post_type['all'] !== $args['post-type-filter'] ) {
				$where .= $wpdb->prepare( " AND $wpdb->posts.post_type = %s ", $args['post-type-filter'] );
			}
		}

		// Create the base GROUP BY clause.
		$groupby = ' ttt.postnumber, ttt.blog_id ';

		// Create the ORDER BY clause.
		$orderby = ' total_count DESC ';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! in_array( $orderby, array( 'title', 'daily_count', 'total_count' ) ) ) { //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
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
	 * Returns the count of records in the database.
	 *
	 * @param  string $args Array of arguments.
	 * @return null|string null|string
	 */
	public function record_count( $args = null ) {

		global $wpdb;

		$where = '';

		$sql = "SELECT COUNT(*) FROM {$wpdb->base_prefix}top_ten as ttt";

		if ( ! $this->network_wide ) {
			$where .= $wpdb->prepare( ' AND blog_id = %d ', get_current_blog_id() );

			if ( ! empty( $args['search'] ) ) {
				$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s ", '%' . $wpdb->esc_like( $args['search'] ) . '%' );
			}

			if ( isset( $args['post-type-filter'] ) && $this->all_post_type['all'] !== $args['post-type-filter'] ) {
				$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_type = %s ", $args['post-type-filter'] );
			}
		}
		return $wpdb->get_var( "$sql WHERE 1=1 $where" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Delete the post count for this post.
	 *
	 * @param int $id      Post ID.
	 * @param int $blog_id Blog ID.
	 */
	public static function delete_post_count( $id, $blog_id ) {
		tptn_delete_count( $id, $blog_id, false );
		tptn_delete_count( $id, $blog_id, true );
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
			'<input type="checkbox" name="%1$s[]" value="%2$s-%3$s" />',
			'bulk-delete',
			$item['ID'],
			$item['blog_id']
		);
	}

	/**
	 * Render the title column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_title( $item ) {

		$delete_nonce = wp_create_nonce( 'tptn_delete_entry' );
		$page         = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post         = $this->network_wide ? get_blog_post( $item['blog_id'], $item['ID'] ) : get_post( $item['ID'] );

		if ( null === $post ) {
			return sprintf(
				'%s <span style="color:grey">(id:%2$s)</span>',
				__( 'Invalid post ID. This post might have been deleted.', 'top-10' ),
				$item['ID']
			);
		}

		$actions = array(
			'view'   => sprintf( '<a href="%s" target="_blank">' . __( 'View', 'top-10' ) . '</a>', get_permalink( $item['ID'] ) ),
			'edit'   => sprintf( '<a href="%s">' . __( 'Edit', 'top-10' ) . '</a>', get_edit_post_link( $item['ID'] ) ),
			'delete' => sprintf(
				'<a href="?page=%1$s&action=%2$s&post=%3$s&blog_id=%4$s&_wpnonce=%5$s">' . __( 'Delete', 'top-10' ) . '</a>',
				esc_attr( $page ),
				'delete',
				absint( $item['ID'] ),
				absint( $item['blog_id'] ),
				$delete_nonce
			),
		);

		// Return the title contents.
		return sprintf(
			'<a href="%4$s" target="_blank">%1$s</a> <span style="color:grey">(id:%2$s)</span>%3$s',
			$post->post_title,
			$item['ID'],
			$this->network_wide ? '' : $this->row_actions( $actions ),
			is_multisite() ? get_blog_permalink( $item['blog_id'], $item['ID'] ) : get_permalink( $item['ID'] )
		);

	}


	/**
	 * Handles the post date column output.
	 *
	 * @param array $item Current item.
	 * @return string Post date.
	 */
	public function column_date( $item ) {

		$post = is_multisite() ? get_blog_post( $item['blog_id'], $item['ID'] ) : get_post( $item['ID'] );

		if ( $post ) {
			$m_time = strtotime( $post->post_date );
			$h_time = wp_date( get_option( 'date_format' ), $m_time );

			return sprintf(
				'<abbr title="%1$s">%1$s</abbr>',
				esc_attr( $h_time )
			);
		}
	}

	/**
	 * Handles the post_type column output.
	 *
	 * @param array $item Current item.
	 * @return string Post Type.
	 */
	public function column_post_type( $item ) {

		$post = is_multisite() ? get_blog_post( $item['blog_id'], $item['ID'] ) : get_post( $item['ID'] );

		if ( $post ) {
			$pt = get_post_type_object( $post->post_type );
			return $pt->labels->singular_name;
		}
	}

	/**
	 * Handles the post author column output.
	 *
	 * @param array $item Current item.
	 * @return string Post Author.
	 */
	public function column_author( $item ) {

		$post = is_multisite() ? get_blog_post( $item['blog_id'], $item['ID'] ) : get_post( $item['ID'] );
		if ( ! $post ) {
			return;
		}

		$author_info = get_userdata( $post->post_author );
		$author_name = ( false === $author_info ) ? '' : ucwords( trim( stripslashes( $author_info->display_name ) ) );

		return sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				add_query_arg(
					array(
						'post_type' => $post->post_type,
						'author'    => ( false === $author_info ) ? 0 : $author_info->ID,
					),
					get_admin_url( $item['blog_id'], 'edit.php' )
				)
			),
			esc_html( $author_name )
		);
	}

	/**
	 * Render the Total Count column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	public function column_total_count( $item ) {
		return sprintf(
			'<div contentEditable="true" class="live_edit" id="total_count_%1$s" data-wp-post-id="%1$s" data-wp-count="%2$s">%3$s</div>',
			$item['ID'],
			$item['total_count'],
			tptn_number_format_i18n( absint( $item['total_count'] ) )
		);
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
			'total_count' => __( 'Total visits', 'top-10' ),
			'daily_count' => __( 'Daily visits', 'top-10' ),
			'post_type'   => __( 'Post type', 'top-10' ),
			'author'      => __( 'Author', 'top-10' ),
			'date'        => __( 'Date', 'top-10' ),
		);

		if ( $this->network_wide ) {
			$columns['blog_id'] = __( 'Blog', 'top-10' );
		}

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
			'title'       => array( 'title', false ),
			'total_count' => array( 'total_count', true ),
			'daily_count' => array( 'daily_count', true ),
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
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page( 'pop_posts_per_page', 20 );

		$current_page = $this->get_pagenum();

		$args = array();

		// If this is a search?
		if ( isset( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// If this is a post type filter?
		if ( isset( $_REQUEST['post-type-filter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['post-type-filter'] = sanitize_text_field( wp_unslash( $_REQUEST['post-type-filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// If this is a post date filter?
		if ( isset( $_REQUEST['post-date-filter-to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['post-date-filter-to'] = sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( isset( $_REQUEST['post-date-filter-from'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['post-date-filter-from'] = sanitize_text_field( wp_unslash( $_REQUEST['post-date-filter-from'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$this->items = self::get_popular_posts( $per_page, $current_page, $args );
		$total_items = self::record_count( $args );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // WE have to calculate the total number of items.
				'per_page'    => $per_page, // WE have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			)
		);

	}

	/**
	 * Handles any bulk actions
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
			$blog_id = isset( $_GET['blog_id'] ) ? absint( $_GET['blog_id'] ) : get_current_blog_id();

			if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'tptn_delete_entry' ) ) {
				self::delete_post_count( $post_id, $blog_id );
			} else {
				die( esc_html__( 'Are you sure you want to do this', 'top-10' ) );
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === $_REQUEST['action'] )
			|| ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === $_REQUEST['action2'] )
		) {
			$delete_ids = isset( $_REQUEST['bulk-delete'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_REQUEST['bulk-delete'] ) ) : array();

			// Loop over the array of record IDs and delete them.
			$post_ids = array();
			$blog_ids = array();
			foreach ( $delete_ids as $id ) {
				$pieces     = explode( '-', $id );
				$post_ids[] = absint( $pieces[0] );
				$blog_ids[] = absint( $pieces[1] );
			}
			tptn_delete_counts(
				array(
					'post_id' => $post_ids,
					'blog_id' => $blog_ids,
					'daily'   => true,
				)
			);
			tptn_delete_counts(
				array(
					'post_id' => $post_ids,
					'blog_id' => $blog_ids,
					'daily'   => false,
				)
			);
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

			if ( ! $this->network_wide ) {
				$post_types = get_post_types(
					array(
						'public' => true,
					)
				);
				$post_types = $this->all_post_type + $post_types;

				if ( $post_types ) {

					echo '<select name="post-type-filter">';

					foreach ( $post_types as $post_type ) {
						$selected = '';
						if ( isset( $_REQUEST['post-type-filter'] ) && $_REQUEST['post-type-filter'] === $post_type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$selected = ' selected = "selected"';
						}
						?>
						<option value="<?php echo esc_attr( $post_type ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $post_type ); ?></option>
						<?php
					}

					echo '</select>';

				}
			}

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
