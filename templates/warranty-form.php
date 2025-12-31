<div class="container mx-auto p-6 max-w-2xl">
    <?php if (isset($_GET['warranty_submitted'])): ?>
        <div class="space-y-4 text-center mb-6">
            <p class="text-green-600"><?php _e('گارانتی محصول شما با موفقیت فعال شد.', 'warranty-plugin'); ?></p>
        </div>
    <?php elseif (isset($_GET['warranty_error'])): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg text-center mb-6"><?php _e('خطا در ثبت گارانتی. لطفاً اطلاعات را بررسی کنید.', 'warranty-plugin'); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
        <?php wp_nonce_field('submit_warranty', 'warranty_nonce'); ?>

        <section class="warranty-section">
            <h3 class="warranty-section-title"><?php _e('بخش A — انتخاب محصول', 'warranty-plugin'); ?></h3>
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700"><?php _e('دسته محصول ⭐', 'warranty-plugin'); ?></p>
                <div class="flex flex-col md:flex-row gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="product_category" value="part" required />
                        <span><?php _e('سرکابل و مفصل', 'warranty-plugin'); ?></span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="product_category" value="tool" required />
                        <span><?php _e('ابزار آلات', 'warranty-plugin'); ?></span>
                    </label>
                </div>
            </div>
        </section>

        <section class="warranty-section">
            <h3 class="warranty-section-title"><?php _e('بخش B — نوع محصول', 'warranty-plugin'); ?></h3>
            <div id="warranty-section-part" class="space-y-4 hidden" data-warranty-section>
                <label for="product_type" class="block text-sm font-medium text-gray-700"><?php _e('نوع محصول ⭐', 'warranty-plugin'); ?></label>
                <select name="product_type" id="product_type" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-required="true" disabled>
                    <option value=""><?php _e('انتخاب کنید', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار ضعیف رزینی (DJR01)"><?php _e('مفصل فشار ضعیف رزینی  (DJR01)', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار ضعیف حرارتی (DJH01)"><?php _e('مفصل فشار ضعیف حرارتی  (DJH01)', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار ضعیف حرارتی تعمیری (DJH01-WAS)"><?php _e('مفصل فشار ضعیف حرارتی تعمیری  (DJH01-WAS)', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط سرد (DTC)"><?php _e('سرکابل فشار متوسط سرد  (DTC)', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط حرارتی (DTH)"><?php _e('سرکابل فشار متوسط حرارتی  (DTH)', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار متوسط حرارتی (DJH)"><?php _e('مفصل فشار متوسط حرارتی (DJH)', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار متوسط پلاگین (DTS)"><?php _e('سرکابل فشار متوسط پلاگین (DTS)', 'warranty-plugin'); ?></option>
                    <option value="سرکابل فشار قوی حرارتی (DTH52)"><?php _e('سرکابل فشار قوی حرارتی (DTH52)', 'warranty-plugin'); ?></option>
                    <option value="مفصل فشار قوی حرارتی (DTH52)"><?php _e('مفصل فشار قوی حرارتی (DTH52)', 'warranty-plugin'); ?></option>
                </select>
            </div>

            <div id="warranty-section-tool" class="space-y-4 hidden" data-warranty-section>
                <label for="device_type" class="block text-sm font-medium text-gray-700"><?php _e('نوع دستگاه ⭐', 'warranty-plugin'); ?></label>
                <select name="device_type" id="device_type" class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" data-required="true" disabled>
                    <option value=""><?php _e('انتخاب کنید', 'warranty-plugin'); ?></option>
                    <option value="دستگاه پرس وایرشو و سرسیم پنوماتیک (PCM)"><?php _e('دستگاه پرس وایرشو و سرسیم پنوماتیک (PCM)', 'warranty-plugin'); ?></option>
                    <option value="دستگاه پرس کابلشو هیدرولیک (HCT)"><?php _e('دستگاه پرس کابلشو هیدرولیک (HCT)', 'warranty-plugin'); ?></option>
                    <option value="دستگاه پرس کابلشو برقی (شارژی) (ECT)"><?php _e('دستگاه پرس کابلشو برقی (شارژی) (ECT)', 'warranty-plugin'); ?></option>
                    <option value="دستگاه پرینتر حرارتی (S900E)"><?php _e('دستگاه پرینتر حرارتی (S900E)', 'warranty-plugin'); ?></option>
                </select>
            </div>
        </section>

        <section class="warranty-section">
            <h3 class="warranty-section-title"><?php _e('بخش C — اطلاعات هولوگرام', 'warranty-plugin'); ?></h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                <div class="space-y-2">
                    <label for="hologram_code" class="block text-sm font-medium text-gray-700"><?php _e('کد هولوگرام طلایی ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="hologram_code" id="hologram_code" inputmode="numeric" pattern="\d{6}" maxlength="6" minlength="6" required class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500"><?php _e('کد ۶ رقمی درج‌شده روی هولوگرام طلایی الصاق‌شده به محصول را وارد نمایید.', 'warranty-plugin'); ?></p>
                </div>
                <?php $hologram_image = get_option('sts_hologram_sample_image'); ?>
                <?php if (!empty($hologram_image)) : ?>
                    <div class="warranty-hologram-preview">
                        <img src="<?php echo esc_url($hologram_image); ?>" alt="<?php esc_attr_e('نمونه هولوگرام', 'warranty-plugin'); ?>">
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="warranty-section">
            <h3 class="warranty-section-title"><?php _e('بخش D — اطلاعات گارانتی', 'warranty-plugin'); ?></h3>
            <div id="warranty-fields-part" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden" data-warranty-fields>
                <div>
                    <label for="purchase_date_part" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ خرید ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="purchase_date" id="purchase_date_part" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="installation_date" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ نصب ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="installation_date" id="installation_date" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="installation_location" class="block text-sm font-medium text-gray-700"><?php _e('محل نصب (نام پروژه) ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="installation_location" id="installation_location" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="installer_name" class="block text-sm font-medium text-gray-700"><?php _e('نام نصاب ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="installer_name" id="installer_name" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="installer_phone" class="block text-sm font-medium text-gray-700"><?php _e('شماره همراه نصاب ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="installer_phone" id="installer_phone" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="installer_email" class="block text-sm font-medium text-gray-700"><?php _e('ایمیل نصاب (اختیاری)', 'warranty-plugin'); ?></label>
                    <input type="email" name="installer_email" id="installer_email" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label for="installed_product_image" class="block text-sm font-medium text-gray-700"><?php _e('تصویر محصول نصب‌شده ⭐', 'warranty-plugin'); ?></label>
                    <input type="file" name="installed_product_image" id="installed_product_image" accept="image/*" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div id="warranty-fields-tool" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden" data-warranty-fields>
                <div>
                    <label for="purchase_date_tool" class="block text-sm font-medium text-gray-700"><?php _e('تاریخ خرید ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="purchase_date" id="purchase_date_tool" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="operator_name" class="block text-sm font-medium text-gray-700"><?php _e('نام بهره‌بردار ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="operator_name" id="operator_name" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="operator_phone" class="block text-sm font-medium text-gray-700"><?php _e('شماره همراه بهره‌بردار ⭐', 'warranty-plugin'); ?></label>
                    <input type="text" name="operator_phone" id="operator_phone" data-required="true" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="operator_email" class="block text-sm font-medium text-gray-700"><?php _e('ایمیل بهره‌بردار (اختیاری)', 'warranty-plugin'); ?></label>
                    <input type="email" name="operator_email" id="operator_email" disabled class="mt-1 block w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </section>

        <section class="warranty-section">
            <h3 class="warranty-section-title"><?php _e('بخش E — تأیید نهایی', 'warranty-plugin'); ?></h3>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="confirm_info" value="1" required />
                <span><?php _e('اینجانب صحت اطلاعات واردشده را تأیید می‌نمایم. ⭐', 'warranty-plugin'); ?></span>
            </label>
        </section>

        <button type="submit" class="mt-6 w-full bg-[#2f2483] text-white py-3 rounded-md focus:outline-none focus:ring-2 focus:ring-[#2f2483]"><?php _e('فعالسازی گارانتی', 'warranty-plugin'); ?></button>
    </form>
</div>
