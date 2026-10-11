---
slug: top-10-abilities-api
title: "Top 10 Abilities API"
products: [top-10]
sections: ["03-top-10-developer-docs"]
tags: [abilities-api, integrations, top-10]
status: publish
order: 0
toc: true
---

[toc]

[Top 10](https://webberzone.com/plugins/top-10/) 4.6.0 registers WordPress abilities for retrieving popular posts and view counts. Top 10 Pro also registers abilities for editing lifetime counts and clearing the cache.

## Requirements and access

Abilities are available on WordPress 6.9 or later. Earlier versions continue to work without abilities. Enable **Abilities API** in **Top 10 → Settings → Features**. The setting is enabled by default and operates independently of **REST API endpoints**.

All abilities use the shared `webberzone` category. Their metadata exposes them to the WordPress abilities REST API and compatible clients, including the WordPress MCP Adapter. Read abilities require the `read` capability. Pro write abilities require `manage_options` and an active Pro license.

Every operation applies to the current site. No ability accepts a blog ID or performs a network-wide operation. Unreadable posts are excluded from popular results, and individual view counts require a readable post.

## Read abilities

### `top-10/get-popular-posts`

Returns an array of objects containing `id`, `title`, `url`, and `views`, ordered by views. Titles are plain text.

| Input | Type | Description |
| --- | --- | --- |
| `count` | integer | Maximum results, from 1 to 100. Default: 10. |
| `period` | string | `overall` for lifetime totals or `daily` for a recent window. Default: `overall`. |
| `post_types` | array of strings | Optional public post type names. Defaults to the configured post types. Returned posts must also pass the plugin's REST readability checks. |
| `daily_range` | integer | Optional recent window in days, from 1 to 36500. Defaults to the configured daily range. Applies when `period` is `daily`. The extra configured hour range is excluded. |

The daily window follows the **Start daily counts at midnight** setting. Results can contain fewer posts than requested because of exclusions or readability checks.

```php
$result = wp_get_ability( 'top-10/get-popular-posts' )->execute(
    array(
        'count'       => 5,
        'period'      => 'daily',
        'daily_range' => 7,
        'post_types'  => array( 'post' ),
    )
);
```

### `top-10/get-post-views`

Requires `post_id`, a positive integer. Returns an object containing integer `total` and `daily` counts. The daily count follows the configured daily range and hour range.

```php
$result = wp_get_ability( 'top-10/get-post-views' )->execute(
    array( 'post_id' => 42 )
);
```

Both read outputs allow future additional metrics. Consumers should read the fields they need and accept additional fields.

## Pro abilities

### `top-10/edit-post-count`

Requires `post_id`, a non-negative integer `total`, and `confirm: true`. Returns `post_id`, `previous_total`, and `total`.

Set `total` to zero to reset the lifetime count. Daily counts remain derived and cannot be edited through this ability. Pending funnel visits remain queued and are added during aggregation.

```php
$result = wp_get_ability( 'top-10/edit-post-count' )->execute(
    array(
        'post_id' => 42,
        'total'   => 100,
        'confirm' => true,
    )
);
```

### `top-10/clear-cache`

Requires `confirm: true`. Clears cached Top 10 results for the current site and returns `cleared` and `count`, the number of cache entries removed.

```php
$result = wp_get_ability( 'top-10/clear-cache' )->execute(
    array( 'confirm' => true )
);
```

Both Pro abilities are marked destructive and idempotent. They require explicit confirmation in their input.

## REST API and MCP

Discover abilities with `GET /wp-json/wp-abilities/v1/abilities`. Run either read ability with `GET /wp-json/wp-abilities/v1/abilities/top-10/<ability>/run`, passing its arguments under `input`. Both destructive Pro abilities use `DELETE` on the corresponding `/run` route.

For HTTP calls, use WordPress authentication such as an Application Password. Cookie-authenticated calls require the WordPress REST nonce. PHP, REST, and MCP execution use the same ability permission callbacks and schemas.

With a configured WordPress MCP Adapter server, expose these abilities through that server's ability list. Registering an ability does not install or configure an MCP server.

Counts reflect aggregated data. Buffered visits become visible after aggregation. Bulk imports, table resets, and network operations remain outside this release's ability set.
