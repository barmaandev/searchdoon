<?php
if (!defined('ABSPATH')) exit;

class BWD_Database_Manager {
    public function __construct() {
        $this->create_progress_table();
    }
    
    private function create_progress_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bwd_progress';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    public function cleanup_orphaned_meta_data() {
        $this->perform_cleanup_orphaned_meta_data();
    }
    
    private function perform_cleanup_orphaned_meta_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';
        $normalized_meta_key = '_searchdoon_data';

        // Find orphaned meta data (posts that have the meta key but no corresponding post)
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
    }

    // Ensure the progress table exists and defaults are set (ported from original class)
    public function ensure_progress_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'bwd_progress';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if (!$table_exists) {
            // Database manager constructor creates the table via dbDelta
            // Instantiating ensures table creation logic has run
            $this->create_progress_table();
            // Re-check existence after attempting creation
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        }
        
        // Ensure activation flag exists
        if (!get_option('bwd_plugin_activated')) {
            add_option('bwd_plugin_activated', true);
        }
        
        // Ensure default settings exist
        if (!get_option('bwd_batch_size')) {
            add_option('bwd_batch_size', 50);
        }
        if (!get_option('bwd_remove_stopwords')) {
            add_option('bwd_remove_stopwords', true);
        }
        if (!get_option('bwd_reprocess_existing')) {
            add_option('bwd_reprocess_existing', false);
        }
        if (!get_option('bwd_auto_normalize')) {
            add_option('bwd_auto_normalize', true);
        }
        if (!get_option('bwd_products_needing_normalization')) {
            add_option('bwd_products_needing_normalization', 0);
        }
        
        return $table_exists;
    }

    private function log_normalization($post_id, $type) {
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
}