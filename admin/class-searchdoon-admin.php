<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
class BWD_Admin {
    public static function add_admin_menu() {
        add_management_page(
            'سرچ‌دون',
            'سرچ‌دون',
            'manage_options',
            'bwd-searchdoon',
            array(self::class, 'admin_page')
        );
    }
    public static function admin_page() {
        // Prepare data for the admin view (no output before include)
        // Ensure progress table exists via database manager (avoids top-level side effects)
        $database_manager = new BWD_Database_Manager();
        $table_exists = $database_manager->ensure_progress_table_exists();

        // Current statistics
        $total_products = get_option('bwd_total_products', 0);
        $processed_products = get_option('bwd_processed_products', 0);
        $products_needing_normalization = get_option('bwd_products_needing_normalization', 0);
        $completed = get_option('bwd_batch_update_completed', false);
        $percentage = $total_products > 0 ? round(($processed_products / $total_products) * 100, 2) : 0;

        // Recent logs
        global $wpdb;
        $table_name = $wpdb->prefix . 'bwd_progress';
        $recent_logs = array();
        if ($table_exists) {
            $recent_logs = $wpdb->get_results(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10"
            );
        }

        include BWD_PLUGIN_PATH . 'admin/partials/searchdoon-admin-display.php';
    }
}
