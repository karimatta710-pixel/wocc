<?php
class MLM_Commissions {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // إجراءات العمولات
    }
    
    public function calculate_commission($order_total, $level) {
        $commission_rates = MLM_Database::get_setting('commission_levels', array());
        
        if (!isset($commission_rates[$level])) {
            return 0;
        }
        
        return ($order_total * $commission_rates[$level]) / 100;
    }
    
    public function get_member_commissions($member_id, $status = 'all') {
        global $wpdb;
        
        $query = "SELECT * FROM {$wpdb->prefix}mlm_commissions WHERE sponsor_id = %d";
        $params = array($member_id);
        
        if ($status !== 'all') {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        $query .= " ORDER BY created_date DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }
    
    public function get_total_commissions($member_id, $status = 'all') {
        global $wpdb;
        
        $query = "SELECT SUM(commission_amount) FROM {$wpdb->prefix}mlm_commissions WHERE sponsor_id = %d";
        $params = array($member_id);
        
        if ($status !== 'all') {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        return $wpdb->get_var($wpdb->prepare($query, $params)) ?: 0;
    }
}
?>