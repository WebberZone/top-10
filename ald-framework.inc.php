<?php
/**********************************************************************
*					Framework file										*
*********************************************************************/
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
if (!defined('ALD_TPTN_DIR')) define('ALD_TPTN_DIR', dirname(__FILE__));
if (!defined('TPTN_LOCAL_NAME')) define('TPTN_LOCAL_NAME', 'tptn');

// Function to get the post thumbnail
if (!function_exists(ald_get_the_post_thumbnail)) { function ald_get_the_post_thumbnail($args = array()) {

	global $ald_url;
	$defaults = array(
		'postid' => '',
		'thumb_height' => '50',			// Max height of thumbnails
		'thumb_width' => '50',			// Max width of thumbnails
		'thumb_meta' => 'post-image',		// Meta field that is used to store the location of default thumbnail image
		'thumb_default' => '',	// Default thumbnail image
		'thumb_default_show' => true,	// Show default thumb if none found (if false, don't show thumb at all)
		'thumb_timthumb' => true,	// Use timthumb
		'scan_images' => false,			// Scan post for images
		'class' => 'ald_thumb',			// Class of the thumbnail
		'filter' => 'ald_postimage',			// Class of the thumbnail
	);
	
	// Parse incomming $args into an array and merge it with $defaults
	$args = wp_parse_args( $args, $defaults );
	
	// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
	extract( $args, EXTR_SKIP );

	$result = get_post($postid);

	$output = '';
	$title = get_the_title($postid);
	
	if (function_exists('has_post_thumbnail') && has_post_thumbnail($result->ID)) {
		$postimage = wp_get_attachment_image_src( get_post_thumbnail_id($result->ID) );
		$postimage = apply_filters( $filter, $postimage[0], $thumb_width, $thumb_height );
		$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;" border="0" class="'.$class.'" />';

//		$output .= get_the_post_thumbnail($result->ID, array($thumb_width,$thumb_height), array('title' => $title,'alt' => $title, 'class' => $class, 'border' => '0'));
	} else {
		$postimage = get_post_meta($result->ID, $thumb_meta, true);	// Check
		if (!$postimage && $scan_images) {
			preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $result->post_content, $matches );
			// any image there?
			if (isset($matches) && $matches[1][0]) {
				if (((strpos($matches[1][0], parse_url(get_option('home'),PHP_URL_HOST)) !== false) && (strpos($matches[1][0], 'http://') !== false))|| ((strpos($matches[1][0], 'http://') === false))) {
					$postimage = $matches[1][0]; // we need the first one only!
				}
			}
		}
		if (!$postimage) $postimage = get_post_meta($result->ID, '_video_thumbnail', true); // If no other thumbnail set, try to get the custom video thumbnail set by the Video Thumbnails plugin
		if ($thumb_default_show && !$postimage) $postimage = $thumb_default; // If no thumb found and settings permit, use default thumb
		if ($postimage) {
			if ($thumb_timthumb) {
				$output .= '<img src="'.$ald_url.'/timthumb/timthumb.php?src='.urlencode($postimage).'&amp;w='.$thumb_width.'&amp;h='.$thumb_height.'&amp;zc=1&amp;q=75" alt="'.$title.'" title="'.$title.'" style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;" border="0" class="'.$class.'" />';
			} else {
				$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" style="max-width:'.$thumb_width.'px;max-height:'.$thumb_height.'px;" border="0" class="'.$class.'" />';
			}
		}
	}
	
	return $output;
}}

// Function to create an excerpt for the post
if (!function_exists(ald_excerpt)) { function ald_excerpt($postid,$excerpt_length){
	$content = get_post($postid)->post_excerpt;
	if ($content=='') $content = get_post($postid)->post_content;
	$out = strip_tags($content);
	$blah = explode(' ',$out);
	if (!$excerpt_length) $excerpt_length = 10;
	if(count($blah) > $excerpt_length){
		$k = $excerpt_length;
		$use_dotdotdot = 1;
	}else{
		$k = count($blah);
		$use_dotdotdot = 0;
	}
	$excerpt = '';
	for($i=0; $i<$k; $i++){
		$excerpt .= $blah[$i].' ';
	}
	$excerpt .= ($use_dotdotdot) ? '...' : '';
	$out = $excerpt;
	return $out;
}}

// Function to save the global page ID. Used for the widget
// Code from: http://indrek.it/blog/wordpress-front-page-vs-home-page-and-getting-post-id-outside-or-after-the-loop-in-every-possible-way/
if (!function_exists(ald_save_page_ID)) { function ald_save_page_ID() {
    // Declare globals as before
    global $ald_page_id;
    global $post;
    $ald_page_id = $post->ID;
}
add_action('wp_head',  'ald_save_page_ID');
}





?>