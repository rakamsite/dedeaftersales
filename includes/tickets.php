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
        'capability_type'    => array('request', 'requests'),
        'map_meta_cap'       => true,
        'capabilities'       => array(
            'edit_post'          => 'edit_request',
            'read_post'          => 'read_request',
            'delete_post'        => 'delete_request',
            'edit_posts'         => 'edit_requests',
            'edit_others_posts'  => 'edit_others_requests',
            'publish_posts'      => 'publish_requests',
            'read_private_posts' => 'read_private_requests',
            'create_posts'       => 'create_requests',
        ),
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

/**
 * Customize submit button text for ticket post type.
 */
function sts_customize_ticket_submit_button($translation, $text, $domain) {
    if (!is_admin() || !function_exists('get_current_screen')) {
        return $translation;
    }

    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'ticket' && ($text === 'Update' || $translation === 'بروزرسانی' || $translation === 'به‌روزرسانی')) {
        return __('ارسال پاسخ', 'simple-ticket');
    }

    return $translation;
}
add_filter('gettext', 'sts_customize_ticket_submit_button', 10, 3);

/**
 * Enqueue admin assets for ticket post type.
 */
function sts_enqueue_admin_ticket_assets($hook) {
    $screen = get_current_screen();

    if ($screen && $screen->post_type === 'ticket') {
        wp_enqueue_style(
            'sts-admin-ticket',
            plugin_dir_url(STS_PLUGIN_FILE) . 'assets/css/admin-ticket.css',
            array(),
            '1.0'
        );
    }
}
add_action('admin_enqueue_scripts', 'sts_enqueue_admin_ticket_assets');

function sts_render_ticket_meta_box($post) {
    wp_nonce_field('sts_save_ticket_meta', 'ticket_meta_nonce');
    $ticket_number      = get_post_meta($post->ID, 'ticket_number', true);
    $order_number       = get_post_meta($post->ID, 'order_number', true);
    $order_date         = get_post_meta($post->ID, 'order_date', true);
    $issue_items        = get_post_meta($post->ID, 'issue_items', true) ?: array();
    if (empty($issue_items)) {
        $legacy_issue_type        = get_post_meta($post->ID, 'issue_type', true);
        $legacy_issue_description = get_post_meta($post->ID, 'issue_description', true);
        $legacy_attachment        = get_post_meta($post->ID, 'attachment', true);

        if ($legacy_issue_type || $legacy_issue_description || $legacy_attachment) {
            $issue_items = array(
                array(
                    'product_name'      => '',
                    'quantity'          => '',
                    'issue_type'        => $legacy_issue_type,
                    'issue_description' => $legacy_issue_description,
                    'attachment'        => $legacy_attachment,
                ),
            );
        }
    }
    $issue_description  = get_post_meta($post->ID, 'issue_description', true);
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
    <div class="sts-ticket-meta">
        <div class="sts-ticket-info-grid">
            <div class="info-item">
                <span class="label"><?php _e('شماره درخواست', 'simple-ticket'); ?></span>
                <span class="value"><?php echo esc_html($ticket_number); ?></span>
            </div>
            <div class="info-item">
                <span class="label"><?php _e('ثبت‌کننده', 'simple-ticket'); ?></span>
                <span class="value"><?php echo esc_html($user_fullname); ?></span>
            </div>
            <div class="info-item">
                <span class="label"><?php _e('شماره سفارش یا فاکتور', 'simple-ticket'); ?></span>
                <span class="value"><?php echo esc_html($order_number); ?></span>
            </div>
            <div class="info-item">
                <span class="label"><?php _e('تاریخ دریافت سفارش', 'simple-ticket'); ?></span>
                <span class="value"><?php echo esc_html($order_date); ?></span>
            </div>
        </div>

        <div class="sts-ticket-summary">
            <p>
                <?php
                printf(
                    /* translators: 1: ticket number, 2: user full name, 3: order number, 4: order date, 5: issues count */
                    __('این درخواست به شماره %1$s توسط %2$s برای شماره سفارش %3$s که در تاریخ %4$s دریافت شده ثبت شده است. این درخواست شامل %5$s مورد مشکل ثبت‌شده است.', 'simple-ticket'),
                    esc_html($ticket_number),
                    esc_html($user_fullname),
                    esc_html($order_number),
                    esc_html($order_date),
                    esc_html(count($issue_items))
                );
                ?>
            </p>
        </div>

        <?php if (!empty($issue_items)) : ?>
            <table class="widefat striped" style="margin-top:10px">
                <thead>
                    <tr>
                        <th><?php _e('نام محصول', 'simple-ticket'); ?></th>
                        <th><?php _e('تعداد', 'simple-ticket'); ?></th>
                        <th><?php _e('نوع مشکل', 'simple-ticket'); ?></th>
                        <th><?php _e('شرح مشکل', 'simple-ticket'); ?></th>
                        <th><?php _e('ضمیمه', 'simple-ticket'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issue_items as $item) : ?>
                        <tr>
                            <td><?php echo esc_html($item['product_name'] ?? ''); ?></td>
                            <td><?php echo esc_html($item['quantity'] ?? ''); ?></td>
                            <td><?php echo esc_html($item['issue_type'] ?? ''); ?></td>
                            <td><?php echo esc_html($item['issue_description'] ?? ''); ?></td>
                            <td>
                                <?php if (!empty($item['attachment'])) : ?>
                                    <a href="<?php echo esc_url($item['attachment']); ?>" target="_blank"><?php _e('دانلود', 'simple-ticket'); ?></a>
                                <?php else : ?>
                                    <?php _e('بدون فایل', 'simple-ticket'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="sts-responses">
            <?php foreach ($responses as $response) : ?>
                <p><strong><?php echo esc_html($response['author'] === 'admin' ? 'ادمین' : $user_fullname); ?> (<?php echo esc_html($response['date']); ?>):</strong><br><?php echo esc_textarea($response['message']); ?></p>
            <?php endforeach; ?>
        </div>

        <p><label><?php _e('پاسخ ادمین:', 'simple-ticket'); ?></label><textarea name="admin_response"></textarea></p>
        <p><label><?php _e('وضعیت درخواست:', 'simple-ticket'); ?></label>
            <select name="ticket_status">
                <option value="new" <?php selected($ticket_status, 'new'); ?>><?php _e('جدید', 'simple-ticket'); ?></option>
                <option value="reviewed" <?php selected($ticket_status, 'reviewed'); ?>><?php _e('بررسی شده', 'simple-ticket'); ?></option>
                <option value="responded" <?php selected($ticket_status, 'responded'); ?>><?php _e('پاسخ داده شده', 'simple-ticket'); ?></option>
                <option value="closed" <?php selected($ticket_status, 'closed'); ?>><?php _e('بسته شده', 'simple-ticket'); ?></option>
            </select>
        </p>
    </div>
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
                $message = sts_build_ticket_notification_message($post_id, array(
                    'type'           => 'admin_response',
                    'status'         => __('پاسخ داده شده', 'simple-ticket'),
                    'admin_response' => $admin_response,
                ));
                sts_send_ticket_notification($post_id, $message);
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
                $statuses = array(
                    'new'       => __('جدید', 'simple-ticket'),
                    'reviewed'  => __('بررسی شده', 'simple-ticket'),
                    'responded' => __('پاسخ داده شده', 'simple-ticket'),
                    'closed'    => __('بسته شده', 'simple-ticket'),
                );
                $message = sts_build_ticket_notification_message($post_id, array(
                    'type'   => 'status_change',
                    'status' => $statuses[$new_status],
                ));
                sts_send_ticket_notification($post_id, $message);
            }
        }
    }
}
add_action('save_post', 'sts_save_ticket_meta');

/**
 * Send a unified ticket notification to admin and user.
 *
 * @param int    $ticket_id Ticket ID.
 * @param string $message   Message body.
 * @return void
 */
function sts_send_ticket_notification($ticket_id, $message) {
    $ticket_number = get_post_meta($ticket_id, 'ticket_number', true);
    $subject       = $ticket_number;

    $admin_email = get_option('sts_admin_email', get_option('admin_email'));
    $user_id     = get_post_meta($ticket_id, 'user_id', true);
    $user        = get_userdata($user_id);

    $recipients = array_filter(array(
        $admin_email,
        $user ? $user->user_email : '',
    ));
    $recipients = array_unique($recipients);

    $headers = array('Content-Type: text/html; charset=UTF-8');

    foreach ($recipients as $recipient) {
        wp_mail($recipient, $subject, $message, $headers);
    }
}

/**
 * Build a unified ticket notification email message.
 *
 * @param int   $ticket_id Ticket ID.
 * @param array $context   Context details.
 * @return string
 */
function sts_build_ticket_notification_message($ticket_id, $context = array()) {
    $ticket_number = get_post_meta($ticket_id, 'ticket_number', true);
    $order_number  = get_post_meta($ticket_id, 'order_number', true);
    $order_date    = get_post_meta($ticket_id, 'order_date', true);
    $issue_items   = get_post_meta($ticket_id, 'issue_items', true) ?: array();
    $responses     = get_post_meta($ticket_id, 'responses', true) ?: array();
    $ticket_status = get_post_meta($ticket_id, 'ticket_status', true);

    $user_id    = get_post_meta($ticket_id, 'user_id', true);
    $user       = get_userdata($user_id);
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name  = get_user_meta($user_id, 'last_name', true);
    $full_name  = trim($first_name . ' ' . $last_name);
    if (empty($full_name)) {
        $full_name = $user ? $user->user_login : __('کاربر', 'simple-ticket');
    }

    $statuses = array(
        'new'       => __('جدید', 'simple-ticket'),
        'reviewed'  => __('بررسی شده', 'simple-ticket'),
        'responded' => __('پاسخ داده شده', 'simple-ticket'),
        'closed'    => __('بسته شده', 'simple-ticket'),
    );
    $status_label = $statuses[$ticket_status] ?? __('نامشخص', 'simple-ticket');

    $card_style        = 'background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;box-shadow:0 2px 8px rgba(15,23,42,0.06);';
    $section_title     = 'font-size:16px;font-weight:700;color:#0f172a;margin:0 0 12px;';
    $label_style       = 'font-size:13px;color:#6b7280;margin-bottom:4px;';
    $value_style       = 'font-size:15px;color:#111827;font-weight:600;margin:0;';
    $badge_style       = 'display:inline-block;background:#e0f2fe;color:#075985;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600;';
    $item_box_style    = 'background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-bottom:10px;';
    $response_box      = 'border-right:3px solid #38bdf8;background:#f0f9ff;padding:12px;border-radius:10px;margin-bottom:10px;';
    $response_meta     = 'font-size:12px;color:#475569;margin-bottom:6px;';
    $response_message  = 'font-size:14px;color:#0f172a;margin:0;white-space:pre-wrap;';

    $status_text = $context['status'] ?? $status_label;
    $issue_rows  = '';
    foreach ($issue_items as $item) {
        $product_name = esc_html($item['product_name'] ?? '');
        $quantity     = esc_html($item['quantity'] ?? '');
        $issue_type   = esc_html($item['issue_type'] ?? '');
        $description  = esc_html($item['issue_description'] ?? '');
        $attachment   = !empty($item['attachment']) ? esc_url($item['attachment']) : '';
        $attachment_link = $attachment ? sprintf('<a href="%s" style="color:#2563eb;text-decoration:none;">%s</a>', $attachment, esc_html__('دانلود ضمیمه', 'simple-ticket')) : esc_html__('بدون فایل', 'simple-ticket');

        $issue_rows .= sprintf(
            '<div style="%1$s">
                <p style="margin:0 0 6px;font-size:15px;font-weight:600;color:#0f172a;">%2$s</p>
                <p style="margin:0;font-size:13px;color:#64748b;">%3$s: %4$s | %5$s: %6$s</p>
                <p style="margin:8px 0 0;font-size:13px;color:#475569;">%7$s</p>
                <p style="margin:8px 0 0;font-size:13px;">%8$s</p>
            </div>',
            $item_box_style,
            $product_name,
            esc_html__('تعداد', 'simple-ticket'),
            $quantity,
            esc_html__('نوع مشکل', 'simple-ticket'),
            $issue_type,
            $description,
            $attachment_link
        );
    }

    if ($issue_rows === '') {
        $issue_rows = sprintf('<p style="margin:0;font-size:14px;color:#64748b;">%s</p>', esc_html__('موردی ثبت نشده است.', 'simple-ticket'));
    }

    $responses_html = '';
    if (!empty($responses)) {
        foreach ($responses as $response) {
            $author_label = $response['author'] === 'admin' ? __('ادمین', 'simple-ticket') : $full_name;
            $date_label   = $response['date'] ?? '';
            $message_text = $response['message'] ?? '';

            $responses_html .= sprintf(
                '<div style="%1$s">
                    <div style="%2$s">%3$s | %4$s</div>
                    <p style="%5$s">%6$s</p>
                </div>',
                $response_box,
                $response_meta,
                esc_html($author_label),
                esc_html($date_label),
                $response_message,
                esc_html($message_text)
            );
        }
    } else {
        $responses_html = sprintf('<p style="margin:0;font-size:14px;color:#64748b;">%s</p>', esc_html__('هنوز پاسخی ثبت نشده است.', 'simple-ticket'));
    }

    $highlight_block = '';
    if (!empty($context['admin_response'])) {
        $highlight_block .= sprintf(
            '<div style="%1$s">
                <div style="%2$s">%3$s</div>
                <p style="%4$s">%5$s</p>
            </div>',
            $response_box,
            $response_meta,
            esc_html__('آخرین پاسخ ادمین', 'simple-ticket'),
            $response_message,
            esc_html($context['admin_response'])
        );
    }
    if (!empty($context['user_response'])) {
        $highlight_block .= sprintf(
            '<div style="%1$s">
                <div style="%2$s">%3$s</div>
                <p style="%4$s">%5$s</p>
            </div>',
            $response_box,
            $response_meta,
            esc_html__('آخرین پاسخ کاربر', 'simple-ticket'),
            $response_message,
            esc_html($context['user_response'])
        );
    }

    $html = sprintf(
        '<div dir="rtl" style="background:#f8fafc;padding:24px;font-family:%1$s;">
            <div style="max-width:720px;margin:0 auto;%2$s">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
                    <div>
                        <p style="margin:0;font-size:20px;font-weight:800;color:#0f172a;">%3$s</p>
                        <p style="margin:6px 0 0;font-size:13px;color:#64748b;">%4$s</p>
                    </div>
                    <span style="%5$s">%6$s</span>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
                    <div style="flex:1 1 220px;background:#f1f5f9;border-radius:10px;padding:12px;">
                        <p style="%7$s">%8$s</p>
                        <p style="%9$s">%10$s</p>
                    </div>
                    <div style="flex:1 1 220px;background:#f1f5f9;border-radius:10px;padding:12px;">
                        <p style="%7$s">%11$s</p>
                        <p style="%9$s">%12$s</p>
                    </div>
                    <div style="flex:1 1 220px;background:#f1f5f9;border-radius:10px;padding:12px;">
                        <p style="%7$s">%13$s</p>
                        <p style="%9$s">%14$s</p>
                    </div>
                </div>

                %15$s

                <div style="margin-top:20px;">
                    <p style="%16$s">%17$s</p>
                    %18$s
                </div>

                <div style="margin-top:20px;">
                    <p style="%16$s">%19$s</p>
                    %20$s
                </div>

                <div style="margin-top:20px;text-align:center;font-size:12px;color:#94a3b8;">
                    %21$s
                </div>
            </div>
        </div>',
        "'Vazirmatn','IRANSans','Segoe UI',Tahoma,sans-serif",
        $card_style,
        esc_html(sprintf(__('گزارش مکاتبات درخواست %s', 'simple-ticket'), $ticket_number)),
        esc_html(__('این ایمیل شامل تمام مکاتبات و وضعیت فعلی درخواست شماست.', 'simple-ticket')),
        $badge_style,
        esc_html($status_text),
        $label_style,
        esc_html__('ثبت‌کننده', 'simple-ticket'),
        $value_style,
        esc_html($full_name),
        $label_style,
        esc_html__('شماره سفارش/فاکتور', 'simple-ticket'),
        $value_style,
        esc_html($order_number),
        $highlight_block,
        $section_title,
        esc_html__('جزئیات مشکلات ثبت‌شده', 'simple-ticket'),
        $issue_rows,
        $section_title,
        esc_html__('تاریخچه مکاتبات', 'simple-ticket'),
        $responses_html,
        esc_html__('لطفاً برای ادامه گفتگو همین ایمیل را پاسخ دهید تا همه موارد در یک رشته واحد باقی بماند.', 'simple-ticket')
    );

    return $html;
}

/**
 * Store ticket attachments outside of the WordPress media library.
 *
 * @param array $dirs Upload directory data.
 * @return array
 */
function sts_ticket_upload_dir($dirs) {
    $ticket_id = $GLOBALS['sts_current_ticket_id'] ?? 0;
    $ticket_id = intval($ticket_id);
    $subdir    = $ticket_id ? '/ticket-attachments/' . $ticket_id : '/ticket-attachments';

    $dirs['subdir'] = $subdir;
    $dirs['path']   = $dirs['basedir'] . $subdir;
    $dirs['url']    = $dirs['baseurl'] . $subdir;

    return $dirs;
}

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

        $order_number        = isset($_POST['order_number']) ? preg_replace('/\D+/', '', (string) $_POST['order_number']) : '';
        $order_date          = sanitize_text_field($_POST['order_date']);

        $product_names       = isset($_POST['product_name']) && is_array($_POST['product_name']) ? array_map('sanitize_text_field', $_POST['product_name']) : array();
        $quantities          = isset($_POST['quantity']) && is_array($_POST['quantity']) ? array_map('intval', $_POST['quantity']) : array();
        $issue_types         = isset($_POST['issue_type']) && is_array($_POST['issue_type']) ? array_map('sanitize_text_field', $_POST['issue_type']) : array();
        $issue_descriptions  = isset($_POST['issue_description']) && is_array($_POST['issue_description']) ? array_map('sanitize_textarea_field', $_POST['issue_description']) : array();

        $allowed_file_types  = array('image/jpeg', 'image/png', 'image/gif');
        $attachments         = $_FILES['attachment'] ?? array();
        $issue_items         = array();
        $collected_files     = array();
        $validation_error    = '';

        foreach ($product_names as $index => $product_name) {
            $quantity          = $quantities[$index] ?? '';
            $issue_type        = $issue_types[$index] ?? '';
            $issue_description = $issue_descriptions[$index] ?? '';

            if ($product_name === '' && $quantity === '' && $issue_type === '' && $issue_description === '') {
                continue;
            }

            if ($product_name === '' || $quantity === '' || $issue_type === '' || $issue_description === '') {
                $validation_error = __('لطفاً تمام فیلدهای هر ردیف مشکل را تکمیل کنید.', 'simple-ticket');
                break;
            }

            if ((int) $quantity <= 0) {
                $validation_error = __('تعداد کالا باید بزرگ‌تر از صفر باشد.', 'simple-ticket');
                break;
            }

            $issue_items[$index] = array(
                'product_name'      => $product_name,
                'quantity'          => $quantity,
                'issue_type'        => $issue_type,
                'issue_description' => $issue_description,
                'attachment'        => '',
            );

            if (!empty($attachments['name'][$index])) {
                $file = array(
                    'name'     => $attachments['name'][$index],
                    'type'     => $attachments['type'][$index],
                    'tmp_name' => $attachments['tmp_name'][$index],
                    'error'    => $attachments['error'][$index],
                    'size'     => $attachments['size'][$index],
                );

                if (!empty($file['error'])) {
                    $validation_error = __('بارگذاری فایل با خطا مواجه شد.', 'simple-ticket');
                    break;
                }

                if ($file['size'] > 10 * 1024 * 1024) {
                    $validation_error = __('حجم فایل ضمیمه نباید بیش از ۱۰ مگابایت باشد.', 'simple-ticket');
                    break;
                }

                $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
                if (empty($filetype['type']) || !in_array($filetype['type'], $allowed_file_types, true)) {
                    $validation_error = __('تنها امکان بارگذاری فرمت‌های رایج تصویر وجود دارد.', 'simple-ticket');
                    break;
                }

                $collected_files[$index] = $file;
            }
        }

        if ($order_number === '') {
            $validation_error = __('شماره سفارش یا فاکتور باید فقط شامل عدد باشد.', 'simple-ticket');
        }

        if ($validation_error || empty($issue_items)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                wp_send_json_error(array('message' => $validation_error ?: __('لطفاً حداقل یک مشکل را وارد کنید.', 'simple-ticket')));
            }

            wp_redirect(add_query_arg('ticket_error', 'true', wp_get_referer()));
            exit();
        }

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
            update_post_meta($ticket_id, 'ticket_status', 'new');
            update_post_meta($ticket_id, 'user_id', get_current_user_id());
            update_post_meta($ticket_id, 'issue_items', $issue_items);

            $uploaded_attachments = array();
            foreach ($issue_items as $index => &$item) {
                if (isset($collected_files[$index])) {
                    $file           = $collected_files[$index];
                    $tmp_name       = $file['tmp_name'] ?? '';
                    if ($tmp_name && isset($uploaded_attachments[$tmp_name])) {
                        $item['attachment'] = $uploaded_attachments[$tmp_name];
                        continue;
                    }

                    $GLOBALS['sts_current_ticket_id'] = $ticket_id;
                    add_filter('upload_dir', 'sts_ticket_upload_dir');

                    $uploaded = wp_handle_upload($file, array('test_form' => false));

                    remove_filter('upload_dir', 'sts_ticket_upload_dir');
                    unset($GLOBALS['sts_current_ticket_id']);

                    if (!empty($uploaded['url'])) {
                        $item['attachment'] = $uploaded['url'];
                        if ($tmp_name) {
                            $uploaded_attachments[$tmp_name] = $uploaded['url'];
                        }
                    }
                }
            }
            unset($item);

            update_post_meta($ticket_id, 'issue_items', $issue_items);

            $compiled_description = array();
            foreach ($issue_items as $item) {
                $compiled_description[] = sprintf(
                    __('%1$s (تعداد: %2$s) - %3$s: %4$s', 'simple-ticket'),
                    $item['product_name'],
                    $item['quantity'],
                    $item['issue_type'],
                    $item['issue_description']
                );
            }
            $compiled_description_text = implode("\n", $compiled_description);
            update_post_meta($ticket_id, 'issue_description', $compiled_description_text);
            $user = get_userdata(get_current_user_id());
            if ($user) {
                $message = sts_build_ticket_notification_message($ticket_id, array(
                    'type'   => 'new_ticket',
                    'status' => __('جدید', 'simple-ticket'),
                ));
                sts_send_ticket_notification($ticket_id, $message);
            }

            $admin_message = sts_build_ticket_notification_message($ticket_id, array(
                'type'   => 'new_ticket',
                'status' => __('جدید', 'simple-ticket'),
            ));
            sts_send_ticket_notification($ticket_id, $admin_message);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                wp_send_json_success(
                    array(
                        'message'       => 'درخواست با موفقیت ثبت شد',
                        'ticket_number' => $new_ticket_number,
                    )
                );
            } else {
                wp_redirect('https://dede.ir/ticket-ok/');
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
            $message       = sts_build_ticket_notification_message($ticket_id, array(
                'type'          => 'user_response',
                'user_response' => $user_response,
            ));
            sts_send_ticket_notification($ticket_id, $message);

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
            'issue_description' => get_post_meta($ticket_id, 'issue_description', true),
            'user_full_name' => $fullname,
        )
    );
}
add_action('wp_ajax_get_ticket_responses', 'sts_get_ticket_responses');
add_action('wp_ajax_nopriv_get_ticket_responses', 'sts_get_ticket_responses');

/**
 * AJAX handler for fetching ticket details.
 */
function sts_get_ticket_details() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('دسترسی غیرمجاز.', 'simple-ticket')));
    }

    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $nonce     = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!$ticket_id || !wp_verify_nonce($nonce, 'get_ticket_details')) {
        wp_send_json_error(array('message' => __('درخواست نامعتبر است.', 'simple-ticket')));
    }

    $ticket = get_post($ticket_id);
    if (!$ticket || $ticket->post_type !== 'ticket') {
        wp_send_json_error(array('message' => __('درخواست یافت نشد.', 'simple-ticket')));
    }

    $current_user_id = get_current_user_id();
    if ((int) $ticket->post_author !== $current_user_id) {
        wp_send_json_error(array('message' => __('دسترسی غیرمجاز.', 'simple-ticket')));
    }

    $issue_items = get_post_meta($ticket_id, 'issue_items', true) ?: array();
    if (empty($issue_items)) {
        $legacy_issue_type        = get_post_meta($ticket_id, 'issue_type', true);
        $legacy_issue_description = get_post_meta($ticket_id, 'issue_description', true);
        $legacy_attachment        = get_post_meta($ticket_id, 'attachment', true);

        if ($legacy_issue_type || $legacy_issue_description || $legacy_attachment) {
            $issue_items = array(
                array(
                    'product_name'      => '',
                    'quantity'          => '',
                    'issue_type'        => $legacy_issue_type,
                    'issue_description' => $legacy_issue_description,
                    'attachment'        => $legacy_attachment,
                ),
            );
        }
    }

    $statuses = array(
        'new'       => __('جدید', 'simple-ticket'),
        'reviewed'  => __('بررسی شده', 'simple-ticket'),
        'responded' => __('پاسخ داده شده', 'simple-ticket'),
        'closed'    => __('بسته شده', 'simple-ticket'),
    );

    $user_id = $ticket->post_author;
    $user    = get_userdata($user_id);
    $first   = get_user_meta($user_id, 'first_name', true);
    $last    = get_user_meta($user_id, 'last_name', true);
    $full    = trim($first . ' ' . $last);
    if (empty($full) && $user) {
        $full = $user->user_login;
    }

    $details = array(
        'ticket_number'     => get_post_meta($ticket_id, 'ticket_number', true),
        'order_number'      => get_post_meta($ticket_id, 'order_number', true),
        'order_date'        => get_post_meta($ticket_id, 'order_date', true),
        'issue_description' => get_post_meta($ticket_id, 'issue_description', true),
        'issue_items'       => $issue_items,
        'responses'         => get_post_meta($ticket_id, 'responses', true) ?: array(),
        'status'            => $statuses[get_post_meta($ticket_id, 'ticket_status', true)] ?? __('نامشخص', 'simple-ticket'),
        'user_full_name'    => $full,
    );

    wp_send_json_success($details);
}
add_action('wp_ajax_get_ticket_details', 'sts_get_ticket_details');
add_action('wp_ajax_nopriv_get_ticket_details', 'sts_get_ticket_details');

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
