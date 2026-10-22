# Changelog

All notable changes to this project are documented here. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-10-22

First release as **Drift: Tags**, a free WordPress.org plugin by Drift Creative Systems. Based on Bonsai Code Injector 1.3.1, which carries on separately for Bonsai client sites. There is no data migration between the two.

### Added
- Footer location (`wp_footer`, priority 100) alongside Head and Body.
- Syntax-highlighted editing with core's code editor.
- `drift_tags_locations`, `drift_tags_output` and `drift_tags_settings_after_fields` hooks for add-ons.
- Saving needs `unfiltered_html` as well as the screen capability. Users without it get a read-only view, and a save attempt leaves the code unchanged rather than wiping it.
- `readme.txt`, `LICENSE` (GPLv2), `uninstall.php` (multisite aware), WordPress.org icon and banners, release workflow with optional SVN deploy.

### Changed
- Renamed to Drift: Tags: slug and text domain `drift-tags`, prefix `drift_tags_` / `DRIFT_TAGS_`.
- Settings moved to Settings → Drift: Tags, with the Drift monogram in indigo (`#6366f1`).
- Code is stored in one array option, `drift_tags_code`, instead of one option per field.
- Minimum PHP lowered to 7.4.
- No HTML comments around the printed code.

### Removed
- Plugin Update Checker and Composer. Updates come from WordPress.org.
- Bundled Bonsai Hub menu and suite installer.
