<?php
class MLM_Reports {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function generate_daily_report() {
        $report_data = array(
            'date' => current_time('Y-m-d'),
            'new_members' => $this->get_new_members_today(),
            'total_commissions' => $this->get_commissions_today(),
            'completed_trees' => $this->get_completed_trees_today(),
            'pending_payouts' => $this->get_pending_payouts()
        );
        
        $this->save_report('daily', $report_data);
        $this->send_daily_report($report_data);
        
        return $report_data;
    }
    
    public function generate_weekly_report() {
        $report_data = array(
            'week_start' => date('Y-m-d', strtotime('monday this week')),
            'week_end' => date('Y-m-d', strtotime('sunday this week')),
            'new_members' => $this->get_new_members_this_week(),
            'total_commissions' => $this->get_commissions_this_week(),
            'completed_trees' => $this->get_completed_trees_this_week(),
            'top_members' => $this->get_top_members_this_week(),
            'growth_rate' => $this->calculate_growth_rate()
        );
        
        $this->save_report('weekly', $report_data);
        $this->send_weekly_report($report_data);
        
        return $report_data;
    }
    
    public function generate_monthly_report() {
        $report_data = array(
            'month' => date('Y-m'),
            'new_members' => $this->get_new_members_this_month(),
            'total_commissions' => $this->get_commissions_this_month(),
            'completed_trees' => $this->get_completed_trees_this_month(),
            'total_payouts' => $this->get_payouts_this_month(),
            'member_activity' => $this->get_member_activity(),
            'performance_metrics' => $this->get_performance_metrics()
        );
        
        $this->save_report('monthly', $report_data);
        $this->send_monthly_report($report_data);
        
        return $report_data;
    }
    
    private function get_new_members_today() {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members 
            WHERE DATE(join_date) = %s",
            current_time('Y-m-d')
        ));
    }
    
    private function get_commissions_today() {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as count,
                SUM(commission_amount) as total,
                AVG(commission_amount) as average
            FROM {$wpdb->prefix}mlm_commissions 
            WHERE DATE(created_date) = %s",
            current_time('Y-m-d')
        ));
        
        return array(
            'count' => $result->count ?: 0,
            'total' => $result->total ?: 0,
            'average' => $result->average ?: 0
        );
    }
    
    private function get_completed_trees_today() {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_trees 
            WHERE is_active = 0 AND DATE(created_date) = %s",
            current_time('Y-m-d')
        ));
    }
    
    private function get_new_members_this_week() {
        global $wpdb;
        
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members 
            WHERE DATE(join_date) BETWEEN %s AND %s",
            $week_start, $week_end
        ));
    }
    
    private function get_commissions_this_week() {
        global $wpdb;
        
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as count,
                SUM(commission_amount) as total,
                AVG(commission_amount) as average
            FROM {$wpdb->prefix}mlm_commissions 
            WHERE DATE(created_date) BETWEEN %s AND %s",
            $week_start, $week_end
        ));
        
        return array(
            'count' => $result->count ?: 0,
            'total' => $result->total ?: 0,
            'average' => $result->average ?: 0
        );
    }
    
    private function get_top_members_this_week() {
        global $wpdb;
        
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                m.id,
                u.display_name,
                u.user_email,
                COUNT(c.id) as commission_count,
                SUM(c.commission_amount) as total_commissions
            FROM {$wpdb->prefix}mlm_commissions c
            JOIN {$wpdb->prefix}mlm_members m ON c.sponsor_id = m.id
            JOIN {$wpdb->prefix}users u ON m.user_id = u.ID
            WHERE DATE(c.created_date) BETWEEN %s AND %s
            GROUP BY m.id
            ORDER BY total_commissions DESC
            LIMIT 5",
            $week_start, $week_end
        ));
    }
    
    private function calculate_growth_rate() {
        global $wpdb;
        
        $current_week = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members 
            WHERE YEARWEEK(join_date) = YEARWEEK(NOW())"
        ));
        
        $last_week = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members 
            WHERE YEARWEEK(join_date) = YEARWEEK(NOW() - INTERVAL 1 WEEK)"
        ));
        
        if ($last_week > 0) {
            return (($current_week - $last_week) / $last_week) * 100;
        }
        
        return $current_week > 0 ? 100 : 0;
    }
    
    private function save_report($type, $data) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'mlm_reports',
            array(
                'report_type' => $type,
                'report_data' => serialize($data),
                'created_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }
    
    private function send_daily_report($data) {
        $admin_email = get_option('admin_email');
        $subject = 'تقرير العمولات اليومي - ' . $data['date'];
        
        $message = $this->format_daily_report($data);
        
        wp_mail($admin_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
    
    private function send_weekly_report($data) {
        $admin_email = get_option('admin_email');
        $subject = 'تقرير العمولات الأسبوعي - ' . $data['week_start'] . ' إلى ' . $data['week_end'];
        
        $message = $this->format_weekly_report($data);
        
        wp_mail($admin_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
    
    private function send_monthly_report($data) {
        $admin_email = get_option('admin_email');
        $subject = 'تقرير العمولات الشهري - ' . $data['month'];
        
        $message = $this->format_monthly_report($data);
        
        wp_mail($admin_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    }
    
    private function format_daily_report($data) {
        ob_start();
        ?>
        <div dir="rtl" style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333; text-align: center;">تقرير العمولات اليومي</h2>
            <p style="text-align: center; color: #666;">تاريخ: <?php echo $data['date']; ?></p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #333;">الإحصائيات اليومية</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <strong>أعضاء جدد:</strong> <?php echo $data['new_members']; ?>
                    </li>
                    <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <strong>عدد العمولات:</strong> <?php echo $data['total_commissions']['count']; ?>
                    </li>
                    <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <strong>إجمالي العمولات:</strong> <?php echo number_format($data['total_commissions']['total'], 2); ?> ج.م
                    </li>
                    <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <strong>متوسط العمولة:</strong> <?php echo number_format($data['total_commissions']['average'], 2); ?> ج.م
                    </li>
                    <li style="padding: 10px;">
                        <strong>أشجار مكتملة:</strong> <?php echo $data['completed_trees']; ?>
                    </li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="<?php echo admin_url('admin.php?page=mlm-dashboard'); ?>" 
                   style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;">
                   عرض التقرير الكامل
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function export_report($type, $format = 'csv') {
        switch ($type) {
            case 'commissions':
                return $this->export_commissions_report($format);
            case 'members':
                return $this->export_members_report($format);
            case 'rewards':
                return $this->export_rewards_report($format);
            default:
                return false;
        }
    }
    
    private function export_commissions_report($format) {
        global $wpdb;
        
        $commissions = $wpdb->get_results("
            SELECT 
                c.*,
                u1.display_name as member_name,
                u2.display_name as sponsor_name
            FROM {$wpdb->prefix}mlm_commissions c
            LEFT JOIN {$wpdb->prefix}users u1 ON c.member_id = u1.ID
            LEFT JOIN {$wpdb->prefix}users u2 ON c.sponsor_id = u2.ID
            ORDER BY c.created_date DESC
        ");
        
        if ($format === 'csv') {
            return $this->generate_csv($commissions, 'commissions');
        }
        
        return $commissions;
    }
    
    private function generate_csv($data, $filename) {
        $filename = $filename . '_' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        
        if (!empty($data)) {
            // Headers
            fputcsv($output, array_keys((array)$data[0]));
            
            // Data
            foreach ($data as $row) {
                fputcsv($output, (array)$row);
            }
        }
        
        fclose($output);
        exit;
    }
}
?>