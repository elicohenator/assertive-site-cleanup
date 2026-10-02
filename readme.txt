=== Assertive Site Cleanup ===
Contributors: elicohenator
Tags: comments, cleanup, security, head, xml-rpc
Requires at least: 6.4
Tested up to: 7.1
Stable tag: 2.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disables unused comments and strips leftover WordPress head output. Each feature has its own checkbox.

== Description ==

Assertive Site Cleanup is a small settings page for the cleanup snippets most sites paste in and then forget.

On by default:

* Comments and trackbacks are closed, and the comments admin UI, widget, feed, and REST routes are removed.
* Emoji detection script, styles, and the emoji DNS prefetch are removed.
* Generator, shortlink, oEmbed discovery, REST discovery, and RSD tags are removed from the head.
* The WordPress version is removed from generator output.
* Failed logins get one generic message. Password-reset errors are left alone.
* `/wp/v2/users` is hidden from logged-out REST requests. Logged-in users still have it.
* XML-RPC, including pingbacks, is disabled.
* The Events and News dashboard widget and the welcome panel are removed.

Off by default, because they break real sites or change URLs:

* Remove RSS feed links.
* Hide the front-end admin bar for non-administrators.
* Redirect `?author=N` to the homepage.
* Disable the plugin and theme file editor.
* Disable speculative loading (link prefetch for logged-out visitors).
* Keep only 5 revisions per post. Existing revisions are not deleted.
* Redirect attachment pages to the file URL.
* Disable the core `wp-sitemap.xml`.
* Stop the editor from downloading remote block patterns.
* Remove jQuery Migrate on the front end.

Nothing here is specific to Cloudways. Breeze can also disable emojis (leaving both on is harmless). Varnish serves cached HTML, so head cleanup only applies on a cache miss. Blocking `xmlrpc.php` in the application settings is stronger than the PHP toggle.

== Installation ==

1. Upload the `assertive-site-cleanup` folder to `/wp-content/plugins/`.
2. Activate Assertive Site Cleanup.
3. Review Settings → Assertive Site Cleanup.

== Frequently Asked Questions ==

= Will this delete existing comments? =

No. It closes comments and hides them. The rows stay in the database.

= Why can logged-in users still list users over REST? =

The block editor needs `/wp/v2/users` for the author panel. The endpoint is removed only for logged-out requests.

= Does limiting revisions delete old ones? =

No. New saves keep the latest 5. Revisions already in the database stay there.

= Will disabling the core sitemap remove my SEO plugin sitemap? =

No. It only turns off WordPress core's `wp-sitemap.xml`.

== Changelog ==

= 2.1.0 =
* Added toggles for speculative loading, revision limits, attachment redirects, the core sitemap, remote block patterns, and jQuery Migrate.
* The Events and News dashboard widget and the welcome panel are removed by default.

= 2.0.0 =
* Added a settings page. Each snippet can be turned off.
* Comment disabling now covers the dashboard widget, Discussion settings, admin bar, REST routes, comment counts, list-table column, comment-reply script, and Recent Comments widget.
* The Recent Comments dashboard widget is removed on `wp_dashboard_setup`, where core actually registers it.
* REST user routes are hidden from logged-out requests only.
* Login errors are rewritten only for bad credentials, so an expired reset link still explains itself.
* Emoji removal targets the WordPress 6.4+ style enqueue, embeds, and `emoji_svg_url`.
* Removed calls for head tags WordPress no longer prints: `index_rel_link`, `start_post_rel_link`, `parent_post_rel_link`, `wlwmanifest_link`, and `adjacent_posts_rel_link_wp_head`.
* RSS feed links and the admin bar are no longer changed unless you opt in.
* Prefixed every function so another snippet cannot fatally collide with this plugin.
* Added uninstall cleanup for the settings option.

= 1.0 =
* Initial release.
