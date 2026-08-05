<?php
/**
 * Plugin Name: ChattyBox AI Chatbot
 * Plugin URI: https://chattybox.ai/wordpress-chatbot-plugin/
 * Description: Add the ChattyBox source-cited AI chatbot to your public WordPress pages without editing theme files.
 * Version: 0.1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: ChattyBox
 * Author URI: https://chattybox.ai/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: chattybox
 *
 * @package ChattyBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CHATTYBOX_VERSION', '0.1.0' );
define( 'CHATTYBOX_FILE', __FILE__ );
define( 'CHATTYBOX_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHATTYBOX_URL', plugin_dir_url( __FILE__ ) );

require_once CHATTYBOX_DIR . 'includes/class-chattybox-plugin.php';

register_activation_hook(
	__FILE__,
	array( 'ChattyBox_Plugin', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'ChattyBox_Plugin', 'deactivate' )
);

add_action(
	'plugins_loaded',
	static function () {
		ChattyBox_Plugin::instance()->boot();
	}
);
