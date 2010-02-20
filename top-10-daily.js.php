<?php
//"top-10-daily.js.php" Display Daily Popular Lists.
Header("content-type: application/x-javascript");

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

// Display counter using Ajax
function tptn_daily_lists() {
	global $wpdb, $siteurl, $tableposts, $id;
	$table_name = $wpdb->prefix . "top_ten_daily";
	
	$is_widget = intval($_GET['widget']);
	
	$tptn_settings = tptn_read_options();
	$limit = $tptn_settings['limit'];
	$daily_range = $tptn_settings[daily_range]-1;
	$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
	$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
	$current_date = date ( 'Y-m-j' , $current_date );
	
	$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
	$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
	if ($tptn_settings['exclude_pages']) $sql .= "AND post_type = 'post' ";
	$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
	$sql .= "GROUP BY postnumber ";
	$sql .= "ORDER BY sumCount DESC LIMIT $limit";

	$results = $wpdb->get_results($sql);
	
	$output = '<div id="tptn_related_daily">';
	if(!$is_widget) $output .= $tptn_settings['title_daily'];

	if ($results) {
		$output .= $tptn_settings['before_list'];
		foreach ($results as $result) {
			$title = trim(stripslashes(get_the_title($result->postnumber)));
			$output .= $tptn_settings['before_list_item'];

			if (($tptn_settings['post_thumb_op']=='inline')||($tptn_settings['post_thumb_op']=='thumbs_only')) {
				$output .= '<a href="'.get_permalink($result->postnumber).'" rel="bookmark">';
				if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail($result->postnumber))) {
					$output .= get_the_post_thumbnail( $result->postnumber, array($tptn_settings[thumb_width],$tptn_settings[thumb_height]), array('title' => $title,'alt' => $title, 'class' => 'tptn_thumb', 'border' => '0'));
				} else {
					$postimage = get_post_meta($result->postnumber, $tptn_settings[thumb_meta], true);	// Check 
					if ((!$postimage)&&($tptn_settings['scan_images'])) {
						preg_match_all( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $result->post_content, $matches );
						// any image there?
						if( isset( $matches ) && $matches[1][0] ) {
							$postimage = $matches[1][0]; // we need the first one only!
						}
					}
					if (!$postimage) $postimage = $tptn_settings[thumb_default];
					$output .= '<img src="'.$postimage.'" alt="'.$title.'" title="'.$title.'" width="'.$tptn_settings[thumb_width].'" height="'.$tptn_settings[thumb_height].'" border="0" class="tptn_thumb" />';
				}
				$output .= '</a> ';
			}
			if (($tptn_settings['post_thumb_op']=='inline')||($tptn_settings['post_thumb_op']=='text_only')) {
				$output .= '<a href="'.get_permalink($result->postnumber).'" rel="bookmark">'.$title.'</a>';
			}		
			if ($tptn_settings['show_excerpt']) {
				$output .= '<span class="tptn_excerpt"> '.tptn_excerpt($result->post_content,$tptn_settings['excerpt_length']).'</span>';
			}
			if ($tptn_settings['disp_list_count']) $output .= ' ('.$result->sumCount.')';
			$output .= $tptn_settings['after_list_item'];
		}
		if ($tptn_settings['show_credit']) $output .= $tptn_settings['before_list_item'].'Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a>'.$tptn_settings['after_list_item'];
		$output .= $tptn_settings['after_list'];
	}
	
	$output .= '</div>';

	echo "document.write('".$output."')";
}
tptn_daily_lists();
?>
