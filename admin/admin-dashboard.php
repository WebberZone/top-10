<?php
/**
 * Top 10 Dashboard display.
 *
 * Functions to add the popular lists to the WordPress Admin Dashboard
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      http://ajaydsouza.com
 * @copyright 2008-2014 Ajay D'Souza
 */

/**** If this file is called directly, abort. ****/
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 *  Create the Dashboard Widget and content of the Popular pages
 *
 * @since	1.3
 *
 * @param	bool	$daily	Switch for Daily or Overall popular posts
 * @param	int		$page	Which page of the lists are we on?
 * @param	int		$limit 	Maximum number of posts per page
 * @param	bool	$widget	Is this a WordPress widget?
 * @return	Formatted list of popular posts
 */
function tptn_pop_display( $daily = FALSE, $page = 0, $limit = FALSE, $widget = FALSE ) {
	global $wpdb, $siteurl, $tableposts, $id, $tptn_settings;

	$table_name = $wpdb->base_prefix . "top_ten";
	if ( $daily ) $table_name .= "_daily";	// If we're viewing daily posts, set this to true

	if ( ! ( $limit ) ) $limit = $tptn_settings['limit'];
	if ( ! ( $page ) ) $page = 0; // Default page value.
	parse_str( $tptn_settings['post_types'], $post_types );	// Save post types in $post_types variable

	$results = tptn_pop_posts('posts_only=1&limit=99999&strict_limit=1&is_widget=1&exclude_post_ids=0&daily='.$daily);
	$numrows = count( $results );

	$pages = intval( $numrows/$limit ); // Number of results pages.

	// $pages now contains int of pages, unless there is a remainder from division.

	if ($numrows % $limit) { $pages++; } // has remainder so add one page

	$current = ( $page/$limit ) + 1; // Current page number.

	if ( ( $pages < 1 ) || ( 0 == $pages ) ) {
		$total = 1;	// If $pages is less than one or equal to 0, total pages is 1.
	} else {
		$total = $pages;	// Else total pages is $pages value.
	}

	$first = $page + 1; // The first result.

	if ( ! ( ( ( $page + $limit ) / $limit ) >= $pages ) && $pages != 1 ) {
		$last = $page + $limit;	//If not last results page, last result equals $page plus $limit.
	} else {
		$last = $numrows;	// If last results page, last result equals total number of results.
	}

	$results = array_slice( $results, $page, $limit );

	$output = '<div id="tptn_popular_posts">';
	$output .= '<table width="100%" border="0">
	 <tr>
	  <td width="50%" align="left">';
	$output .= sprintf( __( 'Results %1$s to %2$s of %3$s', TPTN_LOCAL_NAME ), '<strong>'.$first.'</strong>', '<strong>'.$last.'</strong>', '<strong>'.$numrows.'</strong>');
	$output .= '
	  </td>
	  <td width="50%" align="right">';
	$output .= sprintf( __( 'Page %s of %s', TPTN_LOCAL_NAME ), '<strong>'.$current.'</strong>', '<strong>'.$total.'</strong>' );
	$output .= '
	  </td>
	 </tr>
	 <tr>
	  <td colspan="2" align="right">&nbsp;</td>
	 </tr>
	 <tr>
	  <td align="left">';

	if ( ( $daily && $widget ) || ( ! $daily && ! $widget ) ) {
		$output .= '<a href="./admin.php?page=tptn_manage_daily">';
		$output .= __( 'View Daily Popular Posts', TPTN_LOCAL_NAME );
		$output .= '</a></td>';
		$output .= '<td align="right">';
		if ( ! $widget ) $output .= __( 'Results per-page:', TPTN_LOCAL_NAME );
		if ( ! $widget ) $output .= ' <a href="./admin.php?page=tptn_manage&limit=10">10</a> | <a href="./admin.php?page=tptn_manage&limit=20">20</a> | <a href="./admin.php?page=tptn_manage&limit=50">50</a> | <a href="./admin.php?page=tptn_manage&limit=100">100</a> ';
		$output .= ' 	  </td>
		 </tr>
		 <tr>
		  <td colspan="2" align="right"><hr /></td>
		 </tr>
		</table>';
	} else {
		$output .= '<a href="./admin.php?page=tptn_manage">';
		$output .= __( 'View Overall Popular Posts', TPTN_LOCAL_NAME );
		$output .= '</a></td>';
		$output .= '<td align="right">';
		if ( ! $widget ) $output .= __( 'Results per-page:', TPTN_LOCAL_NAME );
		if ( ! $widget ) $output .= ' <a href="./admin.php?page=tptn_manage_daily&limit=10">10</a> | <a href="./admin.php?page=tptn_manage_daily&limit=20">20</a> | <a href="./admin.php?page=tptn_manage_daily&limit=50">50</a> | <a href="./admin.php?page=tptn_manage_daily&limit=100">100</a> ';
		$output .= ' 	  </td>
		 </tr>
		 <tr>
		  <td colspan="2" align="right"><hr /></td>
		 </tr>
		</table>';
	}

	$dailytag = ( $daily ) ? '_daily' : '';

	$output .=   '<ul>';
	if ( $results ) {
		foreach ( $results as $result ) {
			$output .= '<li><a href="' . get_permalink( $result['postnumber'] ) . '">' . get_the_title( $result['postnumber'] ) . '</a>';
			$output .= ' (' . number_format_i18n( $result['sumCount'] ) . ')';
			$output .= '</li>';
		}
	}
	$output .=   '</ul>';

	$output .=   '<p align="center">';
	if ( 0 != $page ) { // Don't show back link if current page is first page.
		$back_page = $page - $limit;
		$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$back_page&daily=$daily&limit=$limit\">&laquo; ";
		$output .=  __( 'Previous', TPTN_LOCAL_NAME );
		$output .=  "</a>\n";
	}

	$pagination_range = 4;
	for ( $i=1; $i <= $pages; $i++ ) { // loop through each page and give link to it.
		if ( $i >= $current + $pagination_range && $i < $pages ) {
			if ( $i == $current + $pagination_range ) {
				$output .= '&hellip;&nbsp;';
			}
			continue;
		}
		if ( $i < $current - $pagination_range + 1 && $i < $pages ) {
			continue;
		}

		$ppage = $limit * ( $i - 1 );

		if ( $ppage == $page ) {
			$output .=  ("<span class='current'>$i</span>\n"); // If current page don't give link, just text.
		} else {
			$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$ppage&daily=$daily&limit=$limit\">$i</a> \n";
		}
	}

	if ( ! ( ( ( $page + $limit ) / $limit ) >= $pages ) && $pages != 1 ) { // If last page don't give next link.
		$next_page = $page + $limit;
		$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$next_page&daily=$daily&limit=$limit\">";
		$output .=  __( 'Next', TPTN_LOCAL_NAME );
		$output .=  " &raquo;</a>";
	}
	$output .= '</p>';
	$output .= '<p style="text-align:center;border-top: #000 1px solid">Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></p>';
	$output .= '</div>';

	return apply_filters( 'tptn_pop_display', $output );
}


/**
 * Widget for Popular Posts.
 *
 * @since	1.1
 */
function tptn_pop_dashboard() {
	echo tptn_pop_display( false, 0, 10, true );
}


/**
 * Widget for Daily Popular Posts.
 *
 * @since	1.2
 */
function tptn_pop_daily_dashboard() {
	echo tptn_pop_display( true, 0, 10, true );
}


/**
 * Function to add the widgets to the Dashboard.
 *
 * @since	1.1
 */
function tptn_pop_dashboard_setup() {
	global $tptn_settings;

	if ( ( current_user_can( 'manage_options' ) ) || ( $tptn_settings['show_count_non_admins'] ) ) {
		wp_add_dashboard_widget(
			'tptn_pop_dashboard',
			__( 'Popular Posts', TPTN_LOCAL_NAME ),
			'tptn_pop_dashboard'
		);
		wp_add_dashboard_widget(
			'tptn_pop_daily_dashboard',
			__( 'Daily Popular', TPTN_LOCAL_NAME ),
			'tptn_pop_daily_dashboard'
		);
	}
}
add_action( 'wp_dashboard_setup', 'tptn_pop_dashboard_setup' );


?>