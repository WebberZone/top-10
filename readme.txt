=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics, tracker
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 4.1
Tested up to: 4.7
License: GPLv2 or later

Track daily and total visits on your blog posts. Display the count as well as popular and trending posts.

== Description ==

WordPress doesn't have an in-built system to track page views or displaying popular posts. [Top 10](https://webberzone.com/plugins/top-10/) is an easy to use, yet, powerful WordPress plugin that will count the number of page views of your posts, pages and any custom post types. You can then display the page view counts as well as display your most popular posts.

Top 10 will add a widget that you can use to display the popular posts list.

Although several similar plugins exist today, Top 10 is one of the most feature rich popular post plugins with support for thumbnails, shortcodes, widgets, custom post types and CSS styles. The inbuilt caching system also helps reduce server load by caching your popular posts output. The tracking uses ajax and is thus compatible with most popular caching plugins.

Top 10 also has powerful API and is fully extendable with WordPress actions and filters to allow you easily extend the code base to add new features or tweak existing ones.

= Features =

* **Page counter**: Counts page views on single posts, pages and *custom post types* on an hourly basis which can then be easily displayed automatically, using shortcodes or functions
* **Popular posts**: Display a list of popular posts either for total counts or for a custom period. You can choose how many posts are to be displayed along with loads of other customisation options
* **Widget ready**: Sidebar widgets available for daily popular and overall popular posts. Highly customizable widgets to control what you want to display in the list of posts
* **Shortcodes**: The plugin includes two shortcodes `[tptn_list]` and `[tptn_views]` to display the posts list and the number of views respectively
* **Thumbnail support**
	* Support for WordPress post thumbnails. Top 10 will create a custom image size (`tptn_thumbnail`) with the dimensions specified in the Settings page
	* Auto-extract the first image in your post to be displayed as a thumbnail
	* Manually enter the URL of the thumbnail via [WordPress meta fields](http://codex.wordpress.org/Custom_Fields). Specify this using the meta box in your Edit screens.
* **Exclusions**: Exclude posts from select categories from appearing in the top posts list. Also exclude posts by ID from appearing in the list
* **Styles**: The output is wrapped in CSS classes which allows you to easily style the list. You can enter your custom CSS styles from within WordPress Admin area or use the style included.
* **Admin interface**: View list of daily and/or overall popular posts from within the dashboard. Top 10 also adds two sortable columns to your All Posts and All Pages pages in your WordPress Admin area
* **Works with caching plugins** like WP-Super-Cache, W3 Total Cache or Quick Cache
* **Extendable code**: Top 10 has tonnes of filters and actions that allow any developer to easily add features, edit outputs, etc.

= Donations =

I spend a significant amount of my free time maintaining, updating and more importantly supporting this plugin. If you have been using this plugin and find this useful, do consider making a donation. This helps me pay for my hosting and domains.

= Translations =
Top 10 is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.


= Contribute =

Top 10 is also available on [Github](https://github.com/ajaydsouza/top-10)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request.


== Screenshots ==

1. Top 10 options - General options
2. Top 10 options - Counter and Tracker options
3. Top 10 options - Popular post list options
4. Top 10 options - Thumbnail options
5. Top 10 options - Styles
6. Top 10 options - Maintenance
7. Top 10 widget options
8. Top 10 Meta box on the Edit Post screen
9. WordPress Multisite: Import Top 10 v1.x counts
10. Reset count and tools
11. Top 10 - Popular posts view in Admin

== Installation ==

= WordPress install (the easy way) =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Top 10"

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the plugin

2. Extract the contents of top-10.zip to wp-content/plugins/ folder. You should get a folder called top-10.

3. Activate the Plugin in WP-Admin.

4. Go to **Top 10** to configure

5. Go to **Appearance &raquo; Widgets** to add the Popular Posts sidebar widget to your theme

6. Go to **Top 10 &raquo; View Popular Posts** to view the list of popular posts


== Frequently Asked Questions ==

If your question isn't listed here, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/top-10). It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).


= How can I customise the output? =

Several customization options are available via the Settings page in WordPress Admin. You can access this via <strong>Settings &raquo; Top 10</strong>

The main CSS classes include:

* **tptn_posts** and **tptn_posts_daily**: Class of the main wrapper `div`. If you are displaying the related posts on non-singular pages, then you should style this

* **tptn_title**: Class of the `span` tag for title of the post

* **tptn_excerpt**: Class of the `span` tag for excerpt (if included)

* **tptn_thumb**: Class of the post thumbnail `img` tag

* **tptn_list_count**: Class of the `span` tag for post count in top posts list

* **tptn_counter**: Class of the `div` tag that wraps the post count that is driven by the field "Format to display the count in: " under 'Output Options'

= Shortcodes =

`[tptn_list]` lets you insert the popular posts anywhere in your post content. It takes three main optional attributes `limit`, `heading` and `daily` as follows:

*limit* : Maximum number of posts to return. The actual number displayed may be lower depending on the category / post exclusion settings.

*heading* : Set to 0 to disable the heading specified in **Title of popular posts:** under **Output options**

*daily* : If set to 1, then the shortcode will return the daily popular posts list

In addition to these attributes, the shortcode can take all the options as attributes. To see the detailed list take a look at the function `tptn_default_options()` in **top-10.php** file


`[tptn_views]` lets you display the number of visits. The plugin takes one optional attribute `daily` as follows:

*daily* : If set to 1, then the shortcode will return the number of _daily_ views


= Manual install =

The below functions need to be added by editing your theme files where you wish to display them.

Display the post count with `<?php if ( function_exists( 'echo_tptn_post_count' ) ) { echo_tptn_post_count(); } ?>`

Display the overall most popular posts with `<?php if ( function_exists( 'tptn_show_pop_posts' ) ) { tptn_show_pop_posts(); } ?>`

Display the daily/custom period popular posts with `<?php if ( function_exists( 'tptn_show_daily_pop_posts' ) ) { tptn_show_daily_pop_posts(); } ?>`

You can also use the WordPress Widgets to display the popular posts in your sidebar / other widgetized areas of your theme

View [examples of the plugin API](https://gist.github.com/ajaydsouza/c8defd4b46e53240e376) to fetch the popular posts

= Can this plugin replace Google Analytics? =

No. Top 10 has been designed to only track the number of page-views on your blog posts and display the same. It isn't designed to replace Google Analytics or any other full fledged statistics application.

= How does the scheduled maintenance work? =

When you enabled the scheduled maintenance, Top 10 will create a cron job that will run at a predefined interval and clean up old entries from the `wp_top_ten_daily` table.
*Note: If you enable this option, WordPress will execute this job when it is scheduled the first time*

= How to make the columns on the Custom Post Type admin pages sortable? =
Add the following code to your functions.php file of your theme.

`
add_filter( 'manage_edit-{$cpt}_sortable_columns', 'tptn_column_register_sortable' );
`

Replace `{$cpt}` by the slug of your custom post type. E.g. to make the columns on your 'projects' post type sortable, you will need to add:
`
add_filter( 'manage_edit-projects_sortable_columns', 'tptn_column_register_sortable' );
`


== Changelog ==

= 2.4.4 =

* Enhancements:
	* Changed tracker type to Query based for better compatibility

* Bug fixes:
	* Security fix: Potential SQL injection vulnerability. Reported by [DefenseCode ThunderScan](http://www.defensecode.com/)
	* Revisions no longer displayed in the "View Popular Posts" screen in admin area

= 2.4.3 =

* Bug fixes:
	* Fatal error when running PHP 7.1.x

= 2.4.2 =

* Bug fixes:
	* The plugin will no longer generate any notices if post author is missing
	* Fixed T_FUNCTION error in admin area on blogs running on PHP versions before 5.3
	* Fixed bug where any special characters in the post title would break the output

= 2.4.1 =

* Bug fixes:
	* Fixes fatal error caused on installs which have versions below PHP5.6

= 2.4.0 =

* Features:
	* New tracker using a properly enqueued `.js` file. Two inbuilt options to use query variables or ajaxurl to process the counts
	* Shortcode and the widget now have an added parameter for 'offset'. This is useful if you would like to display different widgets/shortcodes but not always start from the first post

* Bug fixes:
	* Attachments now work with the widget and elsewhere
	* New tracker now works when jQuery is loaded in the footer
	* Don't add tracker code when previewing in customizer
	* Doesn't report an error if no author is assigned to a post

* Deprecated:
	* `tptn_add_tracker` and `tptn_add_viewed_count` have been deprecated. These should no longer be needed with the new tracker option.
	* wick script in Settings page which was used for fetching category slugs. You should now use the category name (prompted automatically). Slugs will be automatically converted into names.

= 2.3.2 =

* Bug fixes:
	* Sanitized several unsanitized post and get requests

* Deprecated:
	* External PHP file tracking option introduced in v2.3.0 in line with wordpress.org plugin repository listing requirements.

= 2.3.1 =

* Bug fixes:
	* Potential CSRF issue fixed in admin area

= 2.3.0 =

* Features:
	* Preliminary support for PolyLang
	* Search box and post type filter added in Admin &raquo; View Popular Posts screen
	* Link to Daily Popular posts screen under Top 10 menu in admin area
	* `post_types` parameter now supports comma-separated list of post types. To fetch all built-in post types use 'all'
	* New option to use the external and more efficient javascript file for tracking
	* New function `tptn_add_tracker` to manually include the tracking code. This is useful if your theme doesn't have `the_content` function that Top 10 filters to add the tracker code

* Enhancements:
	* Viewing drafts will no longer increment the counter
	* When using the Left Thumbs style, each widget instance includes the CSS code to display the correct thumbnail size

* Bug fixes:
	* Missing `DISTINCT` keyword in query resulting in duplicate entries in some cases
	* PHP Notice in Widget on empty search and 404 pages
	* Incorrect notice that Contextual Related Posts is installed on Edit Posts pages
	* `tptn_show_daily_pop_posts()` without arguments did not display daily posts
	* Using Exclude categories returned incorrect counts and excluded non-posts
	* Incorrect count on the Admin &raquo; View Popular Posts screen causing incorrect pagination
	* Incorrect thumbnail size being pulled out in some instances
	* Multiple widget instances incorrectly used the same cache
	* Incorrect text domain was initialised

= 2.2.4 =

* Enhancements:
	* Changed text domain to `top-10` in advance of translate.wordpress.org translation system
	* Improved support for WPML. If available, same language posts will be pulled by default. To restrict to the same language [add this code](https://gist.github.com/ajaydsouza/9b1bc56cec79295e784c) to your theme's functions.php file

= 2.2.3 =

* Bug fixes:
	* Shortcode with "exclude_categories" argument works again

= 2.2.2 =

* Bug fixes:
	* Fixed array declaration to support PHP < 5.4

= 2.2.0 =

* Features:
	* Caching system using the Transients API. By default the cache is refreshed every hour
	* Styles interface lets you select between No styles, Left Thumbs (previously the default style) and Text Only
	* Option to limit posts only with a specified date range
	* Option in Top 10 meta box to exclude display of popular posts in widget if needed
	* Option in Top 10 meta box to exclude post from popular posts list
	* Cleaner interface to view popular posts in the admin area

* Enhancements:
	* `strict_limit` is true by default for `get_tptn_pop_posts()`
	* Option to turn off the meta box for everyone or just non-admins
	* Contributors & above can also update the visit count in the meta box if this is enabled for them
	* Category exclusion now works via a filter function vs. multiple lookups, thereby reducing the number of database queries

* Bug fixes:
	* Potential bug when the $wp variable was not detected in rare situations
	* In rare cases category exclusion failed when `term_id` didn't match `term_taxonomy_id`

* Deprecated:
	* `ald_tptn_rss`: Use `tptn_rss_filter` instead
	* `ald_tptn_hook` deprecated and renamed to `tptn_cron_hook`
	* `tptn_manage` and `tptn_manage_daily` which were used to render the admin popular posts screens

= 2.1.0 =

* Features:
	* New: Button in Top 10 settings page to merge posts across blog ID 0 and 1
	* New: Function & filter `get_tptn_pop_posts` that can be used to fetch the popular posts as an object or array. Perfect if you want to write custom code in your theme
	* New: Support for WPML to return the correct language ID. Thanks to Tony Flags' <a href="https://wordpress.org/support/topic/top-10-and-languages-in-wpml?replies=11#post-6622085">code snippet</a>.
	* New: Filter `tptn_list_count` to modify the formatted list count. See a <a href="https://gist.github.com/ajaydsouza/9f04c26814414a57fab4">working example</a>
	* New: Post types can now be selected in the widget. This allows you to select top posts by post type

* Enhancements:
	* Modified: Plugin will attempt to pull the correct size image when fetching the first image in a post
	* Modified: Deprecated "Always display latest post count in the daily lists"
	* Modified: timthumb has been deprecated. The script is no longer packaged within Top 10

* Bug fixes:
	* Fixed: Bug in tracking code when not using Ajax
	* Fixed: Bug in admin column did not check for the blog_id
	* Fixed: Bug where default thumbnail location was not correctly saved
	* Fixed: Incorrect thumbnail was pulled on attachment pages
	* Fixed: blog_id column of the database is correctly initialised as `DEFAULT '1'`

= 2.0.3 =

* Features:
	* New: Options to choose if you want to delete the Top 10 options and/or data when deleting the plugin

* Bug fixes:
	* Fixed: Metabox update did not work properly in v2.0
	* Fixed: Duplicate include files
	* Modified: In the mySQL tables, the blog_id default value is set to 1

= 2.0.2 =

* Features:
	* New: Option to display the daily posts count from midnight. This is enabled by default and mimics the original behaviour of the counter in Top 10 v1.x

* Enhancements:
	* Modified: Posts are tracked hourly based on the time of the blog and not GMT. This was also the default behaviour of the counter in Top 10 v1.x

* Bug fixes:
	* Fixed: Default thumbnail location saved correctly on the Settings page.

= 2.0.1 =

* Bug fixes
	* 500/503 errors caused by 2.0.0

= 2.0.0 =

* Features:
	* New: Multisite support. If you're using multisite and have previously activated Top 10 on individual blogs in the network, then head over to **Top 10 Settings** and import the counts from the old Top 10 1.x tables to the new Top 10 v2.0 tables
	* New: Fully extendable lookup query for the top lists. Now you can create your own functions in functions.php or in addon plugins to modify the mySQL query
	* New: Option to use any of the inbuilt thumbnail sizes or create your own custom image size. If a custom size is chosen, then the plugin uses `add_image_size` to register the custom size. You will need to resize your thumbnails after activating this option
	* New: Actions and filters in the Top 10 Settings page and in the widget which allows for addons to add more settings

* Enhancements:
	* Modified: Post tracking is now done on an hourly basis. Date limiting is also on an hourly basis. So, 1 day is actually the last 24 hours and not from midnight anymore!
	* Modified: Update and View counts now use query variables instead of external JavaScript files. Check http://goo.gl/yemvyM for sample functions to restore the old method
	* Modified: Activating the default styles option will automatically set the thumbnail width and height to 65px, disable author and excerpt and enable crop mode for the thumbnails

* Bug fixes:
	* Fixed: Fix schedule overwrite for the cron job
	* Fixed: Incorrect permission lookup in the metabox

For previous changelog entries, please refer to the separate changelog.txt file


== Upgrade Notice ==

= 2.4.0 =
* Major release. New features and several bug fixes and a new tracker. Please do verify your settings after the upgrade.
Check the Changelog for more details

