# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Top 10 (free) counts daily and total post views and displays popular posts lists. Version 4.2.2. Namespace: `WebberZone\Top_Ten`. Function prefix: `tptn_`. Requires WordPress 6.6+, PHP 7.4+. DB version: `6.0`.

Constants defined in `top-10.php`: `TOP_TEN_VERSION`, `TOP_TEN_PLUGIN_FILE`, `TOP_TEN_PLUGIN_DIR`, `TOP_TEN_PLUGIN_URL`, `TOP_TEN_DEFAULT_THUMBNAIL_URL`, `TOP_TEN_STORE_DATA` (180 days default).

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
npm run build:assets    # Minify CSS/JS, generate RTL CSS
npm run start           # Watch free blocks
npm run lint:js         # ESLint
npm run lint:css        # Stylelint
```

Note: `build:pro`, `build:all`, and their `start:*` counterparts also exist in `package.json` (they target `includes/pro/` paths) but are only meaningful in `top-10-pro`.

## Architecture

### Entry Point & Bootstrap
`top-10.php` defines constants, loads Freemius (`load-freemius.php`; Freemius accessor: `tptn_freemius()`), loads the autoloader (`includes/autoloader.php`), and then registers `load_tptn()` on `plugins_loaded` which calls `Main::get_instance()`.

Four files are `require_once`'d directly (not autoloaded) because they must be available before `plugins_loaded`: `includes/options-api.php`, `includes/wz-pluggables.php`, `includes/class-top-ten-query.php`, `includes/functions.php`.

### Core Components
- **`includes/class-main.php`** — Singleton. Instantiates `Counter`, `Tracker`, `Shortcodes`, `Blocks`, `Feed`, `Styles_Handler`, `Language_Handler`, `Cron`, `Hook_Loader`. In the free plugin the `$pro` property is always `null`.
- **`includes/class-hook-loader.php`** — Registers `init`, `widgets_init`, `rest_api_init`, and `parse_query` hooks.
- **`includes/class-counter.php`** (`Counter`) — Hooks into `the_content` to append the viewed count; only fires in the main loop on singular pages.
- **`includes/class-tracker.php`** (`Tracker`) — Enqueues `tptn_tracker` JS and handles `wp_ajax_tptn_tracker` / `wp_ajax_nopriv_tptn_tracker` AJAX actions that record a view. Tracker type (standard `ajaxurl` vs. pro fast/high-traffic types) is read from `tptn_get_option('tracker_type')`.
- **`includes/class-database.php`** (`Database`) — All direct DB access. Two custom tables: `{prefix}top_ten` (total counts) and `{prefix}top_ten_daily` (per-day counts). Columns: `postnumber`, `cntaccess`, `dp_date` (daily only), `blog_id`.
- **`includes/class-top-ten-core-query.php`** (`Top_Ten_Core_Query`) — Extends `WP_Query`; builds the SQL joining posts against the count tables, ordered by `cntaccess`. Supports daily vs. total, multisite blog arrays, and date-range filtering.
- **`includes/class-top-ten-query.php`** — Public-facing query wrapper; required directly rather than autoloaded.

### Frontend (`includes/frontend/`)
- **`class-display.php`** — Renders the popular posts HTML list.
- **`class-media-handler.php`** — Resolves thumbnails (same priority chain as CRP: custom meta → featured image → content scan → default).
- **`class-shortcodes.php`** — `[tptn_list]` shortcode.
- **`class-rest-api.php`** — REST endpoints for the block editor.
- **`class-feed.php`** / `feed-rss2-popular-posts.php` — Popular posts RSS feed.
- **`blocks/`** — Two free blocks: `popular-posts` and `post-count`, source at `blocks/src/`, built to `blocks/build/`.
- **`widgets/class-posts-widget.php`** — Legacy widget.

### Admin (`includes/admin/`)
- **`class-settings.php`** — Settings page (tabs: General, List, Counter, Thumbnail, Exclusions, Feed, Maintenance, Custom Styles). Settings stored as a single `tptn_settings` array in `wp_options`.
- **`class-cron.php`** — Scheduled maintenance (`tptn_cron_hook`) to prune daily table rows older than `TOP_TEN_STORE_DATA` (180) days.
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

## Key Patterns

- **Settings access:** Always use `tptn_get_option($key, $default)` / `tptn_get_settings()`. Settings are also available in `global $tptn_settings` (populated at plugin load).
- **Pro-gated settings:** Several settings in `class-settings.php` carry `'pro' => true` (e.g. `admin_column_post_types`, `show_dashboard_to_roles`, `show_admin_bar`, `max_execution_time`, `use_global_settings`, `exclude_terms_include_parents`, `maintenance_days`, `feed_category_slugs`). These render as disabled with an upgrade prompt in the free plugin; `Pro::update_registered_settings()` in the pro plugin sets `'pro' => false` to enable them.
- **Mutual exclusion:** Activating either free or pro automatically deactivates the other (`tptn_deactivate_other_instances`).
- **DB writes:** All count increments go through `Database::update_count()` using `INSERT … ON DUPLICATE KEY UPDATE`.
