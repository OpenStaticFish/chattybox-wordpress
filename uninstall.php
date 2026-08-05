<?php
/**
 * Remove ChattyBox settings when the plugin is deleted.
 *
 * @package ChattyBox
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'chattybox_settings' );
