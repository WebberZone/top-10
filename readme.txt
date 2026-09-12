=== WebberZone Top 10 — Popular Posts ===
Tags: popular posts, post views, page views, most viewed posts, popular posts widget, trending posts, post views counter, multisite, block, shortcode
Contributors: webberzone, ajay
Donate link: https://wzn.io/donate-wz
Stable tag: 4.5.0
Requires at least: 6.8
Tested up to: 7.1
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
  * __Site-wide Tracking__: Track views for the front page, posts page, archives, and searches in addition to individual content
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

= Multilingual sites =

Top 10 works with WPML, Polylang and TranslatePress without additional plugin configuration. WPML and Polylang map popular posts to the language being viewed. TranslatePress translates popular post titles, excerpts and links with the rest of the page, including results delivered through the REST API and lazy-loaded lists in Top 10 Pro.

Popular post caches are separated by language, preventing cached lists from serving another language's links.

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

= Unreleased =

**Added**

* Added TranslatePress translation for popular post titles, excerpts and links returned through the REST API.
* [Pro] Added TranslatePress support for lazy-loaded popular posts.

**Fixed**

* Cached popular posts were shared across languages on WPML, Polylang and TranslatePress sites.

= 4.5.0 =

Release date: 7 September 2026
Release post: https://webberzone.com/announcements/top-10-v4-5/

**Added**

* [Pro] Popular Posts elements for Elementor, Bricks Builder and WPBakery Page Builder.
* [Pro] Optional site-wide tracking for front pages, posts pages, archives and searches.
* [Pro] Reduce Daily Table Size tool to combine older hourly records.
* [Pro] `wp top10 db rollup` command for CLI and multisite use.

**Changed**

* Reduced admin overhead on large multisite networks by caching table metadata, probing WPP tables directly and using estimated row counts on the Tools page.
* Sped up the dashboard with index-friendly `dp_date` ranges, optimized network popular-post queries and on-demand loading of historical tabs.
* Setting defaults are now resolved from a lightweight list instead of building every settings field, so reading an option early in the page load no longer loads translations too early.

**Security**

* Hardened settings sanitization for users without the `unfiltered_html` capability.
* Hardened referer and array handling in settings sanitization, and renamed the settings JS globals.

**Fixed**

* View tracking was lost during quick navigation or browser back-button restores.
* View counts were recorded for prerendered or initially hidden pages.
* Bot views were recorded from cached pages, and views were recorded for browser prefetches, prerenders and direct navigations to tracker URLs.
* Tracker scripts did not run when loaded asynchronously after `DOMContentLoaded` had fired.
* Generated output cache keys were not discoverable by the Clear Cache tools or the WP-CLI cache flush command.
* Settings on a multisite network read another site's values in the same request after a `switch_to_blog()` call, when read via `tptn_get_option()` or the global `$tptn_settings`.
* `tptn_get_settings()` returned `false` instead of an empty array when no settings had been saved yet.
* Visit data was silently lost in funnel aggregation, and the Fast Tracker raised an undefined array key warning.
* Plugin data was deleted when uninstalling one version while its paired free or Pro counterpart was active.
* Improved compatibility with PHP 8.6.
* [Pro] Daily counts in the `wp top10 popular` command did not respect the selected custom date range.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/top-10/releases).

== Upgrade Notice ==

= 4.5.0 =
Adds page builder elements, site-wide tracking and a daily-table rollup tool. Fixes several cases of lost view counts and cuts admin overhead on large multisite networks.
