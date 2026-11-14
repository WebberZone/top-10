---
slug: unique-visitors-in-top-10
title: "Unique visitors in Top 10 Pro"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [privacy, pro, top-10, unique-visitors]
status: publish
order: 0
toc: true
---

[toc]

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) 4.7.0 can count each visitor once per post per day, without cookies. Use unique visitor counts alongside views to see which posts reach the most people, not just which posts are reloaded the most.

## Turn on unique visitor counts

**Count unique visitors** is a Pro setting in [Counter and tracker options](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/). It is on by default and needs an active Top 10 Pro license.

Unique visitors are counted for the same views as the regular counters, so your tracker, user group and bot settings still apply. The feature works with both **Funnel** and **Legacy** tracking methods.

## How visitors are counted

For each tracked view, Top 10 builds a digest from the visitor's IP address and user agent, the site, the post and a random salt that changes every day. If that digest has not been seen for that post today, the view counts as a new visitor.

- The IP address and user agent are used only during the request. They are never stored.
- The salt is different for each day, so a visitor cannot be linked from one day to the next. The digest includes the post, so a visitor cannot be linked across posts either.
- Digests are removed after the day ends. Top 10 keeps only the daily visitor totals.
- Visitors with a missing or invalid IP address or user agent are counted as views but not as unique visitors.

A visitor who returns on another day is counted again for that day. Unique visitors for a longer period are the sum of the daily counts, so the same person can be included more than once.

Storage for the daily digests is capped per site and day. If a very busy day reaches the cap, further visitors that day are not counted and the day is reported as partial or unavailable.

## Coverage labels

Visitor counts begin when the setting is first active. Views recorded earlier have no visitor data, and any time the feature is off, paused or at its daily cap leaves a gap. Reports show how complete a figure is:

| Label | Meaning |
| --- | --- |
| Complete | Every day in the period was measured. |
| Partial | Some of the period was not measured. The figure is shown with "(partial)" and is a lower bound. |
| Unavailable | The period was not measured. No figure is shown. |

Turning off **Count unique visitors** or deactivating Top 10 Pro pauses counting and marks that day as incomplete, so the gap shows as partial in reports.

## Where unique visitors appear

- **Popular post lists, widgets, blocks and queries:** sort by unique visitors instead of views. In the **Popular Posts** widget, use **Order posts by**. In the [Popular Posts blocks](https://webberzone.com/support/knowledgebase/popular-posts-blocks/), choose **Unique visitors** under **Order by**. In `Top_Ten_Query`, pass `orderby => 'visitors'`.
- **Posts and Pages screens:** a sortable **Unique Visitors** column is added when **Page views in admin** is enabled.
- **Top 10 dashboard widgets:** each row shows the visitor count after the views, for example "1,240 views · 310 visitors".
- **Statistics screen:** the **Total unique visitors** and **Unique visitors in period** columns can be sorted.
- **WP-CLI:** `wp top10 popular --orderby=visitors` and a **Visitors** column. See [WP-CLI commands in Top 10 Pro](https://webberzone.com/support/knowledgebase/top-10-cli-commands/).
- **Abilities API and REST:** `visitors` and `visitor_coverage` fields. See [Top 10 Abilities API](https://webberzone.com/support/knowledgebase/top-10-abilities-api/).

## Privacy

Top 10 does not use tracking cookies. The free plugin does not store IP addresses or user agents. Top 10 Pro with this feature on processes the request IP address and user agent transiently to build the daily digest, and stores only the digest.

Review your site's privacy information for the features you enable. This description does not determine your site's legal obligations.

## Developer hooks

| Hook | Purpose |
| --- | --- |
| `tptn_unique_visitor_marker_shard_limit` | Filters the number of daily digests stored per shard. Default `16384`, across 64 shards. |
| `tptn_enable_visitor_sort` | Whether `Top_Ten_Query` accepts `visitors` as an `orderby` value. Pro turns this on. |
| `tptn_widget_orderby_options` | Filters the options in the widget's **Order posts by** dropdown. |

## Removing the data

Uninstalling Top 10 removes the unique visitor tables only when **Delete counter data on uninstall** is enabled. Turning the setting off stops new counting and keeps the daily visitor totals already recorded.

## See also

- [Trackers in Top 10](https://webberzone.com/support/knowledgebase/trackers-in-top-10/)
- [Referrer tracking in Top 10 Pro](https://webberzone.com/support/knowledgebase/referrer-tracking-in-top-10/)
