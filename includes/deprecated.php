<?php
/**
 * Deprecated functions from Top 10. You shouldn't
 * use these functions and look for the alternatives instead. The functions will be
 * removed in a later version.
 *
 * @package Top_Ten
 */


/**
 * Filter to add related posts to feeds.
 *
 * @since	1.9.8
 * @deprecated	2.2.0
 *
 * @param	string $content    Post content
 * @return	string	Filtered post content
 */
function ald_tptn_rss( $content ) {

	_deprecated_function( __FUNCTION__, '2.2.0', 'tptn_rss_filter()' );

	return tptn_rss_filter( $content );
}


