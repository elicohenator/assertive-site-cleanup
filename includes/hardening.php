<?php
/**
 * Login, REST, XML-RPC, author, and file-editor hardening.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register hardening hooks for the features that are enabled.
 */
function awpc_boot_hardening() {
	if ( awpc_option( 'generic_login_errors' ) ) {
		add_filter( 'wp_login_errors', 'awpc_generic_login_errors' );
	}

	if ( awpc_option( 'hide_rest_users' ) ) {
		add_filter( 'rest_endpoints', 'awpc_hide_rest_users' );
	}

	if ( awpc_option( 'disable_xmlrpc' ) ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', 'awpc_disable_xmlrpc_methods', 100 );
		add_filter( 'wp_headers', 'awpc_remove_pingback_header' );
		add_filter( 'pings_open', '__return_false', 20, 2 );
	}

	if ( awpc_option( 'block_author_enum' ) ) {
		// Before redirect_canonical (priority 10) turns ?author=1 into /author/slug/.
		add_action( 'template_redirect', 'awpc_block_author_enumeration', 1 );
	}

	if ( awpc_option( 'hide_admin_bar' ) ) {
		add_filter( 'show_admin_bar', 'awpc_hide_admin_bar' );
	}

	if ( awpc_option( 'disable_file_editor' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress core constant.
	}
}
add_action( 'plugins_loaded', 'awpc_boot_hardening' );

/**
 * Replace credential failures only.
 *
 * Runs on the login screen's WP_Error, not on the rendered HTML, so an
 * expired or invalid password-reset link keeps its own message.
 *
 * @param WP_Error $errors Login errors.
 * @return WP_Error
 */
function awpc_generic_login_errors( $errors ) {
	if ( ! $errors instanceof WP_Error ) {
		return $errors;
	}

	$codes   = array( 'invalid_username', 'invalid_email', 'incorrect_password' );
	$matched = false;

	foreach ( $codes as $code ) {
		if ( $errors->get_error_message( $code ) ) {
			$errors->remove( $code );
			$matched = true;
		}
	}

	if ( $matched ) {
		$errors->add(
			'invalid_login',
			__( '<strong>Error:</strong> Incorrect login details.', 'assertive-site-cleanup' )
		);
	}

	return $errors;
}

/**
 * Remove user routes for logged-out requests.
 *
 * Logged-in requests are left alone so the block editor author panel keeps working.
 *
 * @param array<string, mixed> $endpoints Registered REST routes.
 * @return array<string, mixed>
 */
function awpc_hide_rest_users( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}

/**
 * Drop every XML-RPC method, including pingback.ping.
 *
 * @param array<string, string> $methods Registered XML-RPC methods. Ignored.
 * @return array<string, string>
 */
function awpc_disable_xmlrpc_methods( $methods ) {
	return array();
}

/**
 * Remove the X-Pingback response header.
 *
 * @param array<string, string> $headers Response headers.
 * @return array<string, string>
 */
function awpc_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}

/**
 * Redirect ?author=N to the homepage.
 */
function awpc_block_author_enumeration() {
	if ( is_admin() ) {
		return;
	}

	// Public probe, not a submitted form.
	if ( ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}

/**
 * Hide the front-end toolbar for anyone who is not an administrator.
 *
 * @param bool $show Whether to show the admin bar.
 * @return bool
 */
function awpc_hide_admin_bar( $show ) {
	if ( is_admin() ) {
		return $show;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	return $show;
}
