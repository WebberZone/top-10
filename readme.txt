=== Top 10 - WordPress Popular Posts by WebberZone ===
Tags: popular posts, post views, page views, most viewed posts, popular posts widget, trending posts, post views counter, multisite, block, shortcode
Contributors: webberzone, ajay
Donate link: https://ajaydsouza.com/donate/
Stable tag: 4.2.0
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
License: GPLv2 or later

Track post views and page views, and display popular posts and trending content on your WordPress site.

== Description ==

WordPress lacks built-in page view tracking or a popular posts feature. [Top 10](https://webberzone.com/plugins/top-10/) solves this by counting views across posts, pages, and custom post types, then letting you showcase your most popular content.

Top 10 provides blocks, widgets, shortcodes, and template functions for displaying popular posts and view counts across your site. All tracking data is stored locally in your WordPress database, with no external services involved.

Despite numerous similar plugins, Top 10 stands out with comprehensive features including thumbnail support, flexible display options, custom post type support, and developer-friendly extensibility. A built-in caching layer reduces server load, while AJAX-based tracking avoids page cache interference and works with most popular caching plugins.

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

* **Enhanced Tracking and Performance**
	* **Fast Tracker**: Faster tracking method for improved performance on busy sites
	* **High-Traffic Mode**: Secure configuration for large sites with a status validation panel
	* **Query Optimisation**: Optional MySQL `MAX_EXECUTION_TIME` directive to prevent long-running queries, configurable via settings and the `top_ten_query_max_execution_time` filter
	* **Data Retention Override**: Customise data retention period (default 180 days via `TOP_TEN_STORE_DATA`)

* **Advanced Blocks and Widgets**
	* **Top 10 Query Block**: Query and display popular posts directly from the block or site editor
	* **Enhanced Featured Image Block**: Support for multiple image sources
	* **Popular Posts Block Enhancements**: Save and clear default attributes, and auto-insert global settings

* **Enhanced Admin Tools**
	* **Admin Bar Menu**: View daily, total, and overall post counts, access admin pages, and clear cache
	* **Dashboard Access Control**: Control which user roles can view the Top 10 dashboard
	* **Display Settings**: Choose which post types display admin columns
	* **Mini Dashboard Widget**: Compact views-over-time chart on the WordPress Dashboard
	* **Multisite Settings Copy**: Copy settings between sites in a multisite network

* **Custom Display Options**
	* **Taxonomy Filtering**: Restrict displays to specific taxonomy terms using `display_only_on_tax_ids`
	* **Category Inclusion**: Include posts from selected categories via the Edit Post meta box
	* **RSS Feed Filtering**: Filter RSS feeds by category or post type via settings or URL parameters

* **Developer Features**
	* **Extended Filters**: Additional filters including `top_ten_query_exclude_terms_include_parents`, `top_ten_query_include_terms_include_parents`, and `get_tptn_short_circuit`
	* **Sortable Columns**: Sortable admin columns on supported custom post types

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

= How does scheduled maintenance work? =

When enabled, Top 10 runs a scheduled task that periodically removes old entries
from the `wp_top_ten_daily` table.

Note: WordPress executes scheduled tasks on the first eligible page load.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program.
The Patchstack team help validate, triage and handle any security vulnerabilities.
[Report a security vulnerability.](https://patchstack.com/database/vdp/top-10)

== Changelog ==

= 4.2.0 =

* New:
	* Settings wizard to guide initial configuration and review existing settings
	* Network-wide dashboard with aggregated multisite statistics
	* [Pro] High-traffic tracking mode with status validation panel
	* [Pro] Copy settings between multisite network sites
	* [Pro] Compact “Top 10 Views Overview” dashboard widget
	* [Pro] Option to disable the Admin Bar menu
	* [Pro] Maintenance setting to override data retention period

* Improvements:
	* [Pro] Improved dashboard chart bar click-through to open the Popular Posts screen filtered to that day
	* Updated Settings API to version 2.7.1
	* Improved media handler with recursion protection and more robust processing
	* Improved Tools page statistics display and caching
	* Wrapped Import/Export and Tools sections in postbox containers for consistent UI

* Developer / Internal:
	* Refactored popular posts queries for improved performance and WordPress VIP compatibility
	* Updated caching behaviour for dynamic exclusions
	* Refactored database operations into a dedicated Database class
	* Updated Freemius SDK to version 2.13.0

* Fixes:
	* Fixed `Top_Ten_Query` handling of the `date_query` argument
	* Fixed activation redirects on single-site installs within multisite networks
	* Fixed `exclude_current_post` behaviour when caching is enabled
	* Fixed live-edit count updates in multisite statistics

== Upgrade Notice ==

= 4.2.0 =
Major update introducing a new settings wizard, a multisite network-wide dashboard, and multiple Pro enhancements.
