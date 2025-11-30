<?php
/*
Plugin Name: Simple Ticket System
Description: A simple ticket system for WooCommerce with user ticket submission, admin management, and email notifications.
Version: 1.8
Author: sajad
Text Domain: simple-ticket
*/

if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type for Tickets
function sts_register_ticket_post_type() {
    $labels = array(
        'name' => __('درخواست‌ها', 'simple-ticket'),
        'singular_name' => __('درخواست', 'simple-ticket'),
        'menu_name' => __('درخواست‌ها', 'simple-ticket'),
        'add_new' => __('افزودن درخواست', 'simple-ticket'),
        'add_new_item' => __('افزودن درخواست جدید', 'simple-ticket'),
        'edit_item' => __('ویرایش درخواست', 'simple-ticket'),
        'new_item' => __('درخواست جدید', 'simple-ticket'),
        'view_item' => __('نمایش درخواست', 'simple-ticket'),
        'all_items' => __('همه درخواست‌ها', 'simple-ticket'),
        'search_items' => __('جستجوی درخواست‌ها', 'simple-ticket'),
        'not_found' => __('درخواستی یافت نشد', 'simple-ticket'),
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'ticket'),
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => 20,
        'supports' => array('title'),
    );
    register_post_type('ticket', $args);
}
add_action('init', 'sts_register_ticket_post_type');

// Add Settings Submenu
function sts_add_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=ticket',
        __('تنظیمات درخواست‌ها', 'simple-ticket'),
        __('تنظیمات', 'simple-ticket'),
        'manage_options',
        'ticket-settings',
        'sts_settings_page'
    );
}
add_action('admin_menu', 'sts_add_settings_submenu');

// Settings Page
function sts_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('sts_admin_email', sanitize_email($_POST['admin_email']));
        echo '<div class="notice notice-success"><p>' . __('تنظیمات با موفقیت ذخیره شد.', 'simple-ticket') . '</p></div>';
    }
    
    $admin_email = get_option('sts_admin_email', get_option('admin_email'));
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات درخواست‌ها', 'simple-ticket'); ?></h1>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="admin_email"><?php _e('ایمیل ادمین برای نوتیفیکیشن:', 'simple-ticket'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text" />
                        <p class="description"><?php _e('ایمیل‌های نوتیفیکیشن درخواست‌های جدید به این آدرس ارسال می‌شود.', 'simple-ticket'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add Meta Box for Ticket Details
function sts_add_ticket_meta_box() {
    add_meta_box(
        'ticket_details',
        __('جزئیات درخواست', 'simple-ticket'),
        'sts_render_ticket_meta_box',
        'ticket',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sts_add_ticket_meta_box');

function sts_render_ticket_meta_box($post) {
    wp_nonce_field('sts_save_ticket_meta', 'ticket_meta_nonce');
    $ticket_number = get_post_meta($post->ID, 'ticket_number', true);
    $order_number = get_post_meta($post->ID, 'order_number', true);
    $order_date = get_post_meta($post->ID, 'order_date', true);
    $delivery_method = get_post_meta($post->ID, 'delivery_method', true);
    $issue_type = get_post_meta($post->ID, 'issue_type', true);
    $issue_description = get_post_meta($post->ID, 'issue_description', true);
    $response_preference = get_post_meta($post->ID, 'response_preference', true);
    $attachment = get_post_meta($post->ID, 'attachment', true);
    $responses = get_post_meta($post->ID, 'responses', true) ?: array(); // دریافت همه پاسخ‌ها
    $ticket_status = get_post_meta($post->ID, 'ticket_status', true);
    
    // Get user information
    $user_id = get_post_meta($post->ID, 'user_id', true);
    $user = get_userdata($user_id);
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $user_full_name = trim($first_name . ' ' . $last_name);
    if (empty($user_full_name)) {
        $user_full_name = $user ? $user->user_login : 'کاربر';
    }
    ?>
    <p><label><?php _e('شماره درخواست:', 'simple-ticket'); ?></label><input type="text" name="ticket_number" value="<?php echo esc_attr($ticket_number); ?>" readonly></p>
    <p><label><?php _e('کاربر:', 'simple-ticket'); ?></label><input type="text" value="<?php echo esc_attr($user_full_name); ?>" readonly></p>
    <p><label><?php _e('شماره سفارش:', 'simple-ticket'); ?></label><input type="text" name="order_number" value="<?php echo esc_attr($order_number); ?>" readonly></p>
    <p><label><?php _e('تاریخ سفارش (شمسی):', 'simple-ticket'); ?></label><input type="text" name="order_date" value="<?php echo esc_attr($order_date); ?>" readonly></p>
    <p><label><?php _e('نحوه دریافت:', 'simple-ticket'); ?></label><input type="text" name="delivery_method" value="<?php echo esc_attr($delivery_method); ?>" readonly></p>
    <p><label><?php _e('نوع مشکل:', 'simple-ticket'); ?></label><input type="text" name="issue_type" value="<?php echo esc_attr($issue_type); ?>" readonly></p>
    <p><label><?php _e('شرح مشکل:', 'simple-ticket'); ?></label><textarea name="issue_description" readonly><?php echo esc_textarea($issue_description); ?></textarea></p>
    <p><label><?php _e('ترجیح پاسخگویی:', 'simple-ticket'); ?></label><input type="text" name="response_preference" value="<?php echo esc_attr($response_preference); ?>" readonly></p>
    <?php if ($attachment): ?>
        <p><label><?php _e('فایل ضمیمه:', 'simple-ticket'); ?></label><a href="<?php echo esc_url($attachment); ?>" target="_blank"><?php _e('دانلود فایل', 'simple-ticket'); ?></a></p>
    <?php endif; ?>
    <h3><?php _e('پاسخ‌ها:', 'simple-ticket'); ?></h3>
    <?php foreach ($responses as $response): ?>
        <p><strong><?php echo esc_html($response['author'] === 'admin' ? 'ادمین' : $user_full_name); ?> (<?php echo esc_html($response['date']); ?>):</strong><br><?php echo esc_textarea($response['message']); ?></p>
    <?php endforeach; ?>
    <p><label><?php _e('پاسخ ادمین:', 'simple-ticket'); ?></label><textarea name="admin_response"></textarea></p>
    <p><label><?php _e('وضعیت درخواست:', 'simple-ticket'); ?></label>
        <select name="ticket_status">
            <option value="new" <?php selected($ticket_status, 'new'); ?>><?php _e('جدید', 'simple-ticket'); ?></option>
            <option value="reviewed" <?php selected($ticket_status, 'reviewed'); ?>><?php _e('بررسی شده', 'simple-ticket'); ?></option>
            <option value="responded" <?php selected($ticket_status, 'responded'); ?>><?php _e('پاسخ داده شده', 'simple-ticket'); ?></option>
            <option value="closed" <?php selected($ticket_status, 'closed'); ?>><?php _e('بسته شده', 'simple-ticket'); ?></option>
        </select>
    </p>
    <?php
}

// Save Meta Box Data
function sts_save_ticket_meta($post_id) {
    if (!isset($_POST['ticket_meta_nonce']) || !wp_verify_nonce($_POST['ticket_meta_nonce'], 'sts_save_ticket_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    $responses = get_post_meta($post_id, 'responses', true) ?: array();
    if (isset($_POST['admin_response']) && !empty($_POST['admin_response'])) {
        $admin_response = sanitize_textarea_field($_POST['admin_response']);
        $responses[] = array(
            'author' => 'admin',
            'date' => current_time('Y-m-d H:i:s'),
            'message' => $admin_response
        );
        update_post_meta($post_id, 'responses', $responses);
        $ticket_status = get_post_meta($post_id, 'ticket_status', true);
        if ($admin_response && $ticket_status !== 'closed') {
            update_post_meta($post_id, 'ticket_status', 'responded');
            // Send email to user on status change
            $user_id = get_post_meta($post_id, 'user_id', true);
            $user = get_userdata($user_id);
            if ($user) {
                $ticket_number = get_post_meta($post_id, 'ticket_number', true);
                $subject = __('به‌روزرسانی وضعیت درخواست', 'simple-ticket');
                $message = sprintf(__('وضعیت درخواست شماره %s به %s تغییر یافت. پاسخ: %s', 'simple-ticket'), $ticket_number, __('پاسخ داده شده', 'simple-ticket'), $admin_response);
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
    if (isset($_POST['ticket_status'])) {
        $new_status = sanitize_text_field($_POST['ticket_status']);
        $old_status = get_post_meta($post_id, 'ticket_status', true);
        if ($new_status !== $old_status) {
            update_post_meta($post_id, 'ticket_status', $new_status);
            // Send email to user on status change
            $user_id = get_post_meta($post_id, 'user_id', true);
            $user = get_userdata($user_id);
            if ($user) {
                $ticket_number = get_post_meta($post_id, 'ticket_number', true);
                $statuses = array(
                    'new' => __('جدید', 'simple-ticket'),
                    'reviewed' => __('بررسی شده', 'simple-ticket'),
                    'responded' => __('پاسخ داده شده', 'simple-ticket'),
                    'closed' => __('بسته شده', 'simple-ticket'),
                );
                $subject = __('به‌روزرسانی وضعیت درخواست', 'simple-ticket');
                $message = sprintf(__('وضعیت درخواست شماره %s به %s تغییر یافت.', 'simple-ticket'), $ticket_number, $statuses[$new_status]);
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
}
add_action('save_post', 'sts_save_ticket_meta');

// Handle Front-end Form Submission
function sts_handle_ticket_submission() {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    
    // Check if any POST data exists
    if (empty($_POST)) {
        return;
    }
    
    // Handle ticket submission
    if (isset($_POST['ticket_nonce']) && wp_verify_nonce($_POST['ticket_nonce'], 'submit_ticket')) {
        // Debug: Log the submission
        error_log('Ticket submission started');
        
        // Load media handling functions
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $order_number = sanitize_text_field($_POST['order_number']);
        $order_date = sanitize_text_field($_POST['order_date']);
        $delivery_method = sanitize_text_field($_POST['delivery_method']);
        $issue_type = sanitize_text_field($_POST['issue_type']);
        $issue_description = sanitize_textarea_field($_POST['issue_description']);
        $response_preference = sanitize_text_field($_POST['response_preference']);

        // Debug: Log the data
        error_log('Ticket data: ' . print_r($_POST, true));

        // Generate sequential ticket number with CSR format
        $last_ticket_number = (int) get_option('sts_last_ticket_number', 0);
        $new_ticket_number = sprintf('CSR%04d', $last_ticket_number + 1);
        update_option('sts_last_ticket_number', $last_ticket_number + 1);

        $ticket_id = wp_insert_post(array(
            'post_type' => 'ticket',
            'post_title' => $new_ticket_number, // فقط شماره درخواست
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ));

        if ($ticket_id) {
            error_log('Ticket created with ID: ' . $ticket_id);
            
            update_post_meta($ticket_id, 'ticket_number', $new_ticket_number);
            update_post_meta($ticket_id, 'order_number', $order_number);
            update_post_meta($ticket_id, 'order_date', $order_date);
            update_post_meta($ticket_id, 'delivery_method', $delivery_method);
            update_post_meta($ticket_id, 'issue_type', $issue_type);
            update_post_meta($ticket_id, 'issue_description', $issue_description);
            update_post_meta($ticket_id, 'response_preference', $response_preference);
            update_post_meta($ticket_id, 'ticket_status', 'new');
            update_post_meta($ticket_id, 'user_id', get_current_user_id());
            update_post_meta($ticket_id, 'responses', array(array(
                'author' => 'user',
                'date' => current_time('Y-m-d H:i:s'),
                'message' => $issue_description
            ))); // ذخیره شرح اولیه به‌عنوان اولین پاسخ کاربر

            if (!empty($_FILES['attachment']['name'])) {
                $attachment_id = media_handle_upload('attachment', $ticket_id);
                if (!is_wp_error($attachment_id)) {
                    update_post_meta($ticket_id, 'attachment', wp_get_attachment_url($attachment_id));
                }
            }

            // Send initial email notification to user
            $user = get_userdata(get_current_user_id());
            if ($user) {
                $subject = __('درخواست شما ثبت شد', 'simple-ticket');
                $message = sprintf(__('درخواست شماره %s با موفقیت ثبت شد. وضعیت فعلی: %s', 'simple-ticket'), $new_ticket_number, __('جدید', 'simple-ticket'));
                wp_mail($user->user_email, $subject, $message);
            }

            // Send email notification to admin
            $admin_email = get_option('sts_admin_email', get_option('admin_email'));
            $admin_subject = __('درخواست جدید ثبت شد', 'simple-ticket');
            $admin_message = __('درخواست یا پاسخی از جانب درخواست کننده خدمات پس از فروش ثبت شد. به پنل مدیریتی رجوع و در اسرع وقت پاسخ دهید.', 'simple-ticket');
            wp_mail($admin_email, $admin_subject, $admin_message);

            // Debug: Log the redirect
            error_log('Ticket submitted successfully');
            
            // Check if it's an AJAX request
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                wp_send_json_success(array(
                    'message' => 'درخواست با موفقیت ثبت شد',
                    'ticket_number' => $new_ticket_number
                ));
            } else {
                // For non-AJAX requests, redirect
                wp_redirect('https://dede.ir/ok-ticket');
                exit();
            }
            return; // Ensure no code runs after this
        } else {
            error_log('Failed to create ticket');
            wp_redirect(add_query_arg('ticket_error', 'true', wp_get_referer()));
            exit();
        }
    } else {
        // Only log nonce failure if we have POST data but no valid nonce
        if (isset($_POST['ticket_nonce'])) {
            error_log('Ticket submission failed - nonce verification failed');
        }
    }

    // Handle user response submission
    if (isset($_POST['user_response_nonce']) && wp_verify_nonce($_POST['user_response_nonce'], 'submit_user_response')) {
        $ticket_id = intval($_POST['ticket_id']);
        $user_response = sanitize_textarea_field($_POST['user_response']);
        if ($ticket_id && $user_response) {
            $responses = get_post_meta($ticket_id, 'responses', true) ?: array();
            $responses[] = array(
                'author' => 'user',
                'date' => current_time('Y-m-d H:i:s'),
                'message' => $user_response
            );
            update_post_meta($ticket_id, 'responses', $responses); // ذخیره همه پاسخ‌ها
            // Notify admin
            $admin_email = get_option('sts_admin_email', get_option('admin_email'));
            $ticket_number = get_post_meta($ticket_id, 'ticket_number', true);
            $subject = __('پاسخ جدید کاربر برای درخواست', 'simple-ticket');
            $message = sprintf(__('کاربر پاسخی برای درخواست شماره %s ارسال کرده است: %s', 'simple-ticket'), $ticket_number, $user_response);
            wp_mail($admin_email, $subject, $message);
            wp_redirect(add_query_arg('response_submitted', 'true', wp_get_referer()));
            exit();
        }
    }
}
add_action('init', 'sts_handle_ticket_submission');

// Add AJAX handler for getting ticket responses
function sts_get_ticket_responses() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'submit_user_response')) {
        wp_die('Security check failed');
    }
    
    $ticket_id = intval($_POST['ticket_id']);
    $user_id = get_current_user_id();
    
    // Check if user owns this ticket
    $ticket = get_post($ticket_id);
    if (!$ticket || $ticket->post_author != $user_id) {
        wp_die('Access denied');
    }
    
    // Get responses
    $responses = get_post_meta($ticket_id, 'responses', true) ?: array();
    
    // Get user full name
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $user_full_name = trim($first_name . ' ' . $last_name);
    if (empty($user_full_name)) {
        $user = get_userdata($user_id);
        $user_full_name = $user->user_login;
    }
    
    wp_send_json_success(array(
        'responses' => $responses,
        'user_full_name' => $user_full_name
    ));
}
add_action('wp_ajax_get_ticket_responses', 'sts_get_ticket_responses');
add_action('wp_ajax_nopriv_get_ticket_responses', 'sts_get_ticket_responses');

// Add ajaxurl to frontend
function sts_add_ajax_url() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_head', 'sts_add_ajax_url');

// Enqueue Styles and Scripts
function sts_enqueue_assets() {
    wp_enqueue_style('persian-datepicker', 'https://unpkg.com/persian-datepicker@latest/dist/css/persian-datepicker.min.css', array(), '1.0');
    wp_enqueue_style('sts-styles', plugin_dir_url(__FILE__) . 'assets/css/ticket-styles.css', array(), '1.0');
    wp_enqueue_script('persian-datepicker', 'https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js', array('jquery'), '1.0', true);
    wp_enqueue_script('sts-scripts', plugin_dir_url(__FILE__) . 'assets/js/ticket-scripts.js', array('jquery', 'persian-datepicker'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'sts_enqueue_assets');

// Shortcodes
function sts_ticket_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/ticket-form.php';
    return ob_get_clean();
}
add_shortcode('ticket_form', 'sts_ticket_form_shortcode');

function sts_ticket_list_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/ticket-list.php';
    return ob_get_clean();
}
add_shortcode('ticket_list', 'sts_ticket_list_shortcode');
// Register Warranty Shortcode
function sts_warranty_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/warranty-form.php';
    return ob_get_clean();
}
add_shortcode('warranty_form', 'sts_warranty_form_shortcode');

// Handle Warranty Form Submission
function sts_handle_warranty_submission() {
    if (isset($_POST['warranty_nonce']) && wp_verify_nonce($_POST['warranty_nonce'], 'submit_warranty')) {
        $product_type = sanitize_text_field($_POST['product_type']);
        $hologram_code = sanitize_text_field($_POST['hologram_code']);
        $pro_number = sanitize_text_field($_POST['pro_number']);
        $installer_name = sanitize_text_field($_POST['installer_name']);
        $installer_phone = sanitize_text_field($_POST['installer_phone']);
        $installer_email = sanitize_email($_POST['installer_email']);
        $installation_location = sanitize_text_field($_POST['installation_location']);
        $installation_date = sanitize_text_field($_POST['installation_date']);
        $seller = sanitize_text_field($_POST['seller']);

        $required_fields = array($product_type, $hologram_code, $installer_name, $installer_phone, $installation_location, $installation_date);
        if (in_array('', $required_fields)) {
            wp_redirect(add_query_arg('warranty_error', 'true', wp_get_referer()));
            exit;
        }

        // Save warranty data as a custom post type
        $warranty_id = wp_insert_post(array(
            'post_type' => 'warranty',
            'post_title' => sprintf(__('گارانتی - کد هولوگرام: %s', 'warranty-plugin'), $hologram_code),
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

            // Send email to warranty@ajaxir.com
            $to = 'warranty@ajaxir.com';
            $subject = __('ثبت گارانتی جدید', 'warranty-plugin');
            $message = sprintf(__('جزئیات گارانتی جدید:\n\nنوع محصول: %s\nکد هولوگرام: %s\nشماره PRO: %s\nنام نصاب: %s\nشماره همراه نصاب: %s\nایمیل نصاب: %s\nمحل نصب: %s\nتاریخ نصب: %s\nفروشنده: %s', 'warranty-plugin'),
                $product_type, $hologram_code, $pro_number, $installer_name, $installer_phone, $installer_email ?: 'ندارد', $installation_location, $installation_date, $seller);
            wp_mail($to, $subject, $message);

            wp_redirect(add_query_arg('warranty_submitted', 'true', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'sts_handle_warranty_submission');

// Enqueue Warranty Scripts and Styles
function sts_enqueue_warranty_assets() {
    wp_enqueue_script('warranty-scripts', plugin_dir_url(__FILE__) . 'assets/js/warranty-scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('warranty-styles', plugin_dir_url(__FILE__) . 'assets/css/warranty-styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sts_enqueue_warranty_assets');

// Register Warranty Custom Post Type
function sts_register_warranty_post_type() {
    register_post_type('warranty', array(
        'labels' => array(
            'name' => __('گارانتی‌ها', 'warranty-plugin'),
            'singular_name' => __('گارانتی', 'warranty-plugin'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => false,
        'query_var' => false,
        'supports' => array('title'),
        'menu_icon' => 'dashicons-shield',
    ));
}
add_action('init', 'sts_register_warranty_post_type');

// Add Text Domain for Translations
function sts_warranty_load_textdomain() {
    load_textdomain('warranty-plugin', plugin_dir_path(__FILE__) . 'languages/warranty-plugin.mo');
}
add_action('plugins_loaded', 'sts_warranty_load_textdomain');

// Register Warranty Part Shortcode
function sts_warranty_part_form_shortcode() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/warranty-part-form.php';
    return ob_get_clean();
}
add_shortcode('warranty_part_form', 'sts_warranty_part_form_shortcode');

// Handle Warranty Part Form Submission
function sts_handle_warranty_part_submission() {
    if (isset($_POST['warranty_part_nonce']) && wp_verify_nonce($_POST['warranty_part_nonce'], 'submit_warranty_part')) {
        $device_type = sanitize_text_field($_POST['device_type']);
        $hologram_code = sanitize_text_field($_POST['hologram_code']);
        $operator_name = sanitize_text_field($_POST['operator_name']);
        $operator_phone = sanitize_text_field($_POST['operator_phone']);
        $operator_email = sanitize_email($_POST['operator_email']);
        $purchase_date = sanitize_text_field($_POST['purchase_date']);
        $seller = sanitize_text_field($_POST['seller']);

        $required_fields = array($device_type, $hologram_code, $operator_name, $operator_phone, $purchase_date);
        if (in_array('', $required_fields)) {
            wp_redirect(add_query_arg('warranty_part_error', 'true', wp_get_referer()));
            exit;
        }

        // Save warranty data as a custom post type (anonymous user ID as 0)
        $warranty_id = wp_insert_post(array(
            'post_type' => 'warranty_part',
            'post_title' => sprintf(__('گارانتی - کد هولوگرام: %s', 'warranty-part-plugin'), $hologram_code),
            'post_status' => 'publish',
            'post_author' => 0, // Anonymous user
        ));

        if ($warranty_id) {
            update_post_meta($warranty_id, 'device_type', $device_type);
            update_post_meta($warranty_id, 'hologram_code', $hologram_code);
            update_post_meta($warranty_id, 'operator_name', $operator_name);
            update_post_meta($warranty_id, 'operator_phone', $operator_phone);
            update_post_meta($warranty_id, 'operator_email', $operator_email);
            update_post_meta($warranty_id, 'purchase_date', $purchase_date);
            update_post_meta($warranty_id, 'seller', $seller);

            // Send email to warranty@ajaxir.com
            $to = 'warranty@ajaxir.com';
            $subject = __('ثبت گارانتی جدید - بخش گارانتی', 'warranty-part-plugin');
            $message = sprintf(__('جزئیات گارانتی جدید:\n\nنوع دستگاه: %s\nکد هولوگرام: %s\nنام بهره‌ بردار: %s\nشماره همراه بهره‌ بردار: %s\nایمیل بهره‌ بردار: %s\nتاریخ خرید: %s\nفروشنده: %s', 'warranty-part-plugin'),
                $device_type, $hologram_code, $operator_name, $operator_phone, $operator_email ?: 'ندارد', $purchase_date, $seller ?: 'ندارد');
            wp_mail($to, $subject, $message);

            wp_redirect(add_query_arg('warranty_part_submitted', 'true', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'sts_handle_warranty_part_submission');

// Enqueue Warranty Part Scripts and Styles
function sts_enqueue_warranty_part_assets() {
    wp_enqueue_script('warranty-part-scripts', plugin_dir_url(__FILE__) . 'assets/js/warranty-part-scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('warranty-part-styles', plugin_dir_url(__FILE__) . 'assets/css/warranty-part-styles.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'sts_enqueue_warranty_part_assets');

// Register Warranty Part Custom Post Type
function sts_register_warranty_part_post_type() {
    register_post_type('warranty_part', array(
        'labels' => array(
            'name' => __('گارانتی‌های بخش گارانتی', 'warranty-part-plugin'),
            'singular_name' => __('گارانتی بخش گارانتی', 'warranty-part-plugin'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => false,
        'query_var' => false,
        'supports' => array('title'),
        'menu_icon' => 'dashicons-shield-alt',
    ));
}
add_action('init', 'sts_register_warranty_part_post_type');

// Add Text Domain for Translations
function sts_warranty_part_load_textdomain() {
    load_textdomain('warranty-part-plugin', plugin_dir_path(__FILE__) . 'languages/warranty-part-plugin.mo');
}
add_action('plugins_loaded', 'sts_warranty_part_load_textdomain');