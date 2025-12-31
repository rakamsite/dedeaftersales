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
 * Send SMS via WooCommerce SMS Professional plugin when available.
 *
 * @param string $phone
 * @param string $message
 * @return bool
 */
function sts_send_warranty_sms($phone, $message) {
    if (empty($phone) || empty($message)) {
        return false;
    }

    if (function_exists('persian_woocommerce_sms_send')) {
        persian_woocommerce_sms_send($phone, $message);
        return true;
    }

    if (class_exists('PW_WooCommerce_SMS')) {
        $sms_instance = method_exists('PW_WooCommerce_SMS', 'get_instance')
            ? PW_WooCommerce_SMS::get_instance()
            : new PW_WooCommerce_SMS();
        if (method_exists($sms_instance, 'send_sms')) {
            $sms_instance->send_sms($phone, $message);
            return true;
        }
    }

    if (has_action('persian_woocommerce_sms_send')) {
        do_action('persian_woocommerce_sms_send', $phone, $message);
        return true;
    }

    if (function_exists('woocommerce_send_sms')) {
        woocommerce_send_sms($phone, $message);
        return true;
    }

    if (function_exists('woo_sms_send')) {
        woo_sms_send($phone, $message);
        return true;
    }

    if (class_exists('WC_SMS')) {
        $sms_instance = new WC_SMS();
        if (method_exists($sms_instance, 'send_sms')) {
            $sms_instance->send_sms($phone, $message);
            return true;
        }
    }

    if (class_exists('WooCommerce_SMS')) {
        $sms_instance = method_exists('WooCommerce_SMS', 'get_instance')
            ? WooCommerce_SMS::get_instance()
            : new WooCommerce_SMS();
        if (method_exists($sms_instance, 'send_sms')) {
            $sms_instance->send_sms($phone, $message);
            return true;
        }
    }

    do_action('woocommerce_sms_send', $phone, $message);

    return true;
}

/**
 * Handle warranty form submission.
 */
function sts_handle_warranty_submission() {
    if (isset($_POST['warranty_nonce']) && wp_verify_nonce($_POST['warranty_nonce'], 'submit_warranty')) {
        $product_category      = sanitize_text_field(wp_unslash($_POST['product_category'] ?? ''));
        $product_type          = sanitize_text_field(wp_unslash($_POST['product_type'] ?? ''));
        $device_type           = sanitize_text_field(wp_unslash($_POST['device_type'] ?? ''));
        $hologram_code         = sanitize_text_field(wp_unslash($_POST['hologram_code'] ?? ''));
        $purchase_date         = sanitize_text_field(wp_unslash($_POST['purchase_date'] ?? ''));
        $installation_date     = sanitize_text_field(wp_unslash($_POST['installation_date'] ?? ''));
        $installation_location = sanitize_text_field(wp_unslash($_POST['installation_location'] ?? ''));
        $installer_name        = sanitize_text_field(wp_unslash($_POST['installer_name'] ?? ''));
        $installer_phone       = sanitize_text_field(wp_unslash($_POST['installer_phone'] ?? ''));
        $installer_email       = sanitize_email(wp_unslash($_POST['installer_email'] ?? ''));
        $operator_name         = sanitize_text_field(wp_unslash($_POST['operator_name'] ?? ''));
        $operator_phone        = sanitize_text_field(wp_unslash($_POST['operator_phone'] ?? ''));
        $operator_email        = sanitize_email(wp_unslash($_POST['operator_email'] ?? ''));
        $confirm_info          = isset($_POST['confirm_info']);

        $errors = array();

        if (!in_array($product_category, array('part', 'tool'), true)) {
            $errors[] = 'invalid-category';
        }

        if (!preg_match('/^\d{6}$/', $hologram_code)) {
            $errors[] = 'invalid-hologram';
        }

        if (!$confirm_info) {
            $errors[] = 'missing-confirmation';
        }

        if ($product_category === 'part') {
            $required_fields = array($product_type, $purchase_date, $installation_date, $installation_location, $installer_name, $installer_phone);
            if (in_array('', $required_fields, true)) {
                $errors[] = 'missing-part-fields';
            }
        }

        if ($product_category === 'tool') {
            $required_fields = array($device_type, $purchase_date, $operator_name, $operator_phone);
            if (in_array('', $required_fields, true)) {
                $errors[] = 'missing-tool-fields';
            }
        }

        if (!empty($errors)) {
            wp_redirect(add_query_arg('warranty_error', 'true', wp_get_referer()));
            exit;
        }

        $installed_product_image = '';
        if ($product_category === 'part') {
            if (empty($_FILES['installed_product_image']['name'])) {
                wp_redirect(add_query_arg('warranty_error', 'true', wp_get_referer()));
                exit;
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload = wp_handle_upload($_FILES['installed_product_image'], array('test_form' => false));
            if (!empty($upload['error']) || empty($upload['url'])) {
                wp_redirect(add_query_arg('warranty_error', 'true', wp_get_referer()));
                exit;
            }

            $installed_product_image = $upload['url'];
        }

        $warranty_id = wp_insert_post(array(
            'post_type'   => 'warranty',
            'post_title'  => sprintf(__('گارانتی + %s', 'warranty-plugin'), $hologram_code),
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ));

        if ($warranty_id) {
            $tracking_number = sprintf('WR-%s-%d', current_time('Ymd'), $warranty_id);
            $warranty_start_date = $product_category === 'part' ? $installation_date : $purchase_date;
            $display_type = $product_category === 'part' ? $product_type : $device_type;

            update_post_meta($warranty_id, 'warranty_source', 'combined_form');
            update_post_meta($warranty_id, 'product_category', $product_category);
            update_post_meta($warranty_id, 'product_type', $product_type);
            update_post_meta($warranty_id, 'device_type', $device_type);
            update_post_meta($warranty_id, 'hologram_code', $hologram_code);
            update_post_meta($warranty_id, 'purchase_date', $purchase_date);
            update_post_meta($warranty_id, 'warranty_start_date', $warranty_start_date);
            update_post_meta($warranty_id, 'tracking_number', $tracking_number);
            update_post_meta($warranty_id, 'installer_name', $installer_name);
            update_post_meta($warranty_id, 'installer_phone', $installer_phone);
            update_post_meta($warranty_id, 'installer_email', $installer_email);
            update_post_meta($warranty_id, 'installation_location', $installation_location);
            update_post_meta($warranty_id, 'installation_date', $installation_date);
            update_post_meta($warranty_id, 'operator_name', $operator_name);
            update_post_meta($warranty_id, 'operator_phone', $operator_phone);
            update_post_meta($warranty_id, 'operator_email', $operator_email);
            update_post_meta($warranty_id, 'installed_product_image', $installed_product_image);

            $message = sprintf(
                __('گارانتی محصول شما با موفقیت فعال شد.\nشماره پیگیری: %s\nنوع محصول: %s\nتاریخ شروع گارانتی: %s', 'warranty-plugin'),
                $tracking_number,
                $display_type,
                $warranty_start_date
            );

            $subject = sprintf(__('فعال‌سازی گارانتی %s', 'warranty-plugin'), $tracking_number);
            $recipient_phone = $product_category === 'part' ? $installer_phone : $operator_phone;
            $recipient_email = $product_category === 'part' ? $installer_email : $operator_email;

            sts_send_warranty_sms($recipient_phone, $message);

            if (!empty($recipient_email)) {
                wp_mail($recipient_email, $subject, $message);
            }

            wp_redirect(add_query_arg('warranty_submitted', 'true', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'sts_handle_warranty_submission');

/**
 * Add admin metabox to display warranty details.
 */
function sts_add_warranty_details_meta_box() {
    add_meta_box(
        'sts_warranty_details',
        __('اطلاعات ثبت‌شده گارانتی', 'warranty-plugin'),
        'sts_render_warranty_details_meta_box',
        'warranty',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sts_add_warranty_details_meta_box');

/**
 * Render warranty details in admin area.
 */
function sts_render_warranty_details_meta_box($post) {
    $fields = array(
        'warranty_source'        => __('منبع فرم', 'warranty-plugin'),
        'product_category'       => __('دسته محصول', 'warranty-plugin'),
        'product_type'           => __('نوع محصول', 'warranty-plugin'),
        'device_type'            => __('نوع دستگاه', 'warranty-plugin'),
        'hologram_code'          => __('کد هولوگرام طلایی', 'warranty-plugin'),
        'tracking_number'        => __('شماره پیگیری', 'warranty-plugin'),
        'installer_name'         => __('نام نصاب', 'warranty-plugin'),
        'installer_phone'        => __('شماره همراه نصاب', 'warranty-plugin'),
        'installer_email'        => __('ایمیل نصاب', 'warranty-plugin'),
        'installation_location'  => __('محل نصب پروژه', 'warranty-plugin'),
        'installation_date'      => __('تاریخ نصب', 'warranty-plugin'),
        'operator_name'          => __('نام بهره‌بردار', 'warranty-plugin'),
        'operator_phone'         => __('شماره همراه بهره‌بردار', 'warranty-plugin'),
        'operator_email'         => __('ایمیل بهره‌بردار', 'warranty-plugin'),
        'purchase_date'          => __('تاریخ خرید', 'warranty-plugin'),
        'warranty_start_date'    => __('تاریخ شروع گارانتی', 'warranty-plugin'),
        'installed_product_image' => __('تصویر محصول نصب‌شده', 'warranty-plugin'),
    );

    $source_labels = array(
        'combined_form' => __('فرم یکپارچه فعال‌سازی گارانتی', 'warranty-plugin'),
    );

    echo '<table class="widefat striped" style="margin-top:10px">';
    echo '<tbody>';

    foreach ($fields as $meta_key => $label) {
        $raw_value = get_post_meta($post->ID, $meta_key, true);

        if ($meta_key === 'warranty_source') {
            $raw_value = $raw_value && isset($source_labels[$raw_value]) ? $source_labels[$raw_value] : __('نامشخص', 'warranty-plugin');
        }

        if ($raw_value === '') {
            continue;
        }

        if ($meta_key === 'product_category') {
            $raw_value = $raw_value === 'part'
                ? __('سرکابل و مفصل', 'warranty-plugin')
                : ($raw_value === 'tool' ? __('ابزار آلات', 'warranty-plugin') : $raw_value);
        }

        if ($meta_key === 'installed_product_image') {
            printf(
                '<tr><th style="width:30%%">%s</th><td><a href="%s" target="_blank" rel="noopener">%s</a></td></tr>',
                esc_html($label),
                esc_url($raw_value),
                esc_html($raw_value)
            );
            continue;
        }

        printf(
            '<tr><th style="width:30%%">%s</th><td>%s</td></tr>',
            esc_html($label),
            esc_html($raw_value)
        );
    }

    echo '</tbody>';
    echo '</table>';
}

/**
 * Enqueue warranty assets.
 */
function sts_enqueue_warranty_assets() {
    wp_enqueue_script('warranty-scripts', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/js/warranty-scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('warranty-styles', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/css/warranty-styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sts_enqueue_warranty_assets');

/**
 * Warranty settings submenu.
 */
function sts_add_warranty_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=warranty',
        __('تنظیمات گارانتی', 'warranty-plugin'),
        __('تنظیمات', 'warranty-plugin'),
        'manage_options',
        'warranty-settings',
        'sts_warranty_settings_page'
    );
}
add_action('admin_menu', 'sts_add_warranty_settings_submenu');

function sts_warranty_settings_page() {
    if (isset($_POST['submit'])) {
        check_admin_referer('sts_warranty_settings');
        update_option('sts_hologram_sample_image', esc_url_raw($_POST['sts_hologram_sample_image'] ?? ''));
        echo '<div class="notice notice-success"><p>' . __('تنظیمات با موفقیت ذخیره شد.', 'warranty-plugin') . '</p></div>';
    }

    $hologram_image = get_option('sts_hologram_sample_image', '');
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات گارانتی', 'warranty-plugin'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('sts_warranty_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sts_hologram_sample_image"><?php _e('تصویر نمونه هولوگرام', 'warranty-plugin'); ?></label>
                    </th>
                    <td>
                        <div class="sts-media-control">
                            <input type="text" id="sts_hologram_sample_image" name="sts_hologram_sample_image" value="<?php echo esc_url($hologram_image); ?>" class="regular-text" />
                            <button type="button" class="button sts-media-upload"><?php _e('انتخاب تصویر', 'warranty-plugin'); ?></button>
                        </div>
                        <div class="sts-media-preview">
                            <?php if (!empty($hologram_image)) : ?>
                                <img src="<?php echo esc_url($hologram_image); ?>" alt="<?php esc_attr_e('نمونه هولوگرام', 'warranty-plugin'); ?>" style="max-width:200px;margin-top:10px;" />
                            <?php endif; ?>
                        </div>
                        <p class="description"><?php _e('این تصویر در کنار فیلد کد هولوگرام نمایش داده می‌شود.', 'warranty-plugin'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Enqueue admin assets for warranty settings.
 */
function sts_enqueue_warranty_admin_assets($hook) {
    if ($hook !== 'warranty_page_warranty-settings') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script(
        'warranty-admin',
        plugin_dir_url(STS_PLUGIN_FILE) . 'assets/js/warranty-admin.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'sts_enqueue_warranty_admin_assets');
