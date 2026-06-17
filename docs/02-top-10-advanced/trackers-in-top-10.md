---
slug: trackers-in-top-10
title: "Trackers in Top 10 – WordPress Popular Posts plugin"
products: [top-10]
sections: [02-top-10-advanced]
tags: [top-10,trackers]
status: publish
order: 0
---

[Top 10](https://webberzone.com/plugins/top-10/) offers various tracking methods to efficiently record post views. Each tracker type has unique benefits and considerations. This guide explains the different types of trackers available in the Top 10 WordPress plugin to help you select the best option for your site.

## Selecting the Tracker

The tracker can be set in the Top 10 \> Settings \> Counter/Tracker. Scroll down to tracker settings and select the **Tracker type**.

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-1024x404.webp" class="wp-image-9342" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-1024x404.webp 1024w, https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-300x118.webp 300w, https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-768x303.webp 768w, https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-1536x606.webp 1536w, https://webberzone.com/wp-content/uploads/2024/09/Tracker-settings-in-Top-10-Pro-2048x809.webp 2048w" sizes="auto, (max-width: 1024px) 100vw, 1024px" width="1024" height="404" />
</figure>

## Tracker Types

- **REST API-Based Tracker:** Utilizes the WordPress REST API to record visits using a custom endpoint `top-10/v1/tracker`. Offers flexibility and can be used directly in your web app. This will not work if REST API requests are blocked on your website.
- **Query Variable-Based Tracker:** Records visits using query variables appended to URLs. Simple and straightforward implementation, and should work with most sites.
- **Ajaxurl-Based Tracker:** Leverages the built-in `admin-ajax.php` file to process tracking requests. A good alternative to the Query Variable-based tracker.
- **Fast Tracker (Pro only):** A lightweight standalone PHP endpoint (`fast-tracker-js.php`) that handles tracking requests with a minimal WordPress bootstrap — no themes, widgets, or most plugins are loaded. This significantly reduces server overhead per tracked visit. If your hosting or a security plugin blocks access to the file, whitelist `/wp-content/plugins/top-10-pro/includes/pro/fast-tracker-js.php`.
- **High-traffic Tracker (Pro only):** Bypasses WordPress entirely. Tracking requests are handled by `high-traffic-tracker-js.php`, which reads a pre-generated config file (`top-10-fast-config.php`) placed in the WordPress root containing hardcoded database credentials. You must generate this config file from **Top 10 &gt; Settings** before the tracker will work, and regenerate it whenever your database credentials or table prefix changes.

## How Tracking Works

Understanding the full tracking flow helps you diagnose issues and explains why counts may appear with a short delay.

### Step 1 — A visit is recorded

When a visitor loads a post, the tracker JavaScript fires a request to the configured tracker endpoint. Regardless of which tracker type you choose, each visit inserts one row into the **visits funnel table** (`wp_top_ten_visits_funnel`). Each row stores the post ID, blog ID, timestamp, traffic source (web or RSS feed), and which counters to update (overall, daily, or both).

Writing a single row to the funnel is fast and lightweight — it does not touch the main count tables during the page request.

### Step 2 — The funnel is aggregated (every 2 minutes by default)

A WordPress cron job (`tptn_aggregation_cron_hook`) runs every two minutes by default and processes the funnel. In a single database transaction it:

1. **Copies** funnel rows to the **visits log table** (`wp_top_ten_visits_log`) — a raw audit trail of every visit.
2. **Aggregates** funnel rows into the **daily count table** (`wp_top_ten_daily`) — one row per post per hour, incremented by the number of funnel rows in that hour.
3. **Aggregates** funnel rows into the **overall count table** (`wp_top_ten`) — one row per post, running total.
4. **Deletes** the processed funnel rows.

All four steps run inside one transaction. If anything fails the transaction rolls back cleanly and the next cron run retries the same rows — there is no risk of double-counting.

### Step 3 — Old data is pruned

A separate maintenance cron (`tptn_cron_hook`) runs on the schedule you configure and removes old entries from both tables — but with **different retention periods**:

- **`wp_top_ten_daily`** — retains data for 180 days by default (controlled by `TOP_TEN_STORE_DATA` / `tptn_maintenance_days` filter).
- **`wp_top_ten_visits_log`** — retains data for 30 days by default (controlled by `TOP_TEN_LOG_STORE_DATA` / `tptn_log_retention_days` filter).

The log uses a shorter window because it stores one raw row per visit and grows proportionally to traffic. Once aggregation has run, those rows are already reflected in the count tables and serve only as an audit trail. See [Maintenance options](../01-top-10-getting-started/top-10-maintenance-options/) for how to adjust either retention period.

### Changing the aggregation interval

The default two-minute interval can be changed using the `tptn_aggregation_cron_interval` filter. The value must be a registered WP-Cron schedule name. Top 10 registers the following schedules out of the box:

| Schedule name | Interval |
|---|---|
| `one_minute` | Every 1 minute |
| `two_minutes` | Every 2 minutes (default) |
| `three_minutes` | Every 3 minutes |
| `five_minutes` | Every 5 minutes |

Example — run aggregation every 3 minutes:

```php
add_filter( 'tptn_aggregation_cron_interval', function() {
    return 'three_minutes';
} );
```

If you supply a schedule name that is not registered, WordPress will fall back to the nearest available interval. The change takes effect automatically on the next admin page load — no need to deactivate and reactivate the plugin.

### Syncing the funnel manually

If you need counts to update immediately — for example after importing data or testing — you can drain the funnel without waiting for the next cron run. Go to **Top 10 &gt; Tools** and click **Sync Funnel Now**. This runs the same aggregation the cron would run, instantly moving all buffered visits into the count tables.

### The four database tables at a glance

| Table | Purpose |
|---|---|
| `wp_top_ten` | Running total of all-time views per post |
| `wp_top_ten_daily` | Hourly view counts per post (powers the dashboard chart and "daily" popular posts lists) |
| `wp_top_ten_visits_funnel` | Hot buffer — raw incoming visits waiting to be aggregated (normally empty between cron runs) |
| `wp_top_ten_visits_log` | Cold archive — every individual visit, retained for the configured number of days |

## Choosing the Right Tracking Method

The optimal tracking method depends on your website's specific requirements. Consider the following factors:

- **Website size and traffic:** The High-traffic or Fast Trackers are best suited for large/high-traffic websites due to their performance advantages. On most sites the REST API or Query Variable tracker works perfectly well.
- **Plugin compatibility:** Ensure the chosen tracking method is compatible with other plugins you're using. If any plugin or your hosting blocks access to the Fast Tracker file, whitelist `/wp-content/plugins/top-10-pro/includes/pro/fast-tracker-js.php`.
- **Caching:** Because every tracker type writes to the funnel and not directly to the count tables, page caching does not interfere with tracking. Each visit still fires the tracker JS regardless of whether the HTML page itself was served from cache.

### Additional Tips

- **Test and monitor:** Experiment with different tracking methods to determine the best performance for your website.
- **Regularly review and update:** Stay up to date with the latest Top 10 version and best practices.
- **Debug mode:** Enable **Debug mode** in Counter/Tracker settings to have the tracker output a response in the browser console, which is helpful when troubleshooting why counts are not updating.

By understanding the different tracking methods available in Top 10, you can make an informed decision and ensure accurate and efficient post view tracking on your WordPress website.
