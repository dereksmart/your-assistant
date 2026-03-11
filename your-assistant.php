<?php
/**
 * Plugin Name: Your Assistant
 * Description: Create an AI assistant as a real WordPress user, right from the admin.
 * Version: 0.1.0
 * Author: Automattic
 * License: GPL-2.0-or-later
 * Text Domain: your-assistant
 */

defined( 'ABSPATH' ) || exit;

define( 'YOUR_ASSISTANT_VERSION', '0.1.0' );
define( 'YOUR_ASSISTANT_PATH', plugin_dir_path( __FILE__ ) );
define( 'YOUR_ASSISTANT_URL', plugin_dir_url( __FILE__ ) );

require_once YOUR_ASSISTANT_PATH . 'includes/role.php';
require_once YOUR_ASSISTANT_PATH . 'includes/admin.php';
require_once YOUR_ASSISTANT_PATH . 'includes/ajax.php';
require_once YOUR_ASSISTANT_PATH . 'includes/activity.php';

register_activation_hook( __FILE__, 'your_assistant_activate' );
register_deactivation_hook( __FILE__, 'your_assistant_deactivate' );

function your_assistant_activate() {
	your_assistant_register_role();
}

function your_assistant_deactivate() {
	remove_role( 'ai_assistant' );
}

add_action( 'admin_notices', function () {
	echo '<div class="notice notice-info"><p>Hello Dolly!</p></div>';
} );
