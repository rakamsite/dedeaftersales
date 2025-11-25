<div class="container mx-auto p-6 max-w-2xl">
    <?php if (isset($_GET['warranty_submitted'])): ?>
        <div class="space-y-4 text-center mb-6">
            <p class="text-green-600"><?php _e('گارانتی مورد نظر فعال شد.', 'warranty-plugin'); ?></p>
        </div>
    <?php elseif (isset($_GET['warranty_error'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center mb-6"><?php _e('خطا در ثبت گارانتی. لطفاً اطلاعات را بررسی کنید.', 'warranty-plugin'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        <?php wp_nonce_field('submit_warranty', 'warranty_nonce'); ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="product_type" class="block text-sm font-medium text-gray-700"><?php _e('نوع محصول', 'warranty-plugin'); ?></label>
                <select name="product_type" id="product_type" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="مفصل فشار ضعیف رزینی"><?php _e('مفصل فشار ضعیف رزینی', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار ضعیف حرارتی"><?php _e('مفصل فشار ضعیف حرارتی', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار ضعیف حرارتی با روکش تعمیری"><?php _e('مفصل فشار ضعیف حرارتی با روکش تعمیری', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط سرد"><?php _e('سرکابل فشار متوسط سرد', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط حرارتی"><?php _e('سرکابل فشار متوسط حراتی', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط جداشونده (پلاگین)"><?php _e('سرکابل فشار متوسط جداشونده (پلاگین)', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار متوسط سرد"><?php _e('مفصل فشار متوسط سرد', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار متوسط حرارتی"><?php _e('مفصل فشار متوسط حراتی', 'warranty-plugin'); ?></option>
                </select>
            </div>
            <div>
                <label for="hologram_code" class="block text-sm font-medium text-gray-700"><?php _e('کد هولوگرام طلایی الصاق شده بر روی محصول', 'warranty-plugin'); ?> (*)</label>
                <input type="text" name="hologram_code" id="hologram_code" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="pro_number" class="block text-sm font-medium text-gray-700"><?php _e('شماره PRO مندرج در لیبل محصول', 'warranty-plugin'); ?></label>
                <input type="text" name="pro_number" id="pro_number" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="installer_name" class="block text-sm font-medium text-gray-700"><?php _e('نام و نام خانوادگی نصاب', 'warranty-plugin'); ?> (*)</label>
                <input type="text" name="installer_name" id="installer_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="installer_phone" class="block text-sm font-medium text-gray-700"><?php _e('شماره همراه نصاب', 'warranty-plugin'); ?> (*)</label>
                <input type="text" name="installer_phone" id="installer_phone" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="installer_email" class="block text-sm font-medium text-gray-700"><?php _e('ایمیل نصاب', 'warranty-plugin'); ?> (اختیاری)</label>
                <input type="email" name="installer_email" id="installer_email" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="installation_location" class="block text-sm font-medium text-gray-700"><?php _e('محل نصب پروژه', 'warranty-plugin'); ?> (*)</label>
                <input type="text" name="installation_location" id="installation_location" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="installation_date" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ نصب', 'warranty-plugin'); ?> (*)</label>
                <input type="text" name="installation_date" id="installation_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="seller" class="block text-sm font-medium text-gray-700"><?php _e('فروشنده محصول', 'warranty-plugin'); ?></label>
                <input type="text" name="seller" id="seller" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('فعالسازی گارانتی', 'warranty-plugin'); ?></button>
    </form>

    <!-- Popup for Success Message -->
    <div id="warranty-success-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-green-600 text-white p-6 rounded-lg text-center">
            <p><?php _e('گارانتی مورد نظر فعال شد.', 'warranty-plugin'); ?></p>
        </div>
    </div>
</div>