<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Warranty post type registration.
 */
function sts_register_warranty_post_type() {
    register_post_type('warranty', array(
        'labels' => array(
            'name'          => __('گارانتی‌ها', 'warranty-plugin'),
            'singular_name' => __('گارانتی', 'warranty-plugin'),
        ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite'      => false,
        'query_var'    => false,
        'supports'     => array('title'),
        'menu_icon'    => 'dashicons-shield',
    ));
}
add_action('init', 'sts_register_warranty_post_type');

function sts_warranty_load_textdomain() {
    load_textdomain('warranty-plugin', STS_PLUGIN_DIR . 'languages/warranty-plugin.mo');
}
add_action('plugins_loaded', 'sts_warranty_load_textdomain');

/**
 * Warranty form shortcode.
 */
function sts_warranty_form_shortcode() {
    ob_start();
    include STS_PLUGIN_DIR . 'templates/warranty-form.php';
    return ob_get_clean();
}
add_shortcode('warranty_form', 'sts_warranty_form_shortcode');

/**
 * Handle warranty form submission.
 */
function sts_handle_warranty_submission() {
    if (isset($_POST['warranty_nonce']) && wp_verify_nonce($_POST['warranty_nonce'], 'submit_warranty')) {
        $product_type          = sanitize_text_field($_POST['product_type']);
        $hologram_code         = sanitize_text_field($_POST['hologram_code']);
        $pro_number            = sanitize_text_field($_POST['pro_number']);
        $installer_name        = sanitize_text_field($_POST['installer_name']);
        $installer_phone       = sanitize_text_field($_POST['installer_phone']);
        $installer_email       = sanitize_email($_POST['installer_email']);
        $installation_location = sanitize_text_field($_POST['installation_location']);
        $installation_date     = sanitize_text_field($_POST['installation_date']);
        $seller                = sanitize_text_field($_POST['seller']);

        $required_fields = array($product_type, $hologram_code, $installer_name, $installer_phone, $installation_location, $installation_date);
        if (in_array('', $required_fields, true)) {
            wp_redirect(add_query_arg('warranty_error', 'true', wp_get_referer()));
            exit;
        }

        $warranty_id = wp_insert_post(array(
            'post_type'   => 'warranty',
            'post_title'  => sprintf(__('گارانتی - کد هولوگرام: %s', 'warranty-plugin'), $hologram_code),
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ));

        if ($warranty_id) {
            update_post_meta($warranty_id, 'product_type', $product_type);
            update_post_meta($warranty_id, 'hologram_code', $hologram_code);
            update_post_meta($warranty_id, 'pro_number', $pro_number);
            update_post_meta($warranty_id, 'installer_name', $installer_name);
            update_post_meta($warranty_id, 'installer_phone', $installer_phone);
            update_post_meta($warranty_id, 'installer_email', $installer_email);
            update_post_meta($warranty_id, 'installation_location', $installation_location);
            update_post_meta($warranty_id, 'installation_date', $installation_date);
            update_post_meta($warranty_id, 'seller', $seller);

            $to       = 'warranty@ajaxir.com';
            $subject  = sprintf(__('گارانتی %s', 'warranty-plugin'), $hologram_code);
            $message  = sprintf(
                __('%s %s با کد %s که در تاریخ %s در %s نصب کرده است فعال سازی کرد.\nشماره تماس نصاب: %s\nشماره محصول %s مندرج در لیبل محصول میباشد و نصاب محصول را از %s خریداری کرده است.', 'warranty-plugin'),
                $installer_name,
                $product_type,
                $hologram_code,
                $installation_date,
                $installation_location,
                $installer_phone,
                $pro_number,
                $seller
            );
            wp_mail($to, $subject, $message);

            if (!empty($installer_email)) {
                wp_mail($installer_email, $subject, $message);
            }

            wp_redirect(add_query_arg('warranty_submitted', 'true', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'sts_handle_warranty_submission');

/**
 * Enqueue warranty assets.
 */
function sts_enqueue_warranty_assets() {
    wp_enqueue_script('warranty-scripts', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/js/warranty-scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('warranty-styles', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/css/warranty-styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sts_enqueue_warranty_assets');
