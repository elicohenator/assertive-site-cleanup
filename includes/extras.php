<?php
/**
 * Optional cleanup that is not part of the head or comment routines.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the extra cleanup hooks that are enabled.
 */
function awpc_boot_extras() {
	if ( awpc_option( 'remove_dashboard_news' ) ) {
		// Fires after core adds dashboard_primary, and before the welcome panel renders.
		add_action( 'wp_dashboard_setup', 'awpc_remove_dashboard_news' );
	}

	if ( awpc_option( 'disable_speculation' ) ) {
		add_filter( 'wp_speculation_rules_configuration', '__return_null' );
	}

	if ( awpc_option( 'limit_revisions' ) ) {
		add_filter( 'wp_revisions_to_keep', 'awpc_limit_revisions', 10, 2 );
		// The per-post-type filter runs after wp_revisions_to_keep and would otherwise undo this.
		add_action( 'registered_post_type', 'awpc_limit_revisions_for_type' );
	}

	if ( awpc_option( 'redirect_attachments' ) ) {
		add_action( 'template_redirect', 'awpc_redirect_attachment_page' );
	}

	if ( awpc_option( 'disable_sitemaps' ) ) {
		add_filter( 'wp_sitemaps_enabled', '__return_false' );
	}

	if ( awpc_option( 'disable_remote_patterns' ) ) {
		add_filter( 'should_load_remote_block_patterns', '__return_false' );
	}

	if ( awpc_option( 'dequeue_jquery_migrate' ) ) {
		add_action( 'wp_default_scripts', 'awpc_remove_jquery_migrate' );
	}
}
add_action( 'plugins_loaded', 'awpc_boot_extras' );

/**
 * Remove the Events and News widget and the welcome panel.
 */
function awpc_remove_dashboard_news() {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}

/**
 * Cap one post type via wp_{$post_type}_revisions_to_keep.
 *
 * @param string $post_type Post type name.
 */
function awpc_limit_revisions_for_type( $post_type ) {
	add_filter( "wp_{$post_type}_revisions_to_keep", 'awpc_limit_revisions', 10, 2 );
}

/**
 * Keep five revisions for every post type that supports them.
 *
 * Does not delete revisions that are already stored.
 *
 * @param int     $num  Revisions core would otherwise keep.
 * @param WP_Post $post Post being saved.
 * @return int
 */
function awpc_limit_revisions( $num, $post ) {
	if ( ! $post instanceof WP_Post || ! post_type_supports( $post->post_type, 'revisions' ) ) {
		return (int) $num;
	}

	return 5;
}

/**
 * Send an attachment page to the file itself.
 */
function awpc_redirect_attachment_page() {
	if ( ! is_attachment() ) {
		return;
	}

	$url = wp_get_attachment_url( get_queried_object_id() );

	if ( ! $url ) {
		return;
	}

	// The file may live on a CDN host, which wp_safe_redirect would otherwise reject.
	add_filter( 'allowed_redirect_hosts', 'awpc_allow_attachment_redirect_host' );
	wp_safe_redirect( $url, 301 );
	exit;
}

/**
 * Allow the attachment file host during the redirect above.
 *
 * @param string[] $hosts Allowed hosts.
 * @return string[]
 */
function awpc_allow_attachment_redirect_host( $hosts ) {
	$url  = wp_get_attachment_url( get_queried_object_id() );
	$host = $url ? wp_parse_url( $url, PHP_URL_HOST ) : '';

	if ( $host ) {
		$hosts[] = $host;
	}

	return $hosts;
}

/**
 * Drop jQuery Migrate from the front-end jQuery bundle.
 *
 * Admin screens still load it. wp_default_scripts runs before the script is printed.
 *
 * @param WP_Scripts $scripts Script registry.
 */
function awpc_remove_jquery_migrate( $scripts ) {
	if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
		return;
	}

	$deps = $scripts->registered['jquery']->deps;

	if ( ! is_array( $deps ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_diff( $deps, array( 'jquery-migrate' ) );
}
