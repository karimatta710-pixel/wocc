<?php
class MLM_Database {
    
    public static function activate() {
        self::create_tables();
        self::create_roles();
        self::schedule_events();
    }
    
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $tables = array(
            "{$wpdb->prefix}mlm_members" => "
                CREATE TABLE {$wpdb->prefix}mlm_members (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    user_id BIGINT(20) NOT NULL,
                    sponsor_id BIGINT(20) DEFAULT 0,
                    referral_code VARCHAR(50) NOT NULL,
                    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    total_commissions DECIMAL(10,2) DEFAULT 0.00,
                    pending_commissions DECIMAL(10,2) DEFAULT 0.00,
                    paid_commissions DECIMAL(10,2) DEFAULT 0.00,
                    PRIMARY KEY (id),
                    UNIQUE KEY user_id (user_id),
                    UNIQUE KEY referral_code (referral_code),
                    KEY sponsor_id (sponsor_id)
                ) $charset_collate;
            ",
            
            "{$wpdb->prefix}mlm_trees" => "
                CREATE TABLE {$wpdb->prefix}mlm_trees (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    member_id BIGINT(20) NOT NULL,
                    tree_name VARCHAR(100) DEFAULT 'main',
                    level1_left BIGINT(20) DEFAULT 0,
                    level1_right BIGINT(20) DEFAULT 0,
                    level2_left BIGINT(20) DEFAULT 0,
                    level2_right BIGINT(20) DEFAULT 0,
                    level3_left BIGINT(20) DEFAULT 0,
                    level3_right BIGINT(20) DEFAULT 0,
                    total_members INT DEFAULT 0,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY member_id (member_id),
                    KEY tree_name (tree_name)
                ) $charset_collate;
            ",
            
            "{$wpdb->prefix}mlm_commissions" => "
                CREATE TABLE {$wpdb->prefix}mlm_commissions (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    order_id BIGINT(20) NOT NULL,
                    member_id BIGINT(20) NOT NULL,
                    sponsor_id BIGINT(20) NOT NULL,
                    level INT NOT NULL,
                    commission_amount DECIMAL(10,2) NOT NULL,
                    commission_rate DECIMAL(5,2) NOT NULL,
                    order_total DECIMAL(10,2) NOT NULL,
                    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
                    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    paid_date DATETIME NULL,
                    PRIMARY KEY (id),
                    KEY order_id (order_id),
                    KEY member_id (member_id),
                    KEY sponsor_id (sponsor_id),
                    KEY status (status)
                ) $charset_collate;
            ",
            
            "{$wpdb->prefix}mlm_rewards" => "
                CREATE TABLE {$wpdb->prefix}mlm_rewards (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    member_id BIGINT(20) NOT NULL,
                    trees_completed INT DEFAULT 0,
                    reward_amount DECIMAL(10,2) NOT NULL,
                    total_rewards DECIMAL(10,2) DEFAULT 0.00,
                    status ENUM('pending', 'paid') DEFAULT 'pending',
                    achieved_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                    paid_date DATETIME NULL,
                    PRIMARY KEY (id),
                    KEY member_id (member_id)
                ) $charset_collate;
            ",
            
            "{$wpdb->prefix}mlm_settings" => "
                CREATE TABLE {$wpdb->prefix}mlm_settings (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    setting_key VARCHAR(100) NOT NULL,
                    setting_value LONGTEXT NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY setting_key (setting_key)
                ) $charset_collate;
            "
        );
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $table_name => $sql) {
            dbDelta($sql);
        }
        
        // إعدادات افتراضية
        $default_settings = array(
            'min_purchase_amount' => 10000,
            'commission_levels' => array(
                1 => 10.0,
                2 => 2.5,
                3 => 1.25
            ),
            'tree_structure' => array(
                'level1_count' => 2,
                'level2_count' => 4,
                'level3_count' => 8
            ),
            'reward_structure' => array(
                1 => 2000,
                2 => 3500,
                4 => 6500,
                6 => 9500,
                8 => 12500,
                10 => 15500,
                15 => 23000,
                20 => 30500,
                25 => 38000
            ),
            'auto_join' => 'yes'
        );
        
        foreach ($default_settings as $key => $value) {
            self::update_setting($key, $value);
        }
    }
    
    private static function create_roles() {
        add_role('mlm_member', __('MLM Member', 'mlm-wc'), array(
            'read' => true,
        ));
    }
    
    private static function schedule_events() {
        if (!wp_next_scheduled('mlm_daily_commissions')) {
            wp_schedule_event(time(), 'daily', 'mlm_daily_commissions');
        }
    }
    
    public static function update_setting($key, $value) {
        global $wpdb;
        
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }
        
        $wpdb->replace(
            $wpdb->prefix . 'mlm_settings',
            array(
                'setting_key' => $key,
                'setting_value' => $value
            ),
            array('%s', '%s')
        );
    }
    
    public static function get_setting($key, $default = '') {
        global $wpdb;
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$wpdb->prefix}mlm_settings WHERE setting_key = %s",
            $key
        ));
        
        if ($value === null) {
            return $default;
        }
        
        $unserialized = @unserialize($value);
        if ($unserialized !== false) {
            return $unserialized;
        }
        
        return $value;
    }
}
?>