---
slug: top-10-multisite-network-performance
title: "Top 10 Multisite Network Performance"
products: [top-10]
sections: ["02-top-10-advanced"]
tags: [top-10, performance, multisite]
status: publish
order: 0
toc: true
---

[toc]

The Top 10 network admin screens read view counts across every active site in the network, so they do more database work than the equivalent single-site screens. Version 4.5.0 changed how those screens query the database.

This article describes what changed, what it is worth on a real network, and how to measure it on your own. Single-site installs see very little of it. Networks with many sites are where it shows.

## Table checks now come from a cached option

Every [Top 10](https://webberzone.com/plugins/top-10/) admin page used to confirm the plugin's four tables existed before rendering:

```sql
SHOW TABLES LIKE 'wp\_top\_ten';
SHOW TABLES LIKE 'wp\_top\_ten\_daily';
SHOW TABLES LIKE 'wp\_top\_ten\_visits\_log';
SHOW TABLES LIKE 'wp\_top\_ten\_visits\_funnel';
```

That is four uncached queries on every admin page load. They now come from a versioned `tptn_tables_installed` network option instead.

The Tools page is the exception. It still runs the live checks, because that screen reports table status and needs the current answer rather than a cached one.

## The network popular posts query runs in two phases

The network dashboard query used to join the entire daily table as a subquery, then group the result:

```sql
SELECT ttt.postnumber AS ID, ttt.cntaccess AS total_count,
       SUM(ttd.cntaccess) AS daily_count, ttt.blog_id AS blog_id
FROM wp_top_ten AS ttt
LEFT JOIN (
    SELECT *
    FROM wp_top_ten_daily AS ttd
    WHERE DATE(ttd.dp_date) >= DATE('2026-09-01')
      AND DATE(ttd.dp_date) <= DATE('2026-09-01')
) AS ttd
  ON ttt.postnumber = ttd.postnumber AND ttt.blog_id = ttd.blog_id
GROUP BY ttt.postnumber, ttt.blog_id
ORDER BY total_count DESC
LIMIT 0, 9;
```

Wrapping `dp_date` in `DATE()` means no index can be used for that comparison, so the subquery scans the daily table however large it is.

The query now orders first and fetches second. Ordering by total views reads the overall table directly:

```sql
SELECT postnumber AS ID, blog_id, cntaccess AS total_count
FROM wp_top_ten
ORDER BY cntaccess DESC, postnumber ASC, blog_id ASC
LIMIT 0, 9;
```

Ordering by daily views pre-aggregates only the selected date range, using an index-friendly range comparison in place of the `DATE()` calls:

```sql
SELECT postnumber AS ID, blog_id, SUM(cntaccess) AS daily_count
FROM wp_top_ten_daily
WHERE dp_date >= '2026-09-01 00:00:00'
  AND dp_date < '2026-09-02 00:00:00'
GROUP BY postnumber, blog_id
ORDER BY daily_count DESC, postnumber ASC, blog_id ASC
LIMIT 0, 9;
```

The second phase then fetches the other count for the surviving `(postnumber, blog_id)` pairs only.

On the test network, the query plans used `idx_cntaccess` for total ordering (cost 30.7, 304 estimated rows) and an `idx_dp_date` range scan for daily ordering (cost 5.21, 11 estimated rows).

## Historical tabs and Tools statistics

Historical dashboard tabs load on demand rather than all at once, so opening the dashboard no longer pays for tabs you do not look at.

The Tools page uses estimated row counts and direct table probes for popular post data instead of exact counts across every site in the network.

## Measured results

Captured on 2026-09-01 against a local test network running WordPress 7.1 and Top 10 Pro, with Query Monitor 4.0.7. Three sites were active. Each page was loaded ten times with fresh sequential reloads, and the figures below are Query Monitor values.

Before the changes:

| Page | Server mean / median (s) | Server range (s) | DB mean (s) | DB queries | Top 10 queries | `SHOW TABLES` |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Site dashboard | 0.4224 / 0.3942 | 0.3611–0.6781 | 0.0288 | 811 | 26 | 4 |
| Network dashboard | 0.3860 / 0.3844 | 0.3731–0.4161 | 0.0925 | 757 | 23 | 4 |
| Network popular posts, total | 0.3120 / 0.3114 | 0.2968–0.3249 | 0.0644 | 196 | 128 | 4 |
| Network Tools | 0.2830 / 0.2729 | 0.2573–0.3238 | 0.0575 | 88 | 19 | 4 |

After the changes, measured over ten cold network dashboard runs with the widget cache cleared before each request:

| Measure | Server (s) | DB (s) |
| --- | ---: | ---: |
| Mean | 0.320965 | 0.048232 |
| Median | 0.320114 | 0.048548 |

That is 16.85% lower server time and 47.86% lower database time than the network dashboard baseline. A warm cached request came in at 0.298044 seconds, with 6 Top 10 queries and no popular posts query at all.

Your own numbers will differ. The size of the change depends on how many sites are active and how much history the daily table holds.

## Measuring your own network

Use Query Monitor to compare before and after on your own install. The [debugging with Query Monitor](https://webberzone.com/support/knowledgebase/debugging-with-query-monitor/) article covers installing it and locating the Top 10 queries.

Two things matter when taking the measurements:

1. Clear the network widget cache before each cold run. Otherwise you measure the cache, not the query.
2. Load each page several times and take the median. A single reload on a local server varies enough to hide a real difference.
