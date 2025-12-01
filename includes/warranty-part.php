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
    ob_start();
    include STS_PLUGIN_DIR . 'templates/warranty-part-form.php';
    return ob_get_clean();
}
add_shortcode('warranty_part_form', 'sts_warranty_part_form_shortcode');

/**
 * Handle warranty part submission.
 */
function sts_handle_warranty_part_submission() {
    if (isset($_POST['warranty_part_nonce']) && wp_verify_nonce($_POST['warranty_part_nonce'], 'submit_warranty_part')) {
        $device_type    = sanitize_text_field($_POST['device_type']);
        $hologram_code  = sanitize_text_field($_POST['hologram_code']);
        $operator_name  = sanitize_text_field($_POST['operator_name']);
        $operator_phone = sanitize_text_field($_POST['operator_phone']);
        $operator_email = sanitize_email($_POST['operator_email']);
        $purchase_date  = sanitize_text_field($_POST['purchase_date']);
        $seller         = sanitize_text_field($_POST['seller']);

        $required_fields = array($device_type, $hologram_code, $operator_name, $operator_phone, $purchase_date);
        if (in_array('', $required_fields, true)) {
            wp_redirect(add_query_arg('warranty_part_error', 'true', wp_get_referer()));
            exit;
        }

        $warranty_id = wp_insert_post(array(
            'post_type'   => 'warranty',
            'post_title'  => sprintf(__('گارانتی + %s', 'warranty-plugin'), $hologram_code),
            'post_status' => 'publish',
            'post_author' => 0,
        ));

        if ($warranty_id) {
            update_post_meta($warranty_id, 'warranty_source', 'warranty_part');
            update_post_meta($warranty_id, 'device_type', $device_type);
            update_post_meta($warranty_id, 'hologram_code', $hologram_code);
            update_post_meta($warranty_id, 'operator_name', $operator_name);
            update_post_meta($warranty_id, 'operator_phone', $operator_phone);
            update_post_meta($warranty_id, 'operator_email', $operator_email);
            update_post_meta($warranty_id, 'purchase_date', $purchase_date);
            update_post_meta($warranty_id, 'seller', $seller);

            $to      = 'warranty@ajaxir.com';
            $subject = sprintf(__('گارانتی %s', 'warranty-part-plugin'), $hologram_code);
            $message = sprintf(
                __('%s گارانتی %s را با کد %s در تاریخ %s فعال کرد.\nشماره تماس: %s\nایمیل: %s\nاین محصول توسط %s به کاربر فروخته شده است', 'warranty-part-plugin'),
                $operator_name,
                $device_type,
                $hologram_code,
                $purchase_date,
                $operator_phone,
                $operator_email,
                $seller
            );
            wp_mail($to, $subject, $message);

            if (!empty($operator_email)) {
                wp_mail($operator_email, $subject, $message);
            }

            wp_redirect(add_query_arg('warranty_part_submitted', 'true', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'sts_handle_warranty_part_submission');

/**
 * Enqueue warranty part assets.
 */
function sts_enqueue_warranty_part_assets() {
    wp_enqueue_script('warranty-part-scripts', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/js/warranty-part-scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('warranty-part-styles', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/css/warranty-part-styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sts_enqueue_warranty_part_assets');
