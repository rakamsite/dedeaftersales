<?php
/*
Plugin Name: Simple Ticket System
Description: A simple ticket system for WooCommerce with user ticket submission, admin management, and email notifications.
Version: 1.6
Author: sajad
Text Domain: simple-ticket
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('STS_PLUGIN_FILE')) {
    define('STS_PLUGIN_FILE', __FILE__);
}

define('STS_PLUGIN_DIR', plugin_dir_path(STS_PLUGIN_FILE));
/**
 * Load plugin textdomains.
 */
function sts_load_textdomain() {
    load_plugin_textdomain('simple-ticket', false, dirname(plugin_basename(STS_PLUGIN_FILE)) . '/languages');
}
add_action('plugins_loaded', 'sts_load_textdomain');

require_once STS_PLUGIN_DIR . 'includes/tickets.php';
require_once STS_PLUGIN_DIR . 'includes/warranty.php';
require_once STS_PLUGIN_DIR . 'includes/warranty-part.php';
