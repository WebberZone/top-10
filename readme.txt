=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics, tracker
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: 2.9.2
Requires at least: 4.9
Tested up to: 5.4
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
* **Export/Import interface**: Export the count tables and settings to restore in the same site or on other installs
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
7. Top 10 options - Feed
8. Top 10 widget options
9. Top 10 Meta box on the Edit Post screen
10. Top 10 Tools page
11. Top 10 - Popular posts view in Admin
12. Top 10 Export/Import interface
13. Top 10 - Popular posts view in Network Admin

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
It is the fastest way to get support as I monitor the forums regularly. I also provide [*paid* premium support via email](https://webberzone.com/support/).


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

You can find details of the shortcodes in this [knowledge base article](https://webberzone.com/support/knowledgebase/top-10-shortcodes/)

= Manual install =

The below functions need to be added by editing your theme files where you wish to display them.

Display the post count with `<?php if ( function_exists( 'echo_tptn_post_count' ) ) { echo_tptn_post_count(); } ?>`

Display the overall most popular posts with `<?php if ( function_exists( 'tptn_show_pop_posts' ) ) { tptn_show_pop_posts(); } ?>`

Display the daily/custom period popular posts with `<?php if ( function_exists( 'tptn_show_daily_pop_posts' ) ) { tptn_show_daily_pop_posts(); } ?>`

You can also use the WordPress Widgets to display the popular posts in your sidebar / other widgetized areas of your theme

View [examples of the plugin API](https://gist.github.com/ajaydsouza/c8defd4b46e53240e376) to fetch the popular posts

= Can this plugin replace Google Analytics? =

No. Top 10 has been designed to only track the number of page-views on your blog posts and display the same. It isn't designed to replace Google Analytics or any other full fledged analytics application.

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

= 2.9.3 =

Release post: [https://webberzone.com/blog/top-10-v2-9-0/](https://webberzone.com/blog/top-10-v2-9-0/)

* Enhancements:
	* Cache now uses MD5 generated keys based on the Top 10 parameters being passed. This means the cache should now work with shortcodes and manual implementations
	* Database creation now uses the correct collation
    * Password protected posts will no longer show the excerpt

* Bug fixes:
	* `include_cat_ids` didn't work with the shortcode

= 2.9.2 =

Release post: [https://webberzone.com/blog/top-10-v2-9-0/](https://webberzone.com/blog/top-10-v2-9-0/)

* Bug fixes:
	* Custom CSS box would not save

= 2.9.1 =

Release post: [https://webberzone.com/blog/top-10-v2-9-0/](https://webberzone.com/blog/top-10-v2-9-0/)

* Bug fixes:
	* Published age of posts parameter was no longer working
	* Importing settings should now work properly - previously checkboxes all ended up being enabled
	* In rare cases, the tracker would throw PHP errors e.g. on search results page where no posts were loaded and tracker was enabled to load on all pages

= 2.9.0 =

Release post: [https://webberzone.com/blog/top-10-v2-9-0/](https://webberzone.com/blog/top-10-v2-9-0/)

* Features:
	* Edit the total visits from the Top 10 > Popular Posts page
	* New shortcode setting `include_cat_ids` to limit top posts to selected categories/taxonomies. Use a comma separated list of [term_taxonomy_id](https://codex.wordpress.org/WordPress_Taxonomy#wp_term_taxonomy)
	* New setting in widget to only include certain categories. Doesn't work with custom taxonomies

* Enhancements:
	* Implemented CodeMirror to format custom styles box
	* Admin scripts are no longer loaded into the head - but using a separate JavaScript file

* Bug fixes:
	* Fixed media lookup returning PHP errors/warnings in rare cases
	* Use `current_time` and `gmdate` as per latest WordPress Coding Standards
	* No more unnecessary form modified errors in the popular posts tables in Admin area
	* Fixed notices in PHP 7.4 when viewing popular posts listings
	* When editing posts, the count from the metabox will only be used if it is modified

= 2.8.0 =

Release post: [https://webberzone.com/blog/top-10-v2-8-0/](https://webberzone.com/blog/top-10-v2-8-0/)

* Features:
	* Custom feed for popular posts: Find new options under a new tab called Feed in the settings page where you can set the URL for the overall and daily feeds
	* New page in the network admin menu to view the popular posts across the network
	* Also delete transients and other settings on uninstall

= 2.7.0 =

Release post: [https://webberzone.com/blog/top-10-v2-7-0/](https://webberzone.com/blog/top-10-v2-7-0/)

* Features:
	* Recreate the Top 10 database tables. You can find the button in the Tools page. Backup your database first
	* New export/import interface to export and import the Top 10 tables and settings

* Enhancements:
	* `tptn_views` shortcode adds a new parameter 'count' to explicitly set the count type. Choose from daily, total or overall. This is overridden if daily is set to 0 (backwards compatibility)

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/top-10/releases)


== Upgrade Notice ==

= 2.9.3 =
Bug fixes and new features; Check the Changelog for more details

