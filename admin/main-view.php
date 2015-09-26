<?php
/**
 * Top 10 Admin interface - Main screen.
 *
 * This page is accessible via Top 10 Settings menu item
 *
 * @package   Top_Ten
 * @author    Ajay D'Souza <me@ajaydsouza.com>
 * @license   GPL-2.0+
 * @link      https://webberzone.com
 * @copyright 2008-2015 Ajay D'Souza
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wrap">
	<h2><?php _e( 'Top 10 Settings', 'top-10' ); ?></h2>

	<ul class="subsubsub">
		<?php
			/**
			 * Fires before the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'tptn_admin_nav_bar_before' )
		?>

	  	<li><a href="#genopdiv"><?php _e( 'General options', 'top-10' ); ?></a> | </li>
	  	<li><a href="#counteropdiv"><?php _e( 'Counter and tracker options', 'top-10' ); ?></a> | </li>
	  	<li><a href="#pplopdiv"><?php _e( 'Popular post list options', 'top-10' ); ?></a> | </li>
	  	<li><a href="#thumbopdiv"><?php _e( 'Thumbnail options', 'top-10' ); ?></a> | </li>
	  	<li><a href="#customcssdiv"><?php _e( 'Styles', 'top-10' ); ?></a> | </li>
	  	<li><a href="#tptn_maintenance_op"><?php _e( 'Maintenance', 'top-10' ); ?></a></li>

		<?php
			/**
			 * Fires after the navigation bar in the Settings page
			 *
			 * @since 2.0.0
			 */
			do_action( 'tptn_admin_nav_bar_after' )
		?>
	</ul>

	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="tptn_options" name="tptn_options" onsubmit="return checkForm()">
	    <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
			<h3 class='hndle'><span><?php _e( 'General options', 'top-10' ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before General options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_general_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><?php _e( 'Enable trackers:', 'top-10' ); ?></th>
						<td>
							<p>
								<label>
									<input type="checkbox" name="activate_overall" id="activate_overall" <?php if ( $tptn_settings['activate_overall'] ) { echo 'checked="checked"'; } ?> />
									<?php _e( 'Overall', 'top-10' ); ?>
								</label>
							</p>
							<p>
								<label>
									<input type="checkbox" name="activate_daily" id="activate_daily" <?php if ( $tptn_settings['activate_daily'] ) { echo 'checked="checked"'; } ?> />
									<?php _e( 'Daily', 'top-10' ); ?>
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cache"><?php _e( 'Enable cache:', 'top-10' ); ?></label></th>
						<td>
							<p><input type="checkbox" name="cache" id="cache" <?php if ( $tptn_settings['cache'] ) { echo 'checked="checked"'; } ?> /></p>
							<p class="description"><?php _e( 'If activated, Top 10 will use the Transients API to cache the popular posts output for 1 hour.', 'top-10' ); ?></p>
							<p><input type="button" name="cache_clear" id="cache_clear"  value="<?php _e( 'Clear cache', 'top-10' ); ?>" class="button-secondary" onclick="return clearCache();" /></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cache_fix"><?php _e( 'Use Ajax for tracking:', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="cache_fix" id="cache_fix" <?php if ( $tptn_settings['cache_fix'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'This will try to prevent W3 Total Cache and other caching plugins from caching the tracker script of the plugin. Try toggling this option in case you find that your posts are not tracked.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="daily_midnight"><?php _e( 'Start daily counts from midnight:', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="daily_midnight" id="daily_midnight" <?php if ( $tptn_settings['daily_midnight'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'Daily counter will display number of visits from midnight. This option is checked by default and mimics the way most normal counters work. Turning this off will allow you to use the hourly setting in the next option.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="daily_range"><?php _e( 'Daily popular contains top posts over:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="daily_range" id="daily_range" size="3" value="<?php echo stripslashes( $tptn_settings['daily_range'] ); ?>"> <?php _e( 'day(s)', 'top-10' ); ?>
							<input type="textbox" name="hour_range" id="hour_range" size="3" value="<?php echo stripslashes( $tptn_settings['hour_range'] ); ?>"> <?php _e( 'hour(s)', 'top-10' ); ?>
							<p class="description"><?php _e( 'Think of Daily Popular has a custom date range applied as a global setting. Instead of displaying popular posts from the past day, this setting lets you display posts for as many days or as few hours as you want. This can be overridden in the widget.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uninstall_clean_options"><?php _e( 'Delete options on uninstall', 'top-10' ); ?></label></th>
						<td>
					    	<input type="checkbox" name="uninstall_clean_options" id="uninstall_clean_options" <?php if ( $tptn_settings['uninstall_clean_options'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'If this is checked, all settings related to Top 10 are removed from the database if you choose to uninstall/delete the plugin.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="uninstall_clean_tables"><?php _e( 'Delete counter data on uninstall', 'top-10' ); ?></label></th>
						<td>
					    	<input type="checkbox" name="uninstall_clean_tables" id="uninstall_clean_tables" <?php if ( $tptn_settings['uninstall_clean_tables'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'If this is checked, the tables containing the counter statistics are removed from the database if you choose to uninstall/delete the plugin.', 'top-10' ); ?></p>
							<p class="description"><?php _e( "Keep this unchecked if you choose to reinstall the plugin and don't want to lose your counter data.", 'top-10' ); ?></p>
						</td>
					</tr>
					<tr><th scope="row"><label for="show_metabox"><?php _e( 'Show metabox:', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="show_metabox" id="show_metabox" <?php if ( $tptn_settings['show_metabox'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'This will add the Top 10 metabox on Edit Posts or Add New Posts screens. Also applies to Pages and Custom Post Types.', 'top-10' ); ?></p>
						</td>
					</tr>

					<tr><th scope="row"><label for="show_metabox_admins"><?php _e( 'Limit metabox to Admins only:', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="show_metabox_admins" id="show_metabox_admins" <?php if ( $tptn_settings['show_metabox_admins'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'If this is selected, the metabox will be hidden from anyone who is not an Admin. Otherwise, by default, Contributors and above will be able to see the metabox. This applies only if the above option is selected.', 'top-10' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="show_credit"><?php _e( 'Link to Top 10 plugin page', 'top-10' ); ?></label></th>
						<td>
					    	<input type="checkbox" name="show_credit" id="show_credit" <?php if ( $tptn_settings['show_credit'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'A link to the plugin is added as an extra list item to the list of popular posts', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_genop_save" value="<?php _e( 'Save Options', 'top-10' ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after General options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_general_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="counteropdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Counter and tracker options', 'top-10' ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before Counter options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_counter_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><?php _e( 'Display number of views on:', 'top-10' ); ?></th>
						<td>
							<label><input type="checkbox" name="add_to_content" id="add_to_content" <?php if ( $tptn_settings['add_to_content'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Posts', 'top-10' ); ?></label><br />
							<label><input type="checkbox" name="count_on_pages" id="count_on_pages" <?php if ( $tptn_settings['count_on_pages'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Pages', 'top-10' ); ?></label><br />
							<label><input type="checkbox" name="add_to_home" id="add_to_home" <?php if ( $tptn_settings['add_to_home'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Home page', 'top-10' ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_feed" id="add_to_feed" <?php if ( $tptn_settings['add_to_feed'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Feeds', 'top-10' ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_category_archives" id="add_to_category_archives" <?php if ( $tptn_settings['add_to_category_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Category archives', 'top-10' ); ?></label><br />
							<label><input type="checkbox" name="add_to_tag_archives" id="add_to_tag_archives" <?php if ( $tptn_settings['add_to_tag_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Tag archives', 'top-10' ); ?></label></label><br />
							<label><input type="checkbox" name="add_to_archives" id="add_to_archives" <?php if ( $tptn_settings['add_to_archives'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Other archives', 'top-10' ); ?></label></label>
							<p class="description"><?php _e( "If you choose to disable this, please add <code>&lt;?php if ( function_exists ( 'echo_tptn_post_count' ) ) echo_tptn_post_count(); ?&gt;</code> to your template file where you want it displayed", 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'Format to display the post views:', 'top-10' ); ?></label></th>
						<td>
							<textarea name="count_disp_form" id="count_disp_form" cols="50" rows="3" style="width:100%"><?php echo htmlspecialchars( stripslashes( $tptn_settings['count_disp_form'] ) ); ?></textarea>
							<p class="description"><?php _e( 'Use <code>%totalcount%</code> to display the total count, <code>%dailycount%</code> to display the daily count and <code>%overallcount%</code> to display the overall count across all posts on the blog. e.g. the default options displays <code>[Visited 123 times, 23 visits today]</code>', 'top-10' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'What do display when there are no visits?', 'top-10' ); ?></label></th>
						<td>
							<textarea name="count_disp_form_zero" id="count_disp_form_zero" cols="50" rows="3" style="width:100%"><?php echo htmlspecialchars( stripslashes( $tptn_settings['count_disp_form_zero'] ) ); ?></textarea>
					    	<p class="description"><?php _e( "This text applies only when there are 0 hits for the post and it isn't a single page. e.g. if you display post views on the homepage or archives then this text will be used. To override this, just enter the same text as above option.", 'top-10' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="dynamic_post_count"><?php _e( 'Always display latest post count', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="dynamic_post_count" id="dynamic_post_count" <?php if ( $tptn_settings['dynamic_post_count'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'This option uses JavaScript and will increase your page load time. Turn this off if you are not using caching plugins or are OK with displaying older cached counts.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_authors"><?php _e( 'Track visits of authors on their own posts?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="track_authors" id="track_authors" <?php if ( $tptn_settings['track_authors'] ) { echo 'checked="checked"'; } ?> />
					    	<p class="description"><?php _e( 'Disabling this option will stop authors visits tracked on their own posts', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_admins"><?php _e( 'Track visits of admins?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="track_admins" id="track_admins" <?php if ( $tptn_settings['track_admins'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( 'Disabling this option will stop admin visits being tracked.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="track_editors"><?php _e( 'Track visits of Editors?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="track_editors" id="track_editors" <?php if ( $tptn_settings['track_editors'] ) { echo 'checked="checked"'; } ?> />
					    	<p class="description"><?php _e( 'Disabling this option will stop editor visits being tracked.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="pv_in_admin"><?php _e( 'Display page views on Posts and Pages in Admin', 'top-10' ); ?></label></th>
						<td>
					    	<input type="checkbox" name="pv_in_admin" id="pv_in_admin" <?php if ( $tptn_settings['pv_in_admin'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( "Adds three columns called Total Views, Today's Views and Views to All Posts and All Pages", 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_count_non_admins"><?php _e( 'Show number of views to non-admins', 'top-10' ); ?></label></th>
						<td>
					    	<input type="checkbox" name="show_count_non_admins" id="show_count_non_admins" <?php if ( $tptn_settings['show_count_non_admins'] ) { echo 'checked="checked"'; } ?> />
							<p class="description"><?php _e( "If you disable this then non-admins won't see the above columns or view the independent pages with the top posts", 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_counterop_save" value="<?php _e( 'Save Options', 'top-10' ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after Counter options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_counter_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="pplopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Popular post list options', 'top-10' ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before Popular post list options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_list_options_before', $tptn_settings );
					?>

					<tr>
						<th scope="row"><label for="limit"><?php _e( 'Number of popular posts to display:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="limit" id="limit" value="<?php echo esc_attr( stripslashes( $tptn_settings['limit'] ) ); ?>">
							<p class="description"><?php _e( "Maximum number of posts that will be displayed in the list. This option is used if you don't specify the number of posts in the widget or shortcodes", 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="how_old"><?php _e( 'Published age of posts:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="how_old" id="how_old" value="<?php echo esc_attr( stripslashes( $tptn_settings['how_old'] ) ); ?>"> <?php _e( 'days', 'top-10' ); ?>
							<p class="description"><?php _e( 'This options allows you to only show posts that have been published within the above day range. Applies to both overall posts and daily posts lists.', 'top-10' ); ?></p>
							<p class="description"><?php _e( 'e.g. 365 days will only show posts published in the last year in the popular posts lists. Enter 0 for no restriction.', 'top-10' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Post types to include in results (including custom post types)', 'top-10' ); ?></th>
						<td>
							<?php foreach ( $wp_post_types as $wp_post_type ) { ?>

								<label>
									<input type="checkbox" name="post_types[]" value="<?php echo $wp_post_type; ?>" <?php if ( in_array( $wp_post_type, $posts_types_inc ) ) { echo 'checked="checked"'; } ?> />
									<?php echo $wp_post_type; ?>
								</label>
								<br />

							<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_post_ids"><?php _e( 'List of post or page IDs to exclude from the results:', 'top-10' ); ?></label></th>
						<td><input type="textbox" name="exclude_post_ids" id="exclude_post_ids" value="<?php echo esc_attr( stripslashes( $tptn_settings['exclude_post_ids'] ) ); ?>"  style="width:250px">
							<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'top-10' ); ?></p>
							</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_cat_slugs"><?php _e( 'Exclude Categories:', 'top-10' ); ?></label></th>
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
								<textarea class="wickEnabled:MYCUSTOMFLOATER" cols="50" rows="3" wrap="virtual" name="exclude_cat_slugs" style="width:100%"><?php echo ( stripslashes( $tptn_settings['exclude_cat_slugs'] ) ); ?></textarea>
								<p class="description"><?php _e( 'Comma separated list of category slugs. The field above has an autocomplete so simply start typing in the starting letters and it will prompt you with options', 'top-10' ); ?></p>
							</div>
							<p class="description highlight">
								<?php
									_e( 'Excluded category IDs are:', 'top-10' );
									echo ' ' . $tptn_settings['exclude_categories'];
								?>
							</p>
							<p class="description">
								<?php
									_e( 'These might differ from the IDs visible in the Categories page which use the <code>term_id</code>. Top 10 uses the <code>term_taxonomy_id</code> which is unique to this taxonomy.', 'top-10' );
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php _e( 'Title of popular posts:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="title" id="title" value="<?php echo esc_attr( stripslashes( $tptn_settings['title'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title_daily"><?php _e( 'Title of daily popular posts:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="title_daily" id="title_daily" value="<?php echo esc_attr( stripslashes( $tptn_settings['title_daily'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="blank_output"><?php _e( 'When there are no posts, what should be shown?', 'top-10' ); ?></label></th>
						<td>
							<label>
							<input type="radio" name="blank_output" value="blank" id="blank_output_0" <?php if ( $tptn_settings['blank_output'] ) { echo 'checked="checked"'; } ?> />
							<?php _e( 'Blank Output', 'top-10' ); ?></label>
							<br />
							<label>
							<input type="radio" name="blank_output" value="customs" id="blank_output_1" <?php if ( ! $tptn_settings['blank_output'] ) { echo 'checked="checked"'; } ?> />
							<?php _e( 'Display:', 'top-10' ); ?></label>
							<input type="textbox" name="blank_output_text" id="blank_output_text" value="<?php echo esc_attr( stripslashes( $tptn_settings['blank_output_text'] ) ); ?>"  style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_excerpt"><?php _e( 'Show post excerpt in list?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="show_excerpt" id="show_excerpt" <?php if ( $tptn_settings['show_excerpt'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="excerpt_length"><?php _e( 'Length of excerpt (in words):', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="excerpt_length" id="excerpt_length" value="<?php echo stripslashes( $tptn_settings['excerpt_length'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_author"><?php _e( 'Show post author in list?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="show_author" id="show_author" <?php if ( $tptn_settings['show_author'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="show_date"><?php _e( 'Show post date in list?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="show_date" id="show_date" <?php if ( $tptn_settings['show_date'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="title_length"><?php _e( 'Limit post title length (in characters)', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="title_length" id="title_length" value="<?php echo stripslashes( $tptn_settings['title_length'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="disp_list_count"><?php _e( 'Show view count in list?', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="disp_list_count" id="disp_list_count" <?php if ( $tptn_settings['disp_list_count'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="link_new_window	"><?php _e( 'Open links in new window', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="link_new_window" id="link_new_window" <?php if ( $tptn_settings['link_new_window'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="link_nofollow"><?php _e( 'Add nofollow attribute to links in the list', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="link_nofollow" id="link_nofollow" <?php if ( $tptn_settings['link_nofollow'] ) { echo 'checked="checked"'; } ?> />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="exclude_on_post_ids"><?php _e( 'Exclude display of related posts on these posts / pages', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="exclude_on_post_ids" id="exclude_on_post_ids" value="<?php echo esc_attr( stripslashes( $tptn_settings['exclude_on_post_ids'] ) ); ?>"  style="width:250px">
							<p class="description"><?php _e( 'Enter comma separated list of IDs. e.g. 188,320,500', 'top-10' ); ?></p>
						</td>
					</tr>

					<tr style="background: #eee"><th scope="row" colspan="2"><?php _e( 'Customise the list HTML', 'top-10' ); ?></th>
					</tr>
					<tr>
						<th scope="row"><label for="before_list"><?php _e( 'HTML to display before the list of posts:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="before_list" id="before_list" value="<?php echo esc_attr( stripslashes( $tptn_settings['before_list'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="before_list_item"><?php _e( 'HTML to display before each list item:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="before_list_item" id="before_list_item" value="<?php echo esc_attr( stripslashes( $tptn_settings['before_list_item'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="after_list_item"><?php _e( 'HTML to display after each list item:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="after_list_item" id="after_list_item" value="<?php echo esc_attr( stripslashes( $tptn_settings['after_list_item'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="after_list"><?php _e( 'HTML to display after the list of posts:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="after_list" id="after_list" value="<?php echo esc_attr( stripslashes( $tptn_settings['after_list'] ) ); ?>" style="width:250px" />
						</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_pplop_save" value="<?php _e( 'Save Options', 'top-10' ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after Popular post list options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_list_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="thumbopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	    	<h3 class='hndle'><span><?php _e( 'Thumbnail options', 'top-10' ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before thumbnail options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_thumbnail_options_before', $tptn_settings );
					?>

					<tr><th scope="row"><label for="post_thumb_op"><?php _e( 'Location of post thumbnail:', 'top-10' ); ?></label></th>
						<td>
							<label>
								<input type="radio" name="post_thumb_op" value="inline" id="post_thumb_op_0" <?php if ( 'inline' == $tptn_settings['post_thumb_op'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'Display thumbnails inline with posts, before title', 'top-10' ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="after" id="post_thumb_op_1" <?php if ( 'after' == $tptn_settings['post_thumb_op'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'Display thumbnails inline with posts, after title', 'top-10' ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="thumbs_only" id="post_thumb_op_2" <?php if ( 'thumbs_only' == $tptn_settings['post_thumb_op'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'Display only thumbnails, no text', 'top-10' ); ?>
							</label>
							<br />
							<label>
								<input type="radio" name="post_thumb_op" value="text_only" id="post_thumb_op_3" <?php if ( 'text_only' == $tptn_settings['post_thumb_op'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'Do not display thumbnails, only text.', 'top-10' ); ?>
							</label>

							<?php if ( 'left_thumbs' == $tptn_settings['tptn_styles'] ) { ?>
								<p style="color: #F00"><?php _e( 'This setting cannot be changed because an inbuilt style has been selected under the Styles section. If you would like to change this option, please select No styles under the Styles section.', 'top-10' ); ?></p>
							<?php } ?>
						</td>
					</tr>
					<tr><th scope="row"><?php _e( 'Thumbnail size:', 'top-10' ); ?></th>
						<td>
							<?php
								$tptn_get_all_image_sizes = tptn_get_all_image_sizes();
							if ( isset( $tptn_get_all_image_sizes['tptn_thumbnail'] ) ) {
								unset( $tptn_get_all_image_sizes['tptn_thumbnail'] );
							}

							foreach ( $tptn_get_all_image_sizes as $size ) :
							?>
							<label>
								<input type="radio" name="thumb_size" value="<?php echo $size['name'] ?>" id="<?php echo $size['name'] ?>" <?php if ( $tptn_settings['thumb_size'] == $size['name'] ) { echo 'checked="checked"'; } ?> />
								<?php echo $size['name']; ?> ( <?php echo $size['width']; ?>x<?php echo $size['height']; ?>
								<?php
								if ( $size['crop'] ) {
									echo 'cropped';
								}
									?>
									)
								</label>
								<br />
							<?php endforeach; ?>

								<label>
									<input type="radio" name="thumb_size" value="tptn_thumbnail" id="tptn_thumbnail" <?php if ( $tptn_settings['thumb_size'] == 'tptn_thumbnail' ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Custom size', 'top-10' ); ?>
								</label>
								<p class="description">
									<?php _e( 'You can choose from existing image sizes above or create a custom size. If you have chosen Custom size above, then enter the width, height and crop settings below. For best results, use a cropped image.', 'top-10' ); ?><br />
									<?php _e( 'If you change the width and/or height below, existing images will not be automatically resized.', 'top-10' ); ?>
									<?php printf( __( "I recommend using <a href='%s' class='thickbox'>OTF Regenerate Thumbnails</a> or <a href='%s' class='thickbox'>Regenerate Thumbnails</a> to regenerate all image sizes.", 'top-10' ), self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=otf-regenerate-thumbnails&amp;TB_iframe=true&amp;width=600&amp;height=550' ), self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=regenerate-thumbnails&amp;TB_iframe=true&amp;width=600&amp;height=550' ) ); ?>
								</p>
								<p class="description">
									<?php _e( "If you're using the Left Thumbs style below then the thumbnail width and height that you set here will supersede the widget. Alternatively, choose <strong>Style attributes</strong> under <strong>Image size attributes</strong> option below", 'top-10' ); ?>
								</p>
						</td>
					<tr><th scope="row"><label for="thumb_width"><?php _e( 'Width of custom thumbnail:', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="thumb_width" id="thumb_width" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_width'] ) ); ?>" style="width:50px" />px
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_height"><?php _e( 'Height of custom thumbnail', 'top-10' ); ?></label></th>
						<td>
							<input type="textbox" name="thumb_height" id="thumb_height" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_height'] ) ); ?>" style="width:50px" />px
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_crop"><?php _e( 'Crop mode:', 'top-10' ); ?></label></th>
						<td>
							<input type="checkbox" name="thumb_crop" id="thumb_crop" <?php if ( $tptn_settings['thumb_crop'] ) { echo 'checked="checked"'; } ?> />
							<p class="description">
								<?php _e( 'By default, thumbnails will be proportionately cropped. Check this box to hard crop the thumbnails.', 'top-10' ); ?>
								<?php printf( __( "<a href='%s' target='_blank'>Difference between soft and hard crop</a>", 'top-10' ), esc_url( 'http://www.davidtan.org/wordpress-hard-crop-vs-soft-crop-difference-comparison-example/' ) ); ?>
							</p>
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_html"><?php _e( 'Image size attributes:', 'top-10' ); ?></label></th>
						<td>
							<label>
								<input type="radio" name="thumb_html" value="css" id="thumb_html_0" <?php if ( 'css' == $tptn_settings['thumb_html'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'Style attributes are used for width and height.', 'top-10' );
								echo ' <code>style="max-width:' . $tptn_settings['thumb_width'] . 'px;max-height:' . $tptn_settings['thumb_height'] . 'px;"</code>'; ?>
							</label>
							<br />
							<label>
								<input type="radio" name="thumb_html" value="html" id="thumb_html_1" <?php if ( 'html' == $tptn_settings['thumb_html'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'HTML width and height attributes are used for width and height.', 'top-10' );
								echo ' <code>width="' . $tptn_settings['thumb_width'] . '" height="' . $tptn_settings['thumb_height'] . '"</code>'; ?>
							</label>
							<br />
							<label>
								<input type="radio" name="thumb_html" value="none" id="thumb_html_1" <?php if ( 'none' == $tptn_settings['thumb_html'] ) { echo 'checked="checked"'; } ?> />
								<?php _e( 'No HTML or Style attributes set for width and height', 'top-10' ); ?>
							</label>
							<br />
						</td>
					</tr>
					<tr><th scope="row"><label for="thumb_meta"><?php _e( 'Post thumbnail meta field name:', 'top-10' ); ?></label></th>
						<td>
					  		<input type="textbox" name="thumb_meta" id="thumb_meta" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_meta'] ) ); ?>">
					  		<p class="description"><?php _e( 'The value of this field should contain the image source and is set in the <em>Add New Post</em> screen', 'top-10' ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="scan_images"><?php _e( 'If the postmeta is not set, then should the plugin extract the first image from the post?', 'top-10' ); ?></label></th>
						<td>
						  	<input type="checkbox" name="scan_images" id="scan_images" <?php if ( $tptn_settings['scan_images'] ) { echo 'checked="checked"'; } ?> />
						  	<p class="description"><?php _e( 'This could slow down the loading of your page if the first image in the related posts is large in file-size', 'top-10' ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="thumb_default_show"><?php _e( 'Use default thumbnail?', 'top-10' ); ?></label></th>
						<td>
					  		<input type="checkbox" name="thumb_default_show" id="thumb_default_show" <?php if ( $tptn_settings['thumb_default_show'] ) { echo 'checked="checked"'; } ?> />
					  		<p class="description"><?php _e( 'If checked, when no thumbnail is found, show a default one from the URL below. If not checked and no thumbnail is found, no image will be shown.', 'top-10' ); ?></p>
					  	</td>
					</tr>
					<tr><th scope="row"><label for="thumb_default"><?php _e( 'Default thumbnail:', 'top-10' ); ?></label></th>
					  	<td>
					  		<input type="textbox" name="thumb_default" id="thumb_default" value="<?php echo esc_attr( stripslashes( $tptn_settings['thumb_default'] ) ); ?>" style="width:100%"> <br />
					  		<?php if ( '' != $tptn_settings['thumb_default'] ) { echo "<img src='{$tptn_settings['thumb_default']}' style='max-width:200px' />"; } ?>
					  		<p class="description"><?php _e( "The plugin will first check if the post contains a thumbnail. If it doesn't then it will check the meta field. If this is not available, then it will show the default image as specified above", 'top-10' ); ?></p>
					  	</td>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<input type="submit" name="tptn_save" id="tptn_thumbop_save" value="<?php _e( 'Save Options', 'top-10' ); ?>" class="button button-primary" />
						</td>
					</tr>

					<?php
						/**
						 * Fires after thumbnail options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_thumbnail_options_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
	    <div id="customcssdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
			<h3 class='hndle'><span><?php _e( 'Styles', 'top-10' ); ?></span></h3>
			<div class="inside">
				<table class="form-table">

					<?php
						/**
						 * Fires before custom styles options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_custom_styles_before', $tptn_settings );
					?>

					<tr><th scope="row"><?php _e( 'Style of the related posts:', 'top-10' ); ?></th>
					  <td>
						<label>
							<input type="radio" name="tptn_styles" value="no_style" id="tptn_styles_1" <?php if ( 'no_style' == $tptn_settings['tptn_styles'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'No styles', 'top-10' ); ?>
						</label>
						<p class="description"><?php _e( 'Select this option if you plan to add your own styles', 'top-10' ); ?></p>
						<br />

						<label>
							<input type="radio" name="tptn_styles" value="left_thumbs" id="tptn_styles_0" <?php if ( $tptn_settings['include_default_style'] && ( 'left_thumbs' == $tptn_settings['tptn_styles'] ) ) { echo 'checked="checked"'; } ?> />
							<?php _e( 'Left Thumbnails', 'top-10' ); ?>
						</label>
						<p class="description"><img src="<?php echo plugins_url( 'admin/images/tptn-left-thumbs.png', dirname( __FILE__ ) ); ?>" /></p>
						<p class="description"><?php _e( 'Enabling this option will set the post thumbnail to be before text. Disabling this option will not revert any settings.', 'top-10' ); ?></p>
					  	<p class="description"><?php printf( __( 'You can view the default style at <a href="%1$s" target="_blank">%1$s</a>', 'top-10' ), esc_url( 'https://github.com/WebberZone/top-10/blob/master/css/default-style.css' ) ); ?></p>
						<br />

						<label>
							<input type="radio" name="tptn_styles" value="text_only" id="tptn_styles_1" <?php if ( 'text_only' == $tptn_settings['tptn_styles'] ) { echo 'checked="checked"'; } ?> /> <?php _e( 'Text only', 'top-10' ); ?>
						</label>
						<p class="description"><?php _e( 'Enabling this option will disable thumbnails and no longer include the default style sheet included in the plugin.', 'top-10' ); ?></p>

						<?php
							/**
							 * Fires after style checkboxes which allows an addon to add more styles.
							 *
							 * @since 2.2.0
							 *
							 * @param	array	$tptn_settings	Top 10 settings array
							 */
							do_action( 'tptn_admin_tptn_styles', $tptn_settings );
						?>

					  </td>
					</tr>

					<tr><th scope="row" colspan="2"><?php _e( 'Custom CSS to add to header:', 'top-10' ); ?></th>
					</tr>
					<tr>
						<td scope="row" colspan="2">
							<textarea name="custom_CSS" id="custom_CSS" rows="15" cols="80" style="width:100%"><?php echo stripslashes( $tptn_settings['custom_CSS'] ); ?></textarea>
							<p class="description"><?php _e( 'Do not include <code>style</code> tags. Check out the <a href="http://wordpress.org/extend/plugins/top-10/faq/" target="_blank">FAQ</a> for available CSS classes to style.', 'top-10' ); ?></p>
						</td>
					</tr>

					<?php
						/**
						 * Fires after custom styles options block.
						 *
						 * @since 2.0.0
						 *
						 * @param	array	$tptn_settings	Top 10 settings array
						 */
						do_action( 'tptn_admin_custom_styles_after', $tptn_settings );
					?>

				</table>
			</div>
	    </div>
		<p>
			<input type="submit" name="tptn_save" id="tptn_save" value="<?php _e( 'Save Options', 'top-10' ); ?>" class="button button-primary" />
			<input type="submit" name="tptn_default" id="tptn_default" value="<?php _e( 'Default Options', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e( 'Do you want to set options to Default?', 'top-10' ); ?>')) return false;" />
		</p>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

		<?php
			/**
			 * Fires after all option blocks.
			 *
			 * @since 2.0.0
			 *
			 * @param	array	$tptn_settings	Top 10 settings array
			 */
			do_action( 'tptn_admin_options_after', $tptn_settings );
		?>

	  <hr class="clear" />

	  <form method="post" id="tptn_maintenance_op" name="tptn_reset_options" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Maintenance', 'top-10' ); ?></span></h3>
	      <div class="inside">
			  <table class="form-table">
				<tr><td scope="row" colspan="2">
				    <p class="description"><?php _e( 'Over time the Daily Top 10 database grows in size, which reduces the performance of the plugin. Cleaning the database at regular intervals could improve performance, especially on high traffic blogs. Enabling maintenance will automatically delete entries older than 90 days.', 'top-10' ); ?><br />
				    <strong><?php _e( 'Note: When scheduled maintenance is enabled, WordPress will run the cron job everytime the job is rescheduled (i.e. you change the settings below).', 'top-10' ); ?></strong>
				  </td>
				</tr>
				<tr><th scope="row"><label for="cron_on"><?php _e( 'Enable scheduled maintenance of daily tables:', 'top-10' ); ?></label></th>
				  <td><input type="checkbox" name="cron_on" id="cron_on" <?php if ( $tptn_settings['cron_on'] ) { echo 'checked="checked"'; } ?> />
				</td>
				</tr>
				<tr><th scope="row"><label for="cron_hour"><?php _e( 'Time to run maintenance', 'top-10' ); ?></label></th>
				  <td><input type="textbox" name="cron_hour" id="cron_hour" value="<?php echo esc_attr( stripslashes( $tptn_settings['cron_hour'] ) ); ?>" style="width:50px" /> <?php _e( 'hrs', 'top-10' ); ?> : <input type="textbox" name="cron_min" id="cron_min" value="<?php echo esc_attr( stripslashes( $tptn_settings['cron_min'] ) ); ?>" style="width:50px" /> <?php _e( 'min', 'top-10' ); ?></td>
				</tr>
				<tr><th scope="row"><label for="cron_recurrence"><?php _e( 'How often should the maintenance be run:', 'top-10' ); ?></label></th>
				  <td>
					<label>
					<input type="radio" name="cron_recurrence" value="daily" id="cron_recurrence0" <?php if ( 'daily' == $tptn_settings['cron_recurrence'] ) { echo 'checked="checked"'; } ?> />
					<?php _e( 'Daily', 'top-10' ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="weekly" id="cron_recurrence1" <?php if ( 'weekly' == $tptn_settings['cron_recurrence'] ) { echo 'checked="checked"'; } ?> />
					<?php _e( 'Weekly', 'top-10' ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="fortnightly" id="cron_recurrence2" <?php if ( 'fortnightly' == $tptn_settings['cron_recurrence'] ) { echo 'checked="checked"'; } ?> />
					<?php _e( 'Fortnightly', 'top-10' ); ?></label>
					<br />
					<label>
					<input type="radio" name="cron_recurrence" value="monthly" id="cron_recurrence3" <?php if ( 'monthly' == $tptn_settings['cron_recurrence'] ) { echo 'checked="checked"'; } ?> />
					<?php _e( 'Monthly', 'top-10' ); ?></label>
					<br />
				  </td>
				</tr>
				<tr><td scope="row" colspan="2">
					<?php
					if ( ( $tptn_settings['cron_on'] ) || wp_next_scheduled( 'tptn_cron_hook' ) ) {
						if ( wp_next_scheduled( 'tptn_cron_hook' ) ) {
							echo '<span style="color:#0c0">';
							_e( 'The cron job has been scheduled. Maintenance will run', 'top-10' );
							echo ' ' . wp_get_schedule( 'tptn_cron_hook' );
							echo '.</span>';
						} else {
							echo '<span style="color:#e00">';
							_e( 'The cron job is missing. Please resave this page to add the job', 'top-10' );
							echo '</span>';
						}
					} else {
							echo '<span style="color:#FFA500">';
							_e( 'Maintenance is turned off', 'top-10' );
							echo '</span>';
					}
					?>
				</td></tr>
				</table>
			  <input type="submit" name="tptn_mnts_save" id="tptn_mnts_save" value="<?php _e( 'Save Maintenance Options', 'top-10' ); ?>" class="button button-primary" />
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

	  <form method="post" id="tptn_reset_options" name="tptn_reset_options" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'Reset count and other tools', 'top-10' ); ?></span></h3>
	      <div class="inside">
		    <p class="description">
				<?php _e( 'This cannot be reversed. Make sure that your database has been backed up before proceeding', 'top-10' ); ?>
		    </p>
		    <p>
		      <input name="tptn_trunc_all" type="submit" id="tptn_trunc_all" value="<?php _e( 'Reset Popular Posts', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php _e( 'Are you sure you want to reset the popular posts?', 'top-10' ); ?>')) return false;" />
		      <input name="tptn_trunc_daily" type="submit" id="tptn_trunc_daily" value="<?php _e( 'Reset Daily Popular Posts', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" onclick="if (!confirm('<?php _e( 'Are you sure you want to reset the daily popular posts?', 'top-10' ); ?>')) return false;" />
		    </p>
		    <p class="description">
				<?php _e( 'This will merge post counts for posts with table entries of 0 and 1', 'top-10' ); ?>
		    </p>
		    <p>
		      <input name="tptn_merge_blogids" type="submit" id="tptn_merge_blogids" value="<?php _e( 'Merge blog ID 0 and 1 post counts', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e( 'This will merge post counts for blog IDs 0 and 1. Proceed?', 'top-10' ); ?>')) return false;" />
		    </p>
		    <p class="description">
				<?php _e( 'In older versions, the plugin created entries with duplicate post IDs. Clicking the button below will merge these duplicate IDs', 'top-10' ); ?>
		    </p>
		    <p>
		      <input name="tptn_clean_duplicates" type="submit" id="tptn_clean_duplicates" value="<?php _e( 'Merge duplicates across blog IDs', 'top-10' ); ?>" class="button button-secondary" onclick="if (!confirm('<?php _e( 'This will delete the duplicate entries in the tables. Proceed?', 'top-10' ); ?>')) return false;" />
		    </p>
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>

	  	<?php
			/**
			 * Only show the below options if it is multisite
			 */
		if ( is_multisite() ) {
		?>

	  <form method="post" id="tptn_import_mu" name="tptn_import_mu" onsubmit="return checkForm()">
	    <div id="resetopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'top-10' ); ?>"><br /></div>
	  <h3 class='hndle'><span><?php _e( 'WordPress Multisite: Migrate Top 10 v1.x counts to 2.x', 'top-10' ); ?></span></h3>
	  <div class="inside">
		<p class="description">
			<?php _e( "If you've been using Top 10 v1.x on multisite, you would have needed to activate the plugin independently for each site. This would have resulted in two tables being created for each site in the network.", 'top-10' ); ?>
			<?php _e( 'Top 10 v2.x onwards uses only a single table to record the count, keeping your database clean. You can use this tool to import the recorded counts from v1.x tables to the new v2.x table format.', 'top-10' ); ?>
		</p>
		<p class="description">
			<?php _e( 'If you do not see any tables below, then it means that either all data has already been imported or no relevant information has been found.', 'top-10' ); ?>
		</p>
		<p class="description">
			<strong style="color:#C00"><?php _e( 'After running the importer, please verify that all the counts have been successfully imported. Only then should you delete any old tables!', 'top-10' ); ?></strong>
		</p>

			<?php
				$top_ten_mu_tables_sel_blog_ids = get_site_option( 'top_ten_mu_tables_sel_blog_ids', array() );
				$top_ten_mu_tables_blog_ids = array();
				$top_ten_all_mu_tables = array();

				// Get all blogs in the network and activate plugin on each one
				$blog_ids = $wpdb->get_col( "
			        	SELECT blog_id FROM $wpdb->blogs
						WHERE archived = '0' AND spam = '0' AND deleted = '0'
					" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				$top_ten_mu_table = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "top_ten' " );

				if ( ! empty( $top_ten_mu_table ) && ! is_main_site( $blog_id ) ) {
					$top_ten_mu_tables_blog_ids[] = $blog_id;
					$top_ten_all_mu_tables[ $top_ten_mu_table ][0] = $top_ten_mu_table;
					$top_ten_all_mu_tables[ $top_ten_mu_table ][1] = in_array( $blog_id, $top_ten_mu_tables_sel_blog_ids ) ? 1 : 0;
					$top_ten_all_mu_tables[ $top_ten_mu_table ][2] = $blog_id;
				}
			}

				// Switch back to the current blog
				restore_current_blog();

			if ( ! empty( $top_ten_all_mu_tables ) ) {
				?>

			<table class="form-table">
				<tr>
				<th>
				<?php _e( 'Blog ID', 'top-10' ); ?>
				</th>
				<th>
				<?php _e( 'Status', 'top-10' ); ?>
				</th>
				<th>
				<?php _e( 'Select to import', 'top-10' ); ?>
				</th>
				</tr>

				<?php
				foreach ( $top_ten_all_mu_tables as $top_ten_all_mu_table ) {
				?>
				<tr>
					<td>
						<?php
							_e( 'Blog #', 'top-10' );
							echo $top_ten_all_mu_table[2];
							echo ': ';
							echo get_blog_details( $top_ten_all_mu_table[2] )->blogname;
						?>
						</td>
						<td>
						<?php
						if ( 0 == $top_ten_all_mu_table[1] ) {
							echo '<span style="color:#F00">';
							_e( 'Not imported', 'top-10' );
							echo '</span>';
						} else {
							echo '<span style="color:#0F0">';
							_e( 'Imported', 'top-10' );
							echo '</span>';
						}
							?>
						</td>
						<td>
							<?php
							if ( 0 == $top_ten_all_mu_table[1] ) {
								echo '<input type="checkbox" name="top_ten_all_mu_tables[' . $top_ten_all_mu_table[0] . ']" value="' . $top_ten_all_mu_table[2] . '" checked="checked" />';
							} else {
								echo '<input type="checkbox" name="top_ten_all_mu_tables[' . $top_ten_all_mu_table[0] . ']" value="' . $top_ten_all_mu_table[2] . '" />';
							}
							?>
						</td>
					</tr>
				<?php
				}
			    ?>
			</table>
		    <p>
		      <input type="hidden" name="top_ten_mu_tables_blog_ids" value="<?php echo implode( ',', $top_ten_mu_tables_blog_ids ); ?>" />
		      <input name="tptn_import" type="submit" id="tptn_import" value="<?php _e( 'Begin import', 'top-10' ); ?>" class="button button-primary" />
		      <input name="tptn_delete_selected_tables" type="submit" id="tptn_delete_selected_tables" value="<?php _e( 'Delete selected tables', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" />
		      <input name="tptn_delete_imported_tables" type="submit" id="tptn_delete_imported_tables" value="<?php _e( 'Delete all imported tables', 'top-10' ); ?>" class="button button-secondary" style="color:#f00" />
		    </p>
			<?php
			} // End if ( ! empty( $top_ten_all_mu_tables ) )
			?>
	      </div>
	    </div>
		<?php wp_nonce_field( 'tptn-plugin-settings' ); ?>
	  </form>
			<?php
		}
			?>
	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
			<?php tptn_admin_side(); ?>
	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

