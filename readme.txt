=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics, tracker
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: 3.3.1
Requires at least: 5.8
Tested up to: 6.3
Requires PHP: 7.2
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
* **Gutenberg / Block Editor support**: You can find a block called "Popular Posts [Top 10]" with its own configurable set of options
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
14. Top 10 Gutenberg block

== Installation ==

= WordPress install (the easy way) =
1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Top 10"

3. Find the plugin in the list (usually the first result) and click "Install Now"

= Manual install =
1. Download the __top-10.zip__ file from this release post
2. Visit __Plugins__ in your Admin Area
3. Hit the __Add New__ button next to the Plugins heading
4. Hit the __Upload__ button next to the Add Plugins heading
5. Select the __top-10.zip__ file that you downloaded and hit Install Now
6. Activate the Plugin in WP-Admin.
7. Go to __Top 10 &raquo; Settings__ to configure
8. Go to __Appearance &raquo; Widgets__ to add the Popular Posts sidebar widget to your theme
9. Go to __Top 10 &raquo; View Popular Posts__ to view the list of popular posts


== Frequently Asked Questions ==

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/top-10/faq/) and the [FAQ on the WebberZone knowledgebase](https://webberzone.com/support/section/top-10/).
It is the fastest way to get support as I monitor the forums regularly.


= How can I customise the output? =

Details on how to use and customize the output is in this [knowledge base article](https://webberzone.com/support/knowledgebase/using-and-customising-top-10/)

= Shortcodes =

You can find details of the shortcodes in this [knowledge base article](https://webberzone.com/support/knowledgebase/top-10-shortcodes/)

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


== Other Notes ==

Top 10 is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions

== Changelog ==

= 3.3.1 =

* Enhancements/Modifications:
	* When displaying the post thumbnail, the Media Handler will first use the image's alt tag set in the Media editor. If alt tag is empty, then it will use the post title as a fallback. Filter `tptn_thumb_use_image_alt` and set it to false to not use the alt tag. Filter `tptn_thumb_alt_fallback_post_title` and set it to false to disable the alt tag
	* Orderby clause modified to not use visits to ensure compatibility if any other plugin rewrites the WP_Query fields
	* Media Handler will check if the meta field contains a valid URL
	* When saving settings, the thumbnail width and height is forced if either the width or height of the thumbnail setting is set to 0

* Bug fixes:
	* Function `wp_img_tag_add_loading_attr` is deprecated since version 6.3.0
	* Bug in `the_content` filter detection which sometimes caused the counter not to display
	* `tptn_thumbnail` settings size disappeared from Settings page if this was deselected

= 3.3.0 =

Release post: [https://webberzone.com/announcements/top-10-v3-3-0/](https://webberzone.com/announcements/top-10-v3-3-0/)

* Features:
	* Added new setting to stop tracking bots. Top 10 now includes a comprehensive set of bot user agents via https://github.com/janusman/robot-user-agents

* Enhancements/Modifications:
	* Complete rewrite of Top 10 plugin to use Classes and autoloading
	* `get_tptn_post_count_only()` and `get_tptn_post_count` can also take a `WP_Post` object and returns an integer only without the count being number formatted
	* `tptn_list` shortcode now accepts `WP_Query` parameters. You can also pass typical array only parameters as a comma-separated list
	* Tracker script no longer require jQuery
	* Widget styles are handled properly with the block editor

* Bug fixes:
	* Post count should only display once within the content within the main loop
	* Fixed data labels in the Dashboard graphs
	* Custom Post Type labels could cause an issue in Network view of popular posts

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/top-10/releases)


== Upgrade Notice ==

= 3.3.1 =
Several enhancements and bug fixes; Check out the release post.

