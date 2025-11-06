<?php
class MLM_Frontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('init', array($this, 'init'));
        add_shortcode('mlm_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('mlm_referral_link', array($this, 'referral_link_shortcode'));
        add_shortcode('mlm_tree_view', array($this, 'tree_view_shortcode'));
    }
    
    public function init() {
        // إضافة صفحة حساب العمولات
        add_rewrite_endpoint('mlm-dashboard', EP_ROOT | EP_PAGES);
        
        // ربط الإجراءات
        add_action('woocommerce_account_mlm-dashboard_endpoint', array($this, 'dashboard_endpoint'));
    }
    
    public function frontend_scripts() {
        if (is_account_page()) {
            wp_enqueue_style('mlm-frontend-css', MLM_WC_PLUGIN_URL . 'assets/css/frontend.css', array(), MLM_WC_VERSION);
            wp_enqueue_script('mlm-frontend-js', MLM_WC_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), MLM_WC_VERSION, true);
            
            wp_localize_script('mlm-frontend-js', 'mlm_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mlm_frontend_nonce')
            ));
        }
    }
    
    public function dashboard_shortcode($atts) {
        if (!is_user_logged_in()) {
            return __('يجب تسجيل الدخول لعرض هذه الصفحة', 'mlm-wc');
        }
        
        ob_start();
        $this->display_dashboard();
        return ob_get_clean();
    }
    
    public function referral_link_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $user_id = get_current_user_id();
        $member = MLM_Core::get_instance()->get_member_by_user_id($user_id);
        
        if (!$member) {
            return '';
        }
        
        $atts = shortcode_atts(array(
            'text' => __('انضم من خلال رابط الإحالة الخاص بي', 'mlm-wc'),
            'class' => 'mlm-referral-link'
        ), $atts);
        
        $referral_url = add_query_arg('ref', $member->referral_code, home_url());
        
        return sprintf(
            '<a href="%s" class="%s" target="_blank">%s</a>',
            esc_url($referral_url),
            esc_attr($atts['class']),
            esc_html($atts['text'])
        );
    }
    
    public function tree_view_shortcode($atts) {
        if (!is_user_logged_in()) {
            return __('يجب تسجيل الدخول لعرض هذه الصفحة', 'mlm-wc');
        }
        
        ob_start();
        $this->display_tree_view();
        return ob_get_clean();
    }
    
    public function dashboard_endpoint() {
        $this->display_dashboard();
    }
    
    private function display_dashboard() {
        $user_id = get_current_user_id();
        $member = MLM_Core::get_instance()->get_member_by_user_id($user_id);
        
        if (!$member) {
            echo '<div class="mlm-not-member">';
            echo '<p>' . __('أنت لست عضواً في نظام العمولات بعد.', 'mlm-wc') . '</p>';
            echo '</div>';
            return;
        }
        
        $commissions = MLM_Commissions::get_instance()->get_member_commissions($member->id);
        $rewards = MLM_Rewards::get_instance()->get_member_rewards($member->id);
        $reward_progress = MLM_Rewards::get_instance()->get_reward_progress($member->id);
        
        include MLM_WC_PLUGIN_PATH . 'templates/frontend/dashboard.php';
    }
    
    private function display_tree_view() {
        $user_id = get_current_user_id();
        $member = MLM_Core::get_instance()->get_member_by_user_id($user_id);
        
        if (!$member) {
            echo '<div class="mlm-not-member">';
            echo '<p>' . __('أنت لست عضواً في نظام العمولات بعد.', 'mlm-wc') . '</p>';
            echo '</div>';
            return;
        }
        
        $tree_structure = MLM_Trees::get_instance()->get_member_tree_structure($member->id);
        
        include MLM_WC_PLUGIN_PATH . 'templates/frontend/tree-view.php';
    }
    
    public function handle_ajax_request() {
        check_ajax_referer('mlm_frontend_nonce', 'nonce');
        
        $action = $_POST['mlm_action'] ?? '';
        $user_id = get_current_user_id();
        $member = MLM_Core::get_instance()->get_member_by_user_id($user_id);
        
        if (!$member) {
            wp_send_json_error(__('غير مصرح', 'mlm-wc'));
        }
        
        switch ($action) {
            case 'get_tree_data':
                $this->ajax_get_tree_data($member->id);
                break;
            case 'get_commissions':
                $this->ajax_get_commissions($member->id);
                break;
        }
        
        wp_die();
    }
    
    private function ajax_get_tree_data($member_id) {
        $tree_data = MLM_Trees::get_instance()->get_member_tree_structure($member_id);
        wp_send_json_success($tree_data);
    }
    
    private function ajax_get_commissions($member_id) {
        $commissions = MLM_Commissions::get_instance()->get_member_commissions($member_id);
        
        $data = array();
        foreach ($commissions as $commission) {
            $data[] = array(
                'order_id' => $commission->order_id,
                'amount' => $commission->commission_amount,
                'rate' => $commission->commission_rate,
                'level' => $commission->level,
                'date' => $commission->created_date,
                'status' => $commission->status
            );
        }
        
        wp_send_json_success($data);
    }
}
?>