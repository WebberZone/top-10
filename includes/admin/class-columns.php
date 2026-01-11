<?php
/**
 * Columns class.
 *
 * @package WebberZone\Top_Ten\Admin
 */

namespace WebberZone\Top_Ten\Admin;

use WebberZone\Top_Ten\Database;
use WebberZone\Top_Ten\Counter;
use WebberZone\Top_Ten\Util\Helpers;
use WebberZone\Top_Ten\Util\Hook_Registry;

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
		$this->init();
	}

	/**
	 * Initialize hooks and filters.
	 *
	 * @since 4.0.0
	 */
	private function init() {

		/**
		 * Filter the post types to add the columns to.
		 *
		 * @since 4.0.0
		 *
		 * @param array $post_types Array of post types.
		 */
		$post_types = apply_filters( 'tptn_admin_column_post_types', array( 'post', 'page' ) );

		foreach ( $post_types as $post_type ) {
			Hook_Registry::add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_columns' ) );
			Hook_Registry::add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'get_value' ), 10, 2 );
			Hook_Registry::add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'add_columns_register_sortable' ) );
		}

		Hook_Registry::add_filter( 'posts_clauses', array( $this, 'add_columns_clauses' ), 10, 2 );
		Hook_Registry::add_action( 'admin_head', array( $this, 'admin_css' ) );
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
	 * @since 4.0.0
	 *
	 * @param string     $column_name    Name of the column.
	 * @param int|string $id             Post ID.
	 */
	public static function get_value( $column_name, $id ) {
		if ( ! \tptn_get_option( 'pv_in_admin' ) ) {
			return;
		}

		global $wpdb;
		$blog_id = get_current_blog_id();

		$counts = array();

		if ( in_array( $column_name, array( 'tptn_total', 'tptn_both' ), true ) ) {
			$counts['total'] = self::get_total_count( $id, $blog_id );
		}

		if ( in_array( $column_name, array( 'tptn_daily', 'tptn_both' ), true ) ) {
			$counts['daily'] = self::get_daily_count( $id, $blog_id );
		}

		echo esc_html( self::format_output( $counts, $column_name ) );
	}

	/**
	 * Get total view count.
	 *
	 * @param int $id      Post ID.
	 * @param int $blog_id Blog ID.
	 * @return int
	 */
	private static function get_total_count( $id, $blog_id ) {
		return Counter::get_post_count_only( $id, 'total', $blog_id );
	}

	/**
	 * Get daily view count.
	 *
	 * @param int $id      Post ID.
	 * @param int $blog_id Blog ID.
	 * @return int
	 */
	private static function get_daily_count( $id, $blog_id ) {
		return Counter::get_post_count_only( $id, 'daily', $blog_id );
	}

	/**
	 * Format output based on column name.
	 *
	 * @param array  $counts      Array of count values.
	 * @param string $column_name Name of the column.
	 * @return string
	 */
	private static function format_output( $counts, $column_name ) {
		switch ( $column_name ) {
			case 'tptn_total':
				$output = Helpers::number_format_i18n( $counts['total'] );
				break;
			case 'tptn_daily':
				$output = Helpers::number_format_i18n( $counts['daily'] );
				break;
			case 'tptn_both':
				$output = Helpers::number_format_i18n( $counts['total'] ) . ' / ' . Helpers::number_format_i18n( $counts['daily'] );
				break;
			default:
				$output = '';
				break;
		}
		return $output;
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
	 * @since 3.3.0
	 *
	 * @param   array     $clauses    Lookup clauses.
	 * @param   \WP_Query $wp_query   WP Query object.
	 * @return  array   Filtered clauses
	 */
	public static function add_columns_clauses( $clauses, $wp_query ) {
		global $wpdb;

		$orderby = $wp_query->get( 'orderby' );
		if ( ! in_array( $orderby, array( 'tptn_total', 'tptn_daily' ), true ) ) {
			return $clauses;
		}

		$is_daily   = ( 'tptn_daily' === $orderby );
		$table_name = Database::get_table( $is_daily );

		$clauses['join'] .= " LEFT OUTER JOIN {$table_name} ON {$wpdb->posts}.ID = {$table_name}.postnumber";

		if ( $is_daily ) {
			$from_date          = Helpers::get_from_date();
			$clauses['where']  .= $wpdb->prepare( " AND {$table_name}.dp_date >= %s", $from_date ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$clauses['groupby'] = "{$table_name}.postnumber";
			$clauses['orderby'] = "SUM({$table_name}.cntaccess)";
		} else {
			$clauses['orderby'] = "{$table_name}.cntaccess";
		}

		$clauses['orderby'] .= ( 'ASC' === strtoupper( (string) $wp_query->get( 'order' ) ) ) ? ' ASC' : ' DESC';

		return apply_filters( 'tptn_posts_clauses', $clauses, $wp_query );
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
