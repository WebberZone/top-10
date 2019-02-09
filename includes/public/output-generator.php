<?php
/**
 * Generates the output
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2009-2015 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Returns the link attributes.
 *
 * @since   2.2.0
 *
 * @param   array  $args Array of arguments.
 * @param   object $result Result object.
 * @return  string  Space separated list of link attributes
 */
function tptn_link_attributes( $args, $result ) {

	$rel_attribute = ( $args['link_nofollow'] ) ? ' rel="nofollow" ' : ' ';

	$target_attribute = ( $args['link_new_window'] ) ? ' target="_blank" ' : ' ';

	$link_attributes = array(
		'rel_attribute'    => $rel_attribute,
		'target_attribute' => $target_attribute,
	);

	/**
	 * Filter the title of the popular posts list
	 *
	 * @since   2.2.0
	 *
	 * @param   array   $link_attributes    Array of link attributes
	 * @param   array   $args   Array of arguments
	 */
	$link_attributes = apply_filters( 'tptn_link_attributes', $link_attributes, $args, $result );

	// Convert it to a string.
	$link_attributes = implode( ' ', $link_attributes );

	return $link_attributes;

}


/**
 * Returns the heading of the popular posts.
 *
 * @since   2.2.0
 *
 * @param   array $args   Array of arguments.
 * @return  string  Space separated list of link attributes
 */
function tptn_heading_title( $args ) {

	$title = '';

	if ( $args['heading'] && ! $args['is_widget'] ) {
		$title = $args['daily'] ? $args['title_daily'] : $args['title'];
	}

	/**
	 * Filter the title of the Top posts.
	 *
	 * @since   1.9.5
	 *
	 * @param   string  $title  Title/heading of the popular posts list
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_heading_title', $title, $args );
}


/**
 * Returns the opening tag of the popular posts list.
 *
 * @since   2.2.0
 *
 * @param   array $args   Array of arguments.
 * @return  string  Space separated list of link attributes
 */
function tptn_before_list( $args ) {

	$before_list = $args['before_list'];

	/**
	 * Filter the opening tag of the popular posts list
	 *
	 * @since   1.9.10.1
	 *
	 * @param   string  $before_list    Opening tag set in the Settings Page
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_before_list', $before_list, $args );

}


/**
 * Returns the closing tag of the popular posts list.
 *
 * @since   2.2.0
 *
 * @param   array $args   Array of arguments.
 * @return  string  Space separated list of link attributes
 */
function tptn_after_list( $args ) {

	$after_list = $args['after_list'];

	/**
	 * Filter the closing tag of the popular posts list
	 *
	 * @since   1.9.10.1
	 *
	 * @param   string  $after_list Closing tag set in the Settings Page
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_after_list', $after_list, $args );

}


/**
 * Returns the opening tag of each list item.
 *
 * @since   2.2.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string  Space separated list of link attributes
 */
function tptn_before_list_item( $args, $result ) {

	$before_list_item = $args['before_list_item'];

	/**
	 * Filter the opening tag of each list item.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   string  $before_list_item   Tag before each list item. Can be defined in the Settings page.
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_before_list_item', $before_list_item, $result, $args );

}


/**
 * Returns the closing tag of each list item.
 *
 * @since   2.2.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string  Space separated list of link attributes
 */
function tptn_after_list_item( $args, $result ) {

	$after_list_item = $args['after_list_item'];

	/**
	 * Filter the closing tag of each list item.
	 *
	 * @since   1.9.10.1
	 *
	 * @param   string  $after_list_item    Tag after each list item. Can be defined in the Settings page.
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_after_list_item', $after_list_item, $result, $args );   // Pass the post object to the filter.

}


/**
 * Returns the title of each list item.
 *
 * @since   2.2.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string  Space separated list of link attributes
 */
function tptn_post_title( $args, $result ) {

	$title = tptn_trim_char( get_the_title( $result->ID ), $args['title_length'] ); // Get the post title and crop it if needed.

	/**
	 * Filter the post title of each list item.
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $title  Title of the post.
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_post_title', $title, $result, $args );

}


/**
 * Returns the author of each list item.
 *
 * @since   2.2.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string  Space separated list of link attributes
 */
function tptn_author( $args, $result ) {

	$author_info = get_userdata( $result->post_author );
	$author_link = ( false === $author_info ) ? '' : get_author_posts_url( $author_info->ID );
	$author_name = ( false === $author_info ) ? '' : ucwords( trim( stripslashes( $author_info->display_name ) ) );

	/**
	 * Filter the author name.
	 *
	 * @since   1.9.1
	 *
	 * @param   string  $author_name    Proper name of the post author.
	 * @param   object  $author_info    WP_User object of the post author
	 */
	$author_name = apply_filters( 'tptn_author_name', $author_name, $author_info );

	if ( ! empty( $author_name ) ) {
		$tptn_author = '<span class="crp_author"> ' . __( ' by ', 'top-10' ) . '<a href="' . $author_link . '">' . $author_name . '</a></span> ';
	} else {
		$tptn_author = '';
	}

	/**
	 * Filter the text with the author details.
	 *
	 * @since   2.0.0
	 *
	 * @param   string  $tptn_author    Formatted string with author details and link
	 * @param   object  $author_info    WP_User object of the post author
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_author', $tptn_author, $author_info, $result, $args );

}


/**
 * Returns the formatted list item with link and and thumbnail for each list item.
 *
 * @since   2.2.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string  Space separated list of link attributes
 */
function tptn_list_link( $args, $result ) {

	$output          = '';
	$title           = tptn_post_title( $args, $result );
	$link_attributes = tptn_link_attributes( $args, $result );

	if ( 'after' === $args['post_thumb_op'] ) {
		$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.
		$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title if post thumbnail is to be displayed after.
		$output .= '</a>'; // Close the link.
	}

	if ( 'inline' === $args['post_thumb_op'] || 'after' === $args['post_thumb_op'] || 'thumbs_only' === $args['post_thumb_op'] ) {
		$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.

		$output .= tptn_get_the_post_thumbnail(
			array(
				'postid'             => $result,
				'thumb_height'       => $args['thumb_height'],
				'thumb_width'        => $args['thumb_width'],
				'thumb_meta'         => $args['thumb_meta'],
				'thumb_html'         => $args['thumb_html'],
				'thumb_default'      => $args['thumb_default'],
				'thumb_default_show' => $args['thumb_default_show'],
				'scan_images'        => $args['scan_images'],
				'class'              => 'tptn_thumb',
			)
		);

		$output .= '</a>'; // Close the link.
	}

	if ( 'inline' === $args['post_thumb_op'] || 'text_only' === $args['post_thumb_op'] ) {
		$output .= '<span class="tptn_after_thumb">';
		$output .= '<a href="' . get_permalink( $result->ID ) . '" ' . $link_attributes . ' class="tptn_link">'; // Add beginning of link.
		$output .= '<span class="tptn_title">' . $title . '</span>'; // Add title when required by settings.
		$output .= '</a>'; // Close the link.
	}

	/**
	 * Filter Formatted list item with link and and thumbnail.
	 *
	 * @since   2.2.0
	 *
	 * @param   string  $output Formatted list item with link and and thumbnail
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_list_link', $output, $result, $args );

}


/**
 * Returns the title of each list item.
 *
 * @since   2.6.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @return  string Formatted post date
 */
function tptn_date( $args, $result ) {

	$date = mysql2date( get_option( 'date_format', 'd/m/y' ), $result->post_date );

	/**
	 * Filter the post title of each list item.
	 *
	 * @since   2.6.0
	 *
	 * @param   string  $date   Title of the post.
	 * @param   object  $result Object of the current post result
	 * @param   array   $args   Array of arguments
	 */
	return apply_filters( 'tptn_date', $date, $result, $args );

}


/**
 * Returns the title of each list item.
 *
 * @since   2.6.0
 *
 * @param   array  $args   Array of arguments.
 * @param   object $result Object of the current post result.
 * @param   int    $visits Number of visits.
 * @return  string Formatted post date
 */
function tptn_list_count( $args, $result, $visits ) {

	$tptn_list_count = '(' . tptn_number_format_i18n( $visits ) . ')';

	/**
	 * Filter the formatted list count text.
	 *
	 * @since   2.1.0
	 *
	 * @param   string $visits Formatted list count
	 * @param   int    $sum_count       Post count
	 * @param   object $result          Post object
	 * @param   array  $args            Array of arguments.
	 * @param   int    $visits          Number of visits.
	 */
	return apply_filters( 'tptn_list_count', $tptn_list_count, $visits, $result, $args, $visits );

}


