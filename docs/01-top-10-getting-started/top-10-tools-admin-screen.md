---
slug: top-10-tools-admin-screen
title: "Top 10 – Tools Admin Screen"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [settings,tools-page,top-10,top-10-settings]
status: publish
order: 0
---

The Tools screen in Top 10 offers a set of buttons to help you maintain various features of the plugin.

<figure class="wp-block-image size-large">
<img src="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1-986x1024.webp" class="wp-image-8331" loading="lazy" decoding="async" srcset="https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1-986x1024.webp 986w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1-289x300.webp 289w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1-768x797.webp 768w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1-1480x1536.webp 1480w, https://webberzone.com/wp-content/uploads/2024/09/Top-10-Tools-screen-1.webp 1964w" sizes="auto, (max-width: 986px) 100vw, 986px" width="986" height="1024" alt="Top 10 - Tools screen" />
</figure>

### Database Status

Displays the current state of all four Top 10 database tables along with the installed and expected database version numbers.

| Table | Purpose |
|---|---|
| `wp_top_ten` | Running total of all-time views per post |
| `wp_top_ten_daily` | Hourly view counts per post (powers the dashboard chart and daily popular posts lists) |
| `wp_top_ten_visits_funnel` | Hot buffer — incoming visits waiting to be aggregated into the count tables |
| `wp_top_ten_visits_log` | Cold archive — every individual visit, retained for the configured number of days |

Each table row shows whether the table is installed, the number of entries, and its estimated size. If a required table is missing a **Recreate tables** link appears to repair it.

### Clear Cache

Clears the Top 10 cache. This is also done automatically when you save the settings page. You can find a similar button at the bottom of the settings page if you are using Top 10 Pro.

### Sync Funnel Now

Drains the visits funnel table into the count tables immediately, without waiting for the next scheduled cron run.

Top 10 uses a two-stage tracking pipeline: every page view is written as a single row into the `wp_top_ten_visits_funnel` table, and a background cron job aggregates those rows into `wp_top_ten` and `wp_top_ten_daily` every two minutes. This keeps the per-request database write as fast as possible.

Use **Sync Funnel Now** when you need counts to reflect immediately — for example after importing data, during testing, or if you suspect the aggregation cron is not running. It is equivalent to running the aggregation cron job on demand. If the funnel is empty, a lock from a concurrent run is detected, or a database error occurs, a distinct error message is shown rather than a silent no-op.

See [Trackers in Top 10](../02-top-10-advanced/trackers-in-top-10/) for a full explanation of the tracking pipeline and database tables.

### Recreate Primary Key

Deletes and reinitializes the primary key in the database tables. If you encounter an error, you can run the provided SQL code in phpMyAdmin or Adminer. Remember to back up your database first!

### Reset Database

Resets the Top 10 tables. For multisite installs, it resets the popular posts for the current site. On the Network Admin screen, it resets the popular posts across all sites. **This action cannot be reversed, so ensure your database is backed up before proceeding.**

### Recreate Database Tables

Recreates all four Top 10 database tables (`wp_top_ten`, `wp_top_ten_daily`, `wp_top_ten_visits_funnel`, and `wp_top_ten_visits_log`). Each table's data is preserved by copying it to a temporary table before the drop and restoring it afterwards.

Perform a full backup of the database before proceeding. Use any popular backup plugins or phpMyAdmin for this task. This could cause an issue if you have a huge set of tables with the tracked post data.

### Other Tools

#### Delete Old Settings

Deletes old settings for the current blog. Recommended after upgrading to v2.5.x or later. Only proceed if you are comfortable with the new settings.

#### Merge Post Counts

Merges post counts for posts with duplicate entries. This will merge duplicate post IDs created in older versions of the plugin.
