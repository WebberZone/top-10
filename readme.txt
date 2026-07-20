=== WebberZone Top 10 — Popular Posts ===
Tags: popular posts, post views, page views, most viewed posts, popular posts widget, trending posts, post views counter, multisite, block, shortcode
Contributors: webberzone, ajay
Donate link: https://wzn.io/donate-wz
Stable tag: 4.4.0
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
License: GPLv2 or later

Track post views and page views, and display popular posts and trending content on your WordPress site.

== Description ==

WordPress lacks built-in page view tracking or a popular posts feature. [Top 10](https://webberzone.com/plugins/top-10/) solves this by counting views across posts, pages, and custom post types, then letting you showcase your most popular content.

Top 10 provides blocks, widgets, shortcodes, and template functions for displaying popular posts and view counts across your site. All tracking data is stored locally in your WordPress database, with no external services involved.

Top 10 includes comprehensive features such as thumbnail support, flexible display options, custom post type support, and developer-friendly extensibility. A built-in caching layer reduces server load, while AJAX-based tracking avoids page cache interference and works with most popular caching plugins.

Top 10 also exposes a powerful API with WordPress actions and filters, allowing developers to customise queries, tracking behaviour, and output rendering without modifying core plugin files.

= Features =

* **Page Counter**: Tracks hourly post views on posts, pages, and custom post types. Display counts automatically using blocks, shortcodes, or template functions
* **Popular Posts**: Display most viewed posts by total counts or within custom time periods
* **Gutenberg Support**: Dedicated “Popular Posts [Top 10]” block with configurable display options
* **Multisite Dashboard**: Network-wide aggregated statistics across all sites in a multisite installation
* **Widgets**: Sidebar widgets for daily and overall popular posts with extensive customisation
* **Shortcodes**: Use `[tptn_list]` to display popular post lists and `[tptn_views]` to show view counts
* **Thumbnails**:
	* WordPress post thumbnail support with custom `tptn_thumbnail` image size
	* Automatic extraction of the first image from post content
	* Manual thumbnail URLs via Edit Post screens
* **Exclusions**: Exclude posts by category or post ID from popular post lists
* **Styling**: Output wrapped in semantic CSS classes. Add custom CSS via settings or use included styles
* **Admin Interface**: View daily and overall popular posts from the dashboard. Adds sortable view-count columns to post and page lists
* **Export/Import**: Export count tables and settings, and restore them on the same site or other installs
* **Caching Compatibility**: Works with WP Super Cache, W3 Total Cache, Quick Cache, and similar plugins
* **Developer-Friendly**: Extensive filters and actions to customise queries, tracking behaviour, and output rendering

= Features in Top 10 Pro =

* __Enhanced Tracking and Performance__
  * __Fast and High-Traffic Trackers__: Alternative tracking methods for improved performance on busy sites
  * __Query Optimisation__: MySQL `MAX_EXECUTION_TIME` directive to prevent long-running queries, configurable via settings and the `top_ten_query_max_execution_time` filter
  * __Data Retention Override__: Customizable data retention period (default 180 days via `TOP_TEN_STORE_DATA`)
  * __Lazy Loading__: Render popular posts lists via JavaScript only when they are about to scroll into view, so full-page caching plugins can serve a page without baking in a live popular-posts query. Works with content, shortcodes, widgets, and the Popular Posts block; disable per instance with the `lazy_load` shortcode/block attribute

* __Advanced Blocks and Widgets__
  * __Top 10 Query Block__: Query and display popular posts directly from the block or site editor
  * __Enhanced Top 10 Featured Image Block__: Support for multiple image sources with fallbacks
  * __Popular Posts Block Enhancements__:
    * Save and clear default block settings with a single click
    * Auto-insert default and global settings attributes with an option to disable

* __Enhanced Admin Tools__
  * __Admin Bar Integration__: Admin bar menu item to view daily, total, and overall post counts, access admin pages, and clear cache
  * __Disable Admin Bar menu__: Setting to disable the Admin Bar menu
  * __Dashboard Access Control__: Setting to control which user roles can view the Top 10 dashboard
  * __Display Settings__: Setting to choose which post type screens display admin columns
  * __Mini "Top 10 Views Overview" widget__: Compact views-over-time chart on the WordPress Dashboard
  * __Multisite Settings Copy__: Tool to copy settings between sites in a multisite network

* __Custom Display Options__
  * __Taxonomy-Specific Displays__: `display_only_on_tax_ids` parameter to restrict popular post displays to specific taxonomy terms
  * __Category Inclusion__: Edit Post meta box option to include popular posts from specific categories
  * __RSS Feed Filtering__: Filter RSS feeds by category or post type via settings or URL parameters

* __WP-CLI Integration__
  * __`wp top10 counts`__: View, get, set, reset, export, and import post view counts from the command line
  * __`wp top10 db`__: Manage database tables — check status, create, update, recreate, prune old rows, force aggregation, truncate, and clean up orphaned counts
  * __`wp top10 cache`__: Flush, enable, disable, and check the status of the output cache
  * __`wp top10 settings`__: Get, set, export, and import plugin settings as JSON
  * __`wp top10 cron`__: View, enable, disable, and manually trigger the maintenance and aggregation cron jobs
  * __`wp top10 popular`__: List popular posts with counts using the same query as the front end
  * __`wp top10 status`__: Print a full plugin status overview
  * All destructive commands support `--dry-run` and `--force`; multisite commands support `--network` and `--blog-id`

* __Developer-Friendly Features__
  * __Custom Post Type Sortable Columns__: Admin columns on supported custom post types with sortable functionality

= GDPR =

Top 10 does not collect personal visitor data out of the box. Tracking data is stored locally in the `wp_top_ten` and `wp_top_ten_daily` database tables (table prefix may vary).

You are responsible for ensuring GDPR compliance on your website.

= Translations =

Top 10 is available for translation on [WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10).  
See the [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.

= Contribute =

Top 10 is developed openly on [GitHub](https://github.com/webberzone/top-10).  
Fork the project and submit pull requests for bug fixes or improvements. Please do not use GitHub for support requests.

== Other WebberZone Plugins ==

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) – Display related posts on your WordPress site and feeds
* [Better Search](https://wordpress.org/plugins/better-search/) – Enhance WordPress search with relevance-based results
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) – Create a knowledge base or FAQ section
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) – Manage custom HTML, CSS, and JavaScript snippets
* [Auto-Close](https://wordpress.org/plugins/autoclose/) – Automatically close comments, pingbacks, and trackbacks
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) – Display popular authors widgets. Addon for Top 10.
* [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) – Show related posts based on reader journeys
* [WebberZone Link Warnings](https://wordpress.org/plugins/webberzone-link-warnings/) – Add accessible warnings for external links and target="_blank" links

== Screenshots ==

1. Top 10 – Popular posts overview in the WordPress admin
2. Top 10 – Popular posts list with thumbnails

== Installation ==

= WordPress install (easy way) =
1. Go to Plugins → Add New in your WordPress Admin
2. Search for “Top 10”
3. Click Install Now and then Activate

= Manual install =
1. Download the `top-10.zip` file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click Install Now
4. Activate the plugin
5. Configure settings under Top 10 → Settings

= Help and Support =
* [Documentation](https://webberzone.com/support/product/top-10/)
* [WordPress.org Support Forum](https://wordpress.org/support/plugin/top-10)

== Frequently Asked Questions ==

= Where can I get help and support? =

Before opening a support request, please check the following resources:

* [FAQ on the WordPress.org plugin page](https://wordpress.org/plugins/top-10/faq/)
* [FAQ on the WebberZone knowledge base](https://webberzone.com/support/product/top-10/)

These cover the most common questions and are the fastest way to get answers, as they are actively maintained.

If your question is not answered there, please create a new topic in the
[WordPress.org support forum](https://wordpress.org/support/plugin/top-10/).
This is the preferred support channel for the free plugin, and the forums are monitored regularly.

Support for products sold and distributed by WebberZone is available **only**
to users with an active, valid licence. Licensed users can request support [here](https://webberzone.com/request-support/).

= Can this plugin replace Google Analytics? =

No. Top 10 tracks page views and displays popular posts. It is not designed to replace analytics platforms.

= How does tracking work? =

Each visit is queued in a lightweight funnel table and aggregated into the count tables every five minutes by a cron job, so tracking never blocks page loads and works correctly behind page caches.

For a full explanation of tracker types, the funnel flow, and how to adjust the aggregation interval, see [Trackers in Top 10](https://webberzone.com/support/knowledgebase/trackers-in-top-10/).

= How does scheduled maintenance work? =

When enabled, Top 10 runs a scheduled task that periodically removes old entries
from the `wp_top_ten_daily` table.

Note: WordPress executes scheduled tasks on the first eligible page load.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program.
The Patchstack team help validate, triage and handle any security vulnerabilities.
[Report a security vulnerability.](https://patchstack.com/database/vdp/top-10)

== Changelog ==

= 4.4.0 =

*Release Date - 20 July 2026*

Release post: [https://webberzone.com/announcements/top-10-v4-4-0/](https://webberzone.com/announcements/top-10-v4-4-0/)

* Features:
	* New "Features" tab under Settings: turn off plugin features that you do not use and their code will not be loaded at all. You can toggle the Popular Posts and Post Count blocks, popular posts feeds, legacy widgets, Query block, Featured Image block, Popular Posts Pro block, Fast and High-traffic trackers, Pro dashboard widgets, and Popular Authors.
	* New "Number format style" setting under Settings » Counter/Tracker » Counter settings: choose Abbreviated to display large view counts in a compact form, e.g. 1.2k or 3.4M, wherever counts are displayed. The default remains the full locale-formatted number. It can be overridden per instance with the `number_format_style` shortcode/block attribute, and the suffixes and rounding can be customised via the `tptn_number_format_abbreviations` and `tptn_abbreviate_number_decimals` filters.
	* WP-PostRatings integration: new "Show WP-PostRatings rating" setting under Settings » Posts list displays each post's rating next to it in the popular posts list, either as the star rating rendered using the WP-PostRatings plugin's own templates or as the average score number. Off by default and only takes effect when the free WP-PostRatings plugin is active. It can be overridden per instance with the `show_ratings` shortcode/block attribute (`stars` or `score`), and the markup can be customised via the `tptn_ratings` filter.
	* [Pro] New "Lazy load popular posts" setting under Settings » General: load popular posts lists via JavaScript only when they are about to enter the viewport instead of rendering them inline, so full-page caching plugins can serve a page without embedding a live popular-posts query. Applies to content, shortcodes, widgets, and the Popular Posts block. Off by default. Override it per instance with the `lazy_load` shortcode/block attribute, e.g. `lazy_load="0"`. Not applied on feeds, admin, AJAX, cron, WP-CLI, or AMP pages.

* Improvements:
	* The `[tptn_list]` shortcode now accepts all registered settings as attributes, including newly added settings that are not yet present in the saved settings.
	* Redesigned settings page with vertical tab navigation, which is responsive and reverts to horizontal tabs on smaller screens. The settings page now opens on the Features tab.
	* Checkbox settings now display as toggle switches.
	* Settings that have been changed from their default value are marked with an indicator dot, with a legend below the buttons. The default value is also displayed below each setting's description.
	* Admin styles and scripts are now versioned with the plugin version so browsers reliably pick up changes after an update.
	* Bot/crawler detection (used by the "Do not track bots" tracker setting) now uses the actively maintained Crawler-Detect library for broader, more accurate coverage, alongside the existing built-in pattern list.

* Fixed:
	* Admin banner no longer causes a horizontal scrollbar on the plugin's admin pages.
	* Left thumbnail style no longer stacks the thumbnail above the text in narrow containers such as sidebar widgets; it now stays side by side unless there genuinely isn't room.
	* New "Fix Cron Schedules" tool under Top 10 » Tools to clear and reschedule the maintenance and aggregation cron jobs if they stop running.
	* The Tools page and an admin notice now surface WP-Cron scheduling errors (e.g. "The cron event list could not be saved") directly in the dashboard instead of only appearing in the PHP error log.
	* [Pro] Fast and High-traffic trackers now respect the "Do not track bots" setting; previously they recorded views for bots and other automated user agents regardless of this setting.

= Earlier versions =

For the changelog of earlier versions, please refer to the separate changelog.txt file or the [releases page on Github](https://github.com/webberzone/top-10/releases).

== Upgrade Notice ==

= 4.3.0 =
This release introduces buffered visit tracking with a new funnel table. Existing daily-count table indexes are updated automatically for new installs — existing sites should run "Recreate Primary Key" under Tools > Top 10 to apply the performance improvement. Pro users also get feed view tracking and a full WP-CLI command suite.