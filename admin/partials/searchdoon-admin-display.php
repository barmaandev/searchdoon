<div class="wrap">
    <h1>نرمالایزر محصولات فارسی</h1>
    
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
            <!-- Status Overview -->
            <div class="bwd-status-card">
                <h2>وضعیت کلی</h2>
                <?php if (!$table_exists): ?>
                    <div class="bwd-table-error">
                        <p>جدول پیشرفت موجود نیست. آمار قابل نمایش نیست.</p>
                    </div>
                <?php elseif ($total_products == 0): ?>
                    <div class="bwd-no-products">
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
                            <span class="bwd-progress-value"><?php echo get_option('bwd_last_update', 'نامشخص'); ?></span>
                        </div>
                    </div>
                    
                    <div class="bwd-stats-actions">
                        <button type="button" id="bwd-refresh-stats" class="button button-secondary">
                            بروزرسانی آمار
                        </button>
                        <button type="button" id="bwd-cleanup-stats" class="button button-secondary">
                            پاکسازی و محاسبه مجدد
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Batch Processing -->
            <div class="bwd-batch-card">
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
            
            <!-- Manual Normalization -->
            <div class="bwd-manual-card">
                <h2>نرمالایز کردن دستی</h2>
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
            
            <!-- Test Normalization -->
            <div class="bwd-test-card">
                <h2>تست نرمالایز کردن</h2>
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
            
            <!-- Recent Activity -->
            <div class="bwd-activity-card">
                <h2>فعالیت‌های اخیر</h2>
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
                                            switch($log->status) {
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
            
            
            <!-- Settings -->
            <div class="bwd-settings-card">
                <h2>تنظیمات</h2>
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
                                    'از', 'به', 'در', 'با', 'برای', 'که', 'این', 'آن', 'را', 'و', 'یا',
                                    'هم', 'نیز', 'همچنین', 'همچنین', 'همچنین', 'همچنین', 'همچنین',
                                    'اما', 'ولی', 'اگر', 'چون', 'زیرا', 'بنابراین', 'پس', 'قبل',
                                    'بعد', 'بالا', 'پایین', 'چپ', 'راست', 'وسط', 'کنار', 'روبرو'
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
        <div class="bwd-ppn-sidebar">
            <p>
                این پلاگین توسط من، بارمان شکوهی ساخته شده و به رایگان در مخزن وردپرس قرار گرفته.
                اگر این پلاگین برای شما مفید بوده میتونید از طریق لینک زیر ازم حمایت کنید.
            </p>
        </div>
    </div>
</div>