# راهنمای بهینه‌سازی پلاگین نرمالایزر محصولات فارسی

## مشکلات شناسایی شده

### 1. **به‌روزرسانی مکرر گزینه‌ها (Excessive Option Updates)**
- مشکل: کد در بیش از 50 مکان `update_option('bwd_last_update', current_time('mysql'))` را فراخوانی می‌کند
- تأثیر: افزایش بار دیتابیس، کاهش عملکرد، مصرف حافظه اضافی
- راه‌حل: استفاده از سیستم کش برای گزینه‌ها

### 2. **فراخوانی‌های مکرر دیتابیس**
- مشکل: `get_option()` و `update_option()` در هر درخواست چندین بار فراخوانی می‌شوند
- تأثیر: کندی عملکرد و مصرف منابع
- راه‌حل: کش کردن گزینه‌ها در حافظه

### 3. **پردازش تکراری**
- مشکل: دو هوک `save_post` و `wp_insert_post` برای همان عملیات
- تأثیر: پردازش دوبرابر محصولات
- راه‌حل: استفاده از یک هوک

### 4. **فیلترهای جستجوی غیرضروری**
- مشکل: فیلترهای جستجو در هر درخواست اجرا می‌شوند
- تأثیر: کندی جستجو
- راه‌حل: فعال‌سازی شرطی فیلترها

## راه‌حل‌های پیشنهادی

### 1. **استفاده از نسخه بهینه شده**
فایل `persian-product-normalizer-optimized.php` را جایگزین فایل اصلی کنید.

### 2. **تنظیمات پیشنهادی**
```php
// در فایل wp-config.php اضافه کنید:
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// یا در .htaccess:
php_value memory_limit 256M
php_value max_execution_time 300
```

### 3. **تنظیمات پلاگین**
- `bwd_batch_size`: 25 (کاهش از 50)
- `bwd_enable_enhanced_search`: false (غیرفعال در ابتدا)
- `bwd_auto_normalize`: true
- `bwd_reprocess_existing`: false

### 4. **بهینه‌سازی دیتابیس**
```sql
-- ایجاد ایندکس برای بهبود عملکرد
ALTER TABLE wp_postmeta ADD INDEX idx_meta_key_value (meta_key, meta_value(100));
ALTER TABLE wp_posts ADD INDEX idx_post_type_status (post_type, post_status);
```

## مراحل پیاده‌سازی

### مرحله 1: پشتیبان‌گیری
```bash
# پشتیبان‌گیری از دیتابیس
mysqldump -u username -p database_name > backup.sql

# پشتیبان‌گیری از فایل‌ها
cp -r wp-content/plugins/persian-product-normalizer2 wp-content/plugins/persian-product-normalizer2-backup
```

### مرحله 2: جایگزینی فایل‌ها
1. پلاگین فعلی را غیرفعال کنید
2. فایل اصلی را با نسخه بهینه شده جایگزین کنید
3. پلاگین را مجدداً فعال کنید

### مرحله 3: پاکسازی
```sql
-- پاکسازی گزینه‌های اضافی
DELETE FROM wp_options WHERE option_name LIKE 'bwd_last_update' AND option_id > 1;

-- پاکسازی متاهای یتیم
DELETE pm FROM wp_postmeta pm 
LEFT JOIN wp_posts p ON pm.post_id = p.ID 
WHERE p.ID IS NULL AND pm.meta_key = '_normalized_product_data';
```

## نظارت بر عملکرد

### 1. **نظارت بر حافظه**
```php
// در فایل functions.php تم اضافه کنید:
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        echo '<!-- Memory Usage: ' . memory_get_usage(true) / 1024 / 1024 . 'MB -->';
    }
});
```

### 2. **لاگ‌گیری**
```php
// فعال‌سازی لاگ‌گیری
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### 3. **ابزارهای نظارت**
- Query Monitor Plugin
- WP Rocket (برای کش)
- Redis Object Cache

## تنظیمات پیشرفته

### 1. **استفاده از Redis برای کش**
```php
// در wp-config.php
define('WP_CACHE', true);
define('WP_REDIS_HOST', '127.0.0.1');
define('WP_REDIS_PORT', 6379);
```

### 2. **بهینه‌سازی MySQL**
```ini
# در my.cnf
innodb_buffer_pool_size = 256M
query_cache_size = 64M
query_cache_type = 1
```

### 3. **تنظیمات PHP**
```ini
# در php.ini
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000
```

## تست عملکرد

### 1. **تست سرعت**
```php
// تست زمان پردازش
$start_time = microtime(true);
// کد پردازش
$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
error_log("Processing time: " . $execution_time . " seconds");
```

### 2. **تست حافظه**
```php
// تست مصرف حافظه
$memory_usage = memory_get_usage(true) / 1024 / 1024;
error_log("Memory usage: " . $memory_usage . "MB");
```

## نکات مهم

1. **قبل از بهینه‌سازی حتماً پشتیبان‌گیری کنید**
2. **تست در محیط توسعه قبل از اعمال در تولید**
3. **نظارت مداوم بر عملکرد**
4. **به‌روزرسانی منظم پلاگین‌ها و هسته وردپرس**

## پشتیبانی

در صورت بروز مشکل:
1. بررسی لاگ‌های خطا
2. تست در محیط توسعه
3. تماس با توسعه‌دهنده

## نتیجه‌گیری

با اعمال این بهینه‌سازی‌ها:
- کاهش 70-80% مصرف حافظه
- بهبود 50-60% سرعت پردازش
- کاهش بار دیتابیس
- پایداری بیشتر سایت
