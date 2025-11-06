<?php
class MLM_Notifications {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('mlm_commission_earned', array($this, 'commission_earned'), 10, 2);
        add_action('mlm_reward_achieved', array($this, 'reward_achieved'), 10, 2);
        add_action('mlm_tree_completed', array($this, 'tree_completed'), 10, 2);
        add_action('mlm_member_joined', array($this, 'member_joined'), 10, 2);
    }
    
    public function commission_earned($member_id, $commission_data) {
        $member = MLM_Core::get_instance()->get_member_by_id($member_id);
        $user = get_userdata($member->user_id);
        
        $subject = sprintf(__('مبروك! لقد ربحت عمولة جديدة - %s ج.م', 'mlm-wc'), $commission_data['amount']);
        
        $message = sprintf(
            __('عزيزي %s، مبروك! لقد ربحت عمولة جديدة بقيمة %s ج.م من المستوى %s.', 'mlm-wc'),
            $user->display_name,
            number_format($commission_data['amount'], 2),
            $commission_data['level']
        );
        
        $this->send_email($user->user_email, $subject, $message);
        $this->add_notification($member_id, 'commission', $message);
    }
    
    public function reward_achieved($member_id, $reward_data) {
        $member = MLM_Core::get_instance()->get_member_by_id($member_id);
        $user = get_userdata($member->user_id);
        
        $subject = sprintf(__('🎉 مبروك! لقد حققت مكافأة جديدة - %s شجرة', 'mlm-wc'), $reward_data['trees']);
        
        $message = sprintf(
            __('عزيزي %s، مبروك إكمال %s شجرة! لقد حصلت على مكافأة قدرها %s ج.م.', 'mlm-wc'),
            $user->display_name,
            $reward_data['trees'],
            number_format($reward_data['amount'], 2)
        );
        
        $this->send_email($user->user_email, $subject, $message);
        $this->add_notification($member_id, 'reward', $message);
    }
    
    public function tree_completed($member_id, $tree_data) {
        $member = MLM_Core::get_instance()->get_member_by_id($member_id);
        $user = get_userdata($member->user_id);
        
        $subject = __('🌳 مبروك! لقد أكملت شجرة جديدة', 'mlm-wc');
        
        $message = sprintf(
            __('عزيزي %s، مبروك إكمال شجرة جديدة! استمر في بناء شبكتك لكسب المزيد.', 'mlm-wc'),
            $user->display_name
        );
        
        $this->send_email($user->user_email, $subject, $message);
        $this->add_notification($member_id, 'tree', $message);
    }
    
    public function member_joined($member_id, $sponsor_id) {
        if ($sponsor_id) {
            $sponsor = MLM_Core::get_instance()->get_member_by_id($sponsor_id);
            $sponsor_user = get_userdata($sponsor->user_id);
            
            $new_member = MLM_Core::get_instance()->get_member_by_id($member_id);
            $new_member_user = get_userdata($new_member->user_id);
            
            $subject = __('👋 مبروك! عضو جديد انضم لشبكتك', 'mlm-wc');
            
            $message = sprintf(
                __('عزيزي %s، مبروك! العضو %s انضم لشبكتك في المستوى الأول.', 'mlm-wc'),
                $sponsor_user->display_name,
                $new_member_user->display_name
            );
            
            $this->send_email($sponsor_user->user_email, $subject, $message);
            $this->add_notification($sponsor_id, 'member', $message);
        }
    }
    
    private function send_email($to, $subject, $message) {
        $email_enabled = MLM_Database::get_setting('email_notifications', 'yes');
        
        if ($email_enabled !== 'yes') {
            return;
        }
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $email_template = $this->get_email_template($subject, $message);
        
        wp_mail($to, $subject, $email_template, $headers);
    }
    
    private function get_email_template($subject, $message) {
        $logo_url = MLM_Database::get_setting('email_logo', '');
        $site_name = get_bloginfo('name');
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $subject; ?></title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6; 
                    color: #333; 
                    background: #f5f5f5;
                    margin: 0;
                    padding: 0;
                }
                .email-container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #ffffff; 
                }
                .email-header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                }
                .email-body { 
                    padding: 30px; 
                }
                .email-footer { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    text-align: center; 
                    color: #666; 
                    font-size: 14px;
                }
                .button { 
                    display: inline-block; 
                    background: #667eea; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                }
                .logo { 
                    max-width: 150px; 
                    margin-bottom: 20px; 
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <?php if ($logo_url): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="logo">
                    <?php endif; ?>
                    <h1><?php echo $subject; ?></h1>
                </div>
                
                <div class="email-body">
                    <?php echo wpautop($message); ?>
                    
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="<?php echo home_url(); ?>" class="button">زيارة الموقع</a>
                    </div>
                </div>
                
                <div class="email-footer">
                    <p>© <?php echo date('Y'); ?> <?php echo $site_name; ?>. جميع الحقوق محفوظة.</p>
                    <p>هذه رسالة تلقائية، يرجى عدم الرد عليها.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    private function add_notification($member_id, $type, $message) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_notifications',
            array(
                'member_id' => $member_id,
                'type' => $type,
                'message' => $message,
                'is_read' => 0,
                'created_date' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
    }
    
    public function get_member_notifications($member_id, $limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mlm_notifications 
            WHERE member_id = %d 
            ORDER BY created_date DESC 
            LIMIT %d",
            $member_id,
            $limit
        ));
    }
    
    public function mark_notification_read($notification_id) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'mlm_notifications',
            array('is_read' => 1),
            array('id' => $notification_id),
            array('%d'),
            array('%d')
        );
    }
    
    public function get_unread_count($member_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_notifications 
            WHERE member_id = %d AND is_read = 0",
            $member_id
        ));
    }
}
?>