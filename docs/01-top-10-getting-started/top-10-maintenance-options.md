---
slug: top-10-maintenance-options
title: "Top 10 Settings – Maintenance"
products: [top-10]
sections: [01-top-10-getting-started]
tags: [settings,top-10,top-10-settings]
status: publish
order: 0
---

The Top 10 database tables can build up over time, particularly on busy sites. The **Maintenance** section contains options to schedule automatic pruning of old data.

## Enable scheduled maintenance

Enabling maintenance will automatically delete old entries from the database tables on the schedule you configure. It makes use of [WordPress' built-in cron](https://developer.wordpress.org/plugins/cron/).

## Daily table retention period (Pro)

Controls how many days of data are kept in the daily table (`wp_top_ten_daily`). The daily table stores one row per post per hour, so it stays relatively compact. The default is **180 days**.

Enter a number of days and save. Enter `0` to fall back to the default. You can also override the default via **wp-config.php**:

```php
define( 'TOP_TEN_STORE_DATA', 90 );
```

Or via filter in your theme's functions.php or a mu-plugin:

```php
add_filter( 'tptn_maintenance_days', function( $days ) {
    return 90;
} );
```

## Visits log retention period (Pro)

Controls how many days of raw visit rows are kept in the visits log table (`wp_top_ten_visits_log`). Unlike the daily table, the log stores **one row per individual page view** and grows proportionally to raw traffic — on a busy site it can accumulate millions of rows far faster than the daily table.

Because log rows are already aggregated into the count tables within 2 minutes of being written, they serve only as an audit trail after that point. The default is **30 days**, which is deliberately shorter than the daily table's retention.

Enter a number of days and save. Enter `0` to fall back to the default. You can also override the default via **wp-config.php**:

```php
define( 'TOP_TEN_LOG_STORE_DATA', 7 );
```

Or via filter:

```php
add_filter( 'tptn_log_retention_days', function( $days ) {
    return 7;
} );
```

## Time to run maintenance

The two options allow you to set the Hour and Minute of the day when the cron will run.

## Run maintenance

Choose between Daily, Weekly, Fortnightly or Monthly to run the cron task.
