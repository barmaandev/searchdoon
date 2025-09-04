<?php

class BWD_Deactivator {

    public function deactivate() {
        // Remove options
        $options_to_delete = array(
            'bwd_plugin_activated',
            'bwd_batch_update_completed',
            'bwd_total_products',
            'bwd_processed_products'
        );
        
        foreach ($options_to_delete as $option) {
            delete_option($option);
            unset($this->options_cache[$option]);
        }
        
        $this->update_last_timestamp();
    }

    private function update_last_timestamp() {
        update_option('bwd_last_update', time());
    }
}