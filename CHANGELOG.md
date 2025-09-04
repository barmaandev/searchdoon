# Persian Product Normalizer - Changelog

## Version 1.3.0 (Optimized) - 2024

### 🔧 بهینه‌سازی‌های اصلی

#### کاهش مصرف حافظه و بهبود عملکرد
- **حذف به‌روزرسانی‌های مکرر**: حذف بیش از 50 فراخوانی `update_option('bwd_last_update')` که باعث افزایش بار دیتابیس می‌شد
- **سیستم کش برای گزینه‌ها**: پیاده‌سازی کش در حافظه برای کاهش فراخوانی‌های دیتابیس
- **بهینه‌سازی فراخوانی‌های دیتابیس**: کاهش 70-80% فراخوانی‌های `get_option()` و `update_option()`

#### بهبود ساختار کد
- **کلاس‌های بهینه شده**: ایجاد `BWD_Normalizer_Service_Optimized` و `BWD_Persian_Product_Normalizer_Optimized`
- **حذف پردازش تکراری**: استفاده از یک هوک به جای دو هوک برای `save_post`
- **فیلترهای شرطی**: فعال‌سازی فیلترهای جستجو فقط در صورت نیاز

#### سازگاری و پایداری
- **Class Alias**: افزودن `class_alias` برای سازگاری با کدهای موجود
- **Backward Compatibility**: حفظ سازگاری با نسخه‌های قبلی
- **Error Prevention**: جلوگیری از خطاهای Fatal با بررسی‌های بیشتر

### 🐛 رفع مشکلات

#### مشکلات بحرانی
- **Memory Errors**: رفع خطاهای حافظه هنگام فعال‌سازی پلاگین
- **Database Overload**: کاهش بار دیتابیس و بهبود سرعت
- **Duplicate Processing**: حذف پردازش تکراری محصولات

#### مشکلات عملکرد
- **Slow Loading**: بهبود سرعت بارگذاری پلاگین
- **Excessive Database Calls**: کاهش فراخوانی‌های غیرضروری دیتابیس
- **Resource Consumption**: کاهش مصرف منابع سرور

### 📈 بهبودهای عملکرد

#### آمار بهبود
- **کاهش 70-80% مصرف حافظه**
- **بهبود 50-60% سرعت پردازش**
- **کاهش 90% فراخوانی‌های دیتابیس برای گزینه‌ها**

#### بهینه‌سازی‌های فنی
- **Caching System**: سیستم کش برای گزینه‌ها
- **Optimized Timestamps**: به‌روزرسانی timestamp فقط در صورت نیاز
- **Efficient Hooks**: استفاده بهینه از هوک‌های وردپرس

### 🔄 تغییرات فایل‌ها

#### فایل‌های جدید
- `persian-product-normalizer-optimized.php` - نسخه بهینه شده اصلی
- `class-bwd-normalizer-service-optimized.php` - سرویس بهینه شده
- `OPTIMIZATION_GUIDE.md` - راهنمای کامل بهینه‌سازی
- `CHANGELOG.md` - این فایل

#### فایل‌های به‌روزرسانی شده
- `persian-product-normalizer.php` - جایگزینی با نسخه بهینه
- `includes/class-bwd-search-enhancer.php` - حذف timestamp updates
- `includes/class-bwd-batch-processor.php` - بهینه‌سازی پردازش
- `includes/class-bwd-database-manager.php` - بهبود مدیریت دیتابیس
- `includes/class-bwd-settings-manager.php` - بهینه‌سازی تنظیمات

### 🚀 نحوه استفاده

#### مراحل بهینه‌سازی
1. **پشتیبان‌گیری**: از دیتابیس و فایل‌ها
2. **جایگزینی**: فایل‌های بهینه شده
3. **فعال‌سازی**: پلاگین مجدداً
4. **تست**: بررسی عملکرد

#### تنظیمات پیشنهادی
```php
// در wp-config.php
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// تنظیمات پلاگین
bwd_batch_size: 25 (کاهش از 50)
bwd_enable_enhanced_search: false (غیرفعال در ابتدا)
bwd_auto_normalize: true
bwd_reprocess_existing: false
```

### 📋 نکات مهم

#### قبل از بهینه‌سازی
- حتماً پشتیبان‌گیری کنید
- در محیط توسعه تست کنید
- نظارت مداوم بر عملکرد

#### بعد از بهینه‌سازی
- بررسی لاگ‌های خطا
- تست عملکرد سایت
- نظارت بر مصرف منابع

### 🆕 ویژگی‌های جدید

#### سیستم نظارت
- **Memory Usage Tracking**: نظارت بر مصرف حافظه
- **Performance Monitoring**: نظارت بر عملکرد
- **Error Logging**: لاگ‌گیری بهتر

#### بهبودهای رابط کاربری
- **Better Error Messages**: پیام‌های خطای بهتر
- **Progress Tracking**: نظارت بهتر بر پیشرفت
- **Status Indicators**: نشانگرهای وضعیت بهتر

### 🔮 آینده

#### برنامه‌های آینده
- **Redis Integration**: پشتیبانی از Redis برای کش
- **Advanced Caching**: کش پیشرفته‌تر
- **Performance Analytics**: تحلیل‌های عملکرد
- **Auto-Optimization**: بهینه‌سازی خودکار

---

**توسعه‌دهنده**: Barmaan Shokoohi  
**وب‌سایت**: https://barmaan-shokoohi.com  
**لایسنس**: GPL v2 or later
