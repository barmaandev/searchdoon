<?php
if (!defined('ABSPATH')) exit;

class BWD_Batch_Processor {

    private $is_processing = false;
    private $batch_size = 50;
    private $normalized_meta_key = '_normalized_product_data';
    private $database_manager;
    private $persian_product_normalizer;

    public function __construct($plugin_instance = null) {
        $this->database_manager = new BWD_Database_Manager();
        // Prefer injected plugin instance to avoid re-entrancy during plugin construction
        $this->persian_product_normalizer = $plugin_instance;
        if (!$this->persian_product_normalizer) {
            // Fallback only if an instance is already available
            $cls = class_exists('BWD_Persian_Product_Normalizer') ? 'BWD_Persian_Product_Normalizer' : (class_exists('BWD_Persian_Product_Normalizer_Optimized') ? 'BWD_Persian_Product_Normalizer_Optimized' : null);
            if ($cls && is_callable([$cls, 'get_instance'])) {
                $this->persian_product_normalizer = call_user_func([$cls, 'get_instance']);
            }
        }
    }

    public function ajax_batch_update() {
        check_ajax_referer('bwd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->is_processing = true;
        
        // Get batch size from settings
        $batch_size = get_option('bwd_batch_size', $this->batch_size);
        $batch_size = apply_filters('bwd_batch_size', $batch_size);
        
        // Get products to process
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish', // Only published products
            'posts_per_page' => $batch_size,
        );
        
        // Check if we should reprocess existing products
        if (!get_option('bwd_reprocess_existing', false)) {
            $args['meta_query'] = array(
                array(
                    'key' => $this->normalized_meta_key,
                    'compare' => 'NOT EXISTS'
                )
            );
        }
        
        // Add filter to exclude auto-drafts and empty products
        add_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);
        
        $products = get_posts($args);
        
        // Remove the filter
        remove_filter('posts_where', array($this, 'exclude_auto_drafts_from_batch'), 10, 2);
        
        if (empty($products)) {
            update_option('bwd_batch_update_completed', true);
            
            wp_send_json_success(array(
                'message' => 'تمام محصولات نرمالایز شدند',
                'completed' => true
            ));
        }
        
        $processed = 0;
        foreach ($products as $product) {
            $this->persian_product_normalizer->normalize_and_save_product_data($product->ID, $product);
            $processed++;
        }
        
        // Update progress
        $total_processed = get_option('bwd_processed_products', 0) + $processed;
        update_option('bwd_processed_products', $total_processed);
        
        // Clean up orphaned meta data
        $this->database_manager->cleanup_orphaned_meta_data();
        
        // Recalculate accurate statistics
        global $wpdb;
        $total_products = get_option('bwd_total_products', 0);
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
        $actual_normalized = (int) $wpdb->get_var($normalized_query);
        
        // Update with accurate count
        update_option('bwd_processed_products', $actual_normalized);
        
        // Update products needing normalization
        $products_needing_normalization = max(0, $total_products - $actual_normalized);
        update_option('bwd_products_needing_normalization', $products_needing_normalization);
        
        wp_send_json_success(array(
            'processed' => $processed,
            'total_processed' => $actual_normalized,
            'products_needing_normalization' => $products_needing_normalization,
            'completed' => false
        ));
    }
    
    public function ajax_get_progress() {
        check_ajax_referer('bwd_nonce', 'nonce');
        
        $total_products = get_option('bwd_total_products', 0);
        $processed_products = get_option('bwd_processed_products', 0);
        $completed = get_option('bwd_batch_update_completed', false);
        
        if ($total_products == 0) {
            // Count total products
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

            // Clean up orphaned meta data
            $this->database_manager->cleanup_orphaned_meta_data();
            
            // Count normalized products that still exist
            global $wpdb;
            $normalized_query = $wpdb->prepare(
                "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->postmeta} pm
                 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE pm.meta_key = %s 
                 AND pm.meta_value != ''
                 AND p.post_type = 'product'
                 AND p.post_status = 'publish'
                 AND p.post_title != 'AUTO-DRAFT'
                 AND (p.post_content != '' OR p.post_excerpt != '')",
                $this->normalized_meta_key
            );
            $normalized_products = $wpdb->get_var($normalized_query);
            
            // Update processed products count
            $processed_products = $normalized_products;
            update_option('bwd_processed_products', $processed_products);
            update_option('bwd_products_needing_normalization', max(0, $total_products - $normalized_products));
            
            // Update last update timestamp
            update_option('bwd_last_update', current_time('mysql'));
        }
        
        $percentage = $total_products > 0 ? round(($processed_products / $total_products) * 100, 2) : 0;
        
        wp_send_json_success(array(
            'total' => $total_products,
            'processed' => $processed_products,
            'percentage' => $percentage,
            'completed' => $completed,
            'needing_normalization' => get_option('bwd_products_needing_normalization', 0)
        ));
    }

    public function exclude_auto_drafts_from_batch($where, $query) {
        global $wpdb;
        $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_status != %s", 'auto-draft');
        return $where;
    }
}