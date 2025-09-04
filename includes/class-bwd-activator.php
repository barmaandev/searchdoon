<?php

class BWD_Activator {

    public function activate() {
        // Add activation flag and default settings
        $default_options = array(
            'bwd_plugin_activated' => true,
            'bwd_batch_update_completed' => false,
            'bwd_total_products' => 0,
            'bwd_processed_products' => 0,
            'bwd_batch_size' => 50,
            'bwd_remove_stopwords' => true,
            'bwd_reprocess_existing' => false,
            'bwd_auto_normalize' => true,
            'bwd_products_needing_normalization' => 0
        );
        
        foreach ($default_options as $option => $value) {
            add_option($option, $value);
        }
        
        $this->update_last_timestamp();
    }
    
    private function update_last_timestamp() {
        update_option('bwd_last_update', time());
    }
    
    private function update_total_products() {
        $total_products = wp_count_posts('product')->publish;
        update_option('bwd_total_products', $total_products);
    }
}