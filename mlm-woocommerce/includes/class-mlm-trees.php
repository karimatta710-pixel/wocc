<?php
class MLM_Trees {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('mlm_member_joined', array($this, 'add_member_to_tree'), 10, 2);
    }
    
    public function add_member_to_tree($member_id, $sponsor_id) {
        $sponsor_tree = $this->get_member_active_tree($sponsor_id);
        
        if (!$sponsor_tree) {
            return false;
        }
        
        // إيجاد المكان المناسب في الشجرة
        $position = $this->find_available_position($sponsor_tree);
        
        if (!$position) {
            return false;
        }
        
        // إضافة العضو للشجرة
        return $this->insert_member_into_tree($sponsor_tree->id, $member_id, $position);
    }
    
    private function get_member_active_tree($member_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_trees WHERE member_id = %d AND is_active = 1",
            $member_id
        ));
    }
    
    private function find_available_position($tree) {
        // البحث عن أول موضع شاغر في المستويات
        for ($level = 1; $level <= 3; $level++) {
            $current_count = $this->get_level_member_count($tree->id, $level);
            $max_count = $this->get_level_max_count($level);
            
            if ($current_count < $max_count) {
                return array(
                    'level' => $level,
                    'position' => $current_count + 1
                );
            }
        }
        
        return null;
    }
    
    private function get_level_member_count($tree_id, $level) {
        global $wpdb;
        
        $field = "level{$level}_left";
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_trees WHERE id = %d AND $field > 0",
            $tree_id
        ));
    }
    
    private function get_level_max_count($level) {
        $tree_structure = MLM_Database::get_setting('tree_structure', array());
        
        switch ($level) {
            case 1: return $tree_structure['level1_count'] ?? 2;
            case 2: return $tree_structure['level2_count'] ?? 4;
            case 3: return $tree_structure['level3_count'] ?? 8;
            default: return 0;
        }
    }
    
    private function insert_member_into_tree($tree_id, $member_id, $position) {
        global $wpdb;
        
        $field = "level{$position['level']}_left";
        $value = $position['position'];
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mlm_trees',
            array($field => $value),
            array('id' => $tree_id),
            array('%d'),
            array('%d')
        );
        
        if ($result) {
            // تحديث العدد الإجمالي للأعضاء
            $this->update_tree_total_members($tree_id);
            
            // التحقق من اكتمال الشجرة
            $this->check_tree_completion($tree_id);
        }
        
        return $result;
    }
    
    private function update_tree_total_members($tree_id) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mlm_trees 
            SET total_members = (
                SELECT COUNT(*) FROM (
                    SELECT level1_left FROM {$wpdb->prefix}mlm_trees WHERE id = %d AND level1_left > 0
                    UNION ALL
                    SELECT level2_left FROM {$wpdb->prefix}mlm_trees WHERE id = %d AND level2_left > 0
                    UNION ALL
                    SELECT level3_left FROM {$wpdb->prefix}mlm_trees WHERE id = %d AND level3_left > 0
                ) AS members
            ) WHERE id = %d",
            $tree_id, $tree_id, $tree_id, $tree_id
        ));
    }
    
    private function check_tree_completion($tree_id) {
        $tree = $this->get_tree_by_id($tree_id);
        $required_members = $this->get_required_tree_members();
        
        if ($tree->total_members >= $required_members) {
            $this->mark_tree_completed($tree_id);
            $this->create_new_tree($tree->member_id);
        }
    }
    
    private function get_tree_by_id($tree_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_trees WHERE id = %d",
            $tree_id
        ));
    }
    
    private function get_required_tree_members() {
        $tree_structure = MLM_Database::get_setting('tree_structure', array());
        return array_sum($tree_structure);
    }
    
    private function mark_tree_completed($tree_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'mlm_trees',
            array('is_active' => false),
            array('id' => $tree_id),
            array('%d'),
            array('%d')
        );
    }
    
    private function create_new_tree($member_id) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_trees',
            array(
                'member_id' => $member_id,
                'tree_name' => 'tree_' . time(),
                'created_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public function get_member_tree_structure($member_id) {
        $tree = $this->get_member_active_tree($member_id);
        
        if (!$tree) {
            return array();
        }
        
        return array(
            'level1' => $this->get_level_members($tree->id, 1),
            'level2' => $this->get_level_members($tree->id, 2),
            'level3' => $this->get_level_members($tree->id, 3)
        );
    }
    
    private function get_level_members($tree_id, $level) {
        global $wpdb;
        
        $field = "level{$level}_left";
        
        $members = $wpdb->get_col($wpdb->prepare(
            "SELECT $field FROM {$wpdb->prefix}mlm_trees WHERE id = %d AND $field > 0",
            $tree_id
        ));
        
        $result = array();
        foreach ($members as $position) {
            $member_id = $this->get_member_by_tree_position($tree_id, $level, $position);
            if ($member_id) {
                $result[] = array(
                    'member_id' => $member_id,
                    'position' => $position
                );
            }
        }
        
        return $result;
    }
    
    private function get_member_by_tree_position($tree_id, $level, $position) {
        // هذا يحتاج لتطبيق حسب هيكل البيانات
        // تنفيذ مبسط للتوضيح
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT member_id FROM {$wpdb->prefix}mlm_tree_positions 
            WHERE tree_id = %d AND level = %d AND position = %d",
            $tree_id, $level, $position
        ));
    }
}
?>