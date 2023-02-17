# Top 10  - Popular posts plugin for WordPress

![Top 10](https://raw.githubusercontent.com/WebberZone/top-10/master/wporg-assets/banner-1544x500.png)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)

__Requires:__ 5.6

__Tested up to:__ 6.2

__Requires PHP:__ 7.2

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Top 10](https://webberzone.com/plugins/top-10/) | [WordPress.org plugin page](https://wordpress.org/plugins/top-10/)

Track daily and total visits on your blog posts. Display the count as well as popular and trending posts.

## Description

WordPress doesn't have an in-built system to track page views or displaying popular posts. [Top 10](https://webberzone.com/plugins/top-10/) is an easy to use, yet, powerful WordPress plugin that will count the number of page views of your posts, pages and any custom post types. You can then display the page view counts as well as display your most popular posts.

Top 10 adds two widgets that you can use to display a list of popular posts and the counta cross all your blog posts.

Although several similar plugins exist today, Top 10 is one of the most feature-rich popular post plugins with support for thumbnails, shortcodes, widgets, custom post types and CSS styles. The inbuilt caching system also helps reduce server load by caching your popular posts output. The tracking uses ajax and is thus compatible with most popular caching plugins.

Top 10 also has powerful API and is fully extendable with WordPress actions and filters to allow you easily extend the code base to add new features or tweak existing ones.

### Features

* __Page counter__: Counts page views on single posts, pages and *custom post types* on an hourly basis which can then be easily displayed automatically, using shortcodes or functions
* __Popular posts__: Display a list of popular posts either for total counts or for a custom period. You can choose how many posts are to be displayed along with loads of other customisation options
* __Widget ready__: Sidebar widgets available for daily popular and overall popular posts. Highly customizable widgets to control what you want to display in the list of posts
* __Shortcodes__: The plugin includes two shortcodes `[tptn_list]` and `[tptn_views]` to display the posts list and the number of views respectively
* __Thumbnail support__
  * Support for WordPress post thumbnails. Top 10 will create a custom image size (`tptn_thumbnail`) with the dimensions specified in the Settings page
  * Auto-extract the first image in your post to be displayed as a thumbnail
  * Manually enter the URL of the thumbnail via [WordPress meta fields](http://codex.wordpress.org/Custom_Fields). Specify this using the meta box in your Edit screens.
* __Exclusions__: Exclude posts from select categories from appearing in the top posts list. Also exclude posts by ID from appearing in the list
* __Styles__: The output is wrapped in CSS classes which allows you to easily style the list. You can enter your custom CSS styles from within WordPress Admin area or use the style included.
* __Admin interface__: View list of daily and/or overall popular posts from within the dashboard. Top 10 also adds two sortable columns to your All Posts and All Pages pages in your WordPress Admin area
* __Export/Import interface__: Export the count tables and settings to restore in the same site or on other installs
* __Works with caching plugins__ like WP-Super-Cache, W3 Total Cache or Quick Cache
* __Extendable code__: Top 10 has tonnes of filters and actions that allow any developer to easily add features, edit outputs, etc.

### GDPR

Top 10 is GDPR compliant as it doesn't collect any personal data about your visitors when installed out of the box. You can see the data the plugin stores in the `wp_top_ten` and `wp_top_ten_daily` tables in the database. Note: the prefix `wp` might be different if you have changed it from the default.

YOU ARE RESPONSIBLE FOR ENSURING THAT ALL GDPR REQUIREMENTS ARE MET ON YOUR WEBSITE.

### Donations

I spend a significant amount of my free time maintaining, updating and more importantly supporting this plugin. If you have been using this plugin and find this useful, do consider making a donation. This helps me pay for my hosting and domains.

### Translations

Top 10 is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/top-10). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/rosetta/theme-plugin-directories/) to contribute.

## Screenshots

![Style Options](https://raw.github.com/WebberZone/top-10/master/wporg-assets/screenshot-5.png)
*Top 10 settings page - Styles Options*

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

If your question isn't listed there, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/top-10). It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](http://wordpress.org/plugins/top-10). In addition to stable releases, latest beta versions are made available under [releases](https://github.com/WebberZone/top-10/releases).
