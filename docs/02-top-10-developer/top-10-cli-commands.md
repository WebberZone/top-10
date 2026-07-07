---
slug: top-10-cli-commands
title: "WP-CLI commands in Top 10 Pro"
products: [top-10]
sections: [02-top-10-developer]
tags: [top-10,wp-cli]
status: publish
order: 0
---

Top 10 Pro includes a full WP-CLI command suite so you can manage counts, database tables, cache, settings, and cron jobs directly from the terminal — without touching the WordPress admin.

All commands start with `wp top10`. Run any command with `--help` to see its full option list.

## Requirements

- Top 10 **Pro** must be active (WP-CLI commands are a Pro-only feature).
- [WP-CLI](https://wp-cli.org/) installed and configured for your WordPress site.

## Quick reference

| Command group | What it does |
|---|---|
| `wp top10 cache` | Manage the output cache |
| `wp top10 counts` | View, set, reset, export, and import view counts |
| `wp top10 cron` | Manage scheduled cron jobs |
| `wp top10 db` | Database table management |
| `wp top10 popular` | List popular posts |
| `wp top10 settings` | Read, write, export, import, and copy plugin settings |
| `wp top10 status` | Plugin overview: versions, tracker, cron, tables |

## Common flags

These flags work across most commands.

| Flag | Description |
|---|---|
| `--dry-run` | Show what *would* happen without making any changes. Always safe to run. |
| `--force` | Skip the interactive confirmation prompt on destructive commands. |
| `--network` | Run the command for every site in a WordPress multisite network. |
| `--blog-id=<id>` | Target a specific site in a multisite network. |
| `--format=<format>` | Output format: `table` (default), `json`, `csv`, `yaml`, `ids`, `count`. |

## `wp top10 cache`

### `status`

```bash
wp top10 cache status
```

Shows whether the cache is enabled, the cache TTL, and the number of active cache keys.

### `enable` / `disable`

```bash
wp top10 cache enable
wp top10 cache disable
wp top10 cache enable --network
```

### `flush`

```bash
wp top10 cache flush --force
wp top10 cache flush --dry-run
wp top10 cache flush --network
```

## `wp top10 counts`

### `view` — list posts with counts

```bash
wp top10 counts view
wp top10 counts view --table=daily --limit=20
wp top10 counts view --from-date=2025-01-01 --to-date=2025-12-31
wp top10 counts view --network
```

### `get` — get the count for one post

```bash
wp top10 counts get 42
wp top10 counts get 42 --table=daily
```

### `set` — overwrite the count for one post

Only applies to the overall table.

```bash
wp top10 counts set 42 1000
wp top10 counts set 42 1000 --dry-run
```

### `reset` — delete counts

```bash
# Preview what would be deleted
wp top10 counts reset --dry-run

# Reset specific posts in the overall table
wp top10 counts reset --post-id=42,43 --table=overall --force

# Reset everything across all sites (multisite)
wp top10 counts reset --network --force
```

`--table` accepts `overall`, `daily`, or `all` (default).

### `export` — write counts to a CSV file

The CSV format is compatible with the admin Import/Export page.

```bash
wp top10 counts export
wp top10 counts export --file=counts.csv --table=daily --limit=1000
wp top10 counts export --include-urls --network
wp top10 counts export --dry-run
```

The default file name is `tptn-counts-{timestamp}.csv` in the current directory. Files may only be written to the WordPress root, the uploads directory, or the system temp directory.

### `import` — load counts from a CSV file

The file must use the same column layout produced by `counts export` (or the admin export page). The table type — overall or daily — is auto-detected from the header row.

```bash
wp top10 counts import counts.csv
wp top10 counts import counts.csv --dry-run
wp top10 counts import counts.csv --mode=set --force
wp top10 counts import counts-with-urls.csv --use-urls
wp top10 counts import counts.csv --blog-id=3
```

| Option | Default | Description |
|---|---|---|
| `--table=<table>` | auto | Override table detection: `overall` or `daily`. |
| `--mode=<mode>` | `add` | `add` increments existing counts; `set` overwrites them. |
| `--use-urls` | — | Resolve the URL column to a post ID instead of using the Post ID column. |
| `--batch-size=<n>` | 1000 | Rows processed per database batch. |

## `wp top10 cron`

### `status`

```bash
wp top10 cron status
```

Shows the next scheduled run time for both the **maintenance** and **aggregation** cron jobs.

### `enable`

```bash
# Enable both jobs
wp top10 cron enable --job=all

# Schedule maintenance at 02:00 daily
wp top10 cron enable --job=maintenance --hour=2 --min=0 --recurrence=daily

# Enable aggregation only (runs every 2 minutes, no schedule options)
wp top10 cron enable --job=aggregation
```

### `disable`

```bash
wp top10 cron disable --job=maintenance --force
wp top10 cron disable --force
```

### `run` — trigger a cron job immediately

The job must already be scheduled. Useful for testing or flushing a backlog.

```bash
wp top10 cron run --job=aggregation
wp top10 cron run --job=maintenance
wp top10 cron run --dry-run
```

## `wp top10 db`

### `status` — show table info

```bash
wp top10 db status
wp top10 db status --format=json
wp top10 db status --network
```

### `aggregate` — drain the visits funnel immediately

The aggregation cron normally runs every 2 minutes. Use this to force an immediate run.

```bash
wp top10 db aggregate
wp top10 db aggregate --dry-run
wp top10 db aggregate --batch-size=5000
```

### `cleanup-orphans` — remove counts for deleted posts

Count rows that reference posts no longer in the database are removed. On multisite, only rows for the current site are affected.

```bash
# Preview
wp top10 db cleanup-orphans --dry-run

# Clean the overall table
wp top10 db cleanup-orphans --table=overall --force

# Run across all network sites
wp top10 db cleanup-orphans --network --force
```

### `create-tables` — create any missing tables

Safe to run on an existing install; uses `dbDelta` so it will not destroy data.

```bash
wp top10 db create-tables
wp top10 db create-tables --dry-run
```

### `prune` — delete old rows

Removes rows older than the configured retention period. Default: 180 days for the daily table, 30 days for the visits log.

```bash
# Preview how many rows would be deleted
wp top10 db prune --dry-run

# Delete daily rows older than 90 days
wp top10 db prune --days=90 --table=daily --force

# Delete log rows older than 7 days
wp top10 db prune --log-days=7 --table=log --force
```

`--table` accepts `daily`, `log`, or `all` (default).

### `recreate-tables` — drop and rebuild a table

Creates a backup table before dropping by default. Use `--no-backup` to skip. Each table's data is preserved by copying it to a temporary table before the drop and restoring it afterwards.

```bash
wp top10 db recreate-tables --table=daily --dry-run
wp top10 db recreate-tables --table=funnel --force
wp top10 db recreate-tables --table=log --force
wp top10 db recreate-tables --force
```

`--table` accepts `overall`, `daily`, `funnel`, `log`, or `all` (default).

### `truncate` — delete all rows from a table

**Destructive.** Requires `--force` or `--dry-run`.

```bash
wp top10 db truncate --table=funnel --force
wp top10 db truncate --table=all --dry-run
```

`--table` accepts `overall`, `daily`, `log`, `funnel`, or `all` (default).

### `update-tables` — apply pending schema upgrades

Runs the same upgrade routine as plugin activation.

```bash
wp top10 db update-tables
```

## `wp top10 popular`

Lists the most-viewed posts using the same query the plugin uses on the front end.

```bash
# Top 10 posts overall
wp top10 popular

# Top 5 custom period popular posts for the last 7 days
wp top10 popular --daily --days=7 --limit=5

# Multiple post types, CSV output
wp top10 popular --post-type=post,page --format=csv

# Custom period counts between specific dates
wp top10 popular --daily --from-date=2025-01-01 --to-date=2025-12-31

# Posts published in the last 30 days, excluding specific categories
wp top10 popular --how-old=30 --exclude-categories=5,12

# Filter by author, output as JSON
wp top10 popular --author=1,2 --format=json
```

**Options**

| Option | Default | Description |
|---|---|---|
| `--limit=<n>` | 10 | Number of posts to return. |
| `--offset=<n>` | 0 | Number of posts to skip (for pagination). |
| `--daily` | — | Use the custom period table instead of the overall table. |
| `--days=<n>` | plugin setting | Date range for custom period queries. |
| `--from-date=<date>` | — | Start date for the custom period (`YYYY-MM-DD`). Only used with `--daily`. |
| `--to-date=<date>` | — | End date for the custom period (`YYYY-MM-DD`). Only used with `--daily`. |
| `--how-old=<n>` | — | Limit to posts published within this many days. |
| `--post-type=<type>` | all public | Post type slug or comma-separated list (e.g. `post,page`). |
| `--include-cat-ids=<ids>` | — | Comma-separated `term_taxonomy_id`s to include. |
| `--exclude-categories=<ids>` | — | Comma-separated `term_taxonomy_id`s to exclude. |
| `--include-post-ids=<ids>` | — | Comma-separated post IDs to force-include at the top of the list. |
| `--author=<id>` | — | Author ID or comma-separated list of author IDs to filter by. |
| `--blog-id=<id>` | current site | Blog ID or comma-separated list of blog IDs (multisite only). |
| `--format=<format>` | table | Output format. Supports `table`, `json`, `csv`, `ids`, `count`. |

## `wp top10 settings`

### `get`

```bash
wp top10 settings get cache
wp top10 settings get post_title_length --format=json
```

### `set`

```bash
wp top10 settings set cache 1 --type=int
wp top10 settings set post_title_length 60
wp top10 settings set cache_time 3600 --type=int --dry-run
```

`--type` accepts `string`, `int`, `float`, `bool`, `array`. Omit it for auto-detection.

### `export`

Writes all settings to a JSON file.

```bash
wp top10 settings export
wp top10 settings export --file=my-settings.json
wp top10 settings export --network
```

### `import`

```bash
# Replace all settings
wp top10 settings import settings.json

# Merge (only update keys present in the file)
wp top10 settings import settings.json --merge

# Preview
wp top10 settings import settings.json --dry-run
```

### `copy` — copy settings between sites (multisite only)

Copies all Top 10 settings from a source site to one or more destination sites. The source is excluded from the destination list automatically.

```bash
# Copy from current site to site 3
wp top10 settings copy --destination=3

# Copy from site 2 to sites 3 and 4
wp top10 settings copy --source=2 --destination=3,4

# Copy from current site to all other active sites
wp top10 settings copy --destination=all

# Copy from site 1 to all other active sites
wp top10 settings copy --source=1 --destination=all

# Preview without making changes
wp top10 settings copy --destination=all --dry-run
```

**Options**

| Option | Default | Description |
|---|---|---|
| `--source=<id>` | current site | Blog ID to copy settings from. |
| `--destination=<id>` | — | Blog ID(s), comma-separated, or `all` for every other site. Required. |

## `wp top10 status`

Shows a snapshot of the plugin: version, DB version, tracker type, cache, cron schedules, and table row counts.

```bash
wp top10 status
wp top10 status --format=json
wp top10 status --network
```

## Typical workflows

### Back up and migrate counts

```bash
# On the source site
wp top10 counts export --file=/tmp/counts-overall.csv
wp top10 counts export --table=daily --file=/tmp/counts-daily.csv

# On the destination site
wp top10 counts import /tmp/counts-overall.csv --force
wp top10 counts import /tmp/counts-daily.csv --force
```

### Routine database maintenance

```bash
# See what would be pruned
wp top10 db prune --dry-run

# Check for orphan rows
wp top10 db cleanup-orphans --dry-run

# Run both for real
wp top10 db prune --force
wp top10 db cleanup-orphans --force
```

### Network-wide cache flush after a deployment

```bash
wp top10 cache flush --network --force
```

### Copy settings between sites (multisite)

```bash
# From current site to site 2
wp top10 settings copy --destination=2

# From site 1 to all other sites
wp top10 settings copy --source=1 --destination=all
```
