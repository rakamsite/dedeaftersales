<?php
if (!is_user_logged_in()) {
    return <<<HTML
<div class="w-full flex justify-center items-center mt-20">
    <button data-drawer-hide="mobile-menu" class="bg-[#2F2483] w-fit rounded-lg h-fit text-white flex py-3 px-5 mt-1/2 flex justify-between login_register_page">
        <svg width="21" height="26" viewBox="0 0 21 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.3334 10.3333C13.1868 10.3333 15.5 8.02014 15.5 5.16667C15.5 2.3132 13.1868 0 10.3334 0C7.47988 0 5.16669 2.3132 5.16669 5.16667C5.16669 8.02014 7.47988 10.3333 10.3334 10.3333Z" fill="white"></path>
            <path d="M20.6667 20.0208C20.6667 23.2306 20.6667 25.8333 10.3333 25.8333C0 25.8333 0 23.2306 0 20.0208C0 16.8111 4.62675 14.2083 10.3333 14.2083C16.0399 14.2083 20.6667 16.8111 20.6667 20.0208Z" fill="white"></path>
        </svg>
        <a class="mr-2">ورود به حساب کاربری </a>
    </button>
</div>
HTML;
}
?>

<div class="container mx-auto p-4 max-w-2xl dir-rtl">
    <?php if (isset($_GET['ticket_error'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center mb-6"><?php _e('خطا در ثبت درخواست. لطفاً اطلاعات را بررسی کنید.', 'simple-ticket'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-md ticket-form">
        <?php wp_nonce_field('submit_ticket', 'ticket_nonce'); ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="order_number" class="block text-sm font-medium text-gray-700"><?php _e('شماره سفارش یا فاکتور', 'simple-ticket'); ?></label>
                <input type="text" name="order_number" id="order_number" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="order_date" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ دریافت سفارش', 'simple-ticket'); ?></label>
                <input type="text" name="order_date" id="order_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="delivery_method" class="block text-sm font-medium text-gray-700"><?php _e('نحوه دریافت کالا', 'simple-ticket'); ?></label>
                <select name="delivery_method" id="delivery_method" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="پیک"><?php _e('پیک', 'simple-ticket'); ?></option>
                    <option value="باربری"><?php _e('باربری', 'simple-ticket'); ?></option>
                    <option value="تیپاکس"><?php _e('تیپاکس', 'simple-ticket'); ?></option>
                    <option value="حضوری"><?php _e('حضوری', 'simple-ticket'); ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700"><?php _e('نوع مشکل', 'simple-ticket'); ?></label>
                <select name="issue_type" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="نقص کیفی"><?php _e('نقص کیفی در کالا', 'simple-ticket'); ?></option>
                    <option value="مغایرت تعداد"><?php _e('مغایرت تعداد (کم یا زیاد بودن کالا)', 'simple-ticket'); ?></option>
                    <option value="ارسال اشتباه"><?php _e('ارسال کالای اشتباه', 'simple-ticket'); ?></option>
                    <option value="آسیب در حمل"><?php _e('آسیب‌دیدگی در حمل‌ونقل', 'simple-ticket'); ?></option>
                    <option value="سایر"><?php _e('سایر موارد', 'simple-ticket'); ?></option>
                </select>
            </div>
        </div>
        <div>
            <label for="issue_description" class="block text-sm font-medium text-gray-700"><?php _e('شرح مشکل', 'simple-ticket'); ?></label>
            <textarea name="issue_description" id="issue_description" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="5"></textarea>
        </div>
        <div>
            <label for="attachment" class="block text-sm font-medium text-gray-700"><?php _e('ضمیمه فایل یا تصویر (jpg, png, pdf)', 'simple-ticket'); ?></label>
            <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md hover:bg-[#ed1c24] focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('ارسال درخواست', 'simple-ticket'); ?></button>
    </form>
</div>