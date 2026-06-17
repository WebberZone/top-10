---
slug: top-10-settings-counter-tracker-options
title: "Top 10 Settings – Counter and tracker options"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [settings,top-10,top-10-settings]
status: publish
order: 0
---

[kbtoc]

The **Counter and Tracker options** is the second tab in the Top 10 settings page and allows you to tweak the counter and tracker.

## Counter settings

### Display number of views on

This option lets you select where you'd like to display the views of the current post. By default Posts and Pages are enabled.

If you choose to disable this, please add `<?php if ( function_exists ( 'echo_tptn_post_count' ) ) echo_tptn_post_count(); ?>` to your template file where you want it displayed.

### Format to display the post views

Customize the text used to display the post count when you have the above setting enabled or if you're using the function as above.

Use `%totalcount%` to display the total count, `%dailycount%` for daily count and `%overallcount%`for overall counts across all posts.

Default display is: `Visited %totalcount% times, %dailycount% visit(s) today`

### No visits text

This text is displayed when there are no hits for the post and it isn't a single page. For example, if you display post views on the Homepage or Archives, this text will be used. To override this, simply enter the same text as the above option.

### Number format post count

Activating this option will convert the post counts into a <a href="https://developer.wordpress.org/reference/functions/number_format_i18n/" target="_blank" rel="noreferrer noopener" aria-label="number format based on the locale (opens in a new tab)">number format based on the locale</a>.

### Start daily counts at midnight

The daily counter displays visits starting at midnight. This option is enabled by default, similar to most standard counters. If you disable this option, you can use the hourly setting in the next option.

### Default custom period range

Set the number of days and hours that will be used for the "Daily" display.

### Always display latest post count

This option uses JavaScript and could increase your page load time as it isn't cached. Turn this off if you are not using caching plugins or are OK with displaying slightly older cached counts.

### Exclude display on these post IDs

Enter a comma-separated list of post, page or custom post type IDs to exclude displaying the top posts on. e.g. 188,320,500

## Tracker settings

### Enable trackers

Top 10 tracks hits in two tables in the database. The overall table only tracks the total hits per post. The daily table tracks hits per post on an hourly basis. The daily tracker also powers the chart in the Top 10 Dashboard and is recommended to leave on.

### Tracker type

Top 10 includes three tracker types in the free plugin — REST API based, Query variable based, and Ajaxurl based — plus two additional high-performance options in Pro.

All tracker types share the same underlying recording mechanism: each page view writes a single row to the visits funnel table and returns immediately. A background cron job aggregates the funnel into the main count tables every two minutes. See [Trackers in Top 10](../02-top-10-advanced/trackers-in-top-10/) for a full explanation of this flow.

<a href="https://webberzone.com/plugins/top-10/pro/" data-type="page" data-id="8237">Top 10 Pro</a> adds two additional trackers:

- **Fast Tracker** — a standalone PHP endpoint that loads a minimal WordPress environment, reducing server overhead per tracked visit.
- **High-traffic Tracker** — bypasses WordPress entirely using a pre-generated config file. Requires generating the config from the Settings page before use.

### Load tracker on all pages

This will load the tracker js on all pages. Helpful if you are running minification/concatenation plugins.

### Track user groups

Turn on posts for Admins, Editors or Authors of their own posts. If the current user falls into any one of the three groups when browsing a post, then the tracker is disabled.

### Track logged-in users

Uncheck to stop tracking logged in users. Only logged out visitors will be tracked if this is disabled. Unchecking this will override the above setting.

### Page views in admin

Adds three columns called Total Views, Today's Views and Views to All Posts and All Pages. You can selectively disable these by pulling down the Screen Options from the top right of the respective screens.

### Show views to non-admins

If you disable this then non-admins won't see the above columns or browse the "Popular Posts" and "Daily Popular Posts" screens under the Top 10 admin menu.

### Do not track bots

Enable this if you want Top 10 to attempt to stop tracking bots. The plugin includes a comprehensive set of known bot user agents but in some cases this might not be enough to stop tracking bots.

### Debug mode

Setting this to true will force the tracker to display an output in the browser. This is useful if you are having issues and are seeking support.
