<?php
/**
 * Remove plugin settings on uninstall.
 *
 * @package Assertive_Site_Cleanup
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'awpc_settings' );
