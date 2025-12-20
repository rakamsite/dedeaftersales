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
?>

<div class="container mx-auto p-4 max-w-2xl dir-rtl">
    <?php if (isset($_GET['ticket_error'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center mb-6"><?php _e('خطا در ثبت درخواست. لطفاً اطلاعات را بررسی کنید.', 'simple-ticket'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-md ticket-form">
        <?php wp_nonce_field('submit_ticket', 'ticket_nonce'); ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="order_number" class="block text-sm font-medium text-gray-700"><?php _e('شماره سفارش یا فاکتور', 'simple-ticket'); ?></label>
                <input type="number" name="order_number" id="order_number" required inputmode="numeric" pattern="[0-9]*" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="order_date" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ دریافت سفارش', 'simple-ticket'); ?></label>
                <input type="text" name="order_date" id="order_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800"><?php _e('مشکلات کالاها', 'simple-ticket'); ?></h3>
                <p class="text-sm text-gray-500"><?php _e('فرمت مجاز ضمیمه: jpg، jpeg، png، gif - حداکثر ۱۰ مگابایت', 'simple-ticket'); ?></p>
            </div>
            <div id="issue-items" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 issue-item-row items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"><?php _e('نام محصول', 'simple-ticket'); ?></label>
                        <input type="text" name="product_name[]" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php _e('نام کالا', 'simple-ticket'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"><?php _e('تعداد', 'simple-ticket'); ?></label>
                        <input type="number" name="quantity[]" min="1" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="<?php _e('تعداد', 'simple-ticket'); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"><?php _e('نوع مشکل', 'simple-ticket'); ?></label>
                        <select name="issue_type[]" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="نقص کیفی"><?php _e('نقص کیفی در کالا', 'simple-ticket'); ?></option>
                            <option value="مغایرت تعداد"><?php _e('مغایرت تعداد (کم یا زیاد بودن مقدار  ارسالی)', 'simple-ticket'); ?></option>
                            <option value="ارسال اشتباه"><?php _e('ارسال کالای اشتباه', 'simple-ticket'); ?></option>
                            <option value="آسیب در حمل"><?php _e('آسیب دیدگی در حمل و نقل', 'simple-ticket'); ?></option>
                            <option value="سایر"><?php _e('سایر موارد', 'simple-ticket'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"><?php _e('شرح مشکل', 'simple-ticket'); ?></label>
                        <textarea name="issue_description[]" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="<?php _e('توضیح مختصر', 'simple-ticket'); ?>"></textarea>
                    </div>
                    <div class="flex items-end space-x-2 space-x-reverse">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700"><?php _e('ضمیمه فایل یا تصویر', 'simple-ticket'); ?></label>
                            <input type="file" name="attachment[]" class="issue-attachment mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" accept=".jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="flex flex-col gap-2">
                            <button type="button" class="add-issue-row inline-flex items-center justify-center h-12 w-12 text-2xl bg-[#2f2483] text-white rounded-md hover:bg-[#ed1c24] focus:outline-none focus:ring-2 focus:ring-[#2f2483]" aria-label="<?php _e('افزودن ردیف جدید', 'simple-ticket'); ?>">+</button>
                            <button type="button" class="remove-issue-row inline-flex items-center justify-center h-10 w-12 text-xl bg-red-100 text-red-700 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-400 hidden" aria-label="<?php _e('حذف ردیف', 'simple-ticket'); ?>">×</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md hover:bg-[#ed1c24] focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('ارسال درخواست', 'simple-ticket'); ?></button>
    </form>

    <div id="ticket-success-overlay" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50" role="dialog" aria-modal="true" aria-labelledby="ticket-success-message">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center flex flex-col justify-between" style="width: 300px; height: 300px; max-width: 90vw; max-height: 90vh;">
            <p id="ticket-success-message" class="text-lg font-semibold text-gray-800 mt-4"><?php _e('درخواست شما با موفقیت ثبت شد و به زودی بررسی میگردد.', 'simple-ticket'); ?></p>
            <button type="button" id="ticket-success-close" class="w-full bg-[#2f2483] text-white py-3 rounded-md hover:bg-[#ed1c24] focus:outline-none focus:ring-2 focus:ring-[#2f2483]">
                <?php _e('متوجه شدم', 'simple-ticket'); ?>
            </button>
        </div>
    </div>
</div>
