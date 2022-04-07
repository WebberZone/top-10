=== Top 10  - Popular posts plugin for WordPress ===
Tags: popular posts, top 10, counter, top posts, daily popular, page views, statistics, tracker
Contributors: webberzone, Ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: 3.1.0
Requires at least: 5.6
Tested up to: 5.9
Requires PHP: 7.1
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
1. Download the plugin

2. Extract the contents of top-10.zip to wp-content/plugins/ folder. You should get a folder called top-10.

3. Activate the Plugin in WP-Admin.

4. Go to **Top 10 &raquo; Settings** to configure

5. Go to **Appearance &raquo; Widgets** to add the Popular Posts sidebar widget to your theme

6. Go to **Top 10 &raquo; View Popular Posts** to view the list of popular posts


== Frequently Asked Questions ==

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/top-10/faq/) and the [FAQ on the WebberZone knowledgebase](https://webberzone.com/support/section/top-10/).
It is the fastest way to get support as I monitor the forums regularly. I also provide [*paid* premium support via email](https://webberzone.com/support/).


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


== Changelog ==

= 3.1.0 =

Release post: [https://webberzone.com/blog/top-10-v3-1-0/](https://webberzone.com/blog/top-10-v3-1-0/)

* Features:
	* New filter `tptn_show_meta_box` that can be set to false to disable the Top 10 meta box on Edit screens
	* New option to exclude the current post from the list
	* New option "Exclude on Categories" to disable the display of the popular posts on selected categories

* Enhancements/Modifications:
	* Optimised import of tables particularly for larger imports
	* Added wpml-config.xml file that will allow settings to be translated with WPML and PolyLang
	* Upgraded block to the latest API
	* Upgraded thumbnail display. If default image is disabled, then the site icon will be displayed if available
	* Admin dashboard counts match with the Popular posts listings
	* Updated chartjs to the latest version

* Bug fixes:
	* Fixed PHP notice on widgets.php page due to the block
	* Widget checkboxes are not saved in WordPress 5.8
	* `post__in` argument will now remove any false/0 values
	* Disabling/enabling author tracking didn't always work
	* WP Multisite: Creating a new blog with automatically configure the plugin
	* Widget incorrectly included all post types when no post types were selected instead of using the global settings
	* Current post was incorrectly excluded when translation functions were run

= 3.0.0 =

Release post: [https://webberzone.com/blog/top-10-v3-0-0/](https://webberzone.com/blog/top-10-v3-0-0/)

* Features:
    * New Top_Ten_Query class for fetching popular posts. Adds the function `get_tptn_posts()` which replaces `get_tptn_pop_posts()` which will be deprecated in a future version
	* New option to exclude the Front page and Posts page if these are set in Settings > Reading or via Customizer
	* New option in the Widget to include specific post IDs in the top lists. You can also use them in the shortcode using `include_post_ids`
	* New block for Gutenberg aka the block editor. The block is called **Popular Posts [Top 10]** and you can find it under the widgets category
	* Top 10 now supports the WP REST API. The plugin adds a new tracker type called *REST API based* which you can find under Counter/Tracker settings. Additionally, you can now receive the popular posts via a REST Request to `top-10/v1/popular-posts`

* Enhancements/Modifications:
	* No popular posts feed will be added if the corresponding slug is set to blank
	* Changed `sum_count` to `visits`

* Bug fixes:
	* PHP notices when displaying Network Wide Popular Posts in WordPress Multisite
	* Query based tracker gave an ajax error

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/top-10/releases)


== Upgrade Notice ==

= 3.1.0 =
Major release; Please check the plugin settings; Read all details in the release post

