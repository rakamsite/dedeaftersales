<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ticket custom post type registration.
 */
function sts_register_ticket_post_type() {
    $labels = array(
        'name'               => __('درخواست‌ها', 'simple-ticket'),
        'singular_name'      => __('درخواست', 'simple-ticket'),
        'menu_name'          => __('درخواست‌ها', 'simple-ticket'),
        'add_new'            => __('افزودن درخواست', 'simple-ticket'),
        'add_new_item'       => __('افزودن درخواست جدید', 'simple-ticket'),
        'edit_item'          => __('ویرایش درخواست', 'simple-ticket'),
        'new_item'           => __('درخواست جدید', 'simple-ticket'),
        'view_item'          => __('نمایش درخواست', 'simple-ticket'),
        'all_items'          => __('همه درخواست‌ها', 'simple-ticket'),
        'search_items'       => __('جستجوی درخواست‌ها', 'simple-ticket'),
        'not_found'          => __('درخواستی یافت نشد', 'simple-ticket'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'ticket'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array('title'),
    );

    register_post_type('ticket', $args);
}
add_action('init', 'sts_register_ticket_post_type');

/**
 * Settings submenu for ticket configuration.
 */
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

/**
 * Ticket meta box registration.
 */
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
    $ticket_number      = get_post_meta($post->ID, 'ticket_number', true);
    $order_number       = get_post_meta($post->ID, 'order_number', true);
    $order_date         = get_post_meta($post->ID, 'order_date', true);
    $delivery_method    = get_post_meta($post->ID, 'delivery_method', true);
    $issue_type         = get_post_meta($post->ID, 'issue_type', true);
    $issue_description  = get_post_meta($post->ID, 'issue_description', true);
    $response_pref      = get_post_meta($post->ID, 'response_preference', true);
    $attachment         = get_post_meta($post->ID, 'attachment', true);
    $responses          = get_post_meta($post->ID, 'responses', true) ?: array();
    $ticket_status      = get_post_meta($post->ID, 'ticket_status', true);

    $user_id       = get_post_meta($post->ID, 'user_id', true);
    $user          = get_userdata($user_id);
    $first_name    = get_user_meta($user_id, 'first_name', true);
    $last_name     = get_user_meta($user_id, 'last_name', true);
    $user_fullname = trim($first_name . ' ' . $last_name);
    if (empty($user_fullname)) {
        $user_fullname = $user ? $user->user_login : 'کاربر';
    }
    ?>
    <p><label><?php _e('شماره درخواست:', 'simple-ticket'); ?></label><input type="text" name="ticket_number" value="<?php echo esc_attr($ticket_number); ?>" readonly></p>
    <p><label><?php _e('کاربر:', 'simple-ticket'); ?></label><input type="text" value="<?php echo esc_attr($user_fullname); ?>" readonly></p>
    <p><label><?php _e('شماره سفارش:', 'simple-ticket'); ?></label><input type="text" name="order_number" value="<?php echo esc_attr($order_number); ?>" readonly></p>
    <p><label><?php _e('تاریخ سفارش (شمسی):', 'simple-ticket'); ?></label><input type="text" name="order_date" value="<?php echo esc_attr($order_date); ?>" readonly></p>
    <p><label><?php _e('نحوه دریافت:', 'simple-ticket'); ?></label><input type="text" name="delivery_method" value="<?php echo esc_attr($delivery_method); ?>" readonly></p>
    <p><label><?php _e('نوع مشکل:', 'simple-ticket'); ?></label><input type="text" name="issue_type" value="<?php echo esc_attr($issue_type); ?>" readonly></p>
    <p><label><?php _e('شرح مشکل:', 'simple-ticket'); ?></label><textarea name="issue_description" readonly><?php echo esc_textarea($issue_description); ?></textarea></p>
    <p><label><?php _e('ترجیح پاسخگویی:', 'simple-ticket'); ?></label><input type="text" name="response_preference" value="<?php echo esc_attr($response_pref); ?>" readonly></p>
    <?php if ($attachment) : ?>
        <p><label><?php _e('فایل ضمیمه:', 'simple-ticket'); ?></label><a href="<?php echo esc_url($attachment); ?>" target="_blank"><?php _e('دانلود فایل', 'simple-ticket'); ?></a></p>
    <?php endif; ?>
    <h3><?php _e('پاسخ‌ها:', 'simple-ticket'); ?></h3>
    <?php foreach ($responses as $response) : ?>
        <p><strong><?php echo esc_html($response['author'] === 'admin' ? 'ادمین' : $user_fullname); ?> (<?php echo esc_html($response['date']); ?>):</strong><br><?php echo esc_textarea($response['message']); ?></p>
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

/**
 * Save ticket meta.
 */
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
        $responses[]    = array(
            'author' => 'admin',
            'date'   => current_time('Y-m-d H:i:s'),
            'message'=> $admin_response,
        );
        update_post_meta($post_id, 'responses', $responses);

        $ticket_status = get_post_meta($post_id, 'ticket_status', true);
        if ($admin_response && $ticket_status !== 'closed') {
            update_post_meta($post_id, 'ticket_status', 'responded');
            $user_id = get_post_meta($post_id, 'user_id', true);
            $user    = get_userdata($user_id);
            if ($user) {
                $ticket_number = get_post_meta($post_id, 'ticket_number', true);
                $subject       = __('به‌روزرسانی وضعیت درخواست', 'simple-ticket');
                $message       = sprintf(__('وضعیت درخواست شماره %s به %s تغییر یافت. پاسخ: %s', 'simple-ticket'), $ticket_number, __('پاسخ داده شده', 'simple-ticket'), $admin_response);
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }

    if (isset($_POST['ticket_status'])) {
        $new_status = sanitize_text_field($_POST['ticket_status']);
        $old_status = get_post_meta($post_id, 'ticket_status', true);
        if ($new_status !== $old_status) {
            update_post_meta($post_id, 'ticket_status', $new_status);
            $user_id = get_post_meta($post_id, 'user_id', true);
            $user    = get_userdata($user_id);
            if ($user) {
                $ticket_number = get_post_meta($post_id, 'ticket_number', true);
                $statuses      = array(
                    'new'       => __('جدید', 'simple-ticket'),
                    'reviewed'  => __('بررسی شده', 'simple-ticket'),
                    'responded' => __('پاسخ داده شده', 'simple-ticket'),
                    'closed'    => __('بسته شده', 'simple-ticket'),
                );
                $subject = __('به‌روزرسانی وضعیت درخواست', 'simple-ticket');
                $message = sprintf(__('وضعیت درخواست شماره %s به %s تغییر یافت.', 'simple-ticket'), $ticket_number, $statuses[$new_status]);
                wp_mail($user->user_email, $subject, $message);
            }
        }
    }
}
add_action('save_post', 'sts_save_ticket_meta');

/**
 * Handle front-end ticket submissions.
 */
function sts_handle_ticket_submission() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
        return;
    }

    if (isset($_POST['ticket_nonce']) && wp_verify_nonce($_POST['ticket_nonce'], 'submit_ticket')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $order_number        = sanitize_text_field($_POST['order_number']);
        $order_date          = sanitize_text_field($_POST['order_date']);
        $delivery_method     = sanitize_text_field($_POST['delivery_method']);
        $issue_type          = sanitize_text_field($_POST['issue_type']);
        $issue_description   = sanitize_textarea_field($_POST['issue_description']);
        $response_preference = sanitize_text_field($_POST['response_preference']);

        $last_ticket_number = (int) get_option('sts_last_ticket_number', 0);
        $new_ticket_number  = sprintf('CSR%04d', $last_ticket_number + 1);
        update_option('sts_last_ticket_number', $last_ticket_number + 1);

        $ticket_id = wp_insert_post(array(
            'post_type'   => 'ticket',
            'post_title'  => $new_ticket_number,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ));

        if ($ticket_id) {
            update_post_meta($ticket_id, 'ticket_number', $new_ticket_number);
            update_post_meta($ticket_id, 'order_number', $order_number);
            update_post_meta($ticket_id, 'order_date', $order_date);
            update_post_meta($ticket_id, 'delivery_method', $delivery_method);
            update_post_meta($ticket_id, 'issue_type', $issue_type);
            update_post_meta($ticket_id, 'issue_description', $issue_description);
            update_post_meta($ticket_id, 'response_preference', $response_preference);
            update_post_meta($ticket_id, 'ticket_status', 'new');
            update_post_meta($ticket_id, 'user_id', get_current_user_id());
            update_post_meta(
                $ticket_id,
                'responses',
                array(
                    array(
                        'author'  => 'user',
                        'date'    => current_time('Y-m-d H:i:s'),
                        'message' => $issue_description,
                    ),
                )
            );

            if (!empty($_FILES['attachment']['name'])) {
                $attachment_id = media_handle_upload('attachment', $ticket_id);
                if (!is_wp_error($attachment_id)) {
                    update_post_meta($ticket_id, 'attachment', wp_get_attachment_url($attachment_id));
                }
            }

            $user = get_userdata(get_current_user_id());
            if ($user) {
                $subject = __('درخواست شما ثبت شد', 'simple-ticket');
                $message = sprintf(__('درخواست شماره %s با موفقیت ثبت شد. وضعیت فعلی: %s', 'simple-ticket'), $new_ticket_number, __('جدید', 'simple-ticket'));
                wp_mail($user->user_email, $subject, $message);
            }

            $admin_email    = get_option('sts_admin_email', get_option('admin_email'));
            $admin_subject  = __('درخواست جدید ثبت شد', 'simple-ticket');
            $admin_message  = __('درخواست یا پاسخی از جانب درخواست کننده خدمات پس از فروش ثبت شد. به پنل مدیریتی رجوع و در اسرع وقت پاسخ دهید.', 'simple-ticket');
            wp_mail($admin_email, $admin_subject, $admin_message);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                wp_send_json_success(
                    array(
                        'message'       => 'درخواست با موفقیت ثبت شد',
                        'ticket_number' => $new_ticket_number,
                    )
                );
            } else {
                wp_redirect('https://dede.ir/ok-ticket');
                exit();
            }
            return;
        }

        wp_redirect(add_query_arg('ticket_error', 'true', wp_get_referer()));
        exit();
    }

    if (isset($_POST['user_response_nonce']) && wp_verify_nonce($_POST['user_response_nonce'], 'submit_user_response')) {
        $ticket_id     = intval($_POST['ticket_id']);
        $user_response = sanitize_textarea_field($_POST['user_response']);
        if ($ticket_id && $user_response) {
            $responses   = get_post_meta($ticket_id, 'responses', true) ?: array();
            $responses[] = array(
                'author'  => 'user',
                'date'    => current_time('Y-m-d H:i:s'),
                'message' => $user_response,
            );
            update_post_meta($ticket_id, 'responses', $responses);

            $admin_email   = get_option('sts_admin_email', get_option('admin_email'));
            $ticket_number = get_post_meta($ticket_id, 'ticket_number', true);
            $subject       = __('پاسخ جید کاربر برای درخواست', 'simple-ticket');
            $message       = sprintf(__('کاربر پاسخی برای درخواست شماره %s ارسال کرده است: %s', 'simple-ticket'), $ticket_number, $user_response);
            wp_mail($admin_email, $subject, $message);

            wp_redirect(add_query_arg('response_submitted', 'true', wp_get_referer()));
            exit();
        }
    }
}
add_action('init', 'sts_handle_ticket_submission');

/**
 * AJAX handler for fetching ticket responses.
 */
function sts_get_ticket_responses() {
    if (!wp_verify_nonce($_POST['nonce'], 'submit_user_response')) {
        wp_die('Security check failed');
    }

    $ticket_id = intval($_POST['ticket_id']);
    $user_id   = get_current_user_id();

    $ticket = get_post($ticket_id);
    if (!$ticket || $ticket->post_author != $user_id) {
        wp_die('Access denied');
    }

    $responses = get_post_meta($ticket_id, 'responses', true) ?: array();
    $first     = get_user_meta($user_id, 'first_name', true);
    $last      = get_user_meta($user_id, 'last_name', true);
    $fullname  = trim($first . ' ' . $last);
    if (empty($fullname)) {
        $user     = get_userdata($user_id);
        $fullname = $user->user_login;
    }

    wp_send_json_success(
        array(
            'responses'      => $responses,
            'user_full_name' => $fullname,
        )
    );
}
add_action('wp_ajax_get_ticket_responses', 'sts_get_ticket_responses');
add_action('wp_ajax_nopriv_get_ticket_responses', 'sts_get_ticket_responses');

function sts_add_ajax_url() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
    </script>
    <?php
}
add_action('wp_head', 'sts_add_ajax_url');

/**
 * Enqueue ticket assets.
 */
function sts_enqueue_assets() {
    wp_enqueue_style('persian-datepicker', 'https://unpkg.com/persian-datepicker@latest/dist/css/persian-datepicker.min.css', array(), '1.0');
    wp_enqueue_style('sts-styles', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/css/ticket-styles.css', array(), '1.0');
    wp_enqueue_script('persian-datepicker', 'https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js', array('jquery'), '1.0', true);
    wp_enqueue_script('sts-scripts', plugin_dir_url(STS_PLUGIN_FILE) . 'assets/js/ticket-scripts.js', array('jquery', 'persian-datepicker'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'sts_enqueue_assets');

/**
 * Ticket form shortcode.
 */
function sts_ticket_form_shortcode() {
    ob_start();
    include STS_PLUGIN_DIR . 'templates/ticket-form.php';
    return ob_get_clean();
}
add_shortcode('ticket_form', 'sts_ticket_form_shortcode');

function sts_ticket_list_shortcode() {
    ob_start();
    include STS_PLUGIN_DIR . 'templates/ticket-list.php';
    return ob_get_clean();
}
add_shortcode('ticket_list', 'sts_ticket_list_shortcode');
