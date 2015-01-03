=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics
Contributors: Ajay
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.3
Tested up to: 4.2
License: GPLv2 or later


Track daily and total visits on your blog posts. Display the count as well as popular and trending posts.

== Description ==

WordPress doesn't count page views by default. <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10</a> will count the number of page views on your single posts on a daily as well as overall basis. You can then display the page view count on individual posts and pages as well as display a list of most popular posts based on page views.

Includes a sidebar widget to display the popular posts. And, all settings can be configured from within your WordPress Admin area itself! You can choose to disable tracking of author visits on their own posts.

= Features =
* **Page counter**:Counts daily and total page views on single posts, pages and *custom post types*
* **Display the count**: Customize the text that can be displayed
* **Show off popular posts**: Display a list of daily and/or overall popular posts by page count. You can choose how many posts are to be displayed plus loads of other customisation options
* **Widget ready**: Sidebar widgets available for daily popular and overall popular posts. Highly customizable widgets to control what you want to display in the list of posts
* **Customisable output**:
	* Top 10 includes a default CSS style to make your popular posts list look pretty. Choose **Thumbnails inline, before title** under 'Thumbnail options' when using this option
	* Output wrapped in CSS classes that allows you to style the list. You can enter your custom CSS styles from within WordPress Admin area under "Custom Styles"
	* Pick your own HTML tags to use for displaying the output in case you don't prefer the default `list` format
* **Thumbnail support**
	* Support for WordPress post thumbnails
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via <a href="http://codex.wordpress.org/Custom_Fields">WordPress meta fields</a>
	* Use timthumb to crop and resize images
* **Shortcodes**: The plugin includes two shortcodes `[tptn_list]` and `[tptn_views]` to display the posts list and the number of views respectively
* **Exclusions**: Exclude posts from select categories from appearing in the top posts list. Also exclude posts by ID from appearing in the list
* **Admin interface**: View list of daily and/or overall popular posts from within the dashboard. Top 10 will also add two sortable columns to your All Posts and All Pages pages in your WordPress Admin area
* **Works with caching plugins** like WP-Super-Cache, W3 Total Cache or Quick Cache
* **Clean uninstall**: If you choose to delete the plugin from within WP-Admin, the plugin will remove all its data. But why would you?


If you're looking for a plugin to display related, look no further than my other plugin <a href="http://ajaydsouza.com/wordpress/plugins/contextual-related-posts">Contextual Related Posts</a>.


== Screenshots ==

1. Top 10 options - General options
2. Top 10 options - Counter and Tracker options
3. Top 10 options - Popular post list options
4. Top 10 options - Thumbnail options
5. Top 10 options - Custom styles
6. Top 10 options - Maintenance
7. Top 10 widget options
8. Top 10 Meta box on the Edit Post screen
9. WordPress Multisite: Import Top 10 v1.x counts


== Upgrade Notice ==

= 2.0.3 =
* Fixed: Metabox update did not work properly in v2.0; New option to keep data on uninstall.


== Changelog ==

= 2.0.3 =
* New: Options to choose if you want to delete the Top 10 options and/or data when deleting the plugin
* Fixed: Metabox update did not work properly in v2.0
* Fixed: Duplicate include files
* Modified: In the mySQL tables, the blog_id default value is set to 1

= 2.0.2 =
* New: Option to display the daily posts count from midnight. This is enabled by default and mimics the original behaviour of the counter in Top 10 v1.x
* Modified: Posts are tracked hourly based on the time of the blog and not GMT. This was also the default behaviour of the counter in Top 10 v1.x
* Fixed: Default thumbnail location saved correctly on the Settings page.

= 2.0.1 =
* Bug fix release: Fixes 500/503 errors caused by 2.0.0

= 2.0.0 =
* New: Multisite support. If you're using multisite and have previously activated Top 10 on individual blogs in the network, then head over to **Top 10 Settings** and import the counts from the old Top 10 1.x tables to the new Top 10 v2.0 tables
* New: Fully extendable lookup query for the top lists. Now you can create your own functions in functions.php or in addon plugins to modify the mySQL query
* New: Option to use any of the inbuilt thumbnail sizes or create your own custom image size. If a custom size is chosen, then the plugin uses `add_image_size` to register the custom size. I recommend using <a href="https://wordpress.org/plugins/force-regenerate-thumbnails/">Force Regenerate Thumbnails</a>
* New: Actions and filters in the Top 10 Settings page and in the widget which allows for addons to add more settings
* Modified: Post tracking is now done on an hourly basis. Date limiting is also on an hourly basis. So, 1 day is actually the last 24 hours and not from midnight anymore!
* Modified: Update and View counts now use query variables instead of external JavaScript files. Check http://goo.gl/yemvyM for sample functions to restore the old method
* Modified: Activating the default styles option will automatically set the thumbnail width and height to 65px, disable author and excerpt and enable crop mode for the thumbnails
* Fixed: Fix schedule overwrite for the cron job
* Fixed: Incorrect permission lookup in the metabox

= 1.9.10.2 =
* Fixed: Schedules were overwritten when activating the maintenance cron job

= 1.9.10.1 =
* Fixed: Initialisation error for new installs

= 1.9.10 =
* New: Meta box on Edit post / page and similar screens that allow you to set the Top 10 (and my other plugins) specific thumbnail for the current post (different from the Featured thumb)
* New: Admins can edit the number of total views (find it in the same meta box as above)
* New: Turn of tracking for Editors
* New: Added w.org and github.com to list of allowed sites for timthumb
* New: Option to add quality settings for thumbnails created by timthumb
* Modified: Shortcode now accepts all the parameters that `tptn_pop_posts()` can take. For a full list of parameters, please check out the FAQ.
* Modified: Widget initialisation to latest standards
* Fixed: Localisation initialisation
* Fixed: Validation for hour and minute settings for the cron job
* New: Several new filters allowing you to hook in an modify the output without editting plugin files
* Modified: Reformatted code

= 1.9.9.2 =
* Fixed: Show count in widget was always checked
* Fixed: "List of post or page IDs to exclude from the results" did not work for more than one post
* Fixed: First image in the post was not detected in some cases. First image attached is now prioritised over image detection to speed things up

= 1.9.9.1 =
* Fixed: Maintenance cron wasn't running properly

= 1.9.9 =
* New: Default style to make those popular posts a little pretty. Choose **Thumbnails inline, before title** under 'Thumbnail options'
* New: Option to disable display of counts to non-admins. Check out the option 'Show number of views to non-admins'
* New: Option to display different text when there are no hits on the post on non single posts e.g. home page, archives, etc.
* New: Class `tptn_posts_widget` for the widgets
* Modified: Brought back the old columns "Views" in the Admin Posts and Pages which contains both the Overall and Daily counts
* Modified: New admin interface keeping mobile first in mind
* Modified: Optimised widgets loading
* Modified: Cron job will now delete entries from the daily table older than 90 days
* Fixed: mySQL error messages due to improper escaping
* Fixed: Plugin no longer overwrites cron schedules
* Modified: Lot's of code optimisation and cleanup

= 1.9.8.5 =
* Modified: Including the author in the list will now use the Display Name which is set under “Display name publicly as” in the User Profile page
* Fixed: If the Thumbnail meta field is omitted under Output Options, the plugin will automatically revert to its default value i.e. "post-image"
* Modified: Cleaner pagination when viewing the Top posts in the Admin section
* New: Function `get_tptn_post_count_only` to get just the post count. Use it by passing the Post ID and the type of count (total, daily or overall): `get_tptn_post_count_only($id = FALSE, $count = 'total')`
* New: Class `tptn_after_thumb` that wraps around all items of the list after the post thumbnail. This allows you to cleanly style all items to float to the right of the thumbnail
* Modified: Updated timthumb

= 1.9.8.4 =
* Fixed PHP notices on Admin pages

= 1.9.8.3 =
* Fixed: Daily count was selecting an extra date when using the widget
* Fixed: Default settings for the widget weren't initiated correctly in some cases
* Modified: Admin columns of Total and Daily views will be sorted by descending order by default. Click again to sort in ascending order
* Modified: Admin columns are fixed to 100px width by default instead of `auto`.

= 1.9.8.2 =
* New: Option to add author
* New: More options for the Widgets to configure the post lists
* Modified: Shortcodes are now stripped from excerpts
* New: Added *s3.amazonaws.com* to list of allowed sites that timthumb can fetch images from
* Fixed: Counter was not always displayed on posts
* New: All Posts / All Pages have separate *sortable* columns for total and daily counts
* Fixed: Warning messages with WP_DEBUG mode ON

= 1.9.8.1 =
* Fixed: Correct numbers of posts were not being fetched

= 1.9.8 =
* New: Custom post support. Choose which custom post types to display in the top posts
* New: More display options. Select which archives you want to display the post count
* New: Option to open links in new window
* New: Option to add nofollow attribute to links
* New: Option to exclude posts by ID in the list of top posts being displayed
* New: Option to prevent display of the Visit Count on posts by ID
* New: Option to choose between using CSS styles or HTML attributes for thumbnail width and height. *HTML width and height attributes are default*
* New: Option to restrict the title to fixed number of characters
* New: Option to add the date to the list
* Modified: Numbers are now formatted based on the locale
* Fixed: Plugin will now create thumbnails from the first image in gallery custom posts

= 1.9.7 =
* New: Option to toggle using jQuery ON to track counts. Potential fix for counters not working.

= 1.9.6 =
* Fixed: Daily count was not updated

= 1.9.5 =
* New: CSS class `tptn_title` that can be used to style the title of the posts
* New: Option to disable Daily or Overall counters
* Fixed: Counter to work with different directory structures. *Thanks Nathan for the fix*
* Fixed: To make it work with W3 Total Cache. *Thanks Angelo for the fix*
* Modified: timthumb will now work if you have JetPack Proton activated

= 1.9.4 =
* Fixes a bug in the widget introduces in 1.9.3

= 1.9.3 =
* Important security update: Fixed possible XSS vulnerability
* Fixed: Exclude categories was not excluding posts correctly
* New: Classes `tptn_posts` and `tptn_posts_daily` for the widgets that let you easily style the lists

= 1.9.2 =
* New: Top 10 now has its own menu in the administration area. Access settings and view your top posts directly under the new menu: "Top 10"
* New: New classes **tptn_counter** and **tptn_list_count** to style the displayed count
* New: New option "Always display latest count for the post" to not use JavaScript to display the counts for a post. This speeds up since no external JS file is used to display the count. Ideal for those not using caching plugins or are not particularly worried if the counts are slightly older.
* Fixed: PHP notices when WP_DEBUG is turned on
* Modified: Updated timthumb.php

= 1.9.1 =
* Fixed: Plugin will now only reschedule the cron job if there any settings are changed related to it
* Modified: If timthumb is disabled, WordPress post thumbnails are no longer resized using timthumb
* Modified: Extra check for post featured thumbnails to ensure that the src is not missed

= 1.9 =
* New: Option to use timthumb to resize thumbnails
* New: New variable **%overallcount%** that will display the total pageviews on the blog across all posts
* New: Post thumbnails are now properly resized based on width and height settings in the Top 10 settings page
* New: Customise what to display when there are no top posts detected
* New: New scheduled maintenance to clear up daily tables and optimise performance
* New: Custom CSS code to style the output. Check out the available styles in the <a href="http://wordpress.org/extend/plugins/top-10/faq/">FAQ</a>.
* Modified: New "default.png" file based on from KDE’s <a href="http://www.oxygen-icons.org/">Oxygen icon set</a>
* Modified: Dashboard list of posts now displays all the top posts and pages instead of the filtered list based on Settings.
* Modified: Dashboard widget now has options to customise the widget. Old widgets have been deleted
* Modified: When fetching the first image, plugin ignores external images
* Modified: Minor performance tweaks

= 1.8.1 =
* Fixed: Dashboard widgets linking

= 1.8 =
* New: Support for <a href="https://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails</a> plugin
* New: Thumbnail settings now reflect max width and max height instead of fixed width and height
* New: Option to display thumbnails before or after the title
* New: Option to not display thumbnails instead of the default thumbnail
* New: Counts are now neatly formatted with commas
* Modified: Minor tweaks to improve performance

= 1.7.6 =
* Fixed: Bug with Daily posts widget created an extra header tag in certain themes

= 1.7.5 =
* New: Now supports multiple WordPress widgets

= 1.7 =
* New: Exclude posts in the top lists from select categories
* Modified: Performance improvements
* Modified: Better compatibility with the latest versions of WordPress. If you are using the sidebar widgets, please readd them to your theme under Appearance > Widgets

= 1.6.3 =
* Fixed: PHP errors on certain installs
* New: Dutch language

= 1.6.2 =
* Fixed: Multiple rows being created for same ID
* Fixed: Counter display
* New: New button to clear the duplicate rows in the tables
* Fixed: Top 10 should be lighter on the server now

= 1.6.1 =
* Turned the credit option to false by default. This setting won't effect current users.
* Turned off borders on post thumbnails. You can customise the CSS class "tptn_thumb" to style the post thumbnail.
* The plugin will now display a list of changes in the WordPress Admin > Plugins area whenever an update is available
* Fixed: Display of caching plugin compliant daily top posts lists

= 1.6 =
* New: Added support for excerpts and detection of first image in the post
* New: Daily posts are tracked using the blog time instead of server time
* Fixed: On the first visit, display 1 instead of 0
* Fixed: Fixed uninstall script

= 1.5.3 =
* New: You can now use HTML in the counter display

= 1.5.2 =
* Fixed: Fixed display of post thumbnails using postmeta field

= 1.5.1 =
* Fixed some compatibility issues with WordPress 2.9 and YARPP

= 1.5 =
* Added support for post thumbnails feature of WordPress 2.9

= 1.4.1 =
* Fixed compatibility with WordPress 2.9
* Fixed XHTML validation errors in output code
* Added buttons to reset post count of overall and daily posts

= 1.4 =
* Added localisation support
* Separate options to display number of views on posts and pages

= 1.3 =
* "Daily Popular" can now be selected over user selectable number of days.
* Option to turn off display of number of pageviews in popular posts list
* Option to make "Daily Popular" list compatible with caching plugins
* Posts > Top 10 page to view detailed list of popular posts

= 1.2 =
* Do not display Drafts in Related Posts anymore
* Option to disable tracking author visits on their own posts
* Display top posts for the current day

= 1.1 =
* Added the Popular Posts sidebar widget in your theme. Find it under <strong>Appearance > Widgets</strong>
* Uses JavaScript by default to count. Hence, better support for different caching plugins
* Change format to display count. Now, a single textarea instead of two text boxes.
* Added WordPress 2.7 Dashboard widget to display popular posts on your Dashboard

= 1.0.1 =
* Release


== Installation ==

= WordPress install =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Top 10" and select "Keyword" from the dropdown

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of top-10.zip to wp-content/plugins/ folder. You should get a folder called top-10.

3. Activate the Plugin in WP-Admin.

4. Go to **Top 10** to configure

5. Go to **Appearance &raquo; Widgets** to add the Popular Posts sidebar widget to your theme

6. Go to **Top 10 &raquo; Overall Popular Posts** and **Top 10 &raquo; Daily Popular Posts** to view the list of popular posts


== Frequently Asked Questions ==

If your question isn't listed here, please create a new post in the <a href="http://wordpress.org/support/plugin/top-10">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced *paid* support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

= How can I customise the output? =

Several customization options are available via the Settings page in WordPress Admin. You can access this via <strong>Settings &raquo; Top 10</strong>

The plugin also provides you with a set of CSS classes that allow you to style your posts by adding code to the *style.css* sheet. In a future version, I will be adding in CSS support within the plugins Settings page.

The following CSS classes / IDs are available:

* **tptn_related**: ID of the main wrapper `div`. This is only displayed on singular pages, i.e. post, page and attachment

* **tptn_related**: Class of the main wrapper `div`. If you are displaying the related posts on non-singular pages, then you should style this

* **tptn_title**: Class of the `span` tag for title of the post

* **tptn_excerpt**: Class of the `span` tag for excerpt (if included)

* **tptn_thumb**: Class of the post thumbnail `img` tag

* **tptn_list_count**: Class of the `span` tag for post count in top posts list

* **tptn_counter**: Class of the `div` tag that wraps the post count that is driven by the field "Format to display the count in: " under 'Output Options'

For more information, please visit http://ajaydsouza.com/wordpress/plugins/top-10/

= Shortcodes =

You can insert the popular posts anywhere in your post using the `[tptn_list]` shortcode. The plugin takes three optional attributes `limit`, `heading` and `daily` as follows:

`[tptn_list limit="5" heading="1" daily="0"]`

*limit* : Maximum number of posts to return. The actual number displayed may be lower depending on the category / post exclusion settings.

*heading* : By default, the heading you specify in **Title of popular posts:** under **Output options** will be displayed. You can override this by specifying your own heading e.g.

`
<h3>Top posts</h3>
[tptn_list limit="2" heading="0"]
`
*daily* : If set to 1, then the shortcode will return the daily popular posts list


You can also display the number of visits using the `[tptn_views]` shortcode. The plugin takes one optional attribute `daily` as follows:

`[tptn_views daily="0"]`

*daily* : If set to 1, then the shortcode will return the number of _daily_ views


= Filters =

The plugin includes the following filters that allows you to customise the output for several section using <a href="http://codex.wordpress.org/Function_Reference/add_filter">add_filter</a>.

*tptn_heading_title* : Filter for heading title of the posts. This is the text that you enter under *Output options > Title of related posts*

*tptn_title* : Filter for the post title for each of the related posts

I'll be adding more filters eventually. If you are looking for any particular filter do raise a post in the <a href="http://wordpress.org/support/plugin/contextual-related-posts">support forum</a> requesting the same.

= How does the plugin select thumbnails? =

The plugin selects thumbnails in the following order:

1. Post Thumbnail image: The image that you can set while editing your post in WordPress &raquo; New Post screen

2. Post meta field: This is the meta field value you can use when editing your post. The default is `post-image`

3. First image in the post: The plugin will try to fetch the first image in the post

3. Video Thumbnails: Meta field set by <a href="https://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails</a>

4. Default Thumbnail: If enabled, it will use the default thumbnail that you specify in the Settings screen

The plugin uses <a href="http://www.binarymoon.co.uk/projects/timthumb/">timthumb</a> to generate thumbnails by default. Depending on the configuration of your webhost you might run into certain problems. Please check out <a href="http://www.binarymoon.co.uk/2010/11/timthumb-hints-tips/">the timthumb troubleshooting page</a> regarding permission settings for the folder and files.

= Manual install =

You may choose to not display the post count automatically. If you do so, then in order to display the post count, you will need to add `<?php if(function_exists('echo_tptn_post_count')) echo_tptn_post_count(); ?>`.

In order to display the most popular posts, you will need to add `<?php if(function_exists('tptn_show_pop_posts')) tptn_show_pop_posts(); ?>`.

In order to display the most popular posts, you will need to add `<?php if(function_exists('tptn_show_daily_pop_posts')) tptn_show_daily_pop_posts(); ?>`.

You can also use the WordPress Widgets to display the popular posts in your sidebar / other widgetized areas of your theme.

= Can this plugin replace Google Analytics? =

Never. This plugin is designed to only track the number of pageviews on your blog posts and display the same. It cannot replace Google Analytics or any other full fledged statistics application.

= How does the scheduled maintenance work maintenance work? =

When you enabled the scheduled maintenance, Top 10 will create a cron job that will run at a predefined interval and truncate the `wp_top_ten_daily` table.
*Note: If you enable this option, WordPress will execute this job when it is scheduled the first time*

= How to make the columns on the Custom Posts pages sortable? =
Add the following code to your functions.php file of your theme.

`
add_filter( 'manage_edit-{$cpt}_sortable_columns', 'tptn_column_register_sortable' );
`

Replace `{$cpt}` by the slug of your custom post type. E.g. to make the columns on your 'projects' post type sortable, you will need to add:
`
add_filter( 'manage_edit-projects_sortable_columns', 'tptn_column_register_sortable' );
`

