---
slug: top-10-settings-general-options
title: "Top 10 Settings – General options"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [settings,top-10,top-10-settings]
status: publish
order: 0
---

[kbtoc]

The **General options** tab lets you configure the main options for Top 10 on your WordPress blog.

## Enable cache

Top 10 includes an inbuilt caching system that uses the <a href="https://codex.wordpress.org/Transients_API" target="_blank" rel="noopener">Transients API</a> to cache the plugin's complete output. The cache is built to complement other caching plugins and will work alongside them. Select this option to turn the cache on.

You can find a **Clear cache** button to empty the cache under the Tools page.

## Time to cache

Enter the number of seconds to cache the output of the popular posts.

## Lazy load popular posts *(Pro only)*

Load popular posts using JavaScript when the list is about to enter the viewport. This speeds up initial page loads and lets full-page caches serve fresh lists. Search engines may not index links loaded this way. Applies to content, shortcodes, widgets and the Popular Posts block. Use `lazy_load="0"` in the shortcode or block attributes to disable it per instance. Not applied on feeds or AMP pages.

## Delete options on uninstall

If this is checked, all settings related to Top 10 are removed from the database if you choose to uninstall/delete the plugin.

## Delete counter data on uninstall

If this is checked, the tables containing the counter statistics are removed from the database if you choose to uninstall/delete the plugin.

Keep this unchecked if you choose to reinstall the plugin and don't want to lose your counter data.

## Show metabox

Top 10 adds a metabox at the bottom of the Edit screen on posts, pages and custom post types. This allows you to set the Overall post count, exclude the post from showing up in post lists and more.

Disable this option to turn it off.

## Limit metabox to Admins only

If this is selected, the metabox will be hidden from anyone who is not an Admin. Otherwise, by default, Contributors and above can see the metabox. This applies only if the above option is selected.

## Display admin columns

Adds three columns called Total Views, Custom period Views and Views to All Posts and All Pages. The middle column label is dynamic: it shows **Daily Views** when the custom period is set to 1 day, or **Custom (N days) Views** when the range is longer. You can selectively disable these by pulling down the Screen Options from the top right of the respective screens.

## Display columns on post types *(Pro only)*

Select which post types display the admin columns above. Unselect the **Display admin columns** option if you would like to disable the columns entirely.

## Show views to non-admins

If you disable this then non-admins won't see the above columns or view the independent pages with the top posts.

## Also show dashboard to *(Pro only)*

Choose the user roles that should have access to the Top 10 dashboard, which showcases popular posts over time. These roles are linked to specific capabilities, and selecting a lower role automatically grants access to higher roles.

## Show Admin Bar menu *(Pro only)*

Display the Top 10 menu in the WordPress admin bar with quick access to popular posts stats and tools.

## Query Optimization *(Pro only)*

### Max Execution Time

Maximum execution time for MySQL queries in milliseconds. Set to 0 to disable. Default is 3000 (3 seconds).

## Link to Top 10 plugin page

A no-follow link to the plugin is added as an extra list item to the list of popular posts. Turn this on to let your visitors know that you're running this awesome plugin!
