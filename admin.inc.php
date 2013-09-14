<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");
if (!defined('ALD_TPTN_DIR')) define('ALD_TPTN_DIR', dirname(__FILE__));
if (!defined('TPTN_LOCAL_NAME')) define('TPTN_LOCAL_NAME', 'tptn');

/**
 * Plugin settings.
 * 
 * @access public
 * @return void
 */
function tptn_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$tptn_settings = tptn_read_options();
	parse_str($tptn_settings['post_types'],$post_types);
	$wp_post_types	= get_post_types( array(
		'public'	=> true,
	) );
	$posts_types_inc = array_intersect($wp_post_types, $post_types);


	if( (isset($_POST['tptn_save']))&&( check_admin_referer('tptn-plugin') ) ) {
		$tptn_settings['title'] = wp_kses_post($_POST['title']);
		$tptn_settings['title_daily'] = wp_kses_post($_POST['title_daily']);
		$tptn_settings['daily_range'] = intval($_POST['daily_range']);
		$tptn_settings['limit'] = intval($_POST['limit']);
		$tptn_settings['count_disp_form'] = ($_POST['count_disp_form']);
		$tptn_settings['exclude_on_post_ids'] = wp_kses_post($_POST['exclude_on_post_ids']);
		$tptn_settings['exclude_post_ids'] = wp_kses_post($_POST['exclude_post_ids']);

		$tptn_settings['add_to_content'] = (isset($_POST['add_to_content']) ? true : false);
		$tptn_settings['count_on_pages'] = (isset($_POST['count_on_pages']) ? true : false);
		$tptn_settings['add_to_feed'] = (isset($_POST['add_to_feed']) ? true : false);
		$tptn_settings['add_to_home'] = (isset($_POST['add_to_home']) ? true : false);
		$tptn_settings['add_to_category_archives'] = (isset($_POST['add_to_category_archives']) ? true : false);
		$tptn_settings['add_to_tag_archives'] = (isset($_POST['add_to_tag_archives']) ? true : false);
		$tptn_settings['add_to_archives'] = (isset($_POST['add_to_archives']) ? true : false);

		$tptn_settings['activate_overall'] = (isset($_POST['activate_overall']) ? true : false);
		$tptn_settings['activate_daily'] = (isset($_POST['activate_daily']) ? true : false);
		$tptn_settings['track_authors'] = (isset($_POST['track_authors']) ? true : false);
		$tptn_settings['track_admins'] = (isset($_POST['track_admins']) ? true : false);
		$tptn_settings['pv_in_admin'] = (isset($_POST['pv_in_admin']) ? true : false);
		$tptn_settings['disp_list_count'] = (isset($_POST['disp_list_count']) ? true : false);
		$tptn_settings['d_use_js'] = (isset($_POST['d_use_js']) ? true : false);
		$tptn_settings['dynamic_post_count'] = (isset($_POST['dynamic_post_count']) ? true : false);
		$tptn_settings['show_credit'] = (isset($_POST['show_credit']) ? true : false);
		$tptn_settings['blank_output'] = (($_POST['blank_output'] == 'blank' ) ? true : false);
		$tptn_settings['blank_output_text'] = wp_kses_post($_POST['blank_output_text']);

		$tptn_settings['post_thumb_op'] = $_POST['post_thumb_op'];
		$tptn_settings['before_list'] = $_POST['before_list'];
		$tptn_settings['after_list'] = $_POST['after_list'];
		$tptn_settings['before_list_item'] = $_POST['before_list_item'];
		$tptn_settings['after_list_item'] = $_POST['after_list_item'];
		$tptn_settings['thumb_meta'] = $_POST['thumb_meta'];
		$tptn_settings['thumb_default'] = $_POST['thumb_default'];
		$tptn_settings['thumb_html'] = $_POST['thumb_html'];
		$tptn_settings['thumb_height'] = intval($_POST['thumb_height']);
		$tptn_settings['thumb_width'] = intval($_POST['thumb_width']);
		$tptn_settings['thumb_default_show'] = (isset($_POST['thumb_default_show']) ? true : false);
		$tptn_settings['thumb_timthumb'] = (isset($_POST['thumb_timthumb']) ? true : false);
		$tptn_settings['scan_images'] = (isset($_POST['scan_images']) ? true : false);

		$tptn_settings['show_excerpt'] = (isset($_POST['show_excerpt']) ? true : false);
		$tptn_settings['excerpt_length'] = intval($_POST['excerpt_length']);
		$tptn_settings['title_length'] = intval($_POST['title_length']);
		$tptn_settings['show_date'] = (isset($_POST['show_date']) ? true : false);
		$tptn_settings['show_author'] = (isset($_POST['show_author']) ? true : false);
		$tptn_settings['custom_CSS'] = wp_kses_post($_POST['custom_CSS']);

		$tptn_settings['link_new_window'] = (isset($_POST['link_new_window']) ? true : false);
		$tptn_settings['link_nofollow'] = (isset($_POST['link_nofollow']) ? true : false);
		
		$tptn_settings['cache_fix'] = (isset($_POST['cache_fix']) ? true : false);

		// Exclude categories
		$tptn_settings['exclude_cat_slugs'] = ($_POST['exclude_cat_slugs']);

		$exclude_categories_slugs = explode(", ",$tptn_settings['exclude_cat_slugs']);

		//$exclude_categories = '';
		foreach ($exclude_categories_slugs as $exclude_categories_slug) {
			$catObj = get_category_by_slug($exclude_categories_slug);
			if (isset($catObj->term_id)) $exclude_categories[] = $catObj->term_id;
		}
		$tptn_settings['exclude_categories'] = (isset($exclude_categories)) ? join(',', $exclude_categories) : '';

		// Cron maintenance functions
		if ( ($_POST['cron_on']) && ( ($tptn_settings['cron_on']==false) || (intval($_POST['cron_hour'])!=$tptn_settings['cron_hour']) || (intval($_POST['cron_min'])!=$tptn_settings['cron_min']) || ($_POST['cron_recurrence']!=$tptn_settings['cron_recurrence']) ) ) {
			$tptn_settings['cron_on'] = true;
			$tptn_settings['cron_hour'] = intval($_POST['cron_hour']);
			$tptn_settings['cron_min'] = intval($_POST['cron_min']);
			$tptn_settings['cron_recurrence'] = $_POST['cron_recurrence'];
			
			tptn_enable_run($tptn_settings['cron_hour'], $tptn_settings['cron_min'], $tptn_settings['cron_recurrence']);
			echo '<div id="message" class="updated fade"><p>' . __('Scheduled maintenance enabled / modified',TPTN_LOCAL_NAME) .'</p></div>';
		} elseif ($_POST['cron_on']==false) {
			$tptn_settings['cron_on'] = false;
			tptn_disable_run();
			echo '<div id="message" class="updated fade"><p>'. __('Scheduled maintenance disabled',TPTN_LOCAL_NAME) .'</p></div>';
		}
			
		$wp_post_types	= get_post_types( array(
			'public'	=> true,
		) );
		$post_types_arr = (isset($_POST['post_types']) && is_array($_POST['post_types'])) ? $_POST['post_types'] : array('post' => 'post');
		$post_types = array_intersect($wp_post_types, $post_types_arr);
		$tptn_settings['post_types'] = http_build_query($post_types, '', '&');

		update_option('ald_tptn_settings', $tptn_settings);
		
		// Let's get the options again after we update them
		$tptn_settings = tptn_read_options();
		parse_str($tptn_settings['post_types'],$post_types);
		$posts_types_inc = array_intersect($wp_post_types, $post_types);

		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	
	if((isset($_POST['tptn_default']))&&( check_admin_referer('tptn-plugin') ) ) {
		delete_option('ald_tptn_settings');
		$tptn_settings = tptn_default_options();
		update_option('ald_tptn_settings', $tptn_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if((isset($_POST['tptn_trunc_all']))&&( check_admin_referer('tptn-plugin') )) {
		tptn_trunc_count(false);
		$str = '<div id="message" class="updated fade"><p>'. __('Top 10 popular posts reset',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if((isset($_POST['tptn_trunc_daily']))&&( check_admin_referer('tptn-plugin') )) {
		tptn_trunc_count(true);
		$str = '<div id="message" class="updated fade"><p>'. __('Top 10 daily popular posts reset',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}

	if((isset($_POST['tptn_clean_duplicates']))&&( check_admin_referer('tptn-plugin') ) ) {
		tptn_clean_duplicates(true);
		tptn_clean_duplicates(false);
		$str = '<div id="message" class="updated fade"><p>'. __('Duplicate rows cleaned from tables',TPTN_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
	<div id="page-wrap">
	<div id="inside">
		<div id="header">
		<h2>Top 10</h2>
		</div>
	  <div id="side">
		<?php tptn_admin_side(); ?>
	  </div>

	  <div id="options-div">
	  <form method="post" id="tptn_options" name="tptn_options" onsubmit="return checkForm()">
		<fieldset class="options">
		<div class="tabber">
		<div class="tabbertab">
		<h3>
		  <?php _e('General options',TPTN_LOCAL_NAME); ?>
		</h3>
		  <table class="form-table">
			<tr>
			  <th scope="row"><label for="activate_overall"><?php _e('Enable Overall stats',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="activate_overall" id="activate_overall" <?php if ($tptn_settings['activate_overall']) echo 'checked="checked"' ?> />
			  </td>
			</tr>
			<tr>
			  <th scope="row"><label for="activate_daily"><?php _e('Enable Daily stats',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="activate_daily" id="activate_daily" <?php if ($tptn_settings['activate_daily']) echo 'checked="checked"' ?> />
			  </td>
			</tr>
			<tr>
			  <th scope="row"><label for="cache_fix"><?php _e('W3 Total Cache fix:',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="cache_fix" id="cache_fix" <?php if ($tptn_settings['cache_fix']) echo 'checked="checked"' ?> />
			    <p class="description"><?php _e('This will try to prevent W3 Total Cache from caching the addcount script of the plugin. Try toggling this option in case you find that our posts are not tracked.',TPTN_LOCAL_NAME); ?></p>
			  </td>
			</tr>
			<tr>
			  <th scope="row"><label for="limit"><?php _e('Number of popular posts to display: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="limit" id="limit" value="<?php echo esc_attr(stripslashes($tptn_settings['limit'])); ?>"></td>
			</tr>
			<tr>
			  <th scope="row"><label for="daily_range"><?php _e('Daily Popular should contain views of how many days? ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes($tptn_settings['daily_range']); ?>"></td>
			</tr>
			<tr><th scope="row"><?php _e('Post types to include in results (including custom post types)',TPTN_LOCAL_NAME); ?></th>
				<td>
					<?php foreach ($wp_post_types as $wp_post_type) {
						$post_type_op = '<input type="checkbox" name="post_types[]" value="'.$wp_post_type.'" ';
						if (in_array($wp_post_type, $posts_types_inc)) $post_type_op .= ' checked="checked" ';
						$post_type_op .= ' />'.$wp_post_type.'&nbsp;&nbsp;';
						echo $post_type_op;
					}
					?>
				</td>
			</tr>
			<tr><th scope="row"><label for="exclude_post_ids"><?php _e('List of post or page IDs to exclude from the results: ',TPTN_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="exclude_post_ids" id="exclude_post_ids" value="<?php echo esc_attr(stripslashes($tptn_settings['exclude_post_ids'])); ?>"  style="width:250px"></td>
			</tr>
			<tr>
			  <th scope="row"><label for="exclude_cat_slugs"><?php _e('Exclude Categories: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td>
				<div style="position:relative;text-align:left">
					<table id="MYCUSTOMFLOATER" class="myCustomFloater" style="position:absolute;top:50px;left:0;background-color:#cecece;display:none;visibility:hidden">
					<tr><td><!--
							please see: http://chrisholland.blogspot.com/2004/09/geekstuff-css-display-inline-block.html
							to explain why i'm using a table here.
							You could replace the table/tr/td with a DIV, but you'd have to specify it's width and height
							-->
						<div class="myCustomFloaterContent">
						you should never be seeing this
						</div>
					</td></tr>
					</table>
					<textarea class="wickEnabled:MYCUSTOMFLOATER" cols="50" rows="3" wrap="virtual" name="exclude_cat_slugs"><?php echo (stripslashes($tptn_settings['exclude_cat_slugs'])); ?></textarea>
					<p class="description"><?php _e('Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options',TPTN_LOCAL_NAME); ?></p>
				</div>
			  </td>
			</tr>
			<tr>
			  <th scope="row"><?php _e('Display number of views on:',TPTN_LOCAL_NAME); ?></th>
			  <td>
				<label><input type="checkbox" name="add_to_content" id="add_to_content" <?php if ($tptn_settings['add_to_content']) echo 'checked="checked"' ?> /> <?php _e('Posts',TPTN_LOCAL_NAME); ?></label><br />
				<label><input type="checkbox" name="count_on_pages" id="count_on_pages" <?php if ($tptn_settings['count_on_pages']) echo 'checked="checked"' ?> /> <?php _e('Pages',TPTN_LOCAL_NAME); ?></label><br />
				<label><input type="checkbox" name="add_to_home" id="add_to_home" <?php if ($tptn_settings['add_to_home']) echo 'checked="checked"' ?> /> <?php _e('Home page',TPTN_LOCAL_NAME); ?></label></label><br />
				<label><input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ($tptn_settings['add_to_feed']) echo 'checked="checked"' ?> /> <?php _e('Feeds',TPTN_LOCAL_NAME); ?></label></label><br />
				<label><input type="checkbox" name="add_to_category_archives" id="add_to_category_archives" <?php if ($tptn_settings['add_to_category_archives']) echo 'checked="checked"' ?> /> <?php _e('Category archives',TPTN_LOCAL_NAME); ?></label><br />
				<label><input type="checkbox" name="add_to_tag_archives" id="add_to_tag_archives" <?php if ($tptn_settings['add_to_tag_archives']) echo 'checked="checked"' ?> /> <?php _e('Tag archives',TPTN_LOCAL_NAME); ?></label></label><br />
				<label><input type="checkbox" name="add_to_archives" id="add_to_archives" <?php if ($tptn_settings['add_to_archives']) echo 'checked="checked"' ?> /> <?php _e('Other archives',TPTN_LOCAL_NAME); ?></label></label>
				<p class="description"><?php _e('If you choose to disable this, please add <code>&lt;?php if(function_exists(\'echo_ald_tptn\')) echo_ald_tptn(); ?&gt;</code> to your template file where you want it displayed',TPTN_LOCAL_NAME); ?></p>
			  </td>
			</tr>
			<tr><th scope="row"><label for="dynamic_post_count"><?php _e('Always display latest post count',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="dynamic_post_count" id="dynamic_post_count" <?php if ($tptn_settings['dynamic_post_count']) echo 'checked="checked"' ?> />
			    <p class="description"><?php _e('This option uses JavaScript and will increase your page load time. Turn this off if you are not using caching plugins or are OK with displaying older cached counts. When you enable this option, the daily widget will not use the options set there, but options will need to be set on this screen.',TPTN_LOCAL_NAME); ?></p>
			  </td>
			</tr>
			<tr><th scope="row"><label for="d_use_js"><?php _e('Always display latest post count in the daily lists',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="d_use_js" id="d_use_js" <?php if ($tptn_settings['d_use_js']) echo 'checked="checked"' ?> />
			    <p class="description"><?php _e('This option uses JavaScript and will increase your page load time',TPTN_LOCAL_NAME); ?></p>
			  </td>
			</tr>
			<tr><th scope="row"><label for="track_authors"><?php _e('Track visits of authors on their own posts?',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="track_authors" id="track_authors" <?php if ($tptn_settings['track_authors']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="track_admins"><?php _e('Track visits of admins?',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="track_admins" id="track_admins" <?php if ($tptn_settings['track_admins']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="pv_in_admin"><?php _e('Display page views on Posts > All Posts in Admin',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="pv_in_admin" id="pv_in_admin" <?php if ($tptn_settings['pv_in_admin']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="show_credit"><?php _e('Link to Top 10 plugin page',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="show_credit" id="show_credit" <?php if ($tptn_settings['show_credit']) echo 'checked="checked"' ?> />
			    <p class="description"><?php _e('A link to the plugin is added as an extra list item to the list of popular posts',TPTN_LOCAL_NAME); ?></p>
			  </td>
			</tr>
		  </table>		
		</div>
		<div class="tabbertab">
		<h3>
		  <?php _e('Output Options',TPTN_LOCAL_NAME); ?>
		</h3>
		  <table class="form-table">
			<tr><th scope="row"><label for="title"><?php _e('Format to display the count in: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><textarea name="count_disp_form" id="count_disp_form" cols="50" rows="5"><?php echo htmlspecialchars(stripslashes($tptn_settings['count_disp_form'])); ?></textarea>
			    <p class="description"><?php _e('Use <code>%totalcount%</code> to display the total count, <code>%dailycount%</code> to display the daily count and <code>%overallcount%</code> to display the overall count across all posts on the blog. e.g. the default options displays <code>(Visited 123 times, 23 visits today)</code>',TPTN_LOCAL_NAME); ?>
			  </td>
			</tr>
			<tr><th scope="row"><label for="title"><?php _e('Title of popular posts: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="title" id="title" value="<?php echo esc_attr(stripslashes($tptn_settings['title'])); ?>"  style="width:250px" /></td>
			</tr>
			<tr><th scope="row"><label for="title_daily"><?php _e('Title of daily popular posts: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="title_daily" id="title_daily" value="<?php echo esc_attr(stripslashes($tptn_settings['title_daily'])); ?>"  style="width:250px" /></td>
			</tr>
			<tr><th scope="row"><label for="blank_output"><?php _e('When there are no posts, what should be shown?',TPTN_LOCAL_NAME); ?></label></th>
			  <td>
				<label>
				<input type="radio" name="blank_output" value="blank" id="blank_output_0" <?php if ($tptn_settings['blank_output']) echo 'checked="checked"' ?> />
				<?php _e('Blank Output',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="blank_output" value="customs" id="blank_output_1" <?php if (!$tptn_settings['blank_output']) echo 'checked="checked"' ?> />
				<?php _e('Display:',TPTN_LOCAL_NAME); ?></label>
				<input type="textbox" name="blank_output_text" id="blank_output_text" value="<?php echo esc_attr(stripslashes($tptn_settings['blank_output_text'])); ?>"  style="width:250px" />
			  </td>
			</tr>
			<tr><th scope="row"><label for="show_excerpt"><?php _e('Show post excerpt in list?',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ($tptn_settings['show_excerpt']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="excerpt_length"><?php _e('Length of excerpt (in words): ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes($tptn_settings['excerpt_length']); ?>" /></td>
			</tr>
			<tr><th scope="row"><label for="show_author"><?php _e('Show post author in list?',TPTN_LOCAL_NAME); ?></label></th>
				<td><input type="checkbox" name="show_author" id="show_author" <?php if ($tptn_settings['show_author']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="show_date"><?php _e('Show post date in list?',TPTN_LOCAL_NAME); ?></label></th>
				<td><input type="checkbox" name="show_date" id="show_date" <?php if ($tptn_settings['show_date']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="title_length"><?php _e('Limit post title length (in characters)',TPTN_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="title_length" id="title_length" value="<?php echo stripslashes($tptn_settings['title_length']); ?>" /></td>
			</tr>
			<tr><th scope="row"><label for="disp_list_count"><?php _e('Display number of page views in popular lists?',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="disp_list_count" id="disp_list_count" <?php if ($tptn_settings['disp_list_count']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="link_new_window	"><?php _e('Open links in new window',TPTN_LOCAL_NAME); ?></label></th>
			<td><input type="checkbox" name="link_new_window" id="link_new_window" <?php if ($tptn_settings['link_new_window']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="link_nofollow"><?php _e('Add nofollow attribute to links in the list',TPTN_LOCAL_NAME); ?></label></th>
			<td><input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ($tptn_settings['link_nofollow']) echo 'checked="checked"' ?> /></td>
			</tr>
			<tr><th scope="row"><label for="exclude_on_post_ids"><?php _e('Exclude display of related posts on these posts / pages',TPTN_LOCAL_NAME); ?></label></th>
			<td>
				<input type="textbox" name="exclude_on_post_ids" id="exclude_on_post_ids" value="<?php echo esc_attr(stripslashes($tptn_settings['exclude_on_post_ids'])); ?>"  style="width:250px">
				<p class="description"><?php _e('Enter comma separated list of IDs. e.g. 188,320,500',TPTN_LOCAL_NAME); ?></p>
			</td>
			</tr>
			<tr style="vertical-align: top; background: #eee"><th scope="row" colspan="2"><?php _e('Customize the output:',TPTN_LOCAL_NAME); ?></th>
			</tr>
			<tr><th scope="row"><label for="before_list"><?php _e('HTML to display before the list of posts: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="before_list" id="before_list" value="<?php echo esc_attr(stripslashes($tptn_settings['before_list'])); ?>" style="width:250px" /></td>
			</tr>
			<tr><th scope="row"><label for="before_list_item"><?php _e('HTML to display before each list item: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo esc_attr(stripslashes($tptn_settings['before_list_item'])); ?>" style="width:250px" /></td>
			</tr>
			<tr><th scope="row"><label for="after_list_item"><?php _e('HTML to display after each list item: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo esc_attr(stripslashes($tptn_settings['after_list_item'])); ?>" style="width:250px" /></td>
			</tr>
			<tr><th scope="row"><label for="after_list"><?php _e('HTML to display after the list of posts: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="after_list" id="after_list" value="<?php echo esc_attr(stripslashes($tptn_settings['after_list'])); ?>" style="width:250px" /></td>
			</tr>
			<tr style="vertical-align: top; background: #eee"><th scope="row" colspan="2"><?php _e('Post thumbnail options:',TPTN_LOCAL_NAME); ?></th>
			</tr>
			<tr><th scope="row"><label for="post_thumb_op"><?php _e('Location of post thumbnail:',TPTN_LOCAL_NAME); ?></label></th>
			  <td>
				<label>
				<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ($tptn_settings['post_thumb_op']=='inline') echo 'checked="checked"' ?> />
				<?php _e('Display thumbnails inline with posts, before title',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op" value="after" id="post_thumb_op_1" <?php if ($tptn_settings['post_thumb_op']=='after') echo 'checked="checked"' ?> />
				<?php _e('Display thumbnails inline with posts, after title',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_2" <?php if ($tptn_settings['post_thumb_op']=='thumbs_only') echo 'checked="checked"' ?> />
				<?php _e('Display only thumbnails, no text',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_3" <?php if ($tptn_settings['post_thumb_op']=='text_only') echo 'checked="checked"' ?> />
				<?php _e('Do not display thumbnails, only text.',TPTN_LOCAL_NAME); ?></label>
				<br />
			  </td>
			</tr>
			<tr><th scope="row"><label for="thumb_width"><?php _e('Width of the thumbnail: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo esc_attr(stripslashes($tptn_settings['thumb_width'])); ?>" style="width:30px" />px</td>
			</tr>
			<tr><th scope="row"><label for="thumb_height"><?php _e('Height of the thumbnail: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo esc_attr(stripslashes($tptn_settings['thumb_height'])); ?>" style="width:30px" />px</td>
			</tr>
			<tr><th scope="row"><label for="thumb_html"><?php _e('Style attributes / Width and Height HTML attributes:',TPTN_LOCAL_NAME); ?></label></th>
			  <td>
				<label>
				<input type="radio" name="thumb_html" value="css" id="thumb_html_0" <?php if ($tptn_settings['thumb_html']=='css') echo 'checked="checked"' ?> />
				<?php _e('Style attributes are used for width and height. <code>style="max-width:'.$tptn_settings['thumb_width'].'px;max-height:'.$tptn_settings['thumb_height'].'px;"</code>',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="thumb_html" value="html" id="thumb_html_1" <?php if ($tptn_settings['thumb_html']=='html') echo 'checked="checked"' ?> />
				<?php _e('HTML width and height attributes are used for width and height. <code>width="'.$tptn_settings['thumb_width'].'" height="'.$tptn_settings['thumb_height'].'"</code>',TPTN_LOCAL_NAME); ?></label>
				<br />
			  </td>
			</tr>
			<tr><th scope="row"><label for="thumb_timthumb"><?php _e('Use timthumb to generate thumbnails? ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="thumb_timthumb" id="thumb_timthumb" <?php if ($tptn_settings['thumb_timthumb']) echo 'checked="checked"' ?> /> <br /><?php _e('If checked, <a href="http://www.binarymoon.co.uk/projects/timthumb/">timthumb</a> will be used to generate thumbnails',TPTN_LOCAL_NAME); ?></td>
			</tr>
			<tr><th scope="row"><label for="thumb_meta"><?php _e('Post thumbnail meta field name: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo esc_attr(stripslashes($tptn_settings['thumb_meta'])); ?>"> <br /><?php _e('The value of this field should contain the image source and is set in the <em>Add New Post</em> screen',TPTN_LOCAL_NAME); ?></td>
			</tr>
			<tr><th scope="row"><label for="scan_images"><?php _e('If the postmeta is not set, then should the plugin extract the first image from the post?',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="scan_images" id="scan_images" <?php if ($tptn_settings['scan_images']) echo 'checked="checked"' ?> /> <br /><?php _e('This could slow down the loading of your page if the first image in the related posts is large in file-size',TPTN_LOCAL_NAME); ?></td>
			</tr>
			<tr><th scope="row"><label for="thumb_default_show"><?php _e('Use default thumbnail? ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="thumb_default_show" id="thumb_default_show" <?php if ($tptn_settings['thumb_default_show']) echo 'checked="checked"' ?> /> <br /><?php _e('If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.',TPTN_LOCAL_NAME); ?></td>
			</tr>
			<tr><th scope="row"><label for="thumb_default"><?php _e('Default thumbnail: ',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo esc_attr(stripslashes($tptn_settings['thumb_default'])); ?>" style="width:500px"> <br /><?php _e('The plugin will first check if the post contains a thumbnail. If it doesn\'t then it will check the meta field. If this is not available, then it will show the default image as specified above',TPTN_LOCAL_NAME); ?></td>
			</tr>
			</table>
		</div>
		<div class="tabbertab">
		<h3>
		  <?php _e('Custom Styles',TPTN_LOCAL_NAME); ?>
		</h3>
		  <table class="form-table">
			<tr style="vertical-align: top; "><th scope="row" colspan="2"><?php _e('Custom CSS to add to header:',TPTN_LOCAL_NAME); ?></th>
			</tr>
			<tr style="vertical-align: top; "><td scope="row" colspan="2"><textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80"><?php echo stripslashes($tptn_settings['custom_CSS']); ?></textarea>
			   <p class="description"><em><?php _e('Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/top-10/faq/" target="_blank">FAQ</a> for available CSS classes to style.',TPTN_LOCAL_NAME); ?></em></p>
			  </td>
			</tr>
		  </table>		
		</div>
		<div class="tabbertab">
		<h3>
		  <?php _e('Maintenance',TPTN_LOCAL_NAME); ?>
		</h3>
		  <table class="form-table">
			<tr style="vertical-align: top; "><th scope="row" colspan="2">
			    <?php _e('Over time the Daily Top 10 database grows in size, which reduces the performance of the plugin. Cleaning the database at regular intervals could improve performance, especially on high traffic blogs.',TPTN_LOCAL_NAME); ?>
			    <em><?php _e('Note: When scheduled maintenance is enabled, WordPress will run the cron job everytime the job is rescheduled (i.e. you change the settings below). This causes the daily posts table to reset.',TPTN_LOCAL_NAME); ?></em>
			  </th>
			</tr>
			<tr><th scope="row"><label for="cron_on"><?php _e('Enable scheduled maintenance of daily tables:',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="checkbox" name="cron_on" id="cron_on" <?php if ($tptn_settings['cron_on']) echo 'checked="checked"' ?> />
			</td>
			</tr>
			<tr><th scope="row"><label for="cron_hour"><?php _e('Time to run maintenance',TPTN_LOCAL_NAME); ?></label></th>
			  <td><input type="textbox" name="cron_hour" id="cron_hour" value="<?php echo esc_attr(stripslashes($tptn_settings['cron_hour'])); ?>" style="width:10px" /> : <input type="textbox" name="cron_min" id="cron_min" value="<?php echo esc_attr(stripslashes($tptn_settings['cron_min'])); ?>" style="width:10px" /> hrs</td>
			</tr>
			<tr><th scope="row"><label for="cron_recurrence"><?php _e('How often should the maintenance be run:',TPTN_LOCAL_NAME); ?></label></th>
			  <td>
				<label>
				<input type="radio" name="cron_recurrence" value="daily" id="cron_recurrence0" <?php if ($tptn_settings['cron_recurrence']=='daily') echo 'checked="checked"' ?> />
				<?php _e('Daily',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="cron_recurrence" value="weekly" id="cron_recurrence1" <?php if ($tptn_settings['cron_recurrence']=='weekly') echo 'checked="checked"' ?> />
				<?php _e('Weekly',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="cron_recurrence" value="fortnightly" id="cron_recurrence2" <?php if ($tptn_settings['cron_recurrence']=='fortnightly') echo 'checked="checked"' ?> />
				<?php _e('Fortnightly',TPTN_LOCAL_NAME); ?></label>
				<br />
				<label>
				<input type="radio" name="cron_recurrence" value="monthly" id="cron_recurrence3" <?php if ($tptn_settings['cron_recurrence']=='monthly') echo 'checked="checked"' ?> />
				<?php _e('Monthly',TPTN_LOCAL_NAME); ?></label>
				<br />
			  </td>
			</tr>
			<tr><td scope="row" colspan="2">
				<?php 
				if ($tptn_settings['cron_on']) {
					if (wp_next_scheduled('ald_tptn_hook')) {
						echo '<span style="color:#0c0">';
						_e('The cron job has been scheduled. Maintenance will run ',TPTN_LOCAL_NAME);
						echo wp_get_schedule('ald_tptn_hook');
						echo '</span>';
					} else {
						echo '<span style="color:#e00">';
						_e('The cron job is missing. Please resave this page to add the job',TPTN_LOCAL_NAME);
						echo '</span>';
					}
				} else {
						echo '<span style="color:#FFA500">';
						_e('Maintenance is turned off',TPTN_LOCAL_NAME);
						echo '</span>';
				}
				?>				
			</td></tr>
			</table>
		</div>
		<p>
		  <input type="submit" name="tptn_save" id="tptn_save" value="<?php _e('Save Options',TPTN_LOCAL_NAME); ?>" class="button button-primary" />
		  <input type="submit" name="tptn_default" id="tptn_default" value="<?php _e('Default Options',TPTN_LOCAL_NAME); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e('Do you want to set options to Default?',TPTN_LOCAL_NAME); ?>')) return false;" />
		</p>
		</fieldset>
	    <h3>
	      <?php _e('Reset count',TPTN_LOCAL_NAME); ?>
	    </h3>
	    <p>
	      <?php _e('This cannot be reversed. Make sure that your database has been backed up before proceeding',TPTN_LOCAL_NAME); ?>
	    </p>
	    <p>
	      <input name="tptn_trunc_all" type="submit" id="tptn_trunc_all" value="<?php _e('Reset Popular Posts',TPTN_LOCAL_NAME); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e('Are you sure you want to reset the popular posts?',TPTN_LOCAL_NAME); ?>')) return false;" />
	      <input name="tptn_trunc_daily" type="submit" id="tptn_trunc_daily" value="<?php _e('Reset Daily Popular Posts',TPTN_LOCAL_NAME); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e('Are you sure you want to reset the daily popular posts?',TPTN_LOCAL_NAME); ?>')) return false;" />
	      <input name="tptn_clean_duplicates" type="submit" id="tptn_clean_duplicates" value="<?php _e('Clear duplicates',TPTN_LOCAL_NAME); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e('This will delete the duplicate entries in the tables. Proceed?',TPTN_LOCAL_NAME); ?>')) return false;" />
	    </p>
		<?php wp_nonce_field('tptn-plugin'); ?>
	  </form>
	</div>

	  </div>
	  <div style="clear: both;"></div>
	</div>
</div>
<?php

}

/**
 * Function to generate the top 10 daily popular posts page.
 * 
 * @access public
 * @return void
 */
function tptn_manage_daily() {
	tptn_manage(1);
}

/**
 * Function to generate the top 10 daily popular posts page.
 * 
 * @access public
 * @param int $daily (default: 0) Overall popular
 * @return void
 */
function tptn_manage($daily = 0) {

	$paged = (isset($_GET['paged']) ? intval($_GET['paged']) : 0);
	$limit = (isset($_GET['limit']) ? intval($_GET['limit']) : 0);
	$daily = (isset($_GET['daily']) ? intval($_GET['daily']) : $daily);

?>

<div class="wrap">
	<div id="page-wrap">
	<div id="inside">
		<div id="header">
		<h2><?php if (!$daily) _e('Popular Posts',TPTN_LOCAL_NAME); else _e('Daily Popular Posts',TPTN_LOCAL_NAME); ?></h2>
		</div>
	  <div id="side">
		<?php tptn_admin_side(); ?>
	  </div>

	  <div id="options-div">
		  <?php echo tptn_pop_display($daily,$paged,$limit,false); ?>
	  </div> <!-- End options-div -->

	  </div> <!-- End inside -->
	  <div style="clear: both;"></div>
	</div> <!-- End page-wrap -->
</div> <!-- End wrap -->


<?php
}

/**
 * Function to generate the right sidebar of the Settings and Admin popular posts pages.
 * 
 * @access public
 * @return void
 */
function tptn_admin_side() {
?>
		<div class="side-widget">
			<span class="title"><?php _e('Support the development',TPTN_LOCAL_NAME) ?></span>
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="donate@ajaydsouza.com">
				<input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="Donation for Top 10">
				<input type="hidden" name="item_number" value="tptn">
				<strong><?php _e('Enter amount in USD: ',TPTN_LOCAL_NAME) ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="button_subtype" value="services">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e('Send your donation to the author of',TPTN_LOCAL_NAME) ?> Top 10" title="<?php _e('Send your donation to the author of',TPTN_LOCAL_NAME) ?> Top 10">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>
		<div class="side-widget">
		<span class="title"><?php _e('Follow us',TPTN_LOCAL_NAME) ?></span>				
		<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
		<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
		<div class="side-widget">
		<span class="title"><?php _e('Quick Links',TPTN_LOCAL_NAME) ?></span>				
		<ul>
			<li><a href="http://ajaydsouza.com/wordpress/plugins/top-10/"><?php _e('Top 10 plugin page',TPTN_LOCAL_NAME) ?></a></li>
			<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e('Other plugins',TPTN_LOCAL_NAME) ?></a></li>
			<li><a href="http://ajaydsouza.com/"><?php _e('Ajay\'s blog',TPTN_LOCAL_NAME) ?></a></li>
			<li><a href="http://wordpress.org/support/plugin/top-10"><?php _e('Support',TPTN_LOCAL_NAME) ?></a></li>
		</ul>
		</div>
		<div class="side-widget">
		<span class="title"><?php _e('Recent developments',TPTN_LOCAL_NAME) ?></span>				
		<?php require_once(ABSPATH . WPINC . '/class-simplepie.php'); wp_widget_rss_output('http://ajaydsouza.com/archives/category/wordpress/plugins/feed/', array('items' => 5, 'show_author' => 0, 'show_date' => 1));
		?>
		</div>
<?php
}

/**
 * Add Top 10 menu in WP-Admin.
 * 
 * @access public
 * @return void
 */
function tptn_adminmenu() {

	if (function_exists('add_menu_page')) {

		$plugin_page = add_menu_page(__("Top 10 Settings", TPTN_LOCAL_NAME), __("Top 10", TPTN_LOCAL_NAME), 'manage_options', 'tptn_options', 'tptn_options');
		add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

		$plugin_page = add_submenu_page( 'tptn_options', __("Top 10 Settings", TPTN_LOCAL_NAME), __("Top 10 Settings", TPTN_LOCAL_NAME), 'manage_options', 'tptn_options', 'tptn_options');
		add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

		$plugin_page = add_submenu_page( 'tptn_options', __("Overall Popular Posts", TPTN_LOCAL_NAME), __("Overall Popular Posts", TPTN_LOCAL_NAME), 'manage_options', 'tptn_manage', 'tptn_manage');
		add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

		$plugin_page = add_submenu_page( 'tptn_options', __("Daily Popular Posts", TPTN_LOCAL_NAME), __("Daily Popular Posts", TPTN_LOCAL_NAME), 'manage_options', 'tptn_manage_daily', 'tptn_manage_daily');
		add_action( 'admin_head-'. $plugin_page, 'tptn_adminhead' );

	}
}
add_action('admin_menu', 'tptn_adminmenu');

/**
 * Add JS and CSS to admin header.
 * 
 * @access public
 * @return void
 */
function tptn_adminhead() {
	global $tptn_url;

?>
<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/wick/wick.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/admin-styles.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $tptn_url ?>/tabber/tabber.css" />
<script type="text/javascript" language="JavaScript">
function checkForm() {
answer = true;
if (siw && siw.selectingSomething)
	answer = false;
return answer;
}//
</script>
<script type="text/javascript" src="<?php echo $tptn_url ?>/wick/sample_data.js.php"></script>
<script type="text/javascript" src="<?php echo $tptn_url ?>/wick/wick.js"></script>
<script type="text/javascript" src="<?php echo $tptn_url ?>/tabber/tabber.js"></script>
<?php }


/**
 * Function to delete all rows in the posts table.
 * 
 * @access public
 * @param bool $daily (default: false)
 * @return void
 */
function tptn_clean_duplicates($daily = false) {
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";
	$count = 0;

	$wpdb->query("CREATE TEMPORARY TABLE ".$table_name."_temp AS SELECT * FROM ".$table_name." GROUP BY postnumber");
	$wpdb->query("TRUNCATE TABLE $table_name");
	$wpdb->query("INSERT INTO ".$table_name." SELECT * FROM ".$table_name."_temp");
}


/**
 *  Create the Dashboard Widget and content of the Popular pages
 * 
 * @access public
 * @param bool $daily (default: false) Switch for Daily or Overall popular posts
 * @param int $page (default: 0) Which page of the lists are we on?
 * @param int $limit (default: 10) Maximum number of posts per page
 * @param bool $widget (default: false) Is this a WordPress widget?
 * @return void
 */
function tptn_pop_display($daily = false, $page = 0, $limit = 10, $widget = false) {
	global $wpdb, $siteurl, $tableposts, $id;

	$table_name = $wpdb->prefix . "top_ten";
	if ($daily) $table_name .= "_daily";	// If we're viewing daily posts, set this to true
	
	$tptn_settings = tptn_read_options();
	if (!($limit)) $limit = $tptn_settings['limit'];
	if (!($page)) $page = 0; // Default page value.
	parse_str($tptn_settings['post_types'],$post_types);	// Save post types in $post_types variable


	if(!$daily) {
		$sql = "SELECT postnumber, cntaccess as sumCount, ID, post_type ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "ORDER BY sumCount DESC";
	} else {
		$daily_range = $tptn_settings['daily_range']-1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC";
	}

	$results = $wpdb->get_results($sql);
	$numrows = 0;
	if ($results) {
		foreach ($results as $result) {
			$numrows++;
		}
	}
	
	$pages = intval($numrows/$limit); // Number of results pages.

	// $pages now contains int of pages, unless there is a remainder from division.

	if ($numrows % $limit) {$pages++;} // has remainder so add one page

	$current = ($page/$limit) + 1; // Current page number.

	if (($pages < 1) || ($pages == 0)) {$total = 1;} // If $pages is less than one or equal to 0, total pages is 1.
	else {	$total = $pages;} // Else total pages is $pages value.

	$first = $page + 1; // The first result.

	if (!((($page + $limit) / $limit) >= $pages) && $pages != 1) {$last = $page + $limit;} //If not last results page, last result equals $page plus $limit.
	else{$last = $numrows;} // If last results page, last result equals total number of results.
	
	if(!$daily) {
		$sql = "SELECT postnumber, cntaccess as sumCount, ID, post_type ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "ORDER BY sumCount DESC LIMIT $page, $limit";
	} else {
		$sql = "SELECT postnumber, SUM(cntaccess) as sumCount, dp_date, ID, post_type, post_status ";
		$sql .= "FROM $table_name INNER JOIN ". $wpdb->posts ." ON postnumber=ID " ;
		$sql .= "AND post_status = 'publish' AND dp_date >= '$current_date' ";
		$sql .= "AND ( ";
		$multiple = false;
		foreach ($post_types as $post_type) {
			if ( $multiple ) $sql .= ' OR ';
			$sql .= " post_type = '".$post_type."' ";
			$multiple = true;
		}
		$sql .=" ) ";
		$sql .= "GROUP BY postnumber ";
		$sql .= "ORDER BY sumCount DESC LIMIT $page, $limit";
	}

	$results = $wpdb->get_results($sql);

	$output = '<div id="tptn_popular_posts">';
	$output .= '<table width="100%" border="0">
	 <tr>
	  <td width="50%" align="left">';
	$output .= __('Results',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$first.'</strong> - <strong>'.$last.'</strong> ';
	$output .= __('of',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$numrows.'</strong>
	  </td>
	  <td width="50%" align="right">';
	$output .= __('Page',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$current.'</strong> ';
	$output .= __('of',TPTN_LOCAL_NAME);
	$output .= ' <strong>'.$total.'</strong>
	  </td>
	 </tr>
	 <tr>
	  <td colspan="2" align="right">&nbsp;</td>
	 </tr>
	 <tr>
	  <td align="left">';
	
	if(($daily && $widget) || (!$daily && !$widget)) {
		$output .= '<a href="./admin.php?page=tptn_manage_daily">';
		$output .= __('View Daily Popular Posts',TPTN_LOCAL_NAME);
		$output .= '</a></td>';
		$output .= '<td align="right">';
		if (!$widget) $output .= __('Results per-page:',TPTN_LOCAL_NAME);
		if (!$widget) $output .= ' <a href="./admin.php?page=tptn_manage&limit=10">10</a> | <a href="./admin.php?page=tptn_manage&limit=20">20</a> | <a href="./admin.php?page=tptn_manage&limit=50">50</a> | <a href="./admin.php?page=tptn_manage&limit=100">100</a> ';
		$output .= ' 	  </td>
		 </tr>
		 <tr>
		  <td colspan="2" align="right"><hr /></td>
		 </tr>
		</table>';
	} else {
		$output .= '<a href="./admin.php?page=tptn_manage">';
		$output .= __('View Overall Popular Posts',TPTN_LOCAL_NAME);
		$output .= '</a></td>';
		$output .= '<td align="right">';
		if (!$widget) $output .= __('Results per-page:',TPTN_LOCAL_NAME);
		if (!$widget) $output .= ' <a href="./admin.php?page=tptn_manage_daily&limit=10">10</a> | <a href="./admin.php?page=tptn_manage_daily&limit=20">20</a> | <a href="./admin.php?page=tptn_manage_daily&limit=50">50</a> | <a href="./admin.php?page=tptn_manage_daily&limit=100">100</a> ';
		$output .= ' 	  </td>
		 </tr>
		 <tr>
		  <td colspan="2" align="right"><hr /></td>
		 </tr>
		</table>';
	}

	$dailytag = ($daily) ? '_daily' : '';
	
	$output .=   '<ul>';
	if ($results) {
		foreach ($results as $result) {
			$output .= '<li><a href="'.get_permalink($result->postnumber).'">'.get_the_title($result->postnumber).'</a>';
			$output .= ' ('.number_format_i18n($result->sumCount).')';
			$output .= '</li>';
		}
	}
	$output .=   '</ul>';
	
	$output .=   '<p align="center">';
	if ($page != 0) { // Don't show back link if current page is first page.
		$back_page = $page - $limit;
		$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$back_page&daily=$daily&limit=$limit\">&laquo; ";
		$output .=  __('Previous',TPTN_LOCAL_NAME);
		$output .=  "</a>\n";
	}

	for ($i=1; $i <= $pages; $i++) // loop through each page and give link to it.
	{
		$ppage = $limit*($i - 1);
		if ($ppage == $page){
			$output .=  "<b>$i</b>\n";
		} // If current page don't give link, just text.
		else{
			$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$ppage&daily=$daily&limit=$limit\">$i</a> \n";
		}
	}

	if (!((($page+$limit) / $limit) >= $pages) && $pages != 1) { // If last page don't give next link.
		$next_page = $page + $limit;
		$output .=  "<a href=\"./admin.php?page=tptn_manage$dailytag&paged=$next_page&daily=$daily&limit=$limit\">";
		$output .=  __('Next',TPTN_LOCAL_NAME);
		$output .=  " &raquo;</a>";
	}
	$output .=   '</p>';
	$output .=   '<p style="text-align:center;border-top: #000 1px solid">Popular posts by <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10 plugin</a></p>';
	$output .= '</div>';

	return $output;

}
 

/**
 * Widget for Popular Posts.
 * 
 * @access public
 * @return void
 */
function tptn_pop_dashboard() {
	echo tptn_pop_display(false,0,10,true);
}

/**
 * Widget for Daily Popular Posts.
 * 
 * @access public
 * @return void
 */
function tptn_pop_daily_dashboard() {
	echo tptn_pop_display(true,0,10,true);
}
 
/**
 * Function to add the widgets to the Dashboard.
 * 
 * @access public
 * @return void
 */
function tptn_pop_dashboard_setup() {
	if (function_exists('wp_add_dashboard_widget')) {
		wp_add_dashboard_widget( 'tptn_pop_dashboard', __( 'Popular Posts',TPTN_LOCAL_NAME ), 'tptn_pop_dashboard' );
		wp_add_dashboard_widget( 'tptn_pop_daily_dashboard', __( 'Daily Popular',TPTN_LOCAL_NAME ), 'tptn_pop_daily_dashboard' );
	}
}
add_action('wp_dashboard_setup', 'tptn_pop_dashboard_setup');


/**
 * Add an extra column to the All Posts page to display the page views.
 * 
 * @access public
 * @param mixed $cols
 * @return void
 */
function tptn_column($cols) {
	$tptn_settings = tptn_read_options();
	
	if ($tptn_settings['pv_in_admin'])	$cols['tptn_total'] = __('Total Views',TPTN_LOCAL_NAME);
	if ($tptn_settings['pv_in_admin'])	$cols['tptn_daily'] = __('Today\'s Views',TPTN_LOCAL_NAME);
	return $cols;
}
add_filter('manage_posts_columns', 'tptn_column');
add_filter('manage_pages_columns', 'tptn_column');


/**
 * Display page views for each column.
 * 
 * @access public
 * @param string $column_name Name of the column
 * @param int|string $id Post ID
 * @return void
 */
function tptn_value($column_name, $id) {
	global $wpdb;
	$tptn_settings = tptn_read_options();

	// Add Total count
	if (($column_name == 'tptn_total')&&($tptn_settings['pv_in_admin'])) {
		
		$table_name = $wpdb->prefix . "top_ten";
		
		$resultscount = $wpdb->get_row("SELECT postnumber, cntaccess from $table_name WHERE postnumber = $id");
		$cntaccess = number_format_i18n((($resultscount) ? $resultscount->cntaccess : 0));
	}
	
	// Now process daily count
	if (($column_name == 'tptn_daily')&&($tptn_settings['pv_in_admin'])) {
		$table_name = $wpdb->prefix . "top_ten_daily";

		$daily_range = $tptn_settings['daily_range']-1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$resultscount = $wpdb->get_row("SELECT postnumber, SUM(cntaccess) as sumCount FROM $table_name WHERE postnumber = $id AND dp_date >= '$current_date' GROUP BY postnumber ");
		$cntaccess .= number_format_i18n((($resultscount) ? $resultscount->sumCount : 0));
	}
	
	echo $cntaccess;
}
add_action('manage_posts_custom_column', 'tptn_value', 10, 2);
add_action('manage_pages_custom_column', 'tptn_value', 10, 2);


/**
 * Regiter the columns as sortable.
 * 
 * @access public
 * @param mixed $cols
 * @return void
 */
function tptn_column_register_sortable( $cols ) {
	$tptn_settings = tptn_read_options();
	
	if ($tptn_settings['pv_in_admin'])	$cols['tptn_total'] = array('tptn_total', true);
	if ($tptn_settings['pv_in_admin'])	$cols['tptn_daily'] = array('tptn_daily', true);
	return $cols;
}
add_filter( 'manage_edit-post_sortable_columns', 'tptn_column_register_sortable' );
add_filter( 'manage_edit-page_sortable_columns', 'tptn_column_register_sortable' );


function tptn_column_clauses( $clauses, $wp_query ) {
	global $wpdb;
	$tptn_settings = tptn_read_options();

	if ( isset( $wp_query->query['orderby'] ) && 'tptn_total' == $wp_query->query['orderby'] ) {

		$table_name = $wpdb->prefix . "top_ten";
		$clauses['join'] .= "LEFT OUTER JOIN {$table_name} ON {$wpdb->posts}.ID={$table_name}.postnumber";
		$clauses['orderby']  = "cntaccess ";
		$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
	}

	if ( isset( $wp_query->query['orderby'] ) && 'tptn_daily' == $wp_query->query['orderby'] ) {

		$table_name = $wpdb->prefix . "top_ten_daily";
	
		$daily_range = $tptn_settings['daily_range'] - 1;
		$current_time = gmdate( 'Y-m-d', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) );
		$current_date = strtotime ( '-'.$daily_range. ' DAY' , strtotime ( $current_time ) );
		$current_date = date ( 'Y-m-j' , $current_date );
		
		$clauses['join'] .= "LEFT OUTER JOIN {$table_name} ON {$wpdb->posts}.ID={$table_name}.postnumber";
		$clauses['where'] .= " AND {$table_name}.dp_date >= '$current_date' ";
		$clauses['groupby'] = "{$table_name}.postnumber";
		$clauses['orderby']  = "SUM({$table_name}.cntaccess) ";
		$clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
	}

	return $clauses;
}
add_filter( 'posts_clauses', 'tptn_column_clauses', 10, 2 );

/**
 * Output CSS for width of new column.
 * 
 * @access public
 * @return void
 */
function tptn_css() {
?>
<style type="text/css">
	#tptn_total, #tptn_daily { width: 100px; }
</style>
<?php	
}
add_action('admin_head', 'tptn_css');

?>