<?php
/**
 * Manage columns on All Posts and All pages screens.
 *
 * @link https://webberzone.com
 * @since 3.3.0
 *
 * @package Top 10
 */

namespace WebberZone\Top_Ten\Admin;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Columns Class.
 *
 * @since 3.3.0
 */
class Columns {

	/**
	 * Constructor class.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		add_filter( 'manage_posts_columns', array( __CLASS__, 'add_columns' ) );
		add_filter( 'manage_pages_columns', array( __CLASS__, 'add_columns' ) );
		add_action( 'manage_posts_custom_column', array( __CLASS__, 'tptn_value' ), 10, 2 );
		add_action( 'manage_pages_custom_column', array( __CLASS__, 'tptn_value' ), 10, 2 );
		add_filter( 'manage_edit-post_sortable_columns', array( __CLASS__, 'add_columns_register_sortable' ) );
		add_filter( 'manage_edit-page_sortable_columns', array( __CLASS__, 'add_columns_register_sortable' ) );
		add_filter( 'posts_clauses', array( __CLASS__, 'add_columns_clauses' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'admin_css' ) );
	}

	/**
	 * Add an extra column to the All Posts page to display the page views.
	 *
	 * @param  array $cols   Array of all columns on posts page.
	 * @return array Modified array of columns.
	 */
	public static function add_columns( $cols ) {

		if ( ( current_user_can( 'manage_options' ) ) || ( \tptn_get_option( 'show_count_non_admins' ) ) ) {
			if ( \tptn_get_option( 'pv_in_admin' ) ) {
				$cols['tptn_total'] = __( 'Total Views', 'top-10' );
				$cols['tptn_daily'] = __( "Today's Views", 'top-10' );
				$cols['tptn_both']  = __( 'Views', 'top-10' );
			}
		}
		return $cols;
	}

	/**
	 * Display page views for each column.
	 *
	 * @since   1.2
	 *
	 * @param   string     $column_name    Name of the column.
	 * @param   int|string $id             Post ID.
	 */
	public static function tptn_value( $column_name, $id ) {
		global $wpdb;

		$blog_id = get_current_blog_id();

		// Add Total count.
		if ( ( 'tptn_total' === $column_name ) && ( \tptn_get_option( 'pv_in_admin' ) ) ) {
			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( false );

			$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$cntaccess    = \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( ( ( $resultscount ) ? $resultscount->cntaccess : 0 ) );
			echo esc_html( $cntaccess );
		}

		// Now process daily count.
		if ( ( 'tptn_daily' === $column_name ) && ( \tptn_get_option( 'pv_in_admin' ) ) ) {
			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( true );

			$from_date = \WebberZone\Top_Ten\Util\Helpers::get_from_date();

			$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as visits FROM {$table_name} WHERE postnumber = %d AND dp_date >= %s AND blog_id = %d GROUP BY postnumber ", $id, $from_date, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$cntaccess    = \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( ( ( $resultscount ) ? $resultscount->visits : 0 ) );
			echo esc_html( $cntaccess );
		}

		// Now process both.
		if ( ( 'tptn_both' === $column_name ) && ( \tptn_get_option( 'pv_in_admin' ) ) ) {
			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( false );

			$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, cntaccess FROM {$table_name} WHERE postnumber = %d AND blog_id = %d ", $id, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$cntaccess    = \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( ( ( $resultscount ) ? $resultscount->cntaccess : 0 ) );

			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( true );

			$from_date = \WebberZone\Top_Ten\Util\Helpers::get_from_date();

			$resultscount = $wpdb->get_row( $wpdb->prepare( "SELECT postnumber, SUM(cntaccess) as visits FROM {$table_name} WHERE postnumber = %d AND dp_date >= %s AND blog_id = %d GROUP BY postnumber ", $id, $from_date, $blog_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$cntaccess   .= ' / ' . \WebberZone\Top_Ten\Util\Helpers::number_format_i18n( ( ( $resultscount ) ? $resultscount->visits : 0 ) );

			echo esc_html( $cntaccess );
		}
	}

	/**
	 * Register the columns as sortable.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   array $cols   Array with column names.
	 * @return  array   Filtered columns array
	 */
	public static function add_columns_register_sortable( $cols ) {

		if ( \tptn_get_option( 'pv_in_admin' ) ) {
			$cols['tptn_total'] = array( 'tptn_total', true );
			$cols['tptn_daily'] = array( 'tptn_daily', true );
		}

		return $cols;
	}

	/**
	 * Add custom post clauses to sort the columns.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   array     $clauses    Lookup clauses.
	 * @param   \WP_Query $wp_query   WP Query object.
	 * @return  array   Filtered clauses
	 */
	public static function add_columns_clauses( $clauses, $wp_query ) {
		global $wpdb;

		if ( isset( $wp_query->query['orderby'] ) && 'tptn_total' === $wp_query->query['orderby'] ) {

			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( false );

			$clauses['join']    .= "LEFT OUTER JOIN {$table_name} ON {$wpdb->posts}.ID={$table_name}.postnumber";
			$clauses['orderby']  = 'cntaccess ';
			$clauses['orderby'] .= ( 'ASC' === strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
		}

		if ( isset( $wp_query->query['orderby'] ) && 'tptn_daily' === $wp_query->query['orderby'] ) {

			$table_name = \WebberZone\Top_Ten\Util\Helpers::get_tptn_table( true );

			$from_date = \WebberZone\Top_Ten\Util\Helpers::get_from_date();

			$clauses['join']    .= "LEFT OUTER JOIN {$table_name} ON {$wpdb->posts}.ID={$table_name}.postnumber";
			$clauses['where']   .= " AND {$table_name}.dp_date >= '$from_date' ";
			$clauses['groupby']  = "{$table_name}.postnumber";
			$clauses['orderby']  = "SUM({$table_name}.cntaccess) ";
			$clauses['orderby'] .= ( 'ASC' === strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
		}

		return $clauses;
	}

	/**
	 * Output CSS for width of new column.
	 *
	 * @since   1.2
	 */
	public static function admin_css() {
		?>
<style type="text/css">
	#tptn_total, #tptn_daily, #tptn_both { max-width: 100px; }
</style>
		<?php
	}
}
