<?php
class MLM_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_mlm_admin_action', array($this, 'handle_ajax_request'));
    }
    
    public function admin_menu() {
        add_menu_page(
            __('نظام العمولات', 'mlm-wc'),
            __('العمولات المتعددة', 'mlm-wc'),
            'manage_options',
            'mlm-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-networking',
            30
        );
        
        add_submenu_page(
            'mlm-dashboard',
            __('لوحة التحكم', 'mlm-wc'),
            __('لوحة التحكم', 'mlm-wc'),
            'manage_options',
            'mlm-dashboard',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'mlm-dashboard',
            __('إدارة الأعضاء', 'mlm-wc'),
            __('إدارة الأعضاء', 'mlm-wc'),
            'manage_options',
            'mlm-members',
            array($this, 'members_page')
        );
        
        add_submenu_page(
            'mlm-dashboard',
            __('العمولات', 'mlm-wc'),
            __('العمولات', 'mlm-wc'),
            'manage_options',
            'mlm-commissions',
            array($this, 'commissions_page')
        );
        
        add_submenu_page(
            'mlm-dashboard',
            __('المكافآت', 'mlm-wc'),
            __('المكافآت', 'mlm-wc'),
            'manage_options',
            'mlm-rewards',
            array($this, 'rewards_page')
        );
        
        add_submenu_page(
            'mlm-dashboard',
            __('الإعدادات', 'mlm-wc'),
            __('الإعدادات', 'mlm-wc'),
            'manage_options',
            'mlm-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_scripts($hook) {
        if (strpos($hook, 'mlm-') === false) {
            return;
        }
        
        wp_enqueue_style('mlm-admin-css', MLM_WC_PLUGIN_URL . 'assets/css/admin.css', array(), MLM_WC_VERSION);
        wp_enqueue_script('mlm-admin-js', MLM_WC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MLM_WC_VERSION, true);
        
        wp_localize_script('mlm-admin-js', 'mlm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mlm_admin_nonce'),
            'confirm_delete' => __('هل أنت متأكد من الحذف؟', 'mlm-wc'),
            'confirm_pay' => __('هل تريد فعلاً دفع هذه المكافأة؟', 'mlm-wc')
        ));
    }
    
    public function dashboard_page() {
        $stats = $this->get_dashboard_stats();
        include MLM_WC_PLUGIN_PATH . 'templates/admin/dashboard.php';
    }
    
    public function members_page() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'edit':
                $this->edit_member_page();
                break;
            case 'view':
                $this->view_member_page();
                break;
            default:
                $this->list_members_page();
        }
    }
    
    public function commissions_page() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'pay':
                $this->pay_commission_page();
                break;
            default:
                $this->list_commissions_page();
        }
    }
    
    public function rewards_page() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'pay':
                $this->pay_reward_page();
                break;
            default:
                $this->list_rewards_page();
        }
    }
    
    public function settings_page() {
        if ($_POST['save_settings'] ?? false) {
            $this->save_settings();
        }
        
        $settings = $this->get_settings();
        include MLM_WC_PLUGIN_PATH . 'templates/admin/settings.php';
    }
    
    private function get_dashboard_stats() {
        global $wpdb;
        
        return array(
            'total_members' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members"),
            'active_members' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members WHERE status = 'active'"),
            'total_commissions' => $wpdb->get_var("SELECT SUM(commission_amount) FROM {$wpdb->prefix}mlm_commissions"),
            'pending_commissions' => $wpdb->get_var("SELECT SUM(commission_amount) FROM {$wpdb->prefix}mlm_commissions WHERE status = 'pending'"),
            'total_rewards' => $wpdb->get_var("SELECT SUM(reward_amount) FROM {$wpdb->prefix}mlm_rewards"),
            'pending_rewards' => $wpdb->get_var("SELECT SUM(reward_amount) FROM {$wpdb->prefix}mlm_rewards WHERE status = 'pending'"),
            'completed_trees' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_trees WHERE is_active = 0")
        );
    }
    
    private function list_members_page() {
        global $wpdb;
        
        $page = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $search = $_GET['s'] ?? '';
        
        $where = '';
        if ($search) {
            $where = $wpdb->prepare(" AND (u.user_login LIKE %s OR u.user_email LIKE %s OR m.referral_code LIKE %s)", 
                '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
        }
        
        $members = $wpdb->get_results("
            SELECT m.*, u.user_login, u.user_email, u.display_name 
            FROM {$wpdb->prefix}mlm_members m 
            LEFT JOIN {$wpdb->prefix}users u ON m.user_id = u.ID 
            WHERE 1=1 {$where}
            ORDER BY m.join_date DESC 
            LIMIT {$offset}, {$per_page}
        ");
        
        $total_members = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members m WHERE 1=1 {$where}");
        
        include MLM_WC_PLUGIN_PATH . 'templates/admin/members-list.php';
    }
    
    private function view_member_page() {
        $member_id = intval($_GET['member_id']);
        $member = MLM_Core::get_instance()->get_member_by_id($member_id);
        
        if (!$member) {
            wp_die(__('العضو غير موجود', 'mlm-wc'));
        }
        
        $user = get_userdata($member->user_id);
        $commissions = MLM_Commissions::get_instance()->get_member_commissions($member_id);
        $rewards = MLM_Rewards::get_instance()->get_member_rewards($member_id);
        $tree_structure = MLM_Trees::get_instance()->get_member_tree_structure($member_id);
        
        include MLM_WC_PLUGIN_PATH . 'templates/admin/member-view.php';
    }
    
    private function list_commissions_page() {
        global $wpdb;
        
        $page = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $status = $_GET['status'] ?? 'all';
        
        $where = '';
        if ($status !== 'all') {
            $where = $wpdb->prepare(" AND c.status = %s", $status);
        }
        
        $commissions = $wpdb->get_results("
            SELECT c.*, u.user_login, u.display_name, s.user_login as sponsor_name,
                   o.ID as order_id, o.total as order_total
            FROM {$wpdb->prefix}mlm_commissions c 
            LEFT JOIN {$wpdb->prefix}users u ON c.member_id = u.ID 
            LEFT JOIN {$wpdb->prefix}users s ON c.sponsor_id = s.ID 
            LEFT JOIN {$wpdb->prefix}posts o ON c.order_id = o.ID 
            WHERE 1=1 {$where}
            ORDER BY c.created_date DESC 
            LIMIT {$offset}, {$per_page}
        ");
        
        $total_commissions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_commissions c WHERE 1=1 {$where}");
        
        include MLM_WC_PLUGIN_PATH . 'templates/admin/commissions-list.php';
    }
    
    private function list_rewards_page() {
        global $wpdb;
        
        $page = $_GET['paged'] ?? 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $status = $_GET['status'] ?? 'all';
        
        $where = '';
        if ($status !== 'all') {
            $where = $wpdb->prepare(" AND r.status = %s", $status);
        }
        
        $rewards = $wpdb->get_results("
            SELECT r.*, u.user_login, u.display_name 
            FROM {$wpdb->prefix}mlm_rewards r 
            LEFT JOIN {$wpdb->prefix}mlm_members m ON r.member_id = m.id 
            LEFT JOIN {$wpdb->prefix}users u ON m.user_id = u.ID 
            WHERE 1=1 {$where}
            ORDER BY r.achieved_date DESC 
            LIMIT {$offset}, {$per_page}
        ");
        
        $total_rewards = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mlm_rewards r WHERE 1=1 {$where}");
        
        include MLM_WC_PLUGIN_PATH . 'templates/admin/rewards-list.php';
    }
    
    private function get_settings() {
        return array(
            'min_purchase_amount' => MLM_Database::get_setting('min_purchase_amount', 10000),
            'commission_levels' => MLM_Database::get_setting('commission_levels', array()),
            'tree_structure' => MLM_Database::get_setting('tree_structure', array()),
            'reward_structure' => MLM_Database::get_setting('reward_structure', array()),
            'auto_join' => MLM_Database::get_setting('auto_join', 'yes')
        );
    }
    
    private function save_settings() {
        check_admin_referer('mlm_save_settings');
        
        $settings = array(
            'min_purchase_amount' => floatval($_POST['min_purchase_amount']),
            'commission_levels' => array(
                1 => floatval($_POST['commission_level_1']),
                2 => floatval($_POST['commission_level_2']),
                3 => floatval($_POST['commission_level_3'])
            ),
            'tree_structure' => array(
                'level1_count' => intval($_POST['level1_count']),
                'level2_count' => intval($_POST['level2_count']),
                'level3_count' => intval($_POST['level3_count'])
            ),
            'reward_structure' => array(),
            'auto_join' => $_POST['auto_join'] ?? 'no'
        );
        
        // حفظ هيكل المكافآت
        if (!empty($_POST['reward_trees']) && !empty($_POST['reward_amounts'])) {
            foreach ($_POST['reward_trees'] as $key => $trees) {
                if (!empty($trees) && !empty($_POST['reward_amounts'][$key])) {
                    $settings['reward_structure'][intval($trees)] = floatval($_POST['reward_amounts'][$key]);
                }
            }
        }
        
        foreach ($settings as $key => $value) {
            MLM_Database::update_setting($key, $value);
        }
        
        wp_redirect(admin_url('admin.php?page=mlm-settings&saved=1'));
        exit;
    }
    
    public function handle_ajax_request() {
        check_ajax_referer('mlm_admin_nonce', 'nonce');
        
        $action = $_POST['mlm_action'] ?? '';
        
        switch ($action) {
            case 'pay_commission':
                $this->ajax_pay_commission();
                break;
            case 'pay_reward':
                $this->ajax_pay_reward();
                break;
            case 'get_member_tree':
                $this->ajax_get_member_tree();
                break;
        }
        
        wp_die();
    }
    
    private function ajax_pay_commission() {
        $commission_id = intval($_POST['commission_id']);
        
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mlm_commissions',
            array(
                'status' => 'paid',
                'paid_date' => current_time('mysql')
            ),
            array('id' => $commission_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success(__('تم دفع العمولة بنجاح', 'mlm-wc'));
        } else {
            wp_send_json_error(__('فشل في دفع العمولة', 'mlm-wc'));
        }
    }
    
    private function ajax_pay_reward() {
        $reward_id = intval($_POST['reward_id']);
        
        $result = MLM_Rewards::get_instance()->pay_reward($reward_id);
        
        if ($result) {
            wp_send_json_success(__('تم دفع المكافأة بنجاح', 'mlm-wc'));
        } else {
            wp_send_json_error(__('فشل في دفع المكافأة', 'mlm-wc'));
        }
    }
    
    private function ajax_get_member_tree() {
        $member_id = intval($_POST['member_id']);
        $tree_data = MLM_Trees::get_instance()->get_member_tree_structure($member_id);
        
        wp_send_json_success($tree_data);
    }
}
?>