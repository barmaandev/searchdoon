<div class="wrap wbdn-wrapper">
    <h1>سرچ‌دون، نورمالایزر متن فارسی</h1>

    <?php if (!$table_exists): ?>
        <div class="notice notice-error">
            <p><strong>خطای بحرانی:</strong> جدول پیشرفت پلاگین ایجاد نشده است. لطفاً پلاگین را غیرفعال و دوباره فعال کنید.</p>
        </div>
    <?php else: ?>
        <?php
        // Check database connection
        global $wpdb;
        if ($wpdb->last_error) {
            echo '<div class="notice notice-error"><p><strong>خطای دیتابیس:</strong> ' . esc_html($wpdb->last_error) . '</p></div>';
        }

        // Show plugin status
        $plugin_activated = get_option('bwd_plugin_activated', false);
        if (!$plugin_activated) {
            echo '<div class="notice notice-warning"><p><strong>هشدار:</strong> پلاگین به درستی فعال نشده است. لطفاً پلاگین را غیرفعال و دوباره فعال کنید.</p></div>';
        }
        ?>
    <?php endif; ?>
    <div class="bwd-ppb-container">
        <div class="bwd-ppb-main">
            <!-- Tab Navigation -->
            <div class="wbdn-tab-wrapper">
                <a class="wbdn-tab-nav-btn active" data-tab="status">وضعیت کلی</a>
                <a class="wbdn-tab-nav-btn" data-tab="manual">نرمالایز کردن دستی</a>
                <a class="wbdn-tab-nav-btn" data-tab="settings">تنظیمات</a>
                <a class="wbdn-tab-nav-btn" data-tab="activity">فعالیت‌های اخیر</a>
                <a class="wbdn-tab-nav-btn" data-tab="guide">راهنما</a>
            </div>

            <!-- Status Tab Content -->
            <div id="wbdn-tab-status" class="wbdn-tab-content active">
                <?php if (!$table_exists): ?>
                    <div class="wbdn-section-box bwd-table-error">
                        <p>جدول پیشرفت موجود نیست. آمار قابل نمایش نیست.</p>
                    </div>
                <?php elseif ($total_products == 0): ?>
                    <div class="wbdn-section-box bwd-no-products">
                        <p>هنوز محصولی در سایت وجود ندارد یا شمارش نشده است.</p>
                        <button type="button" id="bwd-count-products" class="button button-secondary">
                            شمارش محصولات
                        </button>
                    </div>
                <?php else: ?>
                    <div class="bwd-stats-grid">
                        <div class="bwd-stat-item">
                            <span class="bwd-stat-number"><?php echo number_format($total_products); ?></span>
                            <span class="bwd-stat-label">کل محصولات</span>
                        </div>
                        <div class="bwd-stat-item">
                            <span class="bwd-stat-number"><?php echo number_format($processed_products); ?></span>
                            <span class="bwd-stat-label">نرمالایز شده</span>
                        </div>
                        <div class="bwd-stat-item">
                            <span class="bwd-stat-number"><?php echo number_format($products_needing_normalization); ?></span>
                            <span class="bwd-stat-label">نیاز به نرمالایز</span>
                        </div>
                        <div class="bwd-stat-item">
                            <span class="bwd-stat-number"><?php echo $percentage; ?>%</span>
                            <span class="bwd-stat-label">پیشرفت</span>
                        </div>
                    </div>
                    <div class="wbdn-section-box bwd-status-card">
                        <!-- Progress Bar -->
                        <div class="bwd-progress-container">
                            <div class="bwd-progress-bar">
                                <div class="bwd-progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="bwd-progress-text"><?php echo $percentage; ?>% تکمیل شده</span>
                        </div>

                        <!-- Detailed Progress Info -->
                        <div class="bwd-progress-details">
                            <div class="bwd-progress-item">
                                <span class="bwd-progress-label">محصولات نرمالایز شده:</span>
                                <span class="bwd-progress-value"><?php echo number_format($processed_products); ?></span>
                            </div>
                            <div class="bwd-progress-item">
                                <span class="bwd-progress-label">محصولات باقی‌مانده:</span>
                                <span class="bwd-progress-value"><?php echo number_format($products_needing_normalization); ?></span>
                            </div>
                            <div class="bwd-progress-item">
                                <span class="bwd-progress-label">آخرین بروزرسانی:</span>
                                <span class="bwd-progress-value"><?php
                                                                    $last_update = get_option('bwd_last_update', 'نامشخص');
                                                                    if (is_numeric($last_update)) {
                                                                        echo date('Y-m-d H:i:s', $last_update);
                                                                    } else {
                                                                        echo $last_update;
                                                                    }
                                                                    ?></span>
                            </div>
                        </div>

                        <div class="bwd-stats-actions">
                            <button type="button" id="bwd-refresh-stats" class="button">
                                بروزرسانی آمار
                            </button>
                            <button type="button" id="bwd-cleanup-stats" class="button">
                                پاکسازی و محاسبه مجدد
                            </button>
                            <button type="button" id="bwd-reset-normalized" class="button">
                                حذف داده‌های نرمالایز همه محصولات
                            </button>
                        </div>
                    </div>
                <?php endif; ?>


                <!-- Batch Processing -->
                <div class="wbdn-section-box bwd-batch-card">
                    <h2>پردازش دسته‌ای محصولات</h2>
                    <p>این عملیات تمام محصولات موجود را نرمالایز می‌کند و فیلدهای متا را اضافه می‌کند.</p>

                    <div class="bwd-batch-controls">
                        <button type="button" id="bwd-start-batch" class="button button-primary" <?php echo $completed ? 'disabled' : ''; ?>>
                            <?php echo $completed ? 'تکمیل شده' : 'شروع پردازش دسته‌ای'; ?>
                        </button>
                        <button type="button" id="bwd-reset-progress" class="button button-secondary">
                            ریست کردن
                        </button>
                    </div>

                    <div id="bwd-batch-status" class="bwd-batch-status" style="display: none;">
                        <div class="bwd-status-message"></div>
                        <div class="bwd-status-details"></div>
                    </div>
                </div>

                <!-- End Status Tab Content -->
            </div>

            <!-- Manual Normalization Tab Content -->
            <div id="wbdn-tab-manual" class="wbdn-tab-content">

                <!-- Manual Normalization -->
                <div class="wbdn-section-box bwd-manual-card">
                    <h3>نرمالایز کردن محصول خاص</h3>
                    <p>برای نرمالایز کردن یک محصول خاص، شناسه محصول را وارد کنید:</p>

                    <div class="bwd-manual-controls">
                        <input type="number" id="bwd-product-id" placeholder="شناسه محصول" min="1">
                        <button type="button" id="bwd-normalize-single" class="button button-secondary">
                            نرمالایز کردن
                        </button>
                    </div>

                    <div id="bwd-manual-status" class="bwd-manual-status" style="display: none;">
                        <div class="bwd-status-message"></div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab Content -->
            <div id="wbdn-tab-settings" class="wbdn-tab-content">

                <!-- Test Normalization -->
                <div class="wbdn-section-box bwd-test-card">
                    <h3>تست نرمالایز کردن</h3>
                    <p>متن فارسی خود را وارد کنید تا نتیجه نرمالایز شدن را ببینید(این متنی هست که در ستون مجزایی از نام محصول در دیتابیس شما ذخیره میشود.):</p>

                    <div class="bwd-test-controls">
                        <textarea id="bwd-test-text" placeholder="متن فارسی را اینجا وارد کنید..." rows="4"></textarea>
                        <button type="button" id="bwd-test-normalize" class="button button-secondary">
                            تست نرمالایز کردن
                        </button>
                    </div>

                    <div id="bwd-test-result" class="bwd-test-result" style="display: none;">
                        <h4>نتیجه نرمالایز کردن:</h4>
                        <div class="bwd-result-text"></div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="wbdn-section-box bwd-settings-card">
                    <h3>تنظیمات پلاگین</h3>
                    <form id="bwd-settings-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="bwd-batch-size">سایز بچ(اندازه دسته پردازش)</label>
                                </th>
                                <td>
                                    <input type="number" id="bwd-batch-size" name="batch_size"
                                        value="<?php echo esc_attr(get_option('bwd_batch_size', 50)); ?>"
                                        min="10" max="200">
                                    <p class="description">تعداد محصولاتی که در هر مرحله پردازش می‌شوند (پیشنهاد: 50)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bwd-remove-stopwords">حذف کلمات اضافی</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="bwd-remove-stopwords" name="remove_stopwords"
                                        <?php echo get_option('bwd_remove_stopwords', true) ? 'checked="checked"' : ''; ?>>
                                    <label for="bwd-remove-stopwords">کلمات اضافی فارسی را حذف کن</label>
                                    <p class="description">حالت فعلی: <?php echo get_option('bwd_remove_stopwords', true) ? 'فعال' : 'غیرفعال'; ?>
                                        <br>
                                        کلمات اضافی فارسی عبارتند از:
                                        <?php
                                        $stopwords = array(
                                            'از',
                                            'به',
                                            'در',
                                            'با',
                                            'برای',
                                            'که',
                                            'این',
                                            'آن',
                                            'را',
                                            'و',
                                            'یا',
                                            'هم',
                                            'نیز',
                                            'همچنین',
                                            'همچنین',
                                            'همچنین',
                                            'همچنین',
                                            'همچنین',
                                            'اما',
                                            'ولی',
                                            'اگر',
                                            'چون',
                                            'زیرا',
                                            'بنابراین',
                                            'پس',
                                            'قبل',
                                            'بعد',
                                            'بالا',
                                            'پایین',
                                            'چپ',
                                            'راست',
                                            'وسط',
                                            'کنار',
                                            'روبرو'
                                        );
                                        $stopwords = apply_filters('bwd_stopwords', $stopwords);
                                        echo implode(', ', $stopwords);
                                        ?>
                                        <br>
                                        که
                                        جهت بهینه سازی روند ذخیره سازی و جلوگیری از سنگین شدن دیتابیس میتونید این گزینه را فعال کنید.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bwd-reprocess-existing">پردازش مجدد محصولات موجود</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="bwd-reprocess-existing" name="reprocess_existing"
                                        <?php echo get_option('bwd_reprocess_existing', false) ? 'checked="checked"' : ''; ?>>
                                    <label for="bwd-reprocess-existing">محصولاتی که قبلاً نرمالایز شده‌اند را دوباره پردازش کن</label>
                                    <p class="description">حالت فعلی: <?php echo get_option('bwd_reprocess_existing', false) ? 'فعال' : 'غیرفعال'; ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="bwd-auto-normalize">نرمالایز خودکار</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="bwd-auto-normalize" name="auto_normalize"
                                        <?php echo get_option('bwd_auto_normalize', true) ? 'checked="checked"' : ''; ?>>
                                    <label for="bwd-auto-normalize">محصولات جدید را به صورت خودکار نرمالایز کن</label>
                                    <p class="description">حالت فعلی: <?php echo get_option('bwd_auto_normalize', true) ? 'فعال' : 'غیرفعال'; ?></p>
                                </td>
                            </tr>

                        </table>
                        <button type="submit" class="button button-primary">ذخیره تنظیمات</button>
                        <button type="button" id="bwd-test-settings" class="button button-secondary">تست تنظیمات فعلی</button>
                        <div id="bwd-settings-status" class="bwd-settings-status" style="display: none;">
                            <div class="bwd-status-message"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Activity Tab Content -->
            <div id="wbdn-tab-activity" class="wbdn-tab-content">

                <!-- Recent Activity -->
                <div class="wbdn-section-box bwd-activity-card">
                    <h3>لاگ فعالیت‌ها</h3>
                    <p>این بخش صرفا جهت بررسی روند نورمالایز هست و این فعالیت‌ها در جدولی جداگانه ذخیره میشود. پیشنهاد میشود جهت سنگین نشدن دیتابیس، بعد از اتمام کار لاگ ها را پاک کنید.</p>
                    <?php if (!$table_exists): ?>
                        <div class="bwd-table-error">
                            <p>جدول پیشرفت موجود نیست. لطفاً پلاگین را غیرفعال و دوباره فعال کنید.</p>
                        </div>
                    <?php elseif (!empty($recent_logs)): ?>
                        <div class="bwd-activity-controls">
                            <button type="button" id="bwd-refresh-logs" class="button button-secondary">
                                بروزرسانی لاگ‌ها
                            </button>
                            <button type="button" id="bwd-clear-logs" class="button button-secondary">
                                پاک کردن لاگ‌ها
                            </button>
                        </div>

                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>شناسه محصول</th>
                                    <th>وضعیت</th>
                                    <th>تاریخ ایجاد</th>
                                    <th>تاریخ بروزرسانی</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php if ($log->post_id > 0): ?>
                                                <a href="<?php echo get_edit_post_link($log->post_id); ?>" target="_blank">
                                                    <?php echo $log->post_id; ?>
                                                </a>
                                                <br>
                                                <small><?php echo get_the_title($log->post_id); ?></small>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic;">نمونه داده</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="bwd-status-badge bwd-status-<?php echo $log->status; ?>">
                                                <?php
                                                switch ($log->status) {
                                                    case 'manual_update':
                                                        echo 'بروزرسانی دستی';
                                                        break;
                                                    case 'batch_update':
                                                        echo 'پردازش دسته‌ای';
                                                        break;
                                                    case 'reprocess_update':
                                                        echo 'پردازش مجدد';
                                                        break;
                                                    case 'reprocess_single':
                                                        echo 'پردازش مجدد دستی';
                                                        break;
                                                    case 'publish_new':
                                                        echo 'محصول جدید منتشر شده';
                                                        break;
                                                    case 'publish_update':
                                                        echo 'محصول منتشر شده بروزرسانی';
                                                        break;
                                                    case 'sample_data':
                                                        echo 'نمونه داده';
                                                        break;
                                                    default:
                                                        echo esc_html($log->status);
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            if ($log->created_at) {
                                                $date = new DateTime($log->created_at);
                                                echo $date->format('Y/m/d H:i:s');
                                            } else {
                                                echo 'نامشخص';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($log->updated_at && $log->updated_at !== $log->created_at) {
                                                $date = new DateTime($log->updated_at);
                                                echo $date->format('Y/m/d H:i:s');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($log->post_id > 0): ?>
                                                <button type="button" class="button button-small bwd-reprocess-product" data-post-id="<?php echo $log->post_id; ?>">
                                                    پردازش مجدد
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="bwd-activity-summary">
                            <p><strong>تعداد کل لاگ‌ها:</strong> <?php echo count($recent_logs); ?></p>
                            <p><strong>آخرین بروزرسانی:</strong> <?php echo get_option('bwd_last_update', 'نامشخص'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="bwd-no-activity">
                            <p>هنوز فعالیتی ثبت نشده است.</p>
                            <p class="bwd-help-text">برای شروع، می‌توانید:</p>
                            <ul>
                                <li>یک محصول را به صورت دستی نرمالایز کنید</li>
                                <li>پردازش دسته‌ای را شروع کنید</li>
                                <li>تنظیمات پلاگین را بررسی کنید</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Guide Tab Content -->
            <div id="wbdn-tab-guide" class="wbdn-tab-content">

                <div class="wbdn-section-box bwd-guide-card">
                    <h2>راهنمای استفاده از پلاگین سرچ‌دون</h2>

                    <h3>نحوه استفاده در کوئری‌های جستجو</h3>
                    <p>این پلاگین متن‌های فارسی را نرمالایز می‌کند تا جستجو بهتر کار کند. در ادامه مثال‌هایی از نحوه استفاده آورده شده است:</p>

                    <div class="bwd-example-section">
                        <h4>1. جستجوی محصولات با نام فارسی</h4>
                        <div class="bwd-code-example">
                            <div class="bwd-code-header">
                                <span>کد PHP</span>
                                <button class="bwd-copy-btn" data-copy="wp_query_example">کپی</button>
                            </div>
                            <pre><code id="wp_query_example">&lt;?php
/**
 * جستجوی محصولات با نام فارسی
 * Search products with Persian names
 */

$args = array(
    'post_type' => 'product',
    's' => 'گوشی موبایل', // متن فارسی
    'meta_query' => array(
        array(
            'key' => 'bwd_normalized_title',
            'value' => 'گوشی موبایل',
            'compare' => 'LIKE'
        )
    )
);

$products = new WP_Query($args);

// نمایش نتایج
if ($products->have_posts()) {
    while ($products->have_posts()) {
        $products->the_post();
        echo '<h3>' . get_the_title() . '</h3>';
    }
    wp_reset_postdata();
} else {
    echo 'محصولی یافت نشد';
}
?&gt;</code></pre>
                        </div>
                    </div>

                    <div class="bwd-example-section">
                        <h4>2. جستجوی پیشرفته با چندین فیلد</h4>
                        <div class="bwd-code-example">
                            <div class="bwd-code-header">
                                <span>کد PHP</span>
                                <button class="bwd-copy-btn" data-copy="advanced_search_example">کپی</button>
                            </div>
                            <pre><code id="advanced_search_example">&lt;?php
/**
 * جستجوی پیشرفته در عنوان و توضیحات
 * Advanced search in title and content
 */

$search_term = 'لپ تاپ';
$normalized_term = bwd_normalize_text($search_term);

$args = array(
    'post_type' => 'product',
    'posts_per_page' => 10,
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => 'bwd_normalized_title',
            'value' => $normalized_term,
            'compare' => 'LIKE'
        ),
        array(
            'key' => 'bwd_normalized_content',
            'value' => $normalized_term,
            'compare' => 'LIKE'
        )
    )
);

$products = new WP_Query($args);

// بررسی نتایج
if ($products->found_posts > 0) {
    echo 'تعداد نتایج: ' . $products->found_posts;
    
    while ($products->have_posts()) {
        $products->the_post();
        echo '<div class="product-result">';
        echo '<h3>' . get_the_title() . '</h3>';
        echo '<p>' . wp_trim_words(get_the_content(), 20) . '</p>';
        echo '</div>';
    }
    wp_reset_postdata();
} else {
    echo 'هیچ محصولی یافت نشد';
}
?&gt;</code></pre>
                        </div>
                    </div>
                    <div class="bwd-example-section">
                        <h4>3. استفاده در AJAX جستجو</h4>
                        <div class="bwd-code-example">
                            <div class="bwd-code-header">
                                <span>کد JavaScript</span>
                                <button class="bwd-copy-btn" data-copy="ajax_search_example">کپی</button>
                            </div>
                            <pre><code id="ajax_search_example">/**
 * جستجوی AJAX با سرچ‌دون
 * AJAX Search with SearchDoon
 * 
 * نکته: این کد نمونه است و نیاز به پیاده‌سازی سمت سرور دارد
 * Note: This is example code and requires server-side implementation
 */

jQuery(document).ready(function($) {
    let searchTimeout;
    
    // جستجو با تاخیر (debounce)
    $('#search-input').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        clearTimeout(searchTimeout);
        
        // نمایش حالت بارگذاری
        $('#search-results').html('&lt;div class="loading"&gt;در حال جستجو...&lt;/div&gt;');
        
        // تاخیر 300 میلی‌ثانیه بعد از توقف تایپ
        searchTimeout = setTimeout(function() {
            if (searchTerm.length >= 2) {
                performSearch(searchTerm);
            } else {
                $('#search-results').html('');
            }
        }, 300);
    });
    
    function performSearch(term) {
        $.ajax({
            url: ajaxurl, // متغیر WordPress
            type: 'POST',
            data: {
                action: 'bwd_ajax_search', // نام action
                search_term: term,
                nonce: bwd_ajax.nonce // امنیت
            },
            success: function(response) {
                if (response.success) {
                    $('#search-results').html(response.data);
                } else {
                    $('#search-results').html('&lt;div class="error"&gt;خطا در جستجو&lt;/div&gt;');
                }
            },
            error: function() {
                $('#search-results').html('&lt;div class="error"&gt;خطا در ارتباط با سرور&lt;/div&gt;');
            }
        });
    }
});

// پیاده‌سازی سمت سرور (PHP)
/*
add_action('wp_ajax_bwd_ajax_search', 'handle_ajax_search');
add_action('wp_ajax_nopriv_bwd_ajax_search', 'handle_ajax_search');

function handle_ajax_search() {
    check_ajax_referer('bwd_nonce', 'nonce');
    
    $search_term = sanitize_text_field($_POST['search_term']);
    $normalized_term = bwd_normalize_text($search_term);
    
    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => 'bwd_normalized_title',
                'value' => $normalized_term,
                'compare' => 'LIKE'
            )
        )
    );
    
    $products = new WP_Query($args);
    
    if ($products->have_posts()) {
        $output = '&lt;ul&gt;';
        while ($products->have_posts()) {
            $products->the_post();
            $output .= '&lt;li&gt;' . get_the_title() . '&lt;/li&gt;';
        }
        $output .= '&lt;/ul&gt;';
        wp_reset_postdata();
        
        wp_send_json_success($output);
    } else {
        wp_send_json_error('محصولی یافت نشد');
    }
}
*/</code></pre>
                        </div>
                    </div>
                    <div class="bwd-example-section">
                        <h4>4. تابع نرمالایز کردن دستی</h4>
                        <div class="bwd-code-example">
                            <div class="bwd-code-header">
                                <span>کد PHP</span>
                                <button class="bwd-copy-btn" data-copy="manual_normalize_example">کپی</button>
                            </div>
                            <pre><code id="manual_normalize_example">&lt;?php
/**
 * نرمالایز کردن دستی متن
 * Manual text normalization
 */

// مثال 1: نرمالایز کردن یک متن
$original_text = 'گوشی موبایل سامسونگ';
$normalized_text = bwd_normalize_text($original_text);
echo 'متن اصلی: ' . $original_text . '<br>';
echo 'متن نرمالایز: ' . $normalized_text;
// خروجی: گوشي موبايل سامسونگ

// مثال 2: نرمالایز کردن آرایه از متون
$texts = [
    'گوشی موبایل',
    'لپ تاپ',
    'تبلت'
];

$normalized_texts = array_map('bwd_normalize_text', $texts);

foreach ($normalized_texts as $index => $normalized) {
    echo 'متن ' . ($index + 1) . ': ' . $normalized . '<br>';
}

// مثال 3: استفاده در حلقه
$products = get_posts(array(
    'post_type' => 'product',
    'numberposts' => 5
));

foreach ($products as $product) {
    $title = get_the_title($product->ID);
    $normalized_title = bwd_normalize_text($title);
    
    // ذخیره در متا فیلد
    update_post_meta($product->ID, 'bwd_normalized_title', $normalized_title);
}
?&gt;</code></pre>
                        </div>
                    </div>
                    <div class="bwd-example-section">
                        <h4>5. جستجو در WooCommerce</h4>
                        <div class="bwd-code-example">
                            <div class="bwd-code-header">
                                <span>کد PHP</span>
                                <button class="bwd-copy-btn" data-copy="woocommerce_search_example">کپی</button>
                            </div>
                            <pre><code id="woocommerce_search_example">&lt;?php
/**
 * جستجو در محصولات WooCommerce با سرچ‌دون
 * WooCommerce product search with SearchDoon
 */

// اضافه کردن فیلتر جستجو
add_filter('woocommerce_product_query', 'bwd_woocommerce_search');

function bwd_woocommerce_search($q) {
    // فقط در فرانت‌اند و جستجو
    if (!is_admin() && $q->is_main_query() && $q->is_search()) {
        $search_term = $q->get('s');
        
        if (!empty($search_term)) {
            $normalized_term = bwd_normalize_text($search_term);
            
            // تنظیم متا کوئری برای جستجو در فیلدهای نرمالایز
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => 'bwd_normalized_title',
                    'value' => $normalized_term,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'bwd_normalized_content',
                    'value' => $normalized_term,
                    'compare' => 'LIKE'
                )
            );
            
            $q->set('meta_query', $meta_query);
            
            // غیرفعال کردن جستجوی پیش‌فرض در عنوان
            $q->set('s', '');
        }
    }
    
    return $q;
}

// اضافه کردن فیلتر برای صفحه نتایج جستجو
add_action('woocommerce_before_shop_loop', 'bwd_show_search_info');

function bwd_show_search_info() {
    if (is_search()) {
        global $wp_query;
        $found_posts = $wp_query->found_posts;
        
        if ($found_posts > 0) {
            echo '<div class="search-info">';
            echo '<p>تعداد نتایج یافت شده: ' . $found_posts . '</p>';
            echo '</div>';
        }
    }
}
?&gt;</code></pre>
                        </div>
                    </div>
                    <div class="bwd-tips-section">
                        <h4>💡 نکات مهم:</h4>
                        <ul>
                            <li>همیشه از فیلدهای نرمالایز شده برای جستجو استفاده کنید</li>
                            <li>قبل از جستجو، متن ورودی را نرمالایز کنید</li>
                            <li>برای جستجوی بهتر، از LIKE به جای = استفاده کنید</li>
                            <li>فیلدهای نرمالایز شده: bwd_normalized_title, bwd_normalized_content</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- End Guide Tab Content -->
        </div>
        <!-- End bwd-ppb-main -->

        <div class="wbdn-section-box bwd-ppn-sidebar">
            <p>
                این پلاگین توسط من، بارمان شکوهی ساخته شده و به رایگان در مخزن وردپرس قرار گرفته.
                اگر این پلاگین برای شما مفید بوده میتونید از طریق لینک زیر ازم حمایت کنید.
            </p>
        </div>
    </div>
</div>