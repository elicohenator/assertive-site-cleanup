<?php
/**
 * Disable comments and trackbacks everywhere.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register comment-disabling hooks when the feature is on.
 */
function awpc_boot_comments() {
	if ( ! awpc_option( 'disable_comments' ) ) {
		return;
	}

	// Fires for each post type as it is registered, including ones added late on init.
	add_action( 'registered_post_type', 'awpc_remove_comment_support_from_type' );
	add_action( 'init', 'awpc_remove_comment_support', 999 );
	add_filter( 'comments_open', '__return_false', 20, 2 );
	add_filter( 'pings_open', '__return_false', 20, 2 );
	add_filter( 'comments_array', '__return_empty_array', 10, 2 );
	add_filter( 'feed_links_show_comments_feed', '__return_false' );
	add_filter( 'wp_count_comments', 'awpc_zero_comment_counts' );
	add_filter( 'rest_endpoints', 'awpc_remove_comment_endpoints' );

	// Core adds this widget on wp_dashboard_setup, so admin_init is too early.
	add_action( 'wp_dashboard_setup', 'awpc_remove_comments_dashboard' );
	add_action( 'admin_menu', 'awpc_remove_comments_menus', 99 );
	add_action( 'admin_init', 'awpc_redirect_comment_screens' );
	add_action( 'admin_init', 'awpc_register_comment_column_filters' );
	add_action( 'wp_before_admin_bar_render', 'awpc_remove_comments_admin_bar' );
	add_action( 'wp_enqueue_scripts', 'awpc_dequeue_comment_reply', 100 );
	add_action( 'widgets_init', 'awpc_unregister_recent_comments_widget' );
}
add_action( 'plugins_loaded', 'awpc_boot_comments' );

/**
 * Drop comment and trackback support from one post type.
 *
 * @param string $post_type Post type name.
 */
function awpc_remove_comment_support_from_type( $post_type ) {
	remove_post_type_support( $post_type, 'comments' );
	remove_post_type_support( $post_type, 'trackbacks' );
}

/**
 * Drop comment and trackback support from post types already registered.
 */
function awpc_remove_comment_support() {
	foreach ( get_post_types() as $post_type ) {
		awpc_remove_comment_support_from_type( $post_type );
	}
}

/**
 * Remove the Recent Comments dashboard widget.
 */
function awpc_remove_comments_dashboard() {
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
}

/**
 * Remove the Comments menu and Settings → Discussion.
 */
function awpc_remove_comments_menus() {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}

/**
 * Send direct visits to the comment screens back to the dashboard.
 */
function awpc_redirect_comment_screens() {
	global $pagenow;

	if ( ! in_array( $pagenow, array( 'edit-comments.php', 'options-discussion.php' ), true ) ) {
		return;
	}

	wp_safe_redirect( admin_url() );
	exit;
}

/**
 * Remove the comments node. is_admin_bar_showing() is not reliable on init.
 */
function awpc_remove_comments_admin_bar() {
	global $wp_admin_bar;

	$wp_admin_bar->remove_node( 'comments' );
}

/**
 * Hide comment routes such as /wp/v2/comments and /wp/v2/comments/<id>.
 *
 * @param array<string, mixed> $endpoints Registered REST routes.
 * @return array<string, mixed>
 */
function awpc_remove_comment_endpoints( $endpoints ) {
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/comments' ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}

/**
 * Zero the counts used by At a Glance.
 *
 * The filtered value must be non-empty or core ignores it and runs the query.
 *
 * @return stdClass
 */
function awpc_zero_comment_counts() {
	return (object) array(
		'approved'       => 0,
		'moderated'      => 0,
		'spam'           => 0,
		'trash'          => 0,
		'post-trashed'   => 0,
		'total_comments' => 0,
		'all'            => 0,
	);
}

/**
 * Attach the comments-column filter to every list table.
 */
function awpc_register_comment_column_filters() {
	foreach ( get_post_types() as $post_type ) {
		add_filter( "manage_{$post_type}_posts_columns", 'awpc_remove_comments_column' );
	}

	add_filter( 'manage_media_columns', 'awpc_remove_comments_column' );
}

/**
 * Remove the comments count column.
 *
 * @param array<string, string> $columns List table columns.
 * @return array<string, string>
 */
function awpc_remove_comments_column( $columns ) {
	unset( $columns['comments'] );
	return $columns;
}

/**
 * Stop the comment-reply script from loading.
 */
function awpc_dequeue_comment_reply() {
	wp_dequeue_script( 'comment-reply' );
}

/**
 * Remove the Recent Comments widget.
 *
 * Default widgets are registered before widgets_init fires.
 */
function awpc_unregister_recent_comments_widget() {
	unregister_widget( 'WP_Widget_Recent_Comments' );
}
