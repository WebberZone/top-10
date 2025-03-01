=== Top 10 - WordPress Popular posts by WebberZone ===
Tags: popular posts, top 10, counter, statistics, tracker
Contributors: webberzone, ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: 4.0.3
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 7.4
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

= Features in Top 10 Pro =

* **Advanced Blocks and Widgets**
  - **Top 10 Query Block**: Query and display popular posts directly from the block or site editor.
  - **Enhanced Top 10 Featured Image Block**: Supports multiple image sources for more flexibility.
  - **Popular Posts Block Enhancements**: 
    - Save and clear default block settings with a single click.
    - Auto-insert default and global settings attributes with an option to disable.

* **Improved Admin Tools**
  - **Admin Bar Integration**: New admin bar menu item to view daily, total, and overall post counts, access admin pages, and clear the cache quickly.
  - **Dashboard Access Control**: Control which user roles can view the Top 10 dashboard.
  - **Display Settings**: Choose which post type screens display admin columns.

* **Custom Display Options**
  - **Taxonomy-Specific Displays**: Use the `display_only_on_tax_ids` parameter to restrict popular post displays to specific taxonomy terms.
  - **Category Inclusion**: Include popular posts from specific categories using a new option in the Edit Post meta box.

* **Enhanced Tracking and Performance**
  - **Fast Tracker**: A new, faster tracking method to improve post view speed.
  - **Query Filters**: Enable parent term inclusion in post queries for more accurate filtering.

* **Developer-Friendly Features**
  - **Filters and Hooks**: New filters like `top_ten_query_exclude_terms_include_parents`, `top_ten_query_include_terms_include_parents`, and `get_tptn_short_circuit` for greater customisation.
  - **Custom Post Type Sortable Columns**: Display columns on post types and make them sortable.

= GDPR =
Top 10 is GDPR compliant as it doesn't collect any personal data about your visitors when installed out of the box. You can see the data the plugin stores in the `wp_top_ten` and `wp_top_ten_daily` tables in the database. Note: the prefix `wp` might be different if you have changed it from the default.

YOU ARE RESPONSIBLE FOR ENSURING THAT ALL GDPR REQUIREMENTS ARE MET ON YOUR WEBSITE.

= Translations =
Top 10 is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.


= Contribute =

Top 10 is also available on [Github](https://github.com/webberzone/top-10)
So, if you've got some cool feature that you'd like to implement into the plugin or a bug you've been able to fix, consider forking the project and sending me a pull request. Please don't use that for support requests.

== Other plugins from WebberZone ==

Top 10 - Popular Posts is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
* [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

== Screenshots ==

1. Top 10 - Popular posts view in Admin
2. Top 10 - Left thumbnails style

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
8. Go to __Appearance &raquo; Widgets__ and add __Top 10 Popular Posts__ to your sidebar to display the popular posts in the sidebar

= For help and support =
1. Visit [Top 10 documentation](https://webberzone.com/support/product/top-10/) for extensive information and examples of how to use the plugin
2. Visit the [Support forum](https://wordpress.org/support/plugin/top-10) on WordPress.org

== Frequently Asked Questions ==

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/top-10/faq/) and the [FAQ on the WebberZone knowledgebase](https://webberzone.com/support/product/top-10/). It is the fastest way to get support as I monitor the forums regularly.

If your question isn't listed there, please create a new post at the [WordPress.org support forum](https://wordpress.org/support/plugin/top-10/). It is the fastest way to get support as I monitor the forums regularly.

Support for products sold and distributed by WebberZone is only available for those who have an active, paid extension license. You can [access our support form here](https://webberzone.com/request-support/).

= How can I customise the output? =

 Top 10 is highly customizable. There are several configurable options in the Settings page and you can use CSS to customize the outputs. Learn more by reading [knowledge base article](https://webberzone.com/support/knowledgebase/using-and-customising-top-10/)

= Shortcodes =

You can find details of the shortcodes in this [knowledge base article](https://webberzone.com/support/knowledgebase/top-10-shortcodes/)

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/top-10)

= Can this plugin replace Google Analytics? =

No. Top 10 has been designed to only track the number of page-views on your blog posts and display the same. It isn't designed to replace Google Analytics or any other full fledged analytics application.

= How does the scheduled maintenance work? =

When you enabled the scheduled maintenance, Top 10 will create a cron job that will run at a predefined interval and clean up old entries from the `wp_top_ten_daily` table.
*Note: If you enable this option, WordPress will execute this job when it is scheduled the first time*

== Changelog ==

= 4.1.0 =

* Modifications:
	* Updated ChartJS and replaced Moment adapter with Luxon.
	* An admin notice is now displayed when any Top 10 table is missing. The plugin will also automatically recreate the missing tables.

* Bug fixes:
	* Resolved issue where tables were not automatically created during plugin activation.

= 4.0.4 =

* Modifications:
	* Updated Freemius SDK to v2.11.0.

* Bug fixes:
	* Set correct type for `$settings_api` variable to `Settings_API`.

= 4.0.3 =

* Modifications:
	* Support plugin dependencies tag.
	* Updated Freemius SDK to v2.10.1.
	* Optimized Numbered List format.

= 4.0.2 =

* Updated Freemius SDK to 2.9.0.
* Fixed: Set `widget_id` if it is not set in the widget instance.

= 4.0.1 =

* Modifications:
	* Renamed filter to: `top_ten_posts_post_types`.
	* Updated filter `tptn_query_args_before` to be the queried object instead of just the post.

* Bug fix:
	* Fixed issue where admin columns setting didn't work.
	* Fixed: meta_query was not set.

= 4.0.0 =

Release post: [https://webberzone.com/announcements/top-10-v4-0-0/](https://webberzone.com/announcements/top-10-v4-0-0/)

* Features:
	* Added a new REST API route (`counter`) to fetch the post count for individual posts.
	* Introduced the Top 10 Post Count Block for displaying post counts.
	* Added filters `top_ten_query_exclude_terms_include_parents` and `top_ten_query_include_terms_include_parents` to include parent terms in post queries. Pro users can enable this in settings.
	* New `get_tptn_short_circuit` filter to bypass the plugin’s output.
	* New filter `tptn_dashboard_setup` to disable Top 10 widgets being displayed on the admin dashboard.
	* [Pro] New Top 10 Query Block for querying popular posts directly from the block or site editor.
	* [Pro] Enhanced Top 10 Featured Image Block now supports multiple image sources.
	* [Pro] Popular Posts block now includes:
		* Buttons to save and clear default block settings.
		* Auto-insertion of default and global settings attributes, with an option to disable this in the **Posts List** settings.
	* [Pro] Added a new admin bar menu item to view daily, total, and overall post counts, access Top 10 admin pages, and clear the Top 10 cache.
	* [Pro] Added `display_only_on_tax_ids` parameter to restrict popular posts display to specific taxonomy terms.
	* [Pro] New Fast Tracker improves post view tracking speed. Select it from your settings page.
	* [Pro] "Display columns on post types" setting to choose which post type screens display admin columns.
	* [Pro] "Also show dashboard to" setting to select user roles that can view the dashboard screen.
    * [Pro] New option added to the Edit Post meta box mapped to `include_cat_ids` to include popular posts from specific categories only.

* Enhancements:
	* Direct support for `WP_Query` if `top_ten_query` is used in query arguments.
	* Optimised media handler to reduce queries.
	* New filter: `tptn_shortcode_defaults` for default shortcode arguments.
	* Media Handler improvements:
		* Added `use_site_icon` and `style` parameters.
		* `get_image_html()` now uses `wp_get_attachment_image()` with a valid attachment ID.
		* Support for `decoding`, `loading`, and `fetchpriority` attributes.
		* `get_attachment_id_from_url()` now strips size suffixes before locating the attachment ID.
	* Updated top-10/popular-posts block to API version 3.
	* Added `$more_link_text` parameter for `get_the_excerpt()`.

For previous changelog entries, please refer to the separate changelog.txt file or [Github Releases page](https://github.com/WebberZone/top-10/releases)


== Upgrade Notice ==

= 4.0.4 =
Freemius SDK Updated. Minor other changes. Check out the release post or changelog for further information.
