=== Top 10  - Popular posts plugin for WordPress ===
Tags: top 10, counter, popular posts, top posts, daily popular, page views, statistics
Contributors: Ajay
Donate link: http://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 3.0
Tested up to: 3.6
License: GPLv2 or later


Track daily and total visits on your blog posts and display the count as well as popular posts.

== Description ==

WordPress doesn't count page views by default. <a href="http://ajaydsouza.com/wordpress/plugins/top-10/">Top 10</a> will count the number of page views on your single posts on a daily as well as overall basis. You can then display the page view count on individual posts and pages as well as display a list of most popular posts based on page views.

Includes a sidebar widget to display the popular posts. And, all settings can be configured from within your WordPress Admin area itself! You can choose to disable tracking of author visits on their own posts.

= Features =
* Counts daily and total page views on single posts and pages
* Display the count on the single posts and/or pages. Customize the text that can be displayed
* Display a list of daily and/or overall popular posts by page count. You can choose how many posts are to be displayed
* Thumbnail support
	* Support for WordPress post thumbnails
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via <a href="http://codex.wordpress.org/Custom_Fields">WordPress meta fields</a>
	* Use timthumb to resize images
* Sidebar widgets available for daily popular and overall popular posts
* Exclude posts from select categories from appearing in the top posts list
* View list of daily and/or overall popular posts from within the dashboard
* Output wrapped in CSS classes that allows you to style the list. You can enter your custom CSS styles from within WordPress Admin area
* Customise which HTML tags to use for displaying the output in case you don't prefer the default `list` format
* Clean uninstall if you choose to delete the plugin from within WP-Admin
* Works with caching plugins like WP-Super-Cache, W3 Total Cache or Quick Cache


== Screenshots ==

1. Top-10 options - General options
2. Top-10 options - Output options
3. Top-10 options - Custom styles
4. Top-10 options - Maintenance
5. Top-10 widget options


== Upgrade Notice ==

= 1.9.4 =
* IMPORTANT security update: Fixed possible XSS vulnerability; bug fixes


== Changelog ==

= 1.9.4 =
* Fixes a bug in the widget introduces in 1.9.3


= 1.9.3 =
* Important security update: Fixed possible XSS vulnerability
* Fixed: Exclude categories was not excluding posts correctly
* Added: Classes `tptn_posts` and `tptn_posts_daily` for the widgets that let you easily style the lists

= 1.9.2 =
* Added: Top 10 now has its own menu in the administration area. Access settings and view your top posts directly under the new menu: "Top 10"
* Added: New classes **tptn_counter** and **tptn_list_count** to style the displayed count
* Added: New option "Always display latest count for the post" to not use JavaScript to display the counts for a post. This speeds up since no external JS file is used to display the count. Ideal for those not using caching plugins or are not particularly worried if the counts are slightly older.
* Fixed: PHP notices when WP_DEBUG is turned on
* Modified: Updated timthumb.php

= 1.9.1 =
* Fixed: Plugin will now only reschedule the cron job if there any settings are changed related to it
* Modified: If timthumb is disabled, WordPress post thumbnails are no longer resized using timthumb
* Modified: Extra check for post featured thumbnails to ensure that the src is not missed

= 1.9 =
* Added: Option to use timthumb to resize thumbnails
* Added: New variable **%overallcount%** that will display the total pageviews on the blog across all posts
* Added: Post thumbnails are now properly resized based on width and height settings in the Top 10 settings page 
* Added: Customise what to display when there are no top posts detected
* Added: New scheduled maintenance to clear up daily tables and optimise performance
* Added: Custom CSS code to style the output. Check out the available styles in the <a href="http://wordpress.org/extend/plugins/top-10/faq/">FAQ</a>.
* Modified: New "default.png" file based on from KDE’s <a href="http://www.oxygen-icons.org/">Oxygen icon set</a>
* Modified: Dashboard list of posts now displays all the top posts and pages instead of the filtered list based on Settings.
* Modified: Dashboard widget now has options to customise the widget. Old widgets have been deleted
* Modified: When fetching the first image, plugin ignores external images
* Modified: Minor performance tweaks

= 1.8.1 =
* Fixed: Dashboard widgets linking

= 1.8 =
* Added: Support for <a href="https://wordpress.org/extend/plugins/video-thumbnails/">Video Thumbnails</a> plugin
* Added: Thumbnail settings now reflect max width and max height instead of fixed width and height
* Added: Option to display thumbnails before or after the title
* Added: Option to not display thumbnails instead of the default thumbnail
* Added: Counts are now neatly formatted with commas
* Modified: Minor tweaks to improve performance

= 1.7.6 =
* Fixed: Bug with Daily posts widget created an extra header tag in certain themes

= 1.7.5 =
* Added: Now supports multiple WordPress widgets

= 1.7 =
* Added: Exclude posts in the top lists from select categories
* Modified: Performance improvements
* Modified: Better compatibility with the latest versions of WordPress. If you are using the sidebar widgets, please readd them to your theme under Appearance > Widgets

= 1.6.3 =
* Fixed: PHP errors on certain installs
* Added: Dutch language

= 1.6.2 =
* Fixed: Multiple rows being created for same ID
* Fixed: Counter display
* Added: New button to clear the duplicate rows in the tables
* Fixed: Top 10 should be lighter on the server now

= 1.6.1 =
* Turned the credit option to false by default. This setting won't effect current users.
* Turned off borders on post thumbnails. You can customise the CSS class "tptn_thumb" to style the post thumbnail.
* The plugin will now display a list of changes in the WordPress Admin > Plugins area whenever an update is available
* Fixed: Display of caching plugin compliant daily top posts lists 

= 1.6 =
* Added: Added support for excerpts and detection of first image in the post
* Added: Daily posts are tracked using the blog time instead of server time
* Fixed: On the first visit, display 1 instead of 0
* Fixed: Fixed uninstall script

= 1.5.3 =
* Added: You can now use HTML in the counter display

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

1. Download the plugin

2. Extract the contents of top-10.zip to wp-content/plugins/ folder. You should get a folder called top-10.

3. Activate the Plugin in WP-Admin. 

4. Go to **Top 10** to configure

5. Go to **Appearance &raquo; Widgets** to add the Popular Posts sidebar widget to your theme

6. Go to **Top 10 &raquo; Overall Popular Posts** and **Top 10 &raquo; Daily Popular Posts** to view the list of popular posts



== Frequently Asked Questions ==

If your question isn't listed here, please post a comment at the <a href="http://wordpress.org/support/plugin/top-10">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

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

== Wishlist ==

Below are a few features that I plan on implementing in future versions of the plugin. However, there is no fixed time-frame for this and largely depends on how much time I can contribute to development.

* Select random posts if there are no similar posts
* Top posts by comments
* Smart tracking of hits, i.e. no update on page reload of same visitors within a certain time period
* Shortcode support
* Exclude display on select categories and tags
* Exclude display on select posts 
* Custom post support
* Multi-site support
* Ready-made styles
* Upload your own default thumbnail


If you would like a feature to be added, or if you already have the code for the feature, you can let us know by <a href="http://wordpress.org/support/plugin/top-10">posting in this forum</a>.

