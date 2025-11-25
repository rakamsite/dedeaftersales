<div class="container mx-auto p-6 max-w-2xl">
    <?php if (isset($_GET['warranty_part_submitted'])): ?>
        <div class="space-y-4 text-center mb-6">
            <p class="text-green-600"><?php _e('گارانتی مورد نظر فعال شد.', 'warranty-part-plugin'); ?></p>
        </div>
    <?php elseif (isset($_GET['warranty_part_error'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center mb-6"><?php _e('خطا در ثبت گارانتی. لطفاً اطلاعات را بررسی کنید.', 'warranty-part-plugin'); ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        <?php wp_nonce_field('submit_warranty_part', 'warranty_part_nonce'); ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="device_type" class="block text-sm font-medium text-gray-700"><?php _e('نوع دستگاه', 'warranty-part-plugin'); ?></label>
                <select name="device_type" id="device_type" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="پرس وایرشو و سرسیم پنوماتیک"><?php _e('پرس وایرشو و سرسیم پنوماتیک', 'warranty-part-plugin'); ?></option>
                    <option value="پرس کابلشو برقی"><?php _e('پرس کابلشو برقی', 'warranty-part-plugin'); ?></option>
                    <option value="پرس کابلشو هیدرولیک"><?php _e('پرس کابلشو هیدرولیک', 'warranty-part-plugin'); ?></option>
                    <option value="پرینتر حرارتی"><?php _e('پرینتر حرارتی', 'warranty-part-plugin'); ?></option>
                </select>
            </div>
            <div>
                <label for="hologram_code" class="block text-sm font-medium text-gray-700"><?php _e('کد هولوگرام طلایی الصاق شده بر روی محصول', 'warranty-part-plugin'); ?> (*)</label>
                <input type="text" name="hologram_code" id="hologram_code" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="operator_name" class="block text-sm font-medium text-gray-700"><?php _e('نام و نام خانوادگی بهره‌ بردار', 'warranty-part-plugin'); ?> (*)</label>
                <input type="text" name="operator_name" id="operator_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="operator_phone" class="block text-sm font-medium text-gray-700"><?php _e('شماره همراه بهره‌ بردار', 'warranty-part-plugin'); ?> (*)</label>
                <input type="text" name="operator_phone" id="operator_phone" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="operator_email" class="block text-sm font-medium text-gray-700"><?php _e('ایمیل بهره‌ بردار', 'warranty-part-plugin'); ?> (اختیاری)</label>
                <input type="email" name="operator_email" id="operator_email" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ خرید (شمسی یا میلادی)', 'warranty-part-plugin'); ?> (*)</label>
                <input type="text" name="purchase_date" id="purchase_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="seller" class="block text-sm font-medium text-gray-700"><?php _e('فروشنده محصول', 'warranty-part-plugin'); ?> (اختیاری)</label>
                <input type="text" name="seller" id="seller" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('فعالسازی گارانتی', 'warranty-part-plugin'); ?></button>
    </form>

    <!-- Popup for Success Message -->
    <div id="warranty-part-success-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-green-600 text-white p-6 rounded-lg text-center">
            <p><?php _e('گارانتی مورد نظر فعال شد.', 'warranty-part-plugin'); ?></p>
        </div>
    </div>
</div>