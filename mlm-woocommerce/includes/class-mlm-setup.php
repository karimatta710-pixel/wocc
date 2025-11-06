<?php
class MLM_Setup {
    
    public static function check_requirements() {
        $errors = array();
        
        // التحقق من وجود ووردبريس
        if (!function_exists('add_action')) {
            $errors[] = 'هذا الملف لا يمكن الوصول إليه مباشرة.';
        }
        
        // التحقق من إصدار PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $errors[] = 'يتطلب النظام PHP 7.4 أو أعلى. الإصدار الحالي: ' . PHP_VERSION;
        }
        
        // التحقق من وجود WooCommerce
        if (!class_exists('WooCommerce')) {
            $errors[] = 'يجب تثبيت وتفعيل إضافة WooCommerce أولاً.';
        }
        
        // التحقق من إصدار WooCommerce
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '5.0', '<')) {
            $errors[] = 'يتطلب النظام WooCommerce 5.0 أو أعلى.';
        }
        
        return $errors;
    }
    
    public static function create_pages() {
        $pages = array(
            'mlm-dashboard' => array(
                'title' => 'لوحة العمولات',
                'content' => '[mlm_dashboard]',
                'parent' => 'my-account'
            ),
            'mlm-tree' => array(
                'title' => 'شبكتي',
                'content' => '[mlm_tree_view]',
                'parent' => 'my-account'
            ),
            'mlm-commissions' => array(
                'title' => 'عمولاتي',
                'content' => '[mlm_commissions]',
                'parent' => 'my-account'
            )
        );
        
        foreach ($pages as $slug => $page) {
            $page_id = self::create_page($slug, $page['title'], $page['content'], $page['parent']);
            
            if ($page_id) {
                MLM_Database::update_setting($slug . '_page_id', $page_id);
            }
        }
    }
    
    private static function create_page($slug, $title, $content = '', $parent = '') {
        $page_exists = get_page_by_path($slug);
        
        if (!$page_exists) {
            $page_data = array(
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => 1
            );
            
            if ($parent) {
                $parent_page = get_page_by_path($parent);
                if ($parent_page) {
                    $page_data['post_parent'] = $parent_page->ID;
                }
            }
            
            return wp_insert_post($page_data);
        }
        
        return $page_exists->ID;
    }
    
    public static function setup_roles() {
        // إضافة دور عضو العمولات
        add_role('mlm_member', __('عضو العمولات', 'mlm-wc'), array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        ));
    }
    
    public static function setup_cron_jobs() {
        if (!wp_next_scheduled('mlm_daily_commissions')) {
            wp_schedule_event(time(), 'daily', 'mlm_daily_commissions');
        }
        
        if (!wp_next_scheduled('mlm_weekly_reports')) {
            wp_schedule_event(time(), 'weekly', 'mlm_weekly_reports');
        }
        
        if (!wp_next_scheduled('mlm_monthly_reports')) {
            wp_schedule_event(time(), 'monthly', 'mlm_monthly_reports');
        }
    }
    
    public static function cleanup_cron_jobs() {
        wp_clear_scheduled_hook('mlm_daily_commissions');
        wp_clear_scheduled_hook('mlm_weekly_reports');
        wp_clear_scheduled_hook('mlm_monthly_reports');
    }
}
?>