<?php
if (!is_user_logged_in()) {
    echo <<<HTML
<div class="w-full flex flex-col justify-center items-center mt-20">
    <div class="bg-gray-200 text-gray-800 text-center p-4 rounded-lg mb-4" style="font-size: 18px;">
        لطفا برای استفاده از سیستم خدمات پس از فروش در سایت ثبت نام کنید یا وارد حساب کاربری خود شوید.
    </div>
    <button data-drawer-hide="mobile-menu" class="bg-[#2F2483] w-fit rounded-lg h-fit text-white flex py-3 px-5 mt-1/2 flex justify-between login_register_page">
        <svg width="21" height="26" viewBox="0 0 21 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.3334 10.3333C13.1868 10.3333 15.5 8.02014 15.5 5.16667C15.5 2.3132 13.1868 0 10.3334 0C7.47988 0 5.16669 2.3132 5.16669 5.16667C5.16669 8.02014 7.47988 10.3333 10.3334 10.3333Z" fill="white"></path>
            <path d="M20.6667 20.0208C20.6667 23.2306 20.6667 25.8333 10.3333 25.8333C0 25.8333 0 23.2306 0 20.0208C0 16.8111 4.62675 14.2083 10.3333 14.2083C16.0399 14.2083 20.6667 16.8111 20.6667 20.0208Z" fill="white"></path>
        </svg>
        <a class="mr-2 text-white" style="color: #fff !important;">ورود به حساب کاربری </a>
    </button>
</div>
HTML;

    return;
}

$user_id = get_current_user_id();
$user = get_userdata($user_id);
$first_name = get_user_meta($user_id, 'first_name', true);
$last_name = get_user_meta($user_id, 'last_name', true);
$user_full_name = trim($first_name . ' ' . $last_name);
if (empty($user_full_name)) {
    $user_full_name = $user->user_login;
}
$args = array(
    'post_type' => 'ticket',
    'author' => $user_id,
    'posts_per_page' => -1,
);
$tickets = new WP_Query($args);
?>

<div class="container mx-auto p-4 max-w-4xl dir-rtl">
    <?php if ($tickets->have_posts()): ?>
        <table class="w-full border-collapse bg-white shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border p-3 text-center"><?php _e('درخواست', 'simple-ticket'); ?></th>
                    <th class="border p-3 text-center"><?php _e('تاریخ ثبت', 'simple-ticket'); ?></th>
                    <th class="border p-3 text-center"><?php _e('وضعیت', 'simple-ticket'); ?></th>
                    <th class="border p-3 text-center"><?php _e('مشاهده', 'simple-ticket'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tickets->have_posts()): $tickets->the_post(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="border p-3 text-center"><?php echo esc_html(get_post_meta(get_the_ID(), 'ticket_number', true)); ?></td>
                        <td class="border p-3 text-center"><?php echo esc_html(get_the_date('Y/m/d')); ?></td>
                        <td class="border p-3 text-center">
                            <?php
                            $status = get_post_meta(get_the_ID(), 'ticket_status', true);
                            $statuses = array(
                                'new' => __('جدید', 'simple-ticket'),
                                'reviewed' => __('بررسی شده', 'simple-ticket'),
                                'responded' => __('پاسخ داده شده', 'simple-ticket'),
                                'closed' => __('بسته شده', 'simple-ticket'),
                            );
                            echo esc_html($statuses[$status] ?? __('نامشخص', 'simple-ticket'));
                            ?>
                        </td>
                        <td class="border p-3 text-center">
                            <button class="text-blue-600 hover:underline view-ticket" data-ticket-id="<?php echo get_the_ID(); ?>" data-ticket-details='<?php
                                $issue_items = get_post_meta(get_the_ID(), 'issue_items', true) ?: array();
                                if (empty($issue_items)) {
                                    $legacy_issue_type        = get_post_meta(get_the_ID(), 'issue_type', true);
                                    $legacy_issue_description = get_post_meta(get_the_ID(), 'issue_description', true);
                                    $legacy_attachment        = get_post_meta(get_the_ID(), 'attachment', true);

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

                                $details = array(
                                    'ticket_number' => get_post_meta(get_the_ID(), 'ticket_number', true),
                                    'order_number' => get_post_meta(get_the_ID(), 'order_number', true),
                                    'order_date' => get_post_meta(get_the_ID(), 'order_date', true),
                                    'issue_description' => get_post_meta(get_the_ID(), 'issue_description', true),
                                    'issue_items' => $issue_items,
                                    'responses' => get_post_meta(get_the_ID(), 'responses', true) ?: array(), // همیشه همه پاسخ‌ها
                                    'status' => $statuses[$status] ?? __('نامشخص', 'simple-ticket'),
                                    'status_key' => $status,
                                    'user_full_name' => $user_full_name,
                                );
                                echo esc_attr(json_encode($details));
                            ?>'>
                                <?php _e('مشاهده', 'simple-ticket'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-gray-500 text-center"><?php _e('هیچ درخواستی یافت نشد.', 'simple-ticket'); ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['response_submitted'])): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-lg text-center mt-4"><?php _e('پاسخ شما با موفقیت ثبت شد.', 'simple-ticket'); ?></div>
    <?php endif; ?>
</div>

<!-- Popup for Ticket Details -->
<div id="ticket-popup" class="ticket-popup hidden">
    <div class="ticket-popup__dialog dir-rtl">
        <button id="close-popup" class="ticket-popup__close" aria-label="<?php esc_attr_e('بستن', 'simple-ticket'); ?>">×</button>
        <div class="ticket-popup__body">
            <div class="ticket-popup__header">
                <div>
                    <p class="ticket-popup__subtitle"><?php _e('مشاهده و پیگیری درخواست', 'simple-ticket'); ?> <span id="popup-ticket-number-inline">-</span></p>
                    <h2 class="ticket-popup__title"><?php _e('جزئیات درخواست', 'simple-ticket'); ?></h2>
                </div>
                <div class="ticket-popup__status">
                    <span class="ticket-popup__status-label"><?php _e('وضعیت', 'simple-ticket'); ?></span>
                    <div id="ticket-status-steps" class="ticket-popup__steps"></div>
                </div>
            </div>

            <div class="ticket-popup__chips">
                <div class="ticket-popup__chip">
                    <span><?php _e('شماره درخواست', 'simple-ticket'); ?></span>
                    <strong id="popup-ticket-number">-</strong>
                </div>
                <div class="ticket-popup__chip">
                    <span><?php _e('شماره سفارش', 'simple-ticket'); ?></span>
                    <strong id="popup-order-number">-</strong>
                </div>
                <div class="ticket-popup__chip">
                    <span><?php _e('تاریخ دریافت', 'simple-ticket'); ?></span>
                    <strong id="popup-order-date">-</strong>
                </div>
            </div>

            <p id="popup-summary" class="ticket-popup__summary"></p>

            <h3 class="ticket-popup__section-title"><?php _e('مشخصات کالا', 'simple-ticket'); ?></h3>
            <div id="popup-items" class="ticket-popup__items"></div>

            <h3 class="ticket-popup__section-title"><?php _e('پاسخ‌ها', 'simple-ticket'); ?></h3>
            <div class="ticket-popup__responses">
                <div class="ticket-popup__response-block">
                    <div class="ticket-popup__response-label"><?php _e('پاسخ پشتیبان', 'simple-ticket'); ?></div>
                    <div id="responses-container" class="ticket-popup__response-list"></div>
                </div>
                <div class="ticket-popup__response-block">
                    <div class="ticket-popup__response-label"><?php _e('پاسخ شما', 'simple-ticket'); ?></div>
                    <form method="post" class="user-response-form">
                        <?php wp_nonce_field('submit_user_response', 'user_response_nonce'); ?>
                        <input type="hidden" name="ticket_id" id="ticket-id">
                        <input type="hidden" id="user_response_nonce" value="<?php echo wp_create_nonce('submit_user_response'); ?>">
                        <textarea name="user_response" id="user_response" class="ticket-popup__textarea" rows="4" placeholder="<?php esc_attr_e('پاسخ خود را در این قسمت وارد کنید...', 'simple-ticket'); ?>"></textarea>
                        <button type="submit" class="ticket-popup__submit"><?php _e('ارسال پاسخ', 'simple-ticket'); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>