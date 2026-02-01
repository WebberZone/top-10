# WebberZone Top 10 — Popular Posts

[![Top 10](https://raw.githubusercontent.com/WebberZone/top-10/master/wporg-assets/banner-1544x500.png)](https://wordpress.org/plugins/top-10/)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)

__Requires:__ 6.6

__Tested up to:__ 6.9

__Requires PHP:__ 7.4

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Top 10](https://webberzone.com/plugins/top-10/) | [WordPress.org plugin page](https://wordpress.org/plugins/top-10/)

Track post views and page views, and display popular posts and trending content on your WordPress site.

## Description

WordPress lacks built-in page view tracking or a popular posts feature. [Top 10](https://webberzone.com/plugins/top-10/) solves this by counting views across posts, pages, and custom post types, then letting you showcase your most popular content.

Top 10 provides blocks, widgets, shortcodes, and template functions for displaying popular posts and view counts across your site. All tracking data is stored locally in your WordPress database, with no external services involved.

Top 10 includes comprehensive features such as thumbnail support, flexible display options, custom post type support, and developer-friendly extensibility. A built-in caching layer reduces server load, while AJAX-based tracking avoids page cache interference and works with most popular caching plugins.

Top 10 also exposes a powerful API with WordPress actions and filters, allowing developers to customise queries, tracking behaviour, and output rendering without modifying core plugin files.

### Features

* __Page Counter__: Tracks hourly post views on posts, pages, and custom post types. Display counts automatically using blocks, shortcodes, or template functions
* __Popular Posts__: Display most viewed posts by total counts or within custom time periods
* __Gutenberg Support__: Dedicated "Popular Posts [Top 10]" block with configurable display options
* __Multisite Dashboard__: Network-wide aggregated statistics across all sites in a multisite installation
* __Widgets__: Sidebar widgets for daily and overall popular posts with extensive customisation
* __Shortcodes__: Use `[tptn_list]` to display popular post lists and `[tptn_views]` to show view counts
* __Thumbnails__:
  * WordPress post thumbnail support with custom `tptn_thumbnail` image size
  * Automatic extraction of the first image from post content
  * Manual thumbnail URLs via Edit Post screens
* __Exclusions__: Exclude posts by category or post ID from popular post lists
* __Styling__: Output wrapped in semantic CSS classes. Add custom CSS via settings or use included styles
* __Admin Interface__: View daily and overall popular posts from the dashboard. Adds sortable view-count columns to post and page lists
* __Export/Import__: Export count tables and settings, and restore them on the same site or other installs
* __Caching Compatibility__: Works with WP Super Cache, W3 Total Cache, Quick Cache, and similar plugins
* __Developer-Friendly__: Extensive filters and actions to customise queries, tracking behaviour, and output rendering

### Features in Top 10 Pro

* __Enhanced Tracking and Performance__
  * __Fast Tracker__: Faster tracking method for improved performance on busy sites
  * __High-Traffic Mode__: Secure configuration for large sites with a status validation panel
  * __Query Optimisation__: Optional MySQL `MAX_EXECUTION_TIME` directive to prevent long-running queries, configurable via settings and the `top_ten_query_max_execution_time` filter
  * __Data Retention Override__: Customise data retention period (default 180 days via `TOP_TEN_STORE_DATA`)

* __Advanced Blocks and Widgets__
  * __Top 10 Query Block__: Query and display popular posts directly from the block or site editor.
  * __Enhanced Top 10 Featured Image Block__: Supports multiple image sources for more flexibility.
  * __Popular Posts Block Enhancements__:
    * Save and clear default block settings with a single click.
    * Auto-insert default and global settings attributes with an option to disable.

* __Enhanced Admin Tools__
  * __Admin Bar Integration__: New admin bar menu item to view daily, total, and overall post counts, access admin pages, and clear the cache quickly.
  * __Disable Admin Bar menu__: Option to disable the Admin Bar menu.
  * __Dashboard Access Control__: Control which user roles can view the Top 10 dashboard.
  * __Display Settings__: Choose which post type screens display admin columns.
  * __Mini "Top 10 Views Overview" widget__: Compact views-over-time chart shown on the WordPress Dashboard.
  * __Multisite Settings Copy__: Copy settings between sites in a multisite network.

* __Custom Display Options__
  * __Taxonomy-Specific Displays__: Use the `display_only_on_tax_ids` parameter to restrict popular post displays to specific taxonomy terms.
  * __Category Inclusion__: Include popular posts from specific categories using a new option in the Edit Post meta box.
  * __RSS Feed Filtering__: Enhanced RSS feeds with category and post type filtering.

* __Developer-Friendly Features__
  * __Filters and Hooks__: New filters like `top_ten_query_exclude_terms_include_parents`, `top_ten_query_include_terms_include_parents`, and `get_tptn_short_circuit` for greater customisation.
  * __Custom Post Type Sortable Columns__: Display columns on post types and make them sortable.

### GDPR

Top 10 does not collect personal visitor data out of the box. Tracking data is stored locally in the `wp_top_ten` and `wp_top_ten_daily` database tables (table prefix may vary).

You are responsible for ensuring GDPR compliance on your website.

### Translations

Top 10 is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.

### Other plugins from WebberZone

Top 10 - Popular Posts is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
* [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

## Screenshots

![Top 10 Popular Posts](https://raw.github.com/WebberZone/top-10/master/wporg-assets/screenshot-1.png)
*Top 10 Popular Posts*

For more screenshots visit the [WordPress plugin page](http://wordpress.org/plugins/top-10/screenshots/).

## Installation

### WordPress install (the easy way)

1. Navigate to Plugins within your WordPress Admin Area

2. Click "Add new" and in the search box enter "Top 10"

3. Find the plugin in the list (usually the first result) and click "Install Now"

### Manual install

1. Download the __top-10.zip__ file from this release post
2. Visit __Plugins__ in your Admin Area
3. Hit the __Add New__ button next to the Plugins heading
4. Hit the __Upload__ button next to the Add Plugins heading
5. Select the __top-10.zip__ file that you downloaded and hit Install Now
6. Activate the Plugin in WP-Admin.
7. Go to __Top 10 &raquo; Settings__ to configure
8. Go to __Appearance &raquo; Widgets__ to add the Popular Posts sidebar widget to your theme
9. Go to __Top 10 &raquo; View Popular Posts__ to view the list of popular posts

## Frequently Asked Questions

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/top-10/faq/) and the [FAQ on the WebberZone knowledgebase](https://webberzone.com/support/section/top-10/).

If your question isn't listed there, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/top-10). It is the fastest way to get support as I monitor the forums regularly.

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](http://wordpress.org/plugins/top-10). In addition to stable releases, latest beta versions are made available under [releases](https://github.com/WebberZone/top-10/releases).
