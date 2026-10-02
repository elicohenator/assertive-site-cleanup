<?php
/**
 * Plugin Name: Assertive Site Cleanup
 * Description: Disables unused comments and strips leftover WordPress head output. Every feature can be turned off under Settings → Assertive Site Cleanup.
 * Version: 2.1.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Eli Cohen
 * Author URI: https://www.elicohenator.xyz
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: assertive-site-cleanup
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AWPC_VERSION', '2.1.0' );
define( 'AWPC_OPTION', 'awpc_settings' );

/**
 * Default feature flags.
 *
 * Keys missing from the saved option fall back to these values.
 *
 * @return array<string, bool>
 */
function awpc_defaults() {
	return array(
		'disable_comments'        => true,
		'disable_emojis'          => true,
		'clean_head'              => true,
		'hide_version'            => true,
		'generic_login_errors'    => true,
		'hide_rest_users'         => true,
		'disable_xmlrpc'          => true,
		'remove_dashboard_news'   => true,
		'remove_feed_links'       => false,
		'hide_admin_bar'          => false,
		'block_author_enum'       => false,
		'disable_file_editor'     => false,
		'disable_speculation'     => false,
		'limit_revisions'         => false,
		'redirect_attachments'    => false,
		'disable_sitemaps'        => false,
		'disable_remote_patterns' => false,
		'dequeue_jquery_migrate'  => false,
	);
}

/**
 * Whether a feature is enabled.
 *
 * @param string $key Feature key.
 * @return bool
 */
function awpc_option( $key ) {
	$settings = get_option( AWPC_OPTION, array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$settings = array_merge( awpc_defaults(), $settings );

	return ! empty( $settings[ $key ] );
}

/**
 * Store defaults on first activation.
 *
 * Updates do not re-run this hook. awpc_option() still applies defaults
 * when the option row does not exist yet.
 */
function awpc_activate() {
	if ( false === get_option( AWPC_OPTION ) ) {
		add_option( AWPC_OPTION, awpc_defaults() );
	}
}
register_activation_hook( __FILE__, 'awpc_activate' );

require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/comments.php';
require_once __DIR__ . '/includes/cleanup.php';
require_once __DIR__ . '/includes/hardening.php';
require_once __DIR__ . '/includes/extras.php';
