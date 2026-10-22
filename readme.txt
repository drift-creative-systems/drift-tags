=== Drift: Tags ===
Contributors: driftcreativesystems
Tags: header code, footer code, google tag manager, google analytics, tracking code
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add Google Analytics, Google Tag Manager, Meta Pixel and verification code to your site's head, body or footer without editing theme files.

== Description ==

Drift: Tags gives you one settings screen where you paste tracking and verification code. You don't need to edit theme files or a child theme, and the code stays in place when you change theme.

There are three places to put code:

* **Head**: printed as early as possible inside `<head>`. Use it for the Google Analytics (gtag.js) or Google Tag Manager script, and site verification meta tags for Google Search Console, Bing, Pinterest and so on.
* **Body**: printed straight after the opening `<body>` tag. Use it for the Google Tag Manager `<noscript>` snippet.
* **Footer**: printed just before `</body>`, after the scripts WordPress loads. Use it for chat widgets and scripts that shouldn't slow down the page.

Features:

* Syntax-highlighted editor, using the code editor built into WordPress.
* Settings → Drift: Tags, available only to administrators.
* Saving code needs the `unfiltered_html` capability. On multisite only network admins have it, so site admins can't add scripts to a network.
* Nothing is loaded on the front end apart from your code: no extra scripts, styles, credits or comments.
* Light: one option, loaded once per page.
* Everything is removed when you delete the plugin.

= Privacy =

Drift: Tags doesn't connect to any external service, track you or collect data. It prints only the code you add.

The code you add usually loads a third-party service, such as Google Analytics or a Meta Pixel. Those services can set cookies and collect visitor data. You're responsible for covering them in your privacy policy and, where the law requires it, for getting consent before they load, for example with a cookie consent plugin.

= For developers =

* `drift_tags_capability` (filter): the capability needed to see the settings screen. Default `manage_options`. Saving always needs `unfiltered_html` as well.
* `drift_tags_locations` (filter): add, change or remove places code can be added. Each location has a `label`, `description`, `hook`, `priority` and `rows`.
* `drift_tags_output` (filter): change the code printed at a location, or return an empty string to skip it, e.g. on certain pages.
* `drift_tags_settings_after_fields` (action): add fields to the settings form.

Source code, issues and pull requests: [github.com/drift-creative-systems/drift-tags](https://github.com/drift-creative-systems/drift-tags).

== Installation ==

1. In your WordPress admin, go to Plugins → Add New, search for "Drift: Tags", then install and activate it.
2. Go to Settings → Drift: Tags.
3. Paste your code into the right field and click Save Changes.

= Google Tag Manager =

1. Paste the `<script>` part of the install snippet into **Head**.
2. Paste the `<noscript>` part into **Body**.

= Google Analytics (gtag.js, without Tag Manager) =

Paste the whole gtag.js snippet into **Head**. You don't need anything in Body or Footer.

== Frequently Asked Questions ==

= My Body code doesn't appear =

Your theme needs to call `wp_body_open()` straight after the opening `<body>` tag. All default themes and block themes do this, but some older themes don't. Ask the theme author to add it, or put the code in Head or Footer instead.

= Why can't I save? =

Saving needs the `unfiltered_html` capability. On multisite only network admins have it. It's also removed for everyone if `DISALLOW_UNFILTERED_HTML` is set in `wp-config.php`. Without it you can see the code but not change it.

= Does the plugin check or clean my code? =

No. Code is saved as you enter it (minus surrounding whitespace) and printed unchanged, because tracking snippets need their `<script>` tags. Only add code from sources you trust.

= Is the code added in wp-admin or the login page? =

No. Code is added only to front-end pages that use your theme's `wp_head`, `wp_body_open` and `wp_footer` hooks.

= Does it work with caching plugins? =

Yes. After changing the code, clear your page cache so visitors get the new version.

== Changelog ==

= 1.0.0 =
* First public release: Head, Body and Footer code with the WordPress code editor.

== Upgrade Notice ==

= 1.0.0 =
First public release.
