=== WebberZone Top 10 — Popular Posts ===
Tags: popular posts, post views, page views, most viewed posts, popular posts widget
Contributors: webberzone, ajay
Donate link: https://wzn.io/donate-wz
Stable tag: 4.6.1
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track post views and page views, and display popular posts and trending content on your WordPress site.

== Description ==

[Top 10](https://webberzone.com/plugins/top-10/) is a post views counter and popular posts plugin for WordPress. It counts daily and total page views on posts, pages and custom post types, then displays your most viewed and trending posts with blocks, widgets and shortcodes.

All tracking data stays in your WordPress database, with no external services, no cookies and no personal data. Views are queued in a lightweight table and counted in the background, so tracking works behind page caches without slowing down your pages.

Top 10 also offers thumbnails with fallbacks, flexible display options and a built-in cache. Developers can customize queries, tracking and output with WordPress actions and filters.

= Features =

* **Page Counter**: Tracks hourly post views on posts, pages, and custom post types. Display counts automatically using blocks, shortcodes, or template functions
* **Popular Posts**: Display most viewed posts by total counts or within custom time periods
* **Blocks**: Popular Posts and Post Count blocks with configurable display options
* **Widgets**: Sidebar widgets for daily and overall popular posts
* **Shortcodes**: Use `[tptn_list]` to display popular post lists and `[tptn_views]` to show view counts
* **Thumbnails**: Featured images, the first image in the post, or a default image
* **Exclusions and Styling**: Exclude posts by category or ID, and use the included styles or your own CSS
* **Admin Dashboard**: Daily and overall popular posts with a views chart, plus sortable view-count columns on post lists
* **Multisite Dashboard**: Network-wide statistics across all sites in a multisite network
* **Bot Filtering**: Optionally skip known bots when counting views
* **Koko Analytics Data Source**: Build popular posts lists from Koko Analytics page views, optionally switching off Top 10 tracking
* **Import and Export**: Export and import view counts and settings, and migrate view data from WordPress Popular Posts
* **Popular Posts Feed**: RSS feeds of your daily and overall popular posts
* **REST API and Abilities API**: Popular posts and view counts for integrations, including the Abilities API on WordPress 6.9+
* **Caching Compatibility**: Works with WP Rocket, LiteSpeed Cache, W3 Total Cache, WP Super Cache, and similar plugins
* **Developer-Friendly**: Filters and actions to customize queries, tracking, and output

= Features in Top 10 Pro =

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) adds the following. See [Free vs Pro](https://webberzone.com/support/knowledgebase/free-vs-pro-top-10/) for a full comparison.

* **Faster Tracking**: [Fast and High-traffic trackers](https://webberzone.com/support/knowledgebase/trackers-in-top-10/), site-wide tracking of archives and searches, and feed view counting
* **Advanced Blocks**: [Advanced Popular Posts block](https://webberzone.com/support/knowledgebase/popular-posts-blocks/) with editable cards and six layouts, plus the Query, Featured Image and Popular Posts Pro blocks
* **Page Builders**: [Elementor, Bricks and WPBakery elements](https://webberzone.com/support/knowledgebase/top-10-page-builder-integrations/)
* **Popular Authors**: [Popular authors](https://webberzone.com/support/product/popular-authors/) blocks, shortcodes and widgets, built in
* **Performance**: [Lazy-loaded lists](https://webberzone.com/support/knowledgebase/top-10-settings-general-options/), a query time limit, and [configurable data retention](https://webberzone.com/support/knowledgebase/top-10-maintenance-options/)
* **Admin Tools**: [Admin bar menu, Feed vs web report, dashboard access by role and more](https://webberzone.com/support/knowledgebase/admin-enhancements-in-top-10-pro/)
* **Multisite**: [Network-wide popular posts lists](https://webberzone.com/support/knowledgebase/top-10-multisite-network-performance/), including Koko Analytics data, and copying settings between sites
* **Display Options**: Grid Thumbs style, lists restricted to taxonomy terms, and RSS feeds filtered by category or post type
* **WP-CLI**: [`wp top10` commands](https://webberzone.com/support/knowledgebase/top-10-cli-commands/) for counts, database, cache, settings, cron and status
* **Abilities API**: [Count editing and cache clearing](https://webberzone.com/support/knowledgebase/top-10-abilities-api/) for integrations

= Privacy and GDPR =

Top 10 does not use cookies and does not store personal data. Its database tables (`wp_top_ten`, `wp_top_ten_daily`, `wp_top_ten_visits_funnel` and `wp_top_ten_visits_log`; the prefix may vary) hold only post IDs, site IDs, view counts, visit times and whether a view came from the web or a feed. No IP addresses or user agents are stored. The user agent is read only to skip bots when that option is on.

You are responsible for ensuring GDPR compliance on your website.

= Multilingual sites =

Top 10 works with WPML, Polylang and TranslatePress without extra configuration, and caches popular posts separately for each language. See [Multilingual support](https://webberzone.com/support/knowledgebase/multilingual-support-top-10/).

= Translations and contributions =

Help [translate Top 10](https://translate.wordpress.org/projects/wp-plugins/top-10) on WordPress.org. Top 10 is developed on [GitHub](https://github.com/WebberZone/top-10), where pull requests are welcome. Please use the support forum, not GitHub, for support requests.

== Other WebberZone Plugins ==

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) – Related posts for your site and feeds
* [Better Search](https://wordpress.org/plugins/better-search/) – Relevance-based WordPress search
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) – Knowledge base and FAQ sections
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) – HTML, CSS and JavaScript snippets
* [Auto-Close](https://wordpress.org/plugins/autoclose/) – Close comments, pingbacks and trackbacks
* [WebberZone Link Warnings](https://wordpress.org/plugins/webberzone-link-warnings/) – Accessible warnings for external links

== Screenshots ==

1. Top 10 – Popular posts overview in the WordPress admin
2. Top 10 – Popular posts list with thumbnails

== Installation ==

= WordPress install (easy way) =
1. Go to Plugins → Add New in your WordPress Admin
2. Search for “Top 10”
3. Click Install Now and then Activate
4. Configure settings under Top 10 → Settings

= Manual install =
1. Download the `top-10.zip` file
2. Go to Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click Install Now
4. Activate the plugin
5. Configure settings under Top 10 → Settings

= WP-CLI install =
Run `wp plugin install top-10 --activate`. On a multisite network, use `--activate-network` instead.

== Frequently Asked Questions ==

= Where can I get help and support? =

Check the [Top 10 documentation](https://webberzone.com/support/product/top-10/), then ask in the [WordPress.org support forum](https://wordpress.org/support/plugin/top-10/). Top 10 Pro users with an active license can [request support](https://webberzone.com/request-support/).

= How do I display popular posts? =

Add the Popular Posts block, the Popular Posts widget, or the `[tptn_list]` shortcode. Developers can call `tptn_show_pop_posts()` in a theme template. See [Top 10 shortcodes](https://webberzone.com/support/knowledgebase/top-10-shortcodes/) for the shortcode options.

= How do I show the view count on posts? =

Choose where to show the count under Top 10 → Settings → Counter/Tracker. You can also use the Post Count block or the `[tptn_views]` shortcode.

= How does tracking work? =

Each visit is queued in a lightweight funnel table and added to the count tables every two minutes by a cron job, so tracking never blocks page loads and works correctly behind page caches.

For tracker types and how to change the interval, see [Trackers in Top 10](https://webberzone.com/support/knowledgebase/trackers-in-top-10/).

= Does Top 10 work with page caching plugins? =

Yes. Views are recorded by a JavaScript tracker, so cached pages are still counted. Top 10 Pro can also lazy-load popular posts lists so cached pages show fresh lists.

= Can I import views from WordPress Popular Posts? =

Yes. Go to Top 10 → Import/Export and use the WordPress Popular Posts importer. The WordPress Popular Posts tables must still be in your database.

= Does Top 10 count bots? =

Turn on "Do not track bots" under Top 10 → Settings → Counter/Tracker to skip known bots.

= Can I use Koko Analytics data? =

Yes. Set the Data source under Top 10 → Settings → Counter/Tracker. See [Use Koko Analytics as the data source for Top 10](https://webberzone.com/support/knowledgebase/koko-analytics-data-source/).

= Does it work on multisite? =

Yes. Each site keeps its own counts, and the Network Admin dashboard shows statistics across all sites. Popular posts lists that combine several sites need Top 10 Pro.

= Can this plugin replace Google Analytics? =

No. Top 10 tracks page views and displays popular posts. It is not designed to replace analytics platforms.

= How can I report security bugs? =

Report security bugs through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/top-10). The Patchstack team helps validate, triage and handle any security vulnerabilities.

== Changelog ==

= 4.6.1 =

Release date: 31 October 2026

**Security**

* Hardened popular posts lists so they no longer show attachments of private, draft or other unpublished posts.
* Hardened the `post_status` and `post_type` attributes of the `[tptn_list]` shortcode, which now accept only published content and viewable post types.

= 4.6.0 =

Release date: 15 October 2026
Release post: https://webberzone.com/announcements/top-10-v4-6/

**Added**

* Koko Analytics as an optional data source for popular posts, including a mode that switches off Top 10 tracking.
* [Pro] Koko Analytics data in popular posts lists that span network sites.
* WordPress Abilities API support for popular posts and view counts on WordPress 6.9+.
* [Pro] Abilities for editing view counts and clearing the cache.
* [Pro] Advanced Popular Posts block with editable cards and six layout patterns.
* [Pro] Feed vs web tab on the Top 10 dashboard, reporting feed and web views and the posts most read via feed.
* Feed Visits column in count exports, restored on import.

**Changed**

* Popular posts lists spanning multiple network sites are now Pro only.
* Cached popular posts lists refresh when view counts or posts change, instead of waiting for the cache to expire.
* Daily popular posts queries add up view counts before loading posts, reducing query time on large sites.

**Security**

* Prevented password-unlocked excerpts from being shared with other visitors through cached popular posts lists.
* Prevented thumbnails from exposing images inside or attached to password-protected posts before the password was entered.
* Hardened thumbnail URLs to reject unsafe protocols.
* Restricted the popular posts REST endpoint to its documented parameters (`limit`, `post_types`, `daily` and `context`), preventing unbounded queries and probing of hidden post meta. `limit` is capped at 100 and `post_types` accepts only public post types.
* Prevented the view count REST endpoint from returning counts for posts the visitor cannot read, such as drafts.

**Fixed**

* Malformed custom thumbnail dimensions caused PHP errors during image-size selection.
* The popular posts query cache was never reused because its key changed every second.
* With Do not track bots enabled, views were not counted on cached pages that a bot had generated.
* Custom date ranges, such as those on the Top 10 dashboard, were rounded to midnight or the hour instead of using the exact start and end times.
* Every database upgrade re-altered the visits log and funnel table columns.
* The popular posts REST endpoint and `get_tptn_posts()` returned no posts, or raised a PHP warning, when called without a `limit`.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/top-10/releases).

== Upgrade Notice ==

= 4.6.1 =
Fixes Pro lazy loaded popular posts lists on cached pages showing the global custom period instead of their own, and hardens popular posts lists against showing unpublished content. Update recommended.
