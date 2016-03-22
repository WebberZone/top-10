<?php
/**
 * Deprecated functions and variables from Top 10. You shouldn't
 * use these functions or variables and look for the alternatives instead.
 * The functions will be removed in a later version.
 *
 * @package Top_Ten
 */


/**
 * Holds the filesystem directory path (with trailing slash) for Top 10
 *
 * @since	1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_path = plugin_dir_path( TOP_TEN_PLUGIN_FILE );


/**
 * Holds the URL for Top 10
 *
 * @since	1.5
 * @deprecated 2.3
 *
 * @var string
 */
$tptn_url = plugins_url() . '/' . plugin_basename( dirname( TOP_TEN_PLUGIN_FILE ) );


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


