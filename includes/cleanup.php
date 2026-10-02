<?php
/**
 * Head cleanup, version hiding, and emoji removal.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register cleanup hooks for the features that are enabled.
 */
function awpc_boot_cleanup() {
	if ( awpc_option( 'clean_head' ) ) {
		add_action( 'init', 'awpc_clean_head' );
	}

	if ( awpc_option( 'hide_version' ) ) {
		add_filter( 'the_generator', 'awpc_hide_generator' );
	}

	if ( awpc_option( 'disable_emojis' ) ) {
		add_action( 'init', 'awpc_disable_emojis' );
		// wp-admin/includes/admin-filters.php adds the admin copies after init.
		add_action( 'admin_init', 'awpc_disable_admin_emojis' );
	}

	if ( awpc_option( 'remove_feed_links' ) ) {
		add_action( 'init', 'awpc_remove_feed_links' );
	}
}
add_action( 'plugins_loaded', 'awpc_boot_cleanup' );

/**
 * Remove tags WordPress still prints in wp_head.
 *
 * index_rel_link, start_post_rel_link, parent_post_rel_link, and wlwmanifest_link
 * are no longer hooked (the first three since 3.3, Windows Live Writer since 6.3).
 * adjacent_posts_rel_link_wp_head has not been hooked since WordPress 6.0.
 */
function awpc_clean_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

	// Hooked at priority 4, and again at 10 for back-compat. Remove both,
	// or the priority-10 copy still prints when the first one never runs.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
}

/**
 * Empty the generator string used in the head and in feeds.
 *
 * @return string
 */
function awpc_hide_generator() {
	return '';
}

/**
 * Remove emoji scripts, styles, TinyMCE support, and the SVG DNS prefetch.
 */
function awpc_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'embed_head', 'print_emoji_detection_script' );

	// print_emoji_styles is deprecated since 6.4. Core still hooks it, then
	// wp_enqueue_emoji_styles bails out when that hook is already gone.
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'enqueue_embed_scripts', 'wp_enqueue_emoji_styles' );

	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', 'awpc_disable_emojis_tinymce' );
	add_filter( 'emoji_svg_url', '__return_false' );
}

/**
 * Remove the admin emoji hooks.
 *
 * Those are registered from wp-admin, which loads after init, so the front-end
 * removal never sees them.
 */
function awpc_disable_admin_emojis() {
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
}

/**
 * Drop the emoji plugin from TinyMCE without touching the other plugins.
 *
 * @param mixed $plugins TinyMCE plugin list.
 * @return mixed
 */
function awpc_disable_emojis_tinymce( $plugins ) {
	if ( ! is_array( $plugins ) ) {
		return $plugins;
	}

	return array_diff( $plugins, array( 'wpemoji' ) );
}

/**
 * Remove the site and extra feed link tags.
 */
function awpc_remove_feed_links() {
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
