# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Top 10 Pro is the premium version of Top 10 — it counts daily and total post views and displays popular posts lists. Working version pending release: 4.4.0. Namespace: `WebberZone\Top_Ten`. Function prefix: `tptn_`. Requires WordPress 6.6+, PHP 7.4+. DB version: `7.0`.

webberzone.com: <https://webberzone.com/plugins/top-10/>

This is the pro version. Activating it automatically deactivates the free Top 10 plugin, and vice versa. Both plugins share the same namespace, function prefix, database tables, and settings key (`tptn_settings`).

The Freemius header annotation `@fs_premium_only /includes/pro/, /css/pro/` means those directories are only shipped in the paid build.

Constants defined in `top-10.php`: `TOP_TEN_VERSION`, `TOP_TEN_PLUGIN_FILE`, `TOP_TEN_PLUGIN_DIR`, `TOP_TEN_PLUGIN_URL`, `TOP_TEN_DEFAULT_THUMBNAIL_URL`, `TOP_TEN_STORE_DATA` (180 days default for daily table retention), `TOP_TEN_LOG_STORE_DATA` (30 days default for visits log retention).

## Commands

### PHP

```bash
composer phpcs          # Lint PHP (WordPress coding standards)
composer phpcbf         # Auto-fix PHP code style
composer phpstan        # Static analysis
composer phpcompat      # Check PHP 7.4–8.5 compatibility
composer test           # Run all checks (phpcs + phpcompat + phpstan)
```

### JavaScript/CSS

```bash
npm run build           # Build free blocks (popular-posts, post-count)
npm run build:pro       # Build all three pro blocks (query, featured-image, popular-posts-pro)
npm run build:all       # Build free + pro blocks
npm run build:assets    # Minify CSS/JS, generate RTL CSS
npm run start           # Watch free blocks
npm run start:pro       # Watch all pro blocks (parallel)
npm run start:all       # Watch free + pro blocks
npm run lint:js         # ESLint
npm run lint:css        # Stylelint
```

Pro block sources live in `includes/pro/blocks/src/{query,featured-image,popular-posts-pro}/`; each builds to its own `includes/pro/blocks/build/<name>/` directory.

## Architecture

### Entry Point & Bootstrap

`top-10.php` defines constants, loads Freemius (`load-freemius.php`; Freemius accessor: `tptn_freemius()`), loads the autoloader (`includes/autoloader.php`), and then registers `load_tptn()` on `plugins_loaded` which calls `Main::get_instance()`.

Four files are `require_once`'d directly (not autoloaded) because they must be available before `plugins_loaded`: `includes/options-api.php`, `includes/wz-pluggables.php`, `includes/class-top-ten-query.php`, `includes/functions.php`.

### Core Components

- **`includes/class-main.php`** — Singleton. Instantiates `Counter`, `Tracker`, `Shortcodes`, `Blocks`, `Feed`, `Styles_Handler`, `Language_Handler`, `Cron`, `Hook_Loader`. In the pro plugin the `$pro` property is set to a `Pro\Pro` instance when the premium code is active (see "How the Pro Layer Activates" below).
- **`includes/class-hook-loader.php`** — Registers `init`, `widgets_init`, `rest_api_init`, and `parse_query` hooks.
- **`includes/class-counter.php`** (`Counter`) — Hooks into `the_content` to append the viewed count; only fires in the main loop on singular pages.
- **`includes/class-tracker.php`** (`Tracker`) — Enqueues `tptn_tracker` JS and handles `wp_ajax_tptn_tracker` / `wp_ajax_nopriv_tptn_tracker` AJAX actions that record a view. Tracker type (`rest_based`, `query_based` default, `ajaxurl`, plus pro `fast_tracker` / `high_traffic_tracker`) is read from `tptn_get_option('tracker_type')`. Tracking method (`funnel` default vs. `legacy`) is read from `tptn_get_option('tracking_method')`; `Database::record_view()` dispatches to `append_to_funnel()` or `update_counts_direct()` accordingly.
- **`includes/class-database.php`** (`Database`) — All direct DB access. Four custom tables: `{prefix}top_ten` (total counts), `{prefix}top_ten_daily` (per-hour counts), `{prefix}top_ten_visits_funnel` (hot write buffer, drained every 2 min by cron), `{prefix}top_ten_visits_log` (cold archive, pruned by maintenance cron).
- **`includes/class-top-ten-core-query.php`** (`Top_Ten_Core_Query`) — Extends `WP_Query`; builds the SQL joining posts against the count tables, ordered by `cntaccess`. Supports daily vs. total, multisite blog arrays, and date-range filtering.
- **`includes/class-top-ten-query.php`** — Public-facing query wrapper; required directly rather than autoloaded.

### Frontend (`includes/frontend/`)

- **`class-display.php`** — Renders the popular posts HTML list.
- **`class-media-handler.php`** — Resolves thumbnails (same priority chain as CRP: custom meta → featured image → content scan → default).
- **`class-shortcodes.php`** — `[tptn_list]` shortcode.
- **`class-rest-api.php`** — REST endpoints for the block editor.
- **`class-feed.php`** / `feed-rss2-popular-posts.php` — Popular posts RSS feed.
- **`blocks/`** — Two free blocks: `popular-posts` and `post-count`, source at `blocks/src/`, built to `blocks/build/`.
- **`widgets/class-posts-widget.php`** — Legacy popular posts widget.
- **`widgets/class-count-widget.php`** — Legacy widget to display the overall view count.

### Admin (`includes/admin/`)

- **`class-settings.php`** — Settings page (tabs: General, Counter/Tracker, Posts list, Thumbnail, Styles, Maintenance, Feed). Settings stored as a single `tptn_settings` array in `wp_options`.
- **`class-cron.php`** — Two scheduled jobs: `tptn_cron_hook` prunes daily table rows older than `TOP_TEN_STORE_DATA` (180) days (overridable via `tptn_maintenance_days` filter) and visits log rows older than `TOP_TEN_LOG_STORE_DATA` (30) days (overridable via `tptn_log_retention_days` filter); `tptn_aggregation_cron_hook` drains the funnel into the count tables via `Database::aggregate_visit_log()`.
- **`class-statistics.php`** / **`class-statistics-table.php`** — Admin statistics pages.
- **`class-dashboard.php`** / **`class-dashboard-widgets.php`** — Dashboard widgets.
- **`class-columns.php`** — Admin list-table columns showing view counts.
- **`class-metabox.php`** — Per-post metabox.
- **`class-import-export.php`** / **`class-wpp-importer.php`** — Import from WP-PostViews / WPP.
- **`class-tools-page.php`** — Tools/maintenance page.
- **`network/`** — Multisite network admin panel.
- **`settings/`** — Shared settings framework (Settings_API, Settings_Form, Settings_Sanitize, Metabox_API, Settings_Wizard_API).

### Utilities (`includes/util/`)

- **`class-cache.php`** — Transient-based output cache per query.
- **`class-helpers.php`** — Shared helpers.
- **`class-hook-registry.php`** — Static registry for all registered actions/filters.
- **`class-csv-helper.php`** — Shared CSV read/write logic used by both the admin Import/Export page and the WP-CLI `wp top10 counts export|import` commands.

### How the Pro Layer Activates

In `includes/class-main.php`, after all shared subsystems are instantiated, the pro module is conditionally loaded:

```php
if ( tptn_freemius()->is__premium_only() ) {
    if ( tptn_freemius()->can_use_premium_code() ) {
        $this->pro = new Pro\Pro();
    }
}
```

`Pro\Pro` is the single entry point for all pro features.

### Pro Components (`includes/pro/`) — Pro Only

- **`class-pro.php`** (`Pro\Pro`) — Registers hooks that extend the shared plugin: unlocks all `'pro' => true` settings via `tptn_registered_settings` filter, adds `display_only_on_tax_ids` shortcode attribute, supports per-post `_tptn_include_cat_ids` meta override, injects `MAX_EXECUTION_TIME` MySQL hint via `top_ten_query_posts_request` filter, adds category/post-type filtering to the RSS feed, allows a configurable `maintenance_days` to override the default 180-day daily table retention, and allows a configurable `log_retention_days` to override the default 30-day visits log retention. Also enables feed view tracking via the `track_feed_views` setting.

- **`class-fast-tracker.php`** (`Pro\Fast_Tracker`) — Adds two additional tracker types to the settings dropdown:
  - **Fast tracker** — a lightweight standalone PHP endpoint (`fast-tracker-js.php`) that bypasses WordPress bootstrap.
  - **High-traffic tracker** — an even more minimal endpoint (`high-traffic-tracker-js.php`) that uses a pre-generated config file (`top-10-fast-config.php` placed in the WordPress root) with hardcoded DB credentials, requiring no WordPress load at all. The config is generated/deleted via admin AJAX actions (`tptn_generate_fast_config` / `tptn_delete_fast_config`).

- **`class-styles.php`** (`Pro\Styles`) — Adds the `grid_thumbs` display style via `tptn_get_styles` filter; overrides the CSS path via `tptn_get_style` for pro-only CSS in `css/pro/`.

- **`blocks/class-query.php`** — Pro Query block: a full server-side-rendered block for embedding popular posts lists with per-block settings.
- **`blocks/class-featured-image.php`** — Pro Featured Image block.
- **`blocks/class-popular-posts-pro.php`** — Pro Popular Posts Pro block.
- **`blocks/block-patterns/`** — Six pre-built block patterns (grid posts, grid with thumbs, image-title-excerpt, left thumbnail, numbered list, rounded thumbs).

- **`admin/class-pro-admin.php`** (`Pro\Admin\Pro_Admin`) — Pro admin layer; instantiates `Admin_Bar`.
- **`admin/class-admin-bar.php`** (`Pro\Admin\Admin_Bar`) — Adds a Top 10 node to the WordPress admin bar for quick access to stats.
- **`admin/class-dashboard-widgets.php`** (`Pro\Admin\Dashboard_Widgets`) — Pro dashboard widgets.

- **`cli/class-cli-manager.php`** (`Pro\CLI\CLI_Manager`) — Registers WP-CLI commands under the `wp top10` namespace when WP-CLI is available. Commands: `db` (database operations), `counts` (export/import counts), `cache` (cache management), `settings` (get/set settings), `status` (plugin status), `cron` (cron management), `popular` (popular posts queries). Each command extends `Pro\CLI\Base_Command`.

## Key Patterns

- **Settings access:** Always use `tptn_get_option($key, $default)` / `tptn_get_settings()`. Settings are also available in `global $tptn_settings` (populated at plugin load).
- **Pro-gated settings:** Several settings in `class-settings.php` carry `'pro' => true` (e.g. `admin_column_post_types`, `show_dashboard_to_roles`, `show_admin_bar`, `max_execution_time`, `track_feed_views`, `use_global_settings`, `exclude_terms_include_parents`, `maintenance_days`, `log_retention_days`, `feed_category_slugs`). These render as disabled with an upgrade prompt in the free plugin. In the pro plugin, `Pro::update_registered_settings()` iterates all registered settings and sets `'pro' => false` on any entry marked pro-only, enabling those fields in the UI. It also removes the `match_content` setting entirely (replaced by a pro alternative).
- **Mutual exclusion:** Activating either free or pro automatically deactivates the other (`tptn_deactivate_other_instances`).
- **DB writes (funnel pattern):** Every tracked view appends one row to the funnel table via `Database::append_to_funnel()` (or writes directly via `Database::update_counts_direct()` when `tracking_method` is set to `legacy`). A cron job (`tptn_aggregation_cron_hook`) runs every 2 minutes by default, draining the funnel transactionally into `top_ten` and `top_ten_daily` in batch. The interval can be overridden via the `tptn_aggregation_cron_interval` filter (must be a registered WP-Cron schedule name; built-in options: `one_minute`, `two_minutes`, `three_minutes`, `five_minutes`). `Database::update_count()` is deprecated since 4.3.0 — do not use it for new code.
- **High-traffic tracker config:** The generated `top-10-fast-config.php` must be regenerated whenever DB credentials or the table prefix changes. Prompt the user to regenerate after such changes.
- **Pro gating check:** `tptn_freemius()->is__premium_only()` and `tptn_freemius()->can_use_premium_code()` are the two guards used in `Main::init()`. Individual pro hooks use `Hook_Registry` just like the free plugin.
