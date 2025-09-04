<?php
if (!defined('ABSPATH')) exit;

class BWD_Settings_Manager {
    private $batch_size = 50;
    
    public function ajax_save_settings() {
        check_ajax_referer('bwd_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $batch_size = intval($_POST['batch_size']);
        $remove_stopwords = isset($_POST['remove_stopwords']) && $_POST['remove_stopwords'] === 'true' ? true : false;
        $reprocess_existing = isset($_POST['reprocess_existing']) && $_POST['reprocess_existing'] === 'true' ? true : false;
        $auto_normalize = isset($_POST['auto_normalize']) && $_POST['auto_normalize'] === 'true' ? true : false;
        $enable_enhanced_search = false; // search enhancer removed
        
        if ($batch_size < 10 || $batch_size > 200) {
            wp_send_json_error('اندازه دسته باید بین 10 تا 200 باشد');
        }
        
        // Save settings
        update_option('bwd_batch_size', $batch_size);
        update_option('bwd_remove_stopwords', $remove_stopwords);
        update_option('bwd_reprocess_existing', $reprocess_existing);
        update_option('bwd_auto_normalize', $auto_normalize);
        delete_option('bwd_enable_enhanced_search');
        
        // Return saved values for verification
        wp_send_json_success(array(
            'message' => 'تنظیمات با موفقیت ذخیره شد',
            'saved_values' => array(
                'batch_size' => $batch_size,
                'remove_stopwords' => $remove_stopwords,
                'reprocess_existing' => $reprocess_existing,
                'auto_normalize' => $auto_normalize,
                'enable_enhanced_search' => false
            )
        ));
    }

    public function ajax_test_settings() {
        check_ajax_referer('bwd_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $settings = array(
            'batch_size' => get_option('bwd_batch_size', $this->batch_size),
            'remove_stopwords' => get_option('bwd_remove_stopwords', false),
            'reprocess_existing' => get_option('bwd_reprocess_existing', false),
            'auto_normalize' => get_option('bwd_auto_normalize', true),
            'enable_enhanced_search' => false,
        );

        wp_send_json_success($settings);
    }
}