<?php

/**
 * Plugin Name: SearchDoon
 * Plugin URI: https://barmaan.dev
 * Description: برای حل مشکلات جستجو در متن فارسی در وردپرس طراحی شده است. این افزونه مشکلات جستجو در متن فارسی را حل می‌کند و فیلدهای متا برای بهبود جستجو اضافه می‌کند.
 * Version: 1.0.0
 * Author: Barmaan Shokoohi
 * Author URI: https://barmaan.dev
 * Text Domain: searchdoon
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BWD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BWD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BWD_PLUGIN_VERSION', '1.0.0');

// Re-enable class includes (keep hooks disabled for now)
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-searchdoon-service.php';
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-database-manager.php';
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-batch-processor.php';
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-settings-manager.php';
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-activator.php';
require_once BWD_PLUGIN_PATH . 'includes/class-bwd-deactivator.php';
// Load admin UI class only in admin to ensure callback exists
if (is_admin()) {
    require_once BWD_PLUGIN_PATH . 'admin/class-searchdoon-admin.php';
}

// Main plugin class - Optimized Version
class BWD_SearchDoon
{
    private static $instance = null;
    private $normalized_meta_key = '_searchdoon_data';
    private $batch_size = 50;
    private $is_processing = false;
    private $searchdoon_service;
    private $database_manager;
    private $batch_processor;
    private $settings_manager;
    private $activator;
    private $deactivator;
    // Cache for options to reduce database calls
    private $options_cache = array();
    private $last_update_time = null;

    public function __construct()
    {
        // MINIMAL MODE: Instantiate only the normalizer service for lightweight tests
        $this->searchdoon_service = new BWD_SearchDoon_Service();
        // Ensure DB table exists for logs and stats
        $this->database_manager = new BWD_Database_Manager();
        $this->batch_processor = new BWD_Batch_Processor($this);
        $this->settings_manager = new BWD_Settings_Manager();
        $this->activator = new BWD_Activator();
        $this->deactivator = new BWD_Deactivator();
        $this->init_hooks();
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Optimized option getter with caching
     */
    private function get_cached_option($option_name, $default = false)
    {
        if (!isset($this->options_cache[$option_name])) {
            $this->options_cache[$option_name] = get_option($option_name, $default);
        }
        return $this->options_cache[$option_name];
    }

    /**
     * Optimized option setter with caching
     */
    private function set_cached_option($option_name, $value)
    {
        $this->options_cache[$option_name] = $value;
        update_option($option_name, $value);
    }

    /**
     * Update last update timestamp only when necessary
     */
    private function update_last_timestamp()
    {
        $current_time = current_time('mysql');
        if ($this->last_update_time !== $current_time) {
            $this->last_update_time = $current_time;
            $this->set_cached_option('bwd_last_update', $current_time);
        }
    }

    private function init_hooks()
    {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this->activator, 'activate'));
        register_deactivation_hook(__FILE__, array($this->deactivator, 'deactivate'));

        // Admin hooks (keep only the menu for minimal UI)
        add_action('admin_menu', array('BWD_Admin', 'add_admin_menu'));

        // Load admin assets on the plugin page
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Lightweight AJAX test endpoint
        add_action('wp_ajax_bwd_test_normalize', array($this, 'ajax_test_normalize'));

        // Minimal product normalization endpoint
        add_action('wp_ajax_bwd_normalize_single', array($this, 'ajax_normalize_single'));

        // Enable normalize on product save
        add_action('save_post', array($this, 'normalize_and_save_product_data'), 10, 2);

        // Regenerate normalized data when a product is duplicated in WooCommerce
        add_action('woocommerce_product_duplicate', array($this, 'handle_product_duplicate'), 10, 2);
        // Support popular Duplicate Post plugin (optional)
        add_action('after_duplicate_post', array($this, 'handle_after_duplicate_post'), 10, 2);

        // Batch processing endpoints
        add_action('wp_ajax_bwd_batch_update', array($this->batch_processor, 'ajax_batch_update'));
        add_action('wp_ajax_bwd_get_progress', array($this->batch_processor, 'ajax_get_progress'));

        // Settings endpoints
        add_action('wp_ajax_bwd_save_settings', array($this, 'proxy_save_settings'));
        add_action('wp_ajax_bwd_test_settings', array($this, 'proxy_test_settings'));

        // MINIMAL MODE: Disable all other hooks for now
        add_action('wp_ajax_bwd_reset_progress', array($this, 'ajax_reset_progress'));
        add_action('wp_ajax_bwd_count_products', array($this, 'ajax_count_products'));
        add_action('wp_ajax_bwd_refresh_logs', array($this, 'ajax_refresh_logs'));
        add_action('wp_ajax_bwd_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_bwd_reprocess_product', array($this, 'ajax_reprocess_product'));
        add_action('wp_ajax_bwd_reset_normalized', array($this, 'ajax_reset_normalized'));
        // add_action('wp_ajax_bwd_fix_activation', array($this, 'ajax_fix_activation'));
        add_action('wp_ajax_bwd_cleanup_stats', array($this, 'ajax_cleanup_stats'));

        // Search enhancer removed

        // Show the meta box on product edit screen (no processing happens automatically yet)
        add_action('add_meta_boxes', array($this, 'add_normalized_data_meta_box'));

        // $this->update_last_timestamp();
    }





    public function enqueue_admin_scripts($hook)
    {
        if ('tools_page_bwd-searchdoon' !== $hook) {
            return;
        }

        wp_enqueue_script(
            'bwd-admin-js',
            BWD_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            BWD_PLUGIN_VERSION,
            true
        );
        wp_enqueue_style(
            'bwd-admin-fonts',
            BWD_PLUGIN_URL . 'admin/css/fonts.css',
            array(),
            BWD_PLUGIN_VERSION
        );
        wp_enqueue_style(
            'bwd-admin-css',
            BWD_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            BWD_PLUGIN_VERSION
        );

        wp_localize_script('bwd-admin-js', 'bwd_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bwd_nonce'),
            'strings' => array(
                'processing' => 'در حال پردازش...',
                'completed' => 'تکمیل شد',
                'error' => 'خطا رخ داد',
                'confirm_batch' => 'آیا مطمئن هستید که می‌خواهید تمام محصولات را نرمالایز کنید؟'
            )
        ));
    }

    public function normalize_and_save_product_data($post_id, $post)
    {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (wp_is_post_autosave($post_id)) return;

        // Only process products
        if ($post->post_type !== 'product') return;

        // Skip auto-draft products
        if ($post->post_status === 'auto-draft') return;

        // Skip products with empty or default title only
        if (empty($post->post_title) || $post->post_title === 'AUTO-DRAFT') return;

        // Check if auto-normalize is enabled
        if (!$this->get_cached_option('bwd_auto_normalize', true)) {
            return;
        }

        // Check if product already normalized and reprocessing is disabled
        $existing_data = get_post_meta($post_id, $this->normalized_meta_key, true);
        if ($existing_data && !$this->get_cached_option('bwd_reprocess_existing', false)) {
            return; // Skip if already normalized and reprocessing is disabled
        }

        // Hook before normalization
        do_action('bwd_before_normalize', $post_id, $post);

        // Get product data
        $product_title = $post->post_title;
        $product_content = $post->post_content;
        $product_excerpt = $post->post_excerpt;

        // Normalize the data
        $normalized_data = array(
            'title' => $this->searchdoon_service->normalize_persian_text($product_title),
            'content' => $this->searchdoon_service->normalize_persian_text($product_content),
            'excerpt' => $this->searchdoon_service->normalize_persian_text($product_excerpt),
            'search_keywords' => $this->searchdoon_service->generate_search_keywords($product_title, $product_content),
            'normalized_at' => current_time('mysql')
        );

        // Filter normalized data
        $normalized_data = apply_filters('bwd_normalized_data', $normalized_data, $post_id, $post);

        // Save normalized data
        update_post_meta($post_id, $this->normalized_meta_key, $normalized_data);

        // Update statistics only if this product wasn't normalized before
        if (!$existing_data) {
            $current_processed = $this->get_cached_option('bwd_processed_products', 0);
            $total_products = $this->get_cached_option('bwd_total_products', 0);

            $this->set_cached_option('bwd_processed_products', $current_processed + 1);

            // Update products needing normalization
            $products_needing_normalization = max(0, $total_products - ($current_processed + 1));
            $this->set_cached_option('bwd_products_needing_normalization', $products_needing_normalization);
        }

        // Log the update
        $status = $existing_data ? 'reprocess_update' : 'manual_update';
        $this->log_normalization($post_id, $status);

        // Hook after normalization
        do_action('bwd_after_normalize', $post_id, $normalized_data);
    }

    public function add_normalized_data_meta_box()
    {
        add_meta_box(
            'bwd_normalized_data',
            'داده‌های نرمالایز شده',
            array($this, 'render_normalized_data_meta_box'),
            'product',
            'side',
            'default'
        );
    }

    public function render_normalized_data_meta_box($post)
    {
        $normalized_data = get_post_meta($post->ID, $this->normalized_meta_key, true);

        if ($normalized_data) {
            echo '<div class="bwd-normalized-data">';
            echo '<p><strong>عنوان نرمالایز شده:</strong></p>';
            echo '<p>' . esc_html($normalized_data['title']) . '</p>';
            echo '<p><strong>کلمات کلیدی جستجو:</strong></p>';
            echo '<p>' . esc_html($normalized_data['search_keywords']) . '</p>';
            echo '<p><strong>آخرین بروزرسانی:</strong></p>';
            echo '<p>' . esc_html($normalized_data['normalized_at']) . '</p>';
            echo '</div>';
        } else {
            echo '<p>داده‌های نرمالایز شده موجود نیست.</p>';
            echo '<button type="button" class="button bwd-normalize-now" data-post-id="' . $post->ID . '">نرمالایز کردن</button>';
        }
    }



    // AJAX methods remain the same but use cached options
    public function ajax_normalize_single()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $product_id = intval($_POST['product_id']);

        if (!$product_id) {
            wp_send_json_error('شناسه محصول نامعتبر است');
        }

        $product = get_post($product_id);

        if (!$product || $product->post_type !== 'product') {
            wp_send_json_error('محصول یافت نشد');
        }

        $this->normalize_and_save_product_data($product_id, $product);

        $normalized = get_post_meta($product_id, $this->normalized_meta_key, true);
        if (!$normalized) {
            wp_send_json_error('هیچ داده‌ای ذخیره نشد. ممکن است شرایط نرمالایز برقرار نبوده باشد.');
        }

        wp_send_json_success('محصول با موفقیت نرمالایز شد');
    }

    public function ajax_test_normalize()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $text = sanitize_textarea_field($_POST['text']);

        if (empty($text)) {
            wp_send_json_error('متنی برای نرمالایز کردن وارد نشده است');
        }

        $normalized_text = $this->searchdoon_service->normalize_persian_text($text);

        wp_send_json_success(array(
            'original_text' => $text,
            'normalized_text' => $normalized_text
        ));
    }

    public function ajax_reset_progress()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $options_to_reset = array(
            'bwd_batch_update_completed',
            'bwd_total_products',
            'bwd_processed_products',
            'bwd_products_needing_normalization'
        );

        foreach ($options_to_reset as $option) {
            delete_option($option);
            unset($this->options_cache[$option]);
        }

        // Clear progress table
        global $wpdb;
        $table_name = $wpdb->prefix . 'bwd_progress';
        $wpdb->query("TRUNCATE TABLE $table_name");

        wp_send_json_success('پیشرفت با موفقیت بازنشانی شد');
    }

    public function ajax_count_products()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;

        // Count total published products
        $total_products = 0;
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        // Add filter to exclude auto-drafts
        add_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);
        $total_products = count(get_posts($args));
        remove_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);

        // Count normalized products that still exist
        $normalized_products = 0;
        $normalized_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = %s 
             AND pm.meta_value != ''
             AND p.post_type = 'product'
             AND p.post_status = 'publish'
             AND p.post_title != 'AUTO-DRAFT'",
            $this->normalized_meta_key
        );
        $normalized_products = $wpdb->get_var($normalized_query);

        // Clean up orphaned meta data
        $this->database_manager->cleanup_orphaned_meta_data();

        // Recalculate after cleanup
        $normalized_products = $wpdb->get_var($normalized_query);

        // Count products that need normalization
        $products_needing_normalization = max(0, $total_products - $normalized_products);

        // Update options with caching
        $this->set_cached_option('bwd_total_products', $total_products);
        $this->set_cached_option('bwd_processed_products', $normalized_products);
        $this->set_cached_option('bwd_products_needing_normalization', $products_needing_normalization);

        wp_send_json_success(array(
            'total_products' => $total_products,
            'normalized_products' => $normalized_products,
            'products_needing_normalization' => $products_needing_normalization,
            'percentage' => $total_products > 0 ? round(($normalized_products / $total_products) * 100, 2) : 0
        ));
    }

    public function ajax_refresh_logs()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bwd_progress';
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

        wp_send_json_success($logs);
    }

    public function ajax_clear_logs()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bwd_progress';
        $wpdb->query("TRUNCATE TABLE $table_name");

        wp_send_json_success('لاگ‌ها با موفقیت پاک شدند.');
    }

    public function ajax_reset_normalized()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;

        // Delete all normalized meta rows
        $meta_key = $this->normalized_meta_key;
        $table = $wpdb->postmeta;

        // Count rows before delete for reporting
        $count_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE meta_key = %s",
            $meta_key
        );
        $rows_before = (int) $wpdb->get_var($count_query);

        // Perform deletion
        $deleted = $wpdb->query(
            $wpdb->prepare("DELETE FROM {$table} WHERE meta_key = %s", $meta_key)
        );

        // Recompute stats
        // Count total published, non auto-draft products
        add_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);
        $total_products = count(get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        )));
        remove_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);

        // Update options
        $this->set_cached_option('bwd_processed_products', 0);
        $this->set_cached_option('bwd_products_needing_normalization', max(0, $total_products));
        $this->set_cached_option('bwd_batch_update_completed', false);

        // Log reset action
        $this->log_normalization(0, 'reset_normalized');

        wp_send_json_success(array(
            'message' => 'تمام داده‌های نرمالایز محصولات حذف شد.',
            'deleted_meta_rows' => (int) $deleted,
            'previous_meta_rows' => $rows_before,
            'total_products' => $total_products
        ));
    }

    public function ajax_reprocess_product()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $product_id = intval($_POST['product_id']);

        if (!$product_id) {
            wp_send_json_error('شناسه محصول نامعتبر است');
        }

        $product = get_post($product_id);

        if (!$product || $product->post_type !== 'product') {
            wp_send_json_error('محصول یافت نشد');
        }

        // Delete existing normalized data to force re-normalization
        delete_post_meta($product_id, $this->normalized_meta_key);

        // Log the reprocessing
        $this->log_normalization($product_id, 'reprocess_single');

        wp_send_json_success('محصول با موفقیت دوباره نرمالایز شد.');
    }

    public function ajax_fix_activation()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Check if plugin is activated
        if ($this->get_cached_option('bwd_plugin_activated')) {
            wp_send_json_success('پلاگین قبلاً فعال شده است.');
            return;
        }

        // Add activation flag
        $this->set_cached_option('bwd_plugin_activated', true);
        $this->set_cached_option('bwd_batch_update_completed', false);
        $this->set_cached_option('bwd_total_products', 0);
        $this->set_cached_option('bwd_processed_products', 0);

        wp_send_json_success('پلاگین با موفقیت فعال شد.');
    }

    public function ajax_cleanup_stats()
    {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';
        $normalized_meta_key = $this->normalized_meta_key;

        // Find orphaned meta data
        $orphaned_meta_query = $wpdb->prepare(
            "SELECT DISTINCT pm.meta_id FROM $table_name pm
             LEFT JOIN $wpdb->posts p ON pm.post_id = p.ID
             WHERE p.ID IS NULL AND pm.meta_key = %s",
            $normalized_meta_key
        );
        $orphaned_meta_ids = $wpdb->get_col($orphaned_meta_query);

        if (!empty($orphaned_meta_ids)) {
            $format = implode(',', array_fill(0, count($orphaned_meta_ids), '%d'));
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE meta_id IN ($format)", $orphaned_meta_ids));
        }

        // Recalculate total products
        $total_products = 0;
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        add_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);
        $total_products = count(get_posts($args));
        remove_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);

        // Recalculate normalized products
        $normalized_products = 0;
        $normalized_query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             WHERE pm.meta_key = %s 
             AND pm.meta_value != ''
             AND p.post_type = 'product'
             AND p.post_status = 'publish'
             AND p.post_title != 'AUTO-DRAFT'",
            $this->normalized_meta_key
        );
        $normalized_products = $wpdb->get_var($normalized_query);

        // Recalculate products needing normalization
        $products_needing_normalization = max(0, $total_products - $normalized_products);

        // Update options with caching
        $this->set_cached_option('bwd_total_products', $total_products);
        $this->set_cached_option('bwd_processed_products', $normalized_products);
        $this->set_cached_option('bwd_products_needing_normalization', $products_needing_normalization);

        wp_send_json_success(array(
            'total_products' => $total_products,
            'normalized_products' => $normalized_products,
            'products_needing_normalization' => $products_needing_normalization,
            'percentage' => $total_products > 0 ? round(($normalized_products / $total_products) * 100, 2) : 0
        ));
    }

    public function exclude_auto_drafts_from_batch($where, $query)
    {
        global $wpdb;
        $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_status != %s", 'auto-draft');
        return $where;
    }

    /**
     * Handle WooCommerce product duplication to ensure _searchdoon_data is regenerated for the new product
     */
    public function handle_product_duplicate($duplicate, $product)
    {
        // $duplicate is a WC_Product object
        if (!is_object($duplicate) || !method_exists($duplicate, 'get_id')) {
            return;
        }

        $new_product_id = (int) $duplicate->get_id();
        if ($new_product_id <= 0) {
            return;
        }

        // Remove copied normalized meta from the new product
        delete_post_meta($new_product_id, $this->normalized_meta_key);

        // Regenerate immediately using current post data
        $new_post = get_post($new_product_id);
        if ($new_post && $new_post->post_type === 'product') {
            $this->normalize_and_save_product_data($new_product_id, $new_post);
        }
    }

    /**
     * Handle generic post duplication (e.g., Duplicate Post plugin)
     */
    public function handle_after_duplicate_post($duplicate, $post)
    {
        if (!$duplicate || !isset($duplicate->ID)) {
            return;
        }

        if ($duplicate->post_type !== 'product') {
            return;
        }

        $new_product_id = (int) $duplicate->ID;

        // Remove copied normalized meta from the new product
        delete_post_meta($new_product_id, $this->normalized_meta_key);

        // Regenerate
        $this->normalize_and_save_product_data($new_product_id, $duplicate);
    }

    private function log_normalization($post_id, $type)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bwd_progress';
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'status' => $type
            ),
            array('%d', '%s')
        );
    }

    public function proxy_save_settings()
    {
        return $this->settings_manager->ajax_save_settings();
    }

    public function proxy_test_settings()
    {
        return $this->settings_manager->ajax_test_settings();
    }
}

// Initialize the plugin
function bwd_init_optimized()
{
    return BWD_SearchDoon::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'bwd_init_optimized');

// Backward compatibility: alias old class name to optimized class
if (!class_exists('BWD_SearchDoon') && class_exists('BWD_SearchDoon_Optimized')) {
    class_alias('BWD_SearchDoon_Optimized', 'BWD_SearchDoon');
}
