<?php
if (!is_user_logged_in()) {
    return '<p class="text-red-500 text-center p-4 bg-red-100 rounded">' . __('لطفاً برای ثبت درخواست وارد حساب کاربری خود شوید.', 'simple-ticket') . '</p>';
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
            <label for="response_preference" class="block text-sm font-medium text-gray-700"><?php _e('ترجیح پاسخگویی', 'simple-ticket'); ?></label>
            <select name="response_preference" id="response_preference" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="ایمیل"><?php _e('ایمیل', 'simple-ticket'); ?></option>
                <option value="پیامک"><?php _e('پیامک', 'simple-ticket'); ?></option>
                <option value="تماس تلفنی"><?php _e('تماس تلفنی', 'simple-ticket'); ?></option>
            </select>
        </div>
        <div>
            <label for="attachment" class="block text-sm font-medium text-gray-700"><?php _e('ضمیمه فایل یا تصویر (jpg, png, pdf)', 'simple-ticket'); ?></label>
            <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md hover:bg-[#ed1c24] focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('ارسال درخواست', 'simple-ticket'); ?></button>
    </form>
</div>