# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

WebberZone Top 10 is the free version of Top 10 — it counts daily and total post views and displays popular posts lists. Current version: 4.3.4. Namespace: `WebberZone\Top_Ten`. Function prefix: `tptn_`. Requires WordPress 6.6+, PHP 7.4+. DB version: `7.0`. Text domain: `top-10`.

webberzone.com: <https://webberzone.com/plugins/top-10/>

This is the free version. The pro version (Top 10 Pro, slug `top-10-pro`) is a separate plugin. Activating either one automatically deactivates the other (`tptn_deactivate_other_instances`). Both plugins share the same namespace, function prefix, database tables, and settings key (`tptn_settings`).

The plugin uses Freemius as its SDK/upgrade framework (`load-freemius.php`; Freemius accessor: `tptn_freemius()`). In this free build `is_premium` is `false`; the pro-only code directories (`includes/pro/`, `css/pro/`) are not present in this repository.

Constants defined in `top-10.php`: `TOP_TEN_VERSION`, `TOP_TEN_PLUGIN_FILE`, `TOP_TEN_PLUGIN_DIR`, `TOP_TEN_PLUGIN_URL`, `TOP_TEN_DEFAULT_THUMBNAIL_URL`, `TOP_TEN_STORE_DATA` (180 days default for daily counts), `TOP_TEN_LOG_STORE_DATA` (30 days default for raw visit log rows).

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

Free block sources live in `includes/frontend/blocks/src/{popular-posts,post-count}/`; each builds to `includes/frontend/blocks/build/<name>/`. The pro block sources (`includes/pro/blocks/src/{query,featured-image,popular-posts-pro}/`) are only present in the pro plugin repository; the `build:pro` / `start:pro` scripts target those paths and will not find sources in this free repo.

## Architecture

### Entry Point & Bootstrap

`top-10.php` defines constants, loads Freemius (`load-freemius.php`; Freemius accessor: `tptn_freemius()`), loads the autoloader (`includes/autoloader.php`), and then registers `load_tptn()` on `plugins_loaded` which calls `Main::get_instance()`.

Four files are `require_once`'d directly (not autoloaded) because they must be available before `plugins_loaded`: `includes/options-api.php`, `includes/wz-pluggables.php`, `includes/class-top-ten-query.php`, `includes/functions.php`.

The autoloader (`includes/autoloader.php`) is a custom PSR-4-style loader registered via `spl_autoload_register`. It maps the `WebberZone\Top_Ten` namespace to the `includes/` directory, converts class names to `class-<name>.php` (lowercase, underscores to hyphens), and `require_once`s the file.

### Core Components

- **`includes/class-main.php`** (`Main`) — Singleton. Instantiates `Language_Handler`, `Styles_Handler`, `Counter`, `Tracker`, `Shortcodes`, `Blocks`, `Feed`, `Cron`, and `Hook_Loader` in `init()`. Admin components (`Admin\Admin`, and `Admin\Network\Admin` on multisite) are instantiated on the `init` hook via `init_admin()` to ensure translations are loaded. The `$pro` property is declared but stays `null` in the free version.
- **`includes/class-hook-loader.php`** (`Hook_Loader`) — Registers `init`, `widgets_init`, `rest_api_init`, and `parse_query` hooks.
- **`includes/class-counter.php`** (`Counter`) — Hooks into `the_content` to append the viewed count; only fires in the main loop on singular pages.
- **`includes/class-tracker.php`** (`Tracker`) — Enqueues `tptn_tracker` JS and handles `wp_ajax_tptn_tracker` / `wp_ajax_nopriv_tptn_tracker` AJAX actions that record a view. Also handles `parse_request` / `query_vars` for the query-variable tracker type. Tracker type is read from `tptn_get_option('tracker_type')`; the three built-in types are `rest_based`, `query_based`, and `ajaxurl` (see `Settings::get_tracker_types()`).
- **`includes/class-database.php`** (`Database`) — All direct DB access. Four custom tables: `{prefix}top_ten` (total counts), `{prefix}top_ten_daily` (per-hour counts), `{prefix}top_ten_visits_funnel` (hot write buffer, drained by cron), `{prefix}top_ten_visits_log` (cold archive, pruned by maintenance cron).
- **`includes/class-top-ten-core-query.php`** (`Top_Ten_Core_Query`) — Extends `WP_Query`; builds the SQL joining posts against the count tables, ordered by `cntaccess`. Supports daily vs. total, multisite blog arrays, and date-range filtering.
- **`includes/class-top-ten-query.php`** — Public-facing query wrapper; required directly rather than autoloaded.

### Frontend (`includes/frontend/`)

- **`class-display.php`** (`Display`) — Renders the popular posts HTML list.
- **`class-media-handler.php`** (`Media_Handler`) — Resolves thumbnails (priority chain: custom meta → featured image → content scan → default).
- **`class-shortcodes.php`** (`Shortcodes`) — `[tptn_list]` shortcode.
- **`class-rest-api.php`** (`REST_API`) — REST endpoints for the block editor.
- **`class-feed.php`** (`Feed`) / `feed-rss2-popular-posts.php` — Popular posts RSS feed.
- **`class-styles-handler.php`** (`Styles_Handler`) — Enqueues styles and handles style selection.
- **`class-language-handler.php`** (`Language_Handler`) — Loads plugin translations.
- **`blocks/`** — Two free blocks: `popular-posts` and `post-count`, source at `blocks/src/`, built to `blocks/build/`.
- **`widgets/`** — Legacy widgets: `class-posts-widget.php` (popular posts) and `class-count-widget.php` (post count).

### Admin (`includes/admin/`)

- **`class-admin.php`** (`Admin`) — Admin loader; instantiates `Dashboard`, `Settings`, `Settings_Wizard`, `Statistics`, `Activator`, `Columns`, `Metabox`, `Import_Export`, `Tools_Page`, `Dashboard_Widgets`, `Cache`, `Admin_Notices_API`, `Admin_Notices`, and `Admin_Banner`.
- **`class-settings.php`** (`Settings`) — Settings page (tabs: General, Counter/Tracker, Posts list, Thumbnail, Styles, Maintenance, Feed). Settings stored as a single `tptn_settings` array in `wp_options`.
- **`class-settings-wizard.php`** (`Settings_Wizard`) — First-run setup wizard.
- **`class-cron.php`** (`Cron`) — Two scheduled jobs: `tptn_cron_hook` prunes daily table rows older than `TOP_TEN_STORE_DATA` (180) days and log table rows older than `TOP_TEN_LOG_STORE_DATA` (30) days; `tptn_aggregation_cron_hook` drains the funnel into the count tables via `Database::aggregate_visit_log()`.
- **`class-statistics.php`** / **`class-statistics-table.php`** — Admin statistics pages.
- **`class-dashboard.php`** / **`class-dashboard-widgets.php`** — Dashboard widgets.
- **`class-columns.php`** — Admin list-table columns showing view counts.
- **`class-metabox.php`** — Per-post metabox.
- **`class-import-export.php`** / **`class-wpp-importer.php`** — Import from WP-PostViews / WPP.
- **`class-tools-page.php`** — Tools/maintenance page.
- **`class-activator.php`** — Activation/deactivation hooks (registered in `top-10.php`).
- **`class-admin-banner.php`** / **`class-admin-notices.php`** / **`class-admin-notices-api.php`** — Admin banners and notices.
- **`network/`** — Multisite network admin panel (`class-admin.php`, `class-statistics.php`).
- **`settings/`** — Shared settings framework (Settings_API, Settings_Form, Settings_Sanitize, Metabox_API, Settings_Wizard_API).

### Utilities (`includes/util/`)

- **`class-cache.php`** (`Cache`) — Transient-based output cache per query.
- **`class-helpers.php`** (`Helpers`) — Shared helpers.
- **`class-hook-registry.php`** (`Hook_Registry`) — Static registry for all registered actions/filters.
- **`class-csv-helper.php`** (`CSV_Helper`) — CSV import/export helper.

## Key Patterns

- **Settings access:** Always use `tptn_get_option($key, $default)` / `tptn_get_settings()`. Settings are also available in `global $tptn_settings` (populated at plugin load). The settings key/option name is `tptn_settings`.
- **Mutual exclusion:** Activating either free or pro automatically deactivates the other (`tptn_deactivate_other_instances`).
- **DB writes (funnel pattern):** Every tracked view appends one row to the funnel table via `Database::append_to_funnel()`. A cron job (`tptn_aggregation_cron_hook`) runs every 2 minutes by default (`two_minutes`), draining the funnel transactionally into `top_ten` and `top_ten_daily` in batch. The interval can be overridden via the `tptn_aggregation_cron_interval` filter (must be a registered WP-Cron schedule name; built-in options added by `wz-pluggables.php`: `one_minute`, `two_minutes`, `three_minutes`, `five_minutes`). `Database::update_count()` is deprecated since 4.3.0 — do not use it for new code.
- **Cron schedules:** `includes/wz-pluggables.php` registers additional WP-Cron schedules (`weekly`, `fortnightly`, `monthly`, `quarterly`, `one_minute`, `two_minutes`, `three_minutes`, `five_minutes`) via the `cron_schedules` filter.
