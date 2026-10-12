# WebberZone Top 10 — Popular Posts

[![Top 10](https://raw.githubusercontent.com/WebberZone/top-10/master/wporg-assets/banner-1544x500.png)](https://wordpress.org/plugins/top-10/)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![WordPress Tested](https://img.shields.io/wordpress/plugin/tested/top-10.svg?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/top-10?style=flat-square)](https://wordpress.org/plugins/top-10/)
[![Rating](https://img.shields.io/wordpress/plugin/rating/top-10?style=flat-square)](https://wordpress.org/support/plugin/top-10/reviews/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

Top 10 is a post views counter and popular posts plugin for WordPress. It counts daily and total page views on posts, pages and custom post types, then displays your most viewed and trending posts with blocks, widgets and shortcodes. All data stays in your WordPress database, with no cookies and no personal data.

[WordPress.org](https://wordpress.org/plugins/top-10/) · [Plugin page](https://webberzone.com/plugins/top-10/) · [Documentation](https://webberzone.com/support/product/top-10/) · [Top 10 Pro](https://webberzone.com/plugins/top-10/pro/)

## Features

- Daily and total view counts, tracked in the background so page caches don't block counting
- Popular posts lists by total views or a custom period
- Popular Posts and Post Count blocks, widgets, and the `[tptn_list]` and `[tptn_views]` shortcodes
- Admin dashboard with a views chart, view-count columns, and a multisite network dashboard
- Koko Analytics as an optional data source
- Import and export of counts and settings, and migration from WordPress Popular Posts
- REST API, Abilities API, and extensive actions and filters
- Works with WPML, Polylang and TranslatePress

See the [full feature list on WordPress.org](https://wordpress.org/plugins/top-10/) and the [Free vs Pro comparison](https://webberzone.com/support/knowledgebase/free-vs-pro-top-10/).

## Installation

- **WordPress admin:** Go to Plugins → Add New, search for "Top 10", then install and activate it.
- **WP-CLI:** `wp plugin install top-10 --activate`
- **Zip:** Download a release from [GitHub Releases](https://github.com/WebberZone/top-10/releases) and upload it under Plugins → Add New → Upload Plugin.

Then configure the plugin under Top 10 → Settings.

## Development

This repository holds the latest development version. Stable releases are published on [WordPress.org](https://wordpress.org/plugins/top-10/).

Requirements: WordPress 6.8+, PHP 7.4+, [Composer](https://getcomposer.org/) and [pnpm](https://pnpm.io/).

```bash
composer install
pnpm install
pnpm run build          # Build the blocks
pnpm run build:assets   # Minify CSS and JS, and generate RTL CSS
composer test           # PHPCS, PHP compatibility and PHPStan
composer phpcbf         # Fix coding standards
```

PHPUnit tests live in `phpunit/tests/` and need the WordPress test suite, installed with `phpunit/install.sh`. Architecture notes are in [`CLAUDE.md`](https://github.com/WebberZone/top-10/blob/master/CLAUDE.md).

## Contributing

Pull requests for bug fixes and improvements are welcome. Follow the WordPress coding standards and run `composer test` before submitting. Please use the [WordPress.org support forum](https://wordpress.org/support/plugin/top-10/) for support questions, not GitHub issues.

Help translate Top 10 on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/top-10).

## Security

Report security vulnerabilities through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/top-10). See [`security.md`](https://github.com/WebberZone/top-10/blob/master/security.md).

## Changelog

See the changelog in [`readme.txt`](https://github.com/WebberZone/top-10/blob/master/readme.txt) and the [releases page](https://github.com/WebberZone/top-10/releases).

## License

Top 10 is licensed under the [GNU General Public License v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
