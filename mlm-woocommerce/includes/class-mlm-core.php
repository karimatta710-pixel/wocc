<?php
class MLM_Core {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('user_register', array($this, 'handle_new_user'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'handle_order_placement'));
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completion'));
        add_action('mlm_daily_commissions', array($this, 'process_daily_commissions'));
    }
    
    public function handle_new_user($user_id) {
        $referral_code = $this->generate_referral_code($user_id);
        
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_members',
            array(
                'user_id' => $user_id,
                'referral_code' => $referral_code,
                'join_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s')
        );
        
        // إنشاء الشجرة الأولى
        $this->create_initial_tree($user_id);
    }
    
    private function generate_referral_code($user_id) {
        $user = get_userdata($user_id);
        $base_code = substr(preg_replace('/[^a-z]/i', '', $user->user_login), 0, 5);
        $random = strtoupper(substr(md5($user_id . time()), 0, 3));
        
        return $base_code . $random . $user_id;
    }
    
    private function create_initial_tree($user_id) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_trees',
            array(
                'member_id' => $user_id,
                'tree_name' => 'main',
                'created_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s')
        );
    }
    
    public function handle_order_placement($order_id) {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        
        if (!$user_id) {
            return;
        }
        
        // التحقق مما إذا كان المستخدم عضو في النظام
        $member = $this->get_member_by_user_id($user_id);
        if (!$member) {
            return;
        }
        
        // حفظ رابط الإحالة إذا وجد
        $referral_code = sanitize_text_field($_GET['ref'] ?? '');
        if ($referral_code) {
            update_post_meta($order_id, '_mlm_referral_code', $referral_code);
        }
    }
    
    public function handle_order_completion($order_id) {
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        $min_purchase = MLM_Database::get_setting('min_purchase_amount', 10000);
        
        if ($order_total < $min_purchase) {
            return;
        }
        
        $user_id = $order->get_user_id();
        $referral_code = get_post_meta($order_id, '_mlm_referral_code', true);
        
        if ($referral_code) {
            $this->process_commission($order_id, $user_id, $referral_code);
        }
        
        // التحقق من أهلية الانضمام التلقائي
        $auto_join = MLM_Database::get_setting('auto_join', 'yes');
        if ($auto_join === 'yes') {
            $this->maybe_auto_join_member($user_id, $order_id);
        }
    }
    
    private function process_commission($order_id, $user_id, $referral_code) {
        $sponsor = $this->get_member_by_referral_code($referral_code);
        if (!$sponsor) {
            return;
        }
        
        $commission_levels = MLM_Database::get_setting('commission_levels', array());
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        // حساب العمولات للمستويات المختلفة
        $current_sponsor_id = $sponsor->id;
        
        for ($level = 1; $level <= 3; $level++) {
            if (!isset($commission_levels[$level]) || $commission_levels[$level] <= 0) {
                continue;
            }
            
            $commission_amount = ($order_total * $commission_levels[$level]) / 100;
            
            if ($commission_amount > 0) {
                $this->add_commission(
                    $order_id,
                    $user_id,
                    $current_sponsor_id,
                    $level,
                    $commission_amount,
                    $commission_levels[$level],
                    $order_total
                );
            }
            
            // الانتقال للراعي في المستوى التالي
            $next_sponsor = $this->get_member_by_id($current_sponsor_id);
            if (!$next_sponsor || $next_sponsor->sponsor_id == 0) {
                break;
            }
            
            $current_sponsor_id = $next_sponsor->sponsor_id;
        }
    }
    
    private function add_commission($order_id, $member_id, $sponsor_id, $level, $amount, $rate, $order_total) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_commissions',
            array(
                'order_id' => $order_id,
                'member_id' => $member_id,
                'sponsor_id' => $sponsor_id,
                'level' => $level,
                'commission_amount' => $amount,
                'commission_rate' => $rate,
                'order_total' => $order_total,
                'created_date' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%f', '%f', '%f', '%s')
        );
        
        // تحديث إحصائيات العضو
        $this->update_member_stats($sponsor_id, $amount);
    }
    
    private function update_member_stats($member_id, $commission_amount) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mlm_members 
            SET total_commissions = total_commissions + %f, 
                pending_commissions = pending_commissions + %f 
            WHERE id = %d",
            $commission_amount,
            $commission_amount,
            $member_id
        ));
    }
    
    private function maybe_auto_join_member($user_id, $order_id) {
        $member = $this->get_member_by_user_id($user_id);
        
        if (!$member) {
            // انضمام تلقائي
            $this->handle_new_user($user_id);
        }
    }
    
    public function process_daily_commissions() {
        // معالجة العمولات اليومية
        $this->process_pending_commissions();
        $this->check_rewards_eligibility();
    }
    
    private function process_pending_commissions() {
        // معالجة العمولات المعلقة وتحويلها للدفع
        // يمكن إضافة منطق الدفع هنا
    }
    
    private function check_rewards_eligibility() {
        // التحقق من أهلية المكافآت
        global $wpdb;
        
        $members = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mlm_members WHERE status = 'active'");
        
        foreach ($members as $member) {
            $completed_trees = $this->count_completed_trees($member->id);
            $this->check_and_award_rewards($member->id, $completed_trees);
        }
    }
    
    public function get_member_by_user_id($user_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_members WHERE user_id = %d",
            $user_id
        ));
    }
    
    public function get_member_by_id($member_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_members WHERE id = %d",
            $member_id
        ));
    }
    
    public function get_member_by_referral_code($referral_code) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_members WHERE referral_code = %s",
            $referral_code
        ));
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
    
    private function check_and_award_rewards($member_id, $completed_trees) {
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
    
    private function get_member_rewards($member_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_rewards WHERE member_id = %d ORDER BY trees_completed ASC",
            $member_id
        ));
    }
    
    private function award_reward($member_id, $trees_completed, $reward_amount) {
        global $wpdb;
        
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
    }
}
?>