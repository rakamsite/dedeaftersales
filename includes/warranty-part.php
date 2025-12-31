<?php
if (!defined('ABSPATH')) {
    exit;
}

function sts_warranty_part_load_textdomain() {
    load_textdomain('warranty-part-plugin', STS_PLUGIN_DIR . 'languages/warranty-part-plugin.mo');
}
add_action('plugins_loaded', 'sts_warranty_part_load_textdomain');

/**
 * Warranty part form shortcode.
 */
function sts_warranty_part_form_shortcode() {
    return sts_warranty_form_shortcode();
}
add_shortcode('warranty_part_form', 'sts_warranty_part_form_shortcode');
