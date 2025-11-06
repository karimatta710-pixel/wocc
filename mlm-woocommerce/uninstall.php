<?php
/**
 * Uninstall MLM WooCommerce System
 * 
 * @package MLM_WooCommerce
 */

// إذا لم يتم الوصول من خلال ووردبريس، الخروج
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// التحقق من الصلاحيات
if (!current_user_can('activate_plugins')) {
    return;
}

// خيارات للحذف
$delete_data = get_option('mlm_delete_data_on_uninstall', 'no');

if ($delete_data !== 'yes') {
    return;
}

global $wpdb;

// الجداول التي سيتم حذفها
$tables = array(
    $wpdb->prefix . 'mlm_members',
    $wpdb->prefix . 'mlm_trees',
    $wpdb->prefix . 'mlm_commissions',
    $wpdb->prefix . 'mlm_rewards',
    $wpdb->prefix . 'mlm_settings'
);

// حذف الجداول
foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// حذف خيارات ووردبريس
$options = array(
    'mlm_delete_data_on_uninstall',
    'mlm_version',
    'mlm_db_version',
    'mlm_min_purchase_amount',
    'mlm_commission_levels',
    'mlm_tree_structure',
    'mlm_reward_structure',
    'mlm_auto_join'
);

foreach ($options as $option) {
    delete_option($option);
}

// حذف بيانات ميتا للمستخدمين
$wpdb->query(
    "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%mlm_%'"
);

// حذف بيانات ميتا للطلبات
$wpdb->query(
    "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%_mlm_%'"
);

// إزالة الأدوار المخصصة
remove_role('mlm_member');

// إزالة الأحداث المجدولة
wp_clear_scheduled_hook('mlm_daily_commissions');
wp_clear_scheduled_hook('mlm_weekly_reports');
wp_clear_scheduled_hook('mlm_monthly_reports');

// مسح ذاكرة التخزين المؤقت
wp_cache_flush();

// تسجيل عملية الإلغاء
error_log('MLM WooCommerce System uninstalled successfully');

// إعادة توجيه إذا كان ذلك ممكناً
if (wp_get_referer()) {
    wp_safe_redirect(wp_get_referer());
    exit;
}
?>