<?php
/**
 * Plugin Name: MLM WooCommerce System
 * Description: نظام العمولات متعدد المستويات لمتجر WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: mlm-wc
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

// تعريف ثوابت
define('MLM_WC_VERSION', '1.0.0');
define('MLM_WC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MLM_WC_PLUGIN_PATH', plugin_dir_path(__FILE__));

class MLM_WooCommerce {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init();
    }
    
    private function init() {
        // تحميل الملفات
        $this->includes();
        
        // تنشيط الإضافة
        register_activation_hook(__FILE__, array('MLM_Database', 'activate'));
        
        // إلغاء تنشيط الإضافة
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // تهيئة الإضافة
        add_action('plugins_loaded', array($this, 'setup'));
        
        // تحميل النص
        add_action('init', array($this, 'load_textdomain'));
    }
    
    private function includes() {
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-database.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-core.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-commissions.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-trees.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-rewards.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-admin.php';
        require_once MLM_WC_PLUGIN_PATH . 'includes/class-mlm-frontend.php';
    }
    
    public function setup() {
        MLM_Core::get_instance();
        MLM_Commissions::get_instance();
        MLM_Trees::get_instance();
        MLM_Rewards::get_instance();
        MLM_Admin::get_instance();
        MLM_Frontend::get_instance();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('mlm-wc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function deactivate() {
        // تنظيف البيانات المؤقتة
        wp_clear_scheduled_hook('mlm_daily_commissions');
    }
}

// تشغيل النظام
function MLM_WC() {
    return MLM_WooCommerce::get_instance();
}

// البدء
MLM_WC();
?>