# Drift: Tags

Free WordPress plugin by **Drift Creative Systems**. Add tracking and verification code (Google Analytics, Google Tag Manager, Meta Pixel, site verification tags) to the head, the start of the body and the footer of every page, without editing theme files.

- WordPress.org slug: `drift-creative-tags` (submission pending)
- Licence: GPL-2.0-or-later
- Requires WordPress 6.0+, PHP 7.4+

The user-facing documentation is [readme.txt](readme.txt), which WordPress.org displays. This file is for developers.

## Features

| Location | Hook | Priority | Use for |
|---|---|---|---|
| Head | `wp_head` | 1 | GA4 / GTM `<script>`, verification `<meta>` tags |
| Body | `wp_body_open` | 1 | GTM `<noscript>` |
| Footer | `wp_footer` | 100 | Chat widgets, non-critical scripts |

- Settings → Drift: Tags, with core's CodeMirror editor (`wp_enqueue_code_editor()`).
- Saving needs `manage_options` (filterable) **and** `unfiltered_html`.
- No front-end output except the saved code, and no external requests.
- One autoloaded option, `drift_tags_code` (array keyed by location ID). It's removed in `uninstall.php`, on every site in a multisite network.

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `drift_tags_capability` | filter | Capability to view the settings screen. Default `manage_options` |
| `drift_tags_locations` | filter | Add/change/remove locations (`label`, `description`, `hook`, `priority`, `rows`) |
| `drift_tags_output` | filter | Change or skip (return `''`) code at a location: `( $code, $location_id )` |
| `drift_tags_settings_after_fields` | action | Extra fields inside the settings form |

These are the extension points for a paid add-on, sold outside WordPress.org. Nothing in this plugin is gated.

## Development

- Coding standard: `phpcs.xml.dist` (WordPress, prefixes `drift_tags` / `DRIFT_TAGS`).
- Run **Plugin Check** before every release. The PHPCS-based checks don't work in WordPress Playground (its filesystem can't `flock()`), so use a real install, e.g. WP-CLI with the SQLite drop-in:
  ```sh
  wp plugin install plugin-check --activate
  wp plugin check drift-creative-tags --include-low-severity-errors --include-low-severity-warnings \
    --require=./wp-content/plugins/plugin-check/cli.php
  ```
  Check a copy built from `.distignore`, not the repo, so dev files don't trip the `file_type` check.
- Directory guidelines review: the `wp-plugin-directory-guidelines` Claude Code skill.

## Releasing

1. Bump the version in **three** places: the `Version:` header, `DRIFT_TAGS_VERSION` and `Stable tag:` in `readme.txt`. Add a changelog entry to both `readme.txt` and `CHANGELOG.md`.
2. Merge `develop` → `main`, then publish a GitHub release tagged `vX.Y.Z`.
3. `.github/workflows/release.yml` checks the three versions match, builds `drift-creative-tags.zip` from `.distignore` and attaches it to the release.
4. Once approved on WordPress.org, set the repo variable `WPORG_DEPLOY=true` and the secrets `SVN_USERNAME` / `SVN_PASSWORD`. Releases then deploy to SVN automatically, and readme/asset changes on `main` sync on their own (`wporg-assets.yml`).

WordPress.org assets (icon, banners, screenshots) live in `.wordpress-org/`.

## Licence

GPL-2.0-or-later. See [LICENSE](LICENSE).
