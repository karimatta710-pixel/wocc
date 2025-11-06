<?php
class MLM_Rewards {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('mlm_tree_completed', array($this, 'handle_tree_completion'), 10, 2);
    }
    
    public function handle_tree_completion($member_id, $tree_id) {
        $completed_trees = $this->count_completed_trees($member_id);
        $this->check_and_award_rewards($member_id, $completed_trees);
    }
    
    public function count_completed_trees($member_id) {
        global $wpdb;
        
        $tree_structure = MLM_Database::get_setting('tree_structure', array());
        $required_members = array_sum($tree_structure);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_trees 
            WHERE member_id = %d AND total_members >= %d",
            $member_id,
            $required_members
        ));
        
        return $count ?: 0;
    }
    
    public function check_and_award_rewards($member_id, $completed_trees) {
        if ($completed_trees <= 0) {
            return;
        }
        
        $reward_structure = MLM_Database::get_setting('reward_structure', array());
        $existing_rewards = $this->get_member_rewards($member_id);
        $max_achieved = 0;
        
        foreach ($existing_rewards as $reward) {
            $max_achieved = max($max_achieved, $reward->trees_completed);
        }
        
        for ($trees = $max_achieved + 1; $trees <= $completed_trees; $trees++) {
            if (isset($reward_structure[$trees])) {
                $this->award_reward($member_id, $trees, $reward_structure[$trees]);
            }
        }
    }
    
    public function get_member_rewards($member_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_rewards WHERE member_id = %d ORDER BY trees_completed ASC",
            $member_id
        ));
    }
    
    public function award_reward($member_id, $trees_completed, $reward_amount) {
        global $wpdb;
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_rewards 
            WHERE member_id = %d AND trees_completed = %d",
            $member_id,
            $trees_completed
        ));
        
        if ($existing) {
            return; // المكافأة موجودة مسبقاً
        }
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_rewards',
            array(
                'member_id' => $member_id,
                'trees_completed' => $trees_completed,
                'reward_amount' => $reward_amount,
                'achieved_date' => current_time('mysql')
            ),
            array('%d', '%d', '%f', '%s')
        );
        
        // تحديث إجمالي المكافآت
        $this->update_total_rewards($member_id);
        
        // إشعار العضو بالمكافأة
        $this->notify_member($member_id, $trees_completed, $reward_amount);
    }
    
    private function update_total_rewards($member_id) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mlm_rewards 
            SET total_rewards = (
                SELECT SUM(reward_amount) 
                FROM {$wpdb->prefix}mlm_rewards AS r 
                WHERE r.member_id = %d AND r.trees_completed <= rewards.trees_completed
            ) WHERE member_id = %d",
            $member_id,
            $member_id
        ));
    }
    
    private function notify_member($member_id, $trees_completed, $reward_amount) {
        $member = MLM_Core::get_instance()->get_member_by_id($member_id);
        $user = get_userdata($member->user_id);
        
        $subject = sprintf(__('مبروك! لقد حصلت على مكافأة جديدة - %d شجرة', 'mlm-wc'), $trees_completed);
        $message = sprintf(
            __('عزيزي %s، مبروك إكمال %d شجرة! لقد حصلت على مكافأة قدرها %s جنيهاً.', 'mlm-wc'),
            $user->display_name,
            $trees_completed,
            number_format($reward_amount, 2)
        );
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    public function get_reward_progress($member_id) {
        $completed_trees = $this->count_completed_trees($member_id);
        $reward_structure = MLM_Database::get_setting('reward_structure', array());
        $next_reward = null;
        
        ksort($reward_structure);
        
        foreach ($reward_structure as $trees => $amount) {
            if ($trees > $completed_trees) {
                $next_reward = array(
                    'trees_required' => $trees,
                    'reward_amount' => $amount,
                    'trees_needed' => $trees - $completed_trees
                );
                break;
            }
        }
        
        return array(
            'completed_trees' => $completed_trees,
            'next_reward' => $next_reward,
            'all_rewards' => $reward_structure
        );
    }
    
    public function pay_reward($reward_id) {
        global $wpdb;
        
        $reward = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_rewards WHERE id = %d",
            $reward_id
        ));
        
        if (!$reward || $reward->status === 'paid') {
            return false;
        }
        
        // تنفيذ عملية الدفع هنا (يمكن ربطها بنظام الدفع المفضل)
        $payment_success = $this->process_reward_payment($reward);
        
        if ($payment_success) {
            $wpdb->update(
                $wpdb->prefix . 'mlm_rewards',
                array(
                    'status' => 'paid',
                    'paid_date' => current_time('mysql')
                ),
                array('id' => $reward_id),
                array('%s', '%s'),
                array('%d')
            );
            
            return true;
        }
        
        return false;
    }
    
    private function process_reward_payment($reward) {
        // تنفيذ عملية الدفع
        // يمكن ربطها بـ:
        // 1. محفظة إلكترونية
        // 2. تحويل بنكي
        // 3. إضافة الرصيد لحساب العميل في الموقع
        // حالياً نعود بـ true للاختبار
        
        // TODO: تنفيذ طريقة الدفع الفعلية
        return true;
    }
}
?>