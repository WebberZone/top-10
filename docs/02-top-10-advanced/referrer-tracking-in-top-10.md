---
slug: referrer-tracking-in-top-10
title: "Referrer tracking in Top 10 Pro"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [pro, referrers, statistics, top-10]
status: publish
order: 0
toc: true
---

[toc]

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) 4.7.0 can record which external sites send visitors to each post. Use it to see which sites, search engines and social networks drive your views.

## Turn on referrer tracking

**Track external referrers** is a Pro setting in [Counter and tracker options](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/). It is off by default and needs an active Top 10 Pro license.

It works with both **Funnel** and **Legacy** tracking methods. Top 10 only records referrers for views that it counts, so your tracker, user group and bot settings still apply.

## What is stored

Top 10 stores a daily count of views for each post and external source. Each source is reduced to its scheme and host, for example `https://news.example.com`. Paths, query strings, fragments and credentials are discarded before anything is saved.

- A leading `www.` is removed, so `www.example.com` and `example.com` are one source.
- Referrers from your own site are ignored.
- Only `http`, `https`, `android-app` and `ios-app` sources are recorded.
- Invalid or oversized values are ignored.

Visits with no referrer, such as direct visits and links from sources that hide the referrer, are not recorded as a source.

## Where referrers appear

- **Referrers screen:** the **Referrers** item in the Top 10 admin menu lists up to 100 top external hosts for the site, with their views. It is visible to users with the `manage_options` capability.
- **Statistics screen:** an **External referrers** panel below the table shows the top 20 hosts for the date range selected in the statistics filter. It is visible to users with the `manage_options` capability.
- **Post editor:** the Top 10 metabox shows up to 10 top external referrers for that post, to users who can edit it.

## Retention

Referrer counts are kept for the **Visits log retention period** set in [Maintenance options](https://webberzone.com/support/knowledgebase/top-10-maintenance-options/). The default is 30 days. The scheduled maintenance job removes older rows and any host that no longer has counts. Each screen notes that it shows referrers recorded within that period.

Use the `tptn_log_retention_days` filter to change the period in code.

## Privacy

Top 10 does not use tracking cookies and does not store IP addresses or user agents for referrer tracking. The stored data is limited to the external host, the post, the site and the date. Review your site's privacy information for the features you enable. This description does not determine your site's legal obligations.

## Removing the data

Turning the setting off stops new referrers from being recorded and keeps the counts already stored until retention removes them. Uninstalling Top 10 removes the referrer tables only when **Delete counter data on uninstall** is enabled.

## See also

- [Unique visitors in Top 10 Pro](https://webberzone.com/support/knowledgebase/unique-visitors-in-top-10/)
- [Trackers in Top 10](https://webberzone.com/support/knowledgebase/trackers-in-top-10/)
