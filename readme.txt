=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics, tracker
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: trunk
Requires at least: 4.1
Tested up to: 4.9
License: GPLv2 or later

Track daily and total visits on your blog posts. Display the count as well as popular and trending posts.

== Description ==

WordPress doesn't have an in-built system to track page views or displaying popular posts. [Top 10](https://webberzone.com/plugins/top-10/) is an easy to use, yet, powerful WordPress plugin that will count the number of page views of your posts, pages and any custom post types. You can then display the page view counts as well as display your most popular posts.

Top 10 adds two widgets that you can use to display a list of popular posts and the counta cross all your blog posts.

Although several similar plugins exist today, Top 10 is one of the most feature-rich popular post plugins with support for thumbnails, shortcodes, widgets, custom post types and CSS styles. The inbuilt caching system also helps reduce server load by caching your popular posts output. The tracking uses ajax and is thus compatible with most popular caching plugins.

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

= GDPR =
Top 10 is GDPR compliant as it doesn't collect any personal data about your visitors when installed out of the box. You can see the data the plugin stores in the `wp_top_ten` and `wp_top_ten_daily` tables in the database. Note: the prefix `wp` might be different if you have changed it from the default.

YOU ARE RESPONSIBLE FOR ENSURING THAT ALL GDPR REQUIREMENTS ARE MET ON YOUR WEBSITE.

= Donations =

I spend a significant amount of my free time maintaining, updating and more importantly supporting this plugin. If you have been using this plugin and find this useful, do consider making a donation. This helps me pay for my hosting and domains.

= Translations =
Top 10 is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.


= Contribute =

Top 10 is also available on [Github](https://github.com/ajaydsouza/top-10)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request. Please don't use that for support requests.


== Screenshots ==

1. Top 10 options - General options
2. Top 10 options - Counter and Tracker options
3. Top 10 options - Popular post list options
4. Top 10 options - Thumbnail options
5. Top 10 options - Styles
6. Top 10 options - Maintenance
7. Top 10 widget options
8. Top 10 Meta box on the Edit Post screen
9. Reset count and tools
10. Top 10 - Popular posts view in Admin

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

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/top-10/faq/) and the [FAQ on the WebberZone knowledgebase](https://webberzone.com/support/section/top-10/).
It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).


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

In addition to these attributes, the shortcode can take all the options as attributes. To see the detailed list take a look at the function `tptn_register_settings()` in **includes/admin/register-settings**


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

= 2.5.6 =

* Features:
	* Added new button under Tools to recreate the PRIMARY KEYs

= 2.5.5 =

* Bug fixes:
	* Fixed missing semi-colon in tracker code
	* Multi-site tracking failed in old installs where the PRIMARY KEY was not properly updated in the database. DB version has been updated to 6.0 to initiate primary key across all installs

= 2.5.4 =

* Features:
	* New button in the Tools page to delete the pre-2.5.x settings

* Enhancements:
	* New function `tptn_trim_char` replaces `tptn_max_formatted_content` which should now support non-English characters as well
	* Counts in the "Popular Posts" page are now number formatted

* Bug fixes:
	* Fixed PHP Illegal string offset error

= 2.5.3 =

* Bug fixes:
	* Fixed number of posts displayed in the popular posts list

= 2.5.2 =

* Bug fixes:
	* Settings / Thumbnail / Thumbnail size did not display the correct option even though the admin had this correctly saved

= 2.5.1 =

* Bug fixes:
	* Custom CSS did not get upgraded or saved properly
	* Uninstalling the plugin will delete the settings properly

= 2.5.0 =

* Features:
	* Upgrade old options to Settings API
	* New widget to display the overall count i.e. visits across all your site posts
	* Added a filter to override the number of days that maintenance considers to delete the database entries
	* New option to disable tracking for logged in users. Find "Track logged-in users" under the "Counter/Tracker" tab in the settings screen

* Enhancements:
	* Make $ajax_tptn_tracker global. Contributed by [@pkirk](https://github.com/pkirk)

* Bug fixes:
	* Make sure that $post object is available in `tptn_pc_content` filter. Contributed by [@mtws](https://github.com/mtws)
	* When editing Attachment post types, metabox should now save correctly
	* Disable `top_ten_nonce` as it wasn't needed for front-end users and conflicted with caching plugins

* Deprecated:
	* `tptn_default_options` (no replacement) and `tptn_read_options` (Use `tptn_get_settings` instead)

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

For previous changelog entries, please refer to the separate changelog.txt file


== Upgrade Notice ==

= 2.5.4 =
* Major release! Brand new settings interface so please do verify your settings after the upgrade. Several new features and bug fixes.
Check the Changelog for more details

