# Assertive Site Cleanup

Disables unused comments and strips leftover WordPress head output. Each feature is its own checkbox under **Settings → Assertive Site Cleanup**.

| | |
| --- | --- |
| Requires | WordPress 6.4 or later, PHP 7.4 or later |
| Tested up to | 7.1 |
| License | [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) |

## On by default

- Comments and trackbacks are closed, and the comments admin UI, widget, feed, and REST routes are removed.
- The emoji detection script, styles, and emoji DNS prefetch are removed.
- Generator, shortlink, oEmbed discovery, REST discovery, and RSD tags are removed from the head.
- The WordPress version is removed from generator output.
- Failed logins get one generic message. Password-reset errors are left alone.
- `/wp/v2/users` is hidden from logged-out REST requests. Logged-in users still have it.
- XML-RPC, including pingbacks, is disabled.
- The Events and News dashboard widget and the welcome panel are removed.

## Off by default

These stay off because they can break a real site or change URLs.

- Remove RSS feed links.
- Hide the front-end admin bar for non-administrators.
- Redirect `?author=N` to the homepage.
- Disable the plugin and theme file editor.
- Disable speculative loading (link prefetch for logged-out visitors).
- Keep only 5 revisions per post. Existing revisions are not deleted.
- Redirect attachment pages to the file URL.
- Disable the core `wp-sitemap.xml`.
- Stop the editor from downloading remote block patterns.
- Remove jQuery Migrate on the front end.

Breeze can also disable emojis. Leaving both on is harmless. Varnish serves cached HTML, so head cleanup only applies on a cache miss. Blocking `xmlrpc.php` in the server config is stronger than the PHP toggle.

## Installation

1. Upload the `assertive-site-cleanup` folder to `/wp-content/plugins/`.
2. Activate **Assertive Site Cleanup**.
3. Review **Settings → Assertive Site Cleanup**.

## FAQ

### Will this delete existing comments?

No. It closes comments and hides them. The rows stay in the database.

### Why can logged-in users still list users over REST?

The block editor needs `/wp/v2/users` for the author panel. The endpoint is removed only for logged-out requests.

### Does limiting revisions delete old ones?

No. New saves keep the latest 5. Revisions already in the database stay there.

### Will disabling the core sitemap remove an SEO plugin sitemap?

No. It only turns off WordPress core's `wp-sitemap.xml`.

## Changelog

The WordPress.org changelog lives in [`readme.txt`](readme.txt).
