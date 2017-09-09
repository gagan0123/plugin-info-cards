=== Plugin Info Cards ===
Contributors: gagan0123
Donate Link: https://PayPal.me/gagan0123
Tags: plugin, info, cards
Requires at least: 4.1
Tested up to: 4.8.1
Stable tag: 1.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows you to display information of a plugin in pages/posts using a shortcode

== Description ==

Allows you to showcase information of any plugin in pages/posts using shortcode.

= Example usage: =
To display statistics of the plugin "Akismet" you can use
`[plugin-info-cards akismet]`

To display statistics of multiple plugins you can use
`[plugin-info-cards akismet hello-dolly]`

To display statistics of all the plugins a single author has contributed to, you can use
`[plugin-info-cards author=automattic]`

= Note =
This plugin makes use of API calls to WordPress.org to fetch plugin statistics.
These requests are cached by default for a period of 1 hour.

== Installation ==
1. Add the plugin's folder in the WordPress' plugin directory.
2. Activate the plugin.
3. Use the shortcode `[plugin-info-cards {plugin-slug}]` on posts/pages.

== Screenshots ==
1. Sample output 1
2. Sample output 2
3. Using shortcodes in code editor.

== Changelog ==

= 1.0 =
* Initial Public Release
