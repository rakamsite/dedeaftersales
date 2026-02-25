<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * اسلاگ منوی والد برای افزودن زیرمنوها.
 *
 * اگر در هر پروژه CPT متفاوتی دارید، این مقدار را تغییر دهید.
 */
function stslg_get_parent_menu_slug() {
    return apply_filters('stslg_parent_menu_slug', 'edit.php?post_type=ticket');
}

/**
 * افزودن دو بخش «راهنما» و «بروزرسانی‌ها» به پنل ادمین.
 */
function stslg_register_admin_pages() {
    $parent_slug = stslg_get_parent_menu_slug();

    add_submenu_page(
        $parent_slug,
        __('راهنما', 'sts-log-guide-starter'),
        __('راهنما', 'sts-log-guide-starter'),
        'manage_options',
        'stslg-guide',
        'stslg_render_guide_page'
    );

    add_submenu_page(
        $parent_slug,
        __('بروزرسانی‌ها', 'sts-log-guide-starter'),
        __('بروزرسانی‌ها', 'sts-log-guide-starter'),
        'manage_options',
        'stslg-updates',
        'stslg_render_updates_page'
    );
}
add_action('admin_menu', 'stslg_register_admin_pages');

/**
 * قوانین ثبت لاگ بروزرسانی.
 *
 * نکته: برای جلوگیری از فراموشی، قوانین همیشه در ابتدای صفحه بروزرسانی نمایش داده می‌شوند.
 *
 * @return array<int,string>
 */
function stslg_get_update_log_rules() {
    return array(
        'هر نسخه جدید باید با کلید نسخه (مثل 1.0.1) به ابتدای آرایه اضافه شود.',
        'شرح هر تغییر باید کوتاه، دقیق و قابل فهم برای مدیر محصول باشد.',
        'فقط تغییرات واقعی و Merge شده ثبت شوند؛ تغییرات موقت ثبت نشوند.',
        'هر آیتم لاگ با فعل مشخص شروع شود (افزودن، اصلاح، رفع، بهینه‌سازی و ...).',
        'اگر کاربر صراحتاً درخواست «ثبت لاگ» نداد، نسخه جدید به لاگ اضافه نشود.',
        'ترتیب نمایش نسخه‌ها باید نزولی باشد (جدیدترین نسخه در بالا).',
    );
}

/**
 * لاگ نسخه‌ها.
 *
 * مهم: ورودی‌های این تابع را به‌عنوان قانون مشترک پروژه نگه دارید
 * تا در هر ریپازیتوری نیاز به تعریف مجدد فرآیند ثبت لاگ نباشد.
 *
 * @return array<string,array<int,string>>
 */
function stslg_get_updates_log_entries() {
    return array(
        '0.1.0' => array(
            'ایجاد استارت‌پک افزونه خام شامل دو بخش «راهنما» و «بروزرسانی‌ها».',
            'افزودن قوانین استاندارد ثبت لاگ برای استفاده یکسان در همه پروژه‌ها.',
            'پیاده‌سازی زیرمنوهای آماده جهت کپی سریع در ریپازیتوری‌های جدید.',
        ),
    );
}

/**
 * رندر صفحه بروزرسانی‌ها.
 */
function stslg_render_updates_page() {
    $rules = stslg_get_update_log_rules();
    $logs  = stslg_get_updates_log_entries();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('بروزرسانی‌ها', 'sts-log-guide-starter'); ?></h1>
        <p><?php esc_html_e('تاریخچه تغییرات نسخه‌ها و قوانین ثبت لاگ در این بخش نگهداری می‌شود.', 'sts-log-guide-starter'); ?></p>

        <div class="card" style="max-width:960px;padding:16px;margin-top:16px;">
            <h2 style="margin-top:0;"><?php esc_html_e('قوانین تکمیل لاگ بروزرسانی', 'sts-log-guide-starter'); ?></h2>
            <ol style="list-style:decimal;padding-right:20px;line-height:1.9;">
                <?php foreach ($rules as $rule) : ?>
                    <li><?php echo esc_html($rule); ?></li>
                <?php endforeach; ?>
            </ol>
        </div>

        <?php foreach ($logs as $version => $items) : ?>
            <div class="card" style="max-width:960px;padding:16px;margin-top:16px;">
                <h2 style="margin-top:0;"><?php echo esc_html(sprintf(__('نسخه %s', 'sts-log-guide-starter'), $version)); ?></h2>
                <ul style="list-style:disc;padding-right:20px;line-height:1.9;">
                    <?php foreach ($items as $item) : ?>
                        <li><?php echo esc_html($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * رندر صفحه راهنما.
 *
 * طبق درخواست، محتوای داخلی فعلاً خالی است و فقط تیترها نمایش داده می‌شوند.
 */
function stslg_render_guide_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('راهنما', 'sts-log-guide-starter'); ?></h1>

        <div class="card" style="max-width:960px;padding:16px;margin-top:16px;">
            <h2 style="margin-top:0;"><?php esc_html_e('راهنمای استفاده', 'sts-log-guide-starter'); ?></h2>
        </div>

        <div class="card" style="max-width:960px;padding:16px;margin-top:16px;">
            <h2 style="margin-top:0;"><?php esc_html_e('راهنمای توسعه افزونه', 'sts-log-guide-starter'); ?></h2>
        </div>
    </div>
    <?php
}
