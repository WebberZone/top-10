<?php
/**
 * Top 10 Display statistics page.
 *
 * @package   Top_Ten
 * @subpackage	Top_Ten_Statistics
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2016 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Top_Ten_Statistics_Table class.
 *
 * @extends WP_List_Table
 */
class Top_Ten_Statistics_Table extends WP_List_Table {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'popular_post', 'top-10' ), // Singular name of the listed records.
			'plural'   => __( 'popular_posts', 'top-10' ), // plural name of the listed records.
		) );
	}

	/**
	 * Retrieve the Top 10 posts
	 *
	 * @param int   $per_page Posts per page.
	 * @param int   $page_number Page number.
	 * @param array $args Array of arguments.
	 *
	 * @return	array	Array of popular posts
	 */
	public static function get_popular_posts( $per_page = 5, $page_number = 1, $args = null ) {

		global $wpdb, $tptn_settings;

		$blog_id = get_current_blog_id();

		if ( $tptn_settings['daily_midnight'] ) {
			$current_time = current_time( 'timestamp', 0 );
			$from_date = $current_time - ( max( 0, ( $tptn_settings['daily_range'] - 1 ) ) * DAY_IN_SECONDS );
			$from_date = gmdate( 'Y-m-d 0' , $from_date );
		} else {
			$current_time = current_time( 'timestamp', 0 );
			$from_date = $current_time - ( $tptn_settings['daily_range'] * DAY_IN_SECONDS + $tptn_settings['hour_range'] * HOUR_IN_SECONDS );
			$from_date = gmdate( 'Y-m-d H' , $from_date );
		}

		/* Start creating the SQL */
		$table_name_daily = $wpdb->base_prefix . 'top_ten_daily AS ttd';
		$table_name = $wpdb->base_prefix . 'top_ten AS ttt';

		// Fields to return.
		$fields[] = 'ID';
		$fields[] = 'post_title as title';
		$fields[] = 'post_type';
		$fields[] = 'post_date';
		$fields[] = 'post_author';
		$fields[] = 'ttt.cntaccess as total_count';
		$fields[] = 'SUM(ttd.cntaccess) as daily_count';

		$fields = implode( ', ', $fields );

		// Create the JOIN clause.
		$join = " INNER JOIN {$wpdb->posts} ON ttt.postnumber=ID ";
		$join .= $wpdb->prepare(  // DB call ok; no-cache ok; WPCS: unprepared SQL OK.
			" LEFT JOIN (
			SELECT * FROM {$table_name_daily}
			WHERE ttd.dp_date >= '%s'
			) AS ttd
			ON ttt.postnumber=ttd.postnumber
		", $from_date );

		// Create the base WHERE clause.
		$where = $wpdb->prepare( ' AND ttt.blog_id = %d ', $blog_id ); // Posts need to be from the current blog only.
		$where .= " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit') ";   // Show published posts and attachments.
		$where .= " AND ($wpdb->posts.post_type <> 'revision' ) ";   // No revisions.

		/* If search argument is set, do a search for it. */
		if ( isset( $args['search'] ) ) {
			$where .= $wpdb->prepare( " AND $wpdb->posts.post_title LIKE '%%%s%%' ", $args['search'] );
		}

		/* If post filter argument is set, do a search for it. */
		if ( isset( $args['post-type-filter'] ) && 'All' !== $args['post-type-filter'] ) {
			$where .= $wpdb->prepare( " AND $wpdb->posts.post_type = '%s' ", $args['post-type-filter'] );
		}

		// Create the base GROUP BY clause.
		$groupby = ' ID ';

		// Create the base ORDER BY clause.
		$orderby = ' total_count DESC ';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // Input var okay.
			$orderby = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );

			if ( ! in_array( $orderby, array( 'title', 'daily_count', 'total_count' ) ) ) {
				$orderby = ' total_count ';
			}

			if ( ! empty( $_REQUEST['order'] ) ) {
				$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );

				if ( in_array( $order, array( 'asc', 'ASC', 'desc', 'DESC' ) ) ) {
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

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;

	}


	/**
	 * Delete the post count for this post.
	 *
	 * @param int $id post ID.
	 */
	public static function delete_post_count( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->base_prefix}top_ten",
			array( 'postnumber' => $id ),
			array( '%d' )
		);
		$wpdb->delete(
			"{$wpdb->base_prefix}top_ten_daily",
			array( 'postnumber' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @param  string $args Array of arguments.
	 * @return null|string null|string
	 */
	public static function record_count( $args = null ) {
		global $wpdb;

		$sql = $wpdb->prepare( "
			SELECT COUNT(*) FROM {$wpdb->base_prefix}top_ten as ttt
			INNER JOIN {$wpdb->posts} ON ttt.postnumber=ID
			WHERE blog_id=%d
			AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'inherit')
		", get_current_blog_id() );

		if ( isset( $args['search'] ) ) {
			$sql .= $wpdb->prepare( " AND $wpdb->posts.post_title LIKE '%%%s%%' ", $args['search'] );
		}

		if ( isset( $args['post-type-filter'] ) && 'All' !== $args['post-type-filter'] ) {
			$sql .= $wpdb->prepare( " AND $wpdb->posts.post_type = '%s' ", $args['post-type-filter'] );
		}

		return $wpdb->get_var( $sql );
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
			case 'post_type':
				return $item[ $column_name ];
			case 'total_count':
			case 'daily_count':
				return intval( $item[ $column_name ] );
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
		}
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item Current item.
	 * @return string
	 */
	function column_cb( $item ) {
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
	function column_title( $item ) {

		$delete_nonce = wp_create_nonce( 'tptn_delete_entry' );

		$actions = array(
			'view' => sprintf( '<a href="%s" target="_blank">' . __( 'View', 'top-10' ) . '</a>', get_permalink( $item['ID'] ) ),
			'edit' => sprintf( '<a href="%s">' . __( 'Edit', 'top-10' ) . '</a>', get_edit_post_link( $item['ID'] ) ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&post=%s&_wpnonce=%s">' . __( 'Delete', 'top-10' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
		);

		// Return the title contents.
		return sprintf( '<a href="%4$s">%1$s</a> <span style="color:silver">(id:%2$s)</span>%3$s',
			$item['title'],
			$item['ID'],
			$this->row_actions( $actions ),
			get_edit_post_link( $item['ID'] )
		);

	}


	/**
	 * Handles the post date column output.
	 *
	 * @param array $item Current item.
	 * @return void
	 */
	public function column_date( $item ) {

		$t_time = get_the_time( __( 'Y/m/d g:i:s a', 'top-10' ) );
		$m_time = $item['post_date'];
		$time = get_post_time( 'G', true, $item['ID'] );

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
			$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
		} else {
			$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
		}

		echo '<abbr title="' . esc_attr( $t_time ) . '">' . esc_attr( $h_time ) . '</abbr>';
	}

	/**
	 * Handles the post author column output.
	 *
	 * @param array $item Current item.
	 * @return void
	 */
	public function column_author( $item ) {
		$author_info = get_userdata( $item['post_author'] );
		$author_name = ( false === $author_info ) ? '' : ucwords( trim( stripslashes( $author_info->display_name ) ) );

		printf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array(
				'post_type' => $item['post_type'],
				'author' => ( false === $author_info ) ? 0 : $author_info->ID,
			), 'edit.php' ) ),
			esc_html( $author_name )
		);
	}

	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'			=> '<input type="checkbox" />',
			'title'			=> __( 'Title', 'top-10' ),
			'total_count'	=> __( 'Total visits', 'top-10' ),
			'daily_count'	=> __( 'Daily visits', 'top-10' ),
			'post_type'		=> __( 'Post type', 'top-10' ),
			'author'		=> __( 'Author', 'top-10' ),
			'date'			=> __( 'Date', 'top-10' ),
		);

		/**
		 * Filter the columns displayed in the Posts list table.
		 *
		 * @since 1.5.0
		 *
		 * @param	array	$columns	An array of column names.
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
			'title' => array( 'title', false ),
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

		$per_page     = $this->get_items_per_page( 'pop_posts_per_page', 20 );

		$current_page = $this->get_pagenum();

		$total_items  = self::record_count( $args );

		$this->set_pagination_args( array(
			'total_items' => $total_items, // WE have to calculate the total number of items
			'per_page'    => $per_page, // WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ),// WE have to calculate the total number of pages
		) );

		$this->items = self::get_popular_posts( $per_page, $current_page, $args );
	}

	/**
	 * Handles any bulk actions
	 */
	public function process_bulk_action() {

		// Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'tptn_delete_entry' ) ) {
				die( esc_html__( 'Are you sure you want to do this', 'top-10' ) );
			} else {
				self::delete_post_count( absint( $_GET['post'] ) );
			}
		}

		// If the delete bulk action is triggered.
		if ( ( isset( $_REQUEST['action'] ) && 'bulk-delete' === $_REQUEST['action'] )
		     || ( isset( $_REQUEST['action2'] ) && 'bulk-delete' === $_REQUEST['action2'] )
		) {
			$delete_ids = sanitize_text_field( wp_unslash( $_REQUEST['bulk-delete'] ) );

			// Loop over the array of record IDs and delete them,
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
		$post_types = get_post_types( array( 'public' => true ) );
		$all = array(
			'all' => 'All',
		);
		$post_types = $all + $post_types;

		if ( $post_types ) {

			echo '<select name="post-type-filter">';

			foreach ( $post_types as $post_type ) {
				$selected = '';
				if ( isset( $_REQUEST['post-type-filter'] ) && $_REQUEST['post-type-filter'] === $post_type ) {
					$selected = ' selected = "selected"';
				}
				?>
				<option value="<?php echo esc_attr( $post_type ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $post_type ); ?></option>
				<?php
			}

			echo '</select>';

			submit_button( __( 'Filter', 'top-10' ), 'button', 'filter_action', false, array( 'id' => 'top-10-query-submit' ) );
		}
	}
	?>
		</div>
	<?php
	}
}

/**
 * Top_Ten_Statistics class.
 */
class Top_Ten_Statistics {

	/**
	 * Class instance.
	 */
	static $instance;

	/**
	 * WP_List_Table object.
	 */
	public $pop_posts_obj;

	/**
	 * Class constructor.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
	}

	/**
	 * Set screen.
	 *
	 * @param  string $status Status of screen.
	 * @param  string $option Option name.
	 * @param  string $value  Option value
	 * @return string Value.
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		$args = null;
		?>
		<div class="wrap">
			<h1><?php printf( _x( '%s Popular Posts', 'Plugin name', 'top-10' ), 'Top 10' ); ?></h1>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="get">
								<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
								<?php
								// If this is a search?
								if ( isset( $_REQUEST['s'] ) ) {
									$args['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
								}
								// If this is a post type filter?
								if ( isset( $_REQUEST['post-type-filter'] ) ) {
									$args['post-type-filter'] = sanitize_text_field( wp_unslash( $_REQUEST['post-type-filter'] ) );
								}

								$this->pop_posts_obj->prepare_items( $args );

								$this->pop_posts_obj->search_box( __( 'Search Table', 'top-10' ), 'top-10' );

								$this->pop_posts_obj->display();
								?>
							</form>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<?php tptn_admin_side(); ?>
						</div><!-- /side-sortables -->
					</div><!-- /postbox-container-1 -->
				</div><!-- /post-body -->
				<br class="clear" />
			</div><!-- /poststuff -->
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Popular Posts', 'top-10' ),
			'default' => 20,
			'option'  => 'pop_posts_per_page',
		);
		add_screen_option( $option, $args );
		$this->pop_posts_obj = new Top_Ten_Statistics_Table();
	}

	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

/**
 * Function to initialise stats page.
 *
 * @since 2.4.2
 */
function tptn_stats_page() {
	Top_Ten_Statistics::get_instance();
}
add_action( 'plugins_loaded', 'tptn_stats_page' );
