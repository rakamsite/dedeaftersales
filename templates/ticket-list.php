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
                            <button
                                class="text-blue-600 hover:underline view-ticket"
                                data-ticket-id="<?php echo get_the_ID(); ?>"
                                data-ticket-nonce="<?php echo esc_attr(wp_create_nonce('get_ticket_details')); ?>"
                            >
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
<div id="ticket-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 shadow-lg w-full max-w-5xl relative dir-rtl rounded-2xl">
        <div class="flex items-start justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800"><?php _e('مشاهده درخواست', 'simple-ticket'); ?></h2>
            <button id="close-popup" class="text-gray-600 hover:text-gray-800 text-3xl leading-none">×</button>
        </div>
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-[7px]" id="ticket-summary">
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-center">
                    <p class="text-sm text-gray-500"><?php _e('شماره درخواست', 'simple-ticket'); ?></p>
                    <p id="ticket-number" class="text-lg font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-center">
                    <p class="text-sm text-gray-500"><?php _e('وضعیت', 'simple-ticket'); ?></p>
                    <p id="ticket-status" class="text-lg font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-center">
                    <p class="text-sm text-gray-500"><?php _e('شماره سفارش یا فاکتور', 'simple-ticket'); ?></p>
                    <p id="order-number" class="text-lg font-semibold text-gray-800"></p>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-center">
                    <p class="text-sm text-gray-500"><?php _e('تاریخ دریافت سفارش', 'simple-ticket'); ?></p>
                    <p id="order-date" class="text-lg font-semibold text-gray-800"></p>
                </div>
            </div>

            <div class="border border-gray-200 rounded-xl p-4 bg-white space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800"><?php _e('جزئیات مشکلات ثبت‌شده', 'simple-ticket'); ?></h3>
                    <span id="ticket-owner" class="text-sm text-gray-500"></span>
                </div>
                <div id="issue-items-container" class="space-y-3">
                    <!-- Issue items will be populated by JavaScript -->
                </div>
            </div>

            <div class="border border-gray-200 rounded-xl h-96 overflow-y-auto p-4 text-gray-800 space-y-4 bg-white" id="responses-container">
                <!-- Responses will be populated by JavaScript -->
            </div>

            <form method="post" class="user-response-form space-y-3">
                <?php wp_nonce_field('submit_user_response', 'user_response_nonce'); ?>
                <input type="hidden" name="ticket_id" id="ticket-id">
                <input type="hidden" id="user_response_nonce" value="<?php echo wp_create_nonce('submit_user_response'); ?>">
                <textarea name="user_response" id="user_response" class="w-full border border-gray-300 rounded-lg p-4 h-32 focus:ring-2 focus:ring-indigo-700 focus:border-indigo-700" placeholder="<?php _e('پاسخ خود را بنویسید...', 'simple-ticket'); ?>"></textarea>
                <button type="submit" class="bg-[#2F2483] w-full rounded-lg h-fit text-white flex py-3 px-5 mt-1/2 justify-center">
                    <?php _e('ارسال پاسخ', 'simple-ticket'); ?>
                </button>
            </form>
        </div>
    </div>
</div>
