<?php
/**
 * Deprecated functions from Top 10. You shouldn't
 * use these functions and look for the alternatives instead. The functions will be
 * removed in a later version.
 *
 * @package Top_Ten
 */


/**
 * Filter function to resize post thumbnail. Filters tptn_postimage.
 *
 * @since	1.9.2
 * @param	string		$postimage			Post Image URL
 * @param	string|int	$thumb_width		Thumbnail width
 * @param	string|int	$thumb_height		Thumbnail height
 * @param	string|int	$thumb_timthumb		Timthumb flag
 * @param	strint|int	$thumb_timthumb_q	Quality of the thumbnail
 * @return	string 		Post image output	Post image
 */
function tptn_scale_thumbs( $postimage, $thumb_width, $thumb_height, $thumb_timthumb, $thumb_timthumb_q, $post ) {
	global $tptn_url;

	if ( $thumb_timthumb ) {
		$new_pi = $tptn_url . '/timthumb/timthumb.php?src=' . urlencode( $postimage ) . '&amp;w=' . $thumb_width . '&amp;h=' . $thumb_height . '&amp;zc=1&amp;q=' . $thumb_timthumb_q;
	} else {
		$new_pi = $postimage;
	}
	return $new_pi;
}
add_filter( 'tptn_postimage', 'tptn_scale_thumbs', 10, 6 );


?>