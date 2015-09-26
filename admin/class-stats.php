<?php
/**
 * Top 10 Display statistics page.
 *
 * @package   Top_Ten
 * @subpackage	Top_Ten_Statistics
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
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
			'singular' => __( 'popular_post', 'top-10' ), // singular name of the listed records
			'plural'   => __( 'popular_posts', 'top-10' ), // plural name of the listed records
			// 'ajax'     => false //does this table support ajax?
		) );
	}

	/**
	 * Retrieve the Top 10 posts
	 *
	 * @param	int  $per_page
	 * @param	int  $page_number
	 * @param	bool $daily
	 *
	 * @return	array	Array of popular posts
	 */
	public static function get_popular_posts( $per_page = 5, $page_number = 1, $daily = false ) {

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

		// Fields to return
		$fields[] = 'ID';
		$fields[] = 'post_title as title';
		$fields[] = 'post_type';
		$fields[] = 'post_date';
		$fields[] = 'post_author';
		$fields[] = 'ttt.cntaccess as total_count';
		$fields[] = 'SUM(ttd.cntaccess) as daily_count';

		$fields = implode( ', ', $fields );

		// Create the JOIN clause
		$join = " INNER JOIN {$wpdb->posts} ON ttt.postnumber=ID ";
		$join .= $wpdb->prepare( " LEFT JOIN (
			SELECT * FROM {$table_name_daily}
			WHERE ttd.dp_date >= '%s'
			) AS ttd
			ON ttt.postnumber=ttd.postnumber
		", $from_date );

		// Create the base WHERE clause
		$where = $wpdb->prepare( ' AND ttt.blog_id = %d ', $blog_id );				// Posts need to be from the current blog only
		$where .= " AND $wpdb->posts.post_status = 'publish' ";					// Only show published posts

		// Create the base GROUP BY clause
		$groupby = ' ID ';

		// Create the base ORDER BY clause
		$orderby = ' total_count DESC ';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$orderby = esc_sql( $_REQUEST['orderby'] );
			$orderby .= ! empty( $_REQUEST['order'] ) ? ' ' . ( $_REQUEST['order'] ) : ' DESC';
		}

		// Create the base LIMITS clause
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
	 * @param int $id post ID
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
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->base_prefix}top_ten WHERE blog_id=%d", get_current_blog_id() );
		return $wpdb->get_var( $sql );
	}

	/**
	 * Text displayed when no post data is available
	 */
	public function no_items() {
		_e( 'No popular posts available.', 'top-10' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item
	 * @param string $column_name
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
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'bulk-delete',  // Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['ID']                // The value of the checkbox should be the record's id
		);
	}

	/**
	 * Render the title column.
	 *
	 * @param array $item
	 * @return string
	 */
	function column_title( $item ) {

		$delete_nonce = wp_create_nonce( 'tptn_delete_entry' );

		$actions = array(
			'view' => sprintf( '<a href="%s" target="_blank">' . __( 'View', 'top-10' ) . '</a>', get_permalink( $item['ID'] ) ),
			'edit' => sprintf( '<a href="%s">' . __( 'Edit', 'top-10' ) . '</a>', get_edit_post_link( $item['ID'] ) ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&post=%s&_wpnonce=%s">' . __( 'Delete', 'top-10' ) . '</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce ),
		);

		// Return the title contents
		return sprintf( '<a href="%4$s">%1$s</a> <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $item['title'],
			/*$2%s*/ $item['ID'],
			/*$3%s*/ $this->row_actions( $actions ),
			/*$3%s*/ get_edit_post_link( $item['ID'] )
		);

	}


	/**
	 * Handles the post date column output.
	 *
	 * @param array $item
	 * @return string
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

		echo '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}

	/**
	 * Handles the post author column output.
	 *
	 * @param array $item
	 * @return string
	 */
	public function column_author( $item ) {
		$author_info = get_userdata( $item['post_author'] );
		$author_name = ucwords( trim( stripslashes( $author_info->display_name ) ) );
		$author_link = get_author_posts_url( $author_info->ID );

		printf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array(
				'post_type' => $item['post_type'],
				'author' => $author_info->ID,
			), 'edit.php' ) ),
			$author_name
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
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'pop_posts_per_page', 20 );

		$current_page = $this->get_pagenum();

		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, // WE have to calculate the total number of items
			'per_page'    => $per_page, // WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items / $per_page ),// WE have to calculate the total number of pages
		) );

		$this->items = self::get_popular_posts( $per_page, $current_page );
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
				die( __( 'Are you sure you want to do this', 'top-10' ) );
			} else {
				self::delete_post_count( absint( $_GET['post'] ) );
			}
		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_post_count( $id );
			}
		}
	}
}

/**
 * Top_Ten_Statistics class.
 */
class Top_Ten_Statistics {

	// class instance
	static $instance;

	// WP_List_Table object
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

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h1><?php printf( _x( '%s Popular Posts', 'Plugin name', 'top-10' ), 'Top 10' ); ?></h1>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->pop_posts_obj->prepare_items();
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

