---
slug: feed-vs-web-statistics
title: "Feed vs web statistics"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [feeds, pro, statistics, top-10]
status: publish
order: 0
toc: true
---

[toc]

[Top 10 Pro](https://webberzone.com/plugins/top-10/pro/) 4.6.0 adds a **Feed vs web** tab to the Top 10 dashboard. Use it to compare views from RSS and Atom readers with other views and identify the posts most read through feeds.

## Enable feed tracking

Turn on **Track feed views** in [Counter and tracker options](https://webberzone.com/support/knowledgebase/top-10-settings-counter-tracker-options/). It is disabled by default. Top 10 adds a tracking pixel to feeds, and a view is counted only when the feed reader loads that image.

Feed views remain part of the regular total and custom-period counts. The report separates their share rather than adding them a second time. Readers that block remote images do not generate pixel views, so these figures are not subscriber counts.

**Koko Analytics only** mode stops Top 10 tracking, including feed tracking. **Hybrid** keeps Top 10 tracking active. This report always uses Top 10's count tables, even when Koko Analytics powers your popular posts lists.

## Read the report

Open **Feed vs web** on the Top 10 dashboard. The summary shows the last 7 days, last 30 days, and all time. The recent periods include today in the site's timezone.

| Column | Meaning |
| --- | --- |
| Feed views | Views identified as coming from feed tracking. |
| Web views | Total views minus identified feed views. |
| Total | Combined regular view count. |
| Feed share | Feed views as a percentage of the total. |

Two lists show up to ten posts ranked by feed views: **Top posts read via feed (last 30 days)** and **Top posts read via feed (all time)**. Site-wide context IDs, such as archives and searches, are excluded.

The Network Admin dashboard combines counts across sites and includes a Site column. Figures are cached for 15 minutes by default, so new views can take time to appear after aggregation.

## Historical feed views

The upgrade adds a separate feed count to the existing overall and daily tables. Top 10 backfills historical feed views from the visits log in the background. Figures can grow while that process runs.

Only feed visits still present in the log can be recovered. The report shows the date from which feed views are counted separately. Earlier feed views remain included in the Web views figure. Legacy tracking does not populate the visits log, so it cannot supply historical rows for this backfill.

The recent report also depends on retained daily data. See [Maintenance options](https://webberzone.com/support/knowledgebase/top-10-maintenance-options/) for daily and visits-log retention.

## If the report needs a database update

If the database cannot add the feed column automatically, the report asks you to enable feed view counts from the [Tools screen](https://webberzone.com/support/knowledgebase/top-10-tools-admin-screen/). Use **Add Feed Views Column** during a quiet period because the operation may rebuild large count tables. On multisite, use the Network Admin Tools screen.

Top 10 Pro also provides:

```bash
wp top10 db add-feed-columns --dry-run
wp top10 db add-feed-columns
```

The dry run makes no changes. After the column is enabled, historical backfill runs in the background.

## Export and import

Count CSV exports include a trailing **Feed Visits** column when feed count data is available. Admin imports and WP-CLI imports in `add` mode restore this value along with Visits. WP-CLI `set` mode ignores Feed Visits. Feed Visits is a subset of Visits, so do not add the two columns together when calculating totals.

## See also

- [Trackers in Top 10](https://webberzone.com/support/knowledgebase/trackers-in-top-10/)
- [WP-CLI commands in Top 10 Pro](https://webberzone.com/support/knowledgebase/top-10-cli-commands/)
