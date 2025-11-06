<?php
if (!defined('ABSPATH')) {
    exit;
}

// الحصول على الإحصائيات
$stats = $this->get_dashboard_stats();
?>

<div class="wrap mlm-admin-wrap">
    <div class="mlm-header">
        <h1><span class="dashicons dashicons-dashboard"></span> لوحة تحكم نظام العمولات المتعددة</h1>
        <p>نظرة عامة على أداء النظام وإحصائياته</p>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="mlm-stats-grid">
        <div class="mlm-stat-card">
            <h3>إجمالي الأعضاء</h3>
            <div class="stat-number"><?php echo number_format($stats['total_members']); ?></div>
            <div class="stat-desc">مسجلين في النظام</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>الأعضاء النشطين</h3>
            <div class="stat-number"><?php echo number_format($stats['active_members']); ?></div>
            <div class="stat-desc">أعضاء نشطين حالياً</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>إجمالي العمولات</h3>
            <div class="stat-number"><?php echo number_format($stats['total_commissions'], 2); ?> ج.م</div>
            <div class="stat-desc">عمولات منذ البداية</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>عمولات معلقة</h3>
            <div class="stat-number"><?php echo number_format($stats['pending_commissions'], 2); ?> ج.م</div>
            <div class="stat-desc">في انتظار الدفع</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>إجمالي المكافآت</h3>
            <div class="stat-number"><?php echo number_format($stats['total_rewards'], 2); ?> ج.م</div>
            <div class="stat-desc">مكافآت مستحقة</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>مكافآت معلقة</h3>
            <div class="stat-number"><?php echo number_format($stats['pending_rewards'], 2); ?> ج.م</div>
            <div class="stat-desc">في انتظار الدفع</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>الأشجار المكتملة</h3>
            <div class="stat-number"><?php echo number_format($stats['completed_trees']); ?></div>
            <div class="stat-desc">شجرة مكتملة</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>متوسط العمولة</h3>
            <div class="stat-number">
                <?php 
                $avg_commission = $stats['total_members'] > 0 ? 
                    $stats['total_commissions'] / $stats['total_members'] : 0;
                echo number_format($avg_commission, 2); 
                ?> ج.م
            </div>
            <div class="stat-desc">لكل عضو</div>
        </div>
    </div>

    <div class="mlm-content">
        <!-- الصف الأول: الرسوم البيانية -->
        <div class="mlm-row">
            <div class="mlm-col-6">
                <div class="mlm-settings-section">
                    <h3>توزيع الأعضاء</h3>
                    <div class="mlm-chart-container">
                        <canvas id="membersChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="mlm-col-6">
                <div class="mlm-settings-section">
                    <h3>العمولات الشهرية</h3>
                    <div class="mlm-chart-container">
                        <canvas id="commissionsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- الصف الثاني: النشاط الحديث وأفضل الأعضاء -->
        <div class="mlm-row">
            <div class="mlm-col-6">
                <div class="mlm-settings-section">
                    <h3>النشاط الحديث</h3>
                    <div class="mlm-activity-list">
                        <?php
                        global $wpdb;
                        $recent_activity = $wpdb->get_results("
                            (SELECT 'commission' as type, created_date as date, 
                                    CONCAT('عمولة جديدة: ', commission_amount, ' ج.م') as description,
                                    order_id as reference
                             FROM {$wpdb->prefix}mlm_commissions 
                             ORDER BY created_date DESC LIMIT 5)
                            UNION ALL
                            (SELECT 'reward' as type, achieved_date as date, 
                                    CONCAT('مكافأة جديدة: ', reward_amount, ' ج.م') as description,
                                    trees_completed as reference
                             FROM {$wpdb->prefix}mlm_rewards 
                             ORDER BY achieved_date DESC LIMIT 5)
                            UNION ALL
                            (SELECT 'member' as type, join_date as date, 
                                    'عضو جديد انضم للنظام' as description,
                                    user_id as reference
                             FROM {$wpdb->prefix}mlm_members 
                             ORDER BY join_date DESC LIMIT 5)
                            ORDER BY date DESC LIMIT 10
                        ");
                        
                        if ($recent_activity):
                            foreach ($recent_activity as $activity):
                                $icon = $activity->type === 'commission' ? '💰' : 
                                       ($activity->type === 'reward' ? '🎁' : '👤');
                        ?>
                            <div class="mlm-activity-item">
                                <div class="activity-icon"><?php echo $icon; ?></div>
                                <div class="activity-content">
                                    <p><?php echo $activity->description; ?></p>
                                    <span class="activity-date">
                                        <?php echo human_time_diff(strtotime($activity->date), current_time('timestamp')); ?> مضت
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <p style="text-align: center; color: #666; padding: 20px;">لا يوجد نشاط حديث</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mlm-col-6">
                <div class="mlm-settings-section">
                    <h3>أفضل الأعضاء أداءً</h3>
                    <table class="mlm-table">
                        <thead>
                            <tr>
                                <th>العضو</th>
                                <th>العمولات</th>
                                <th>الأعضاء</th>
                                <th>الأشجار</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $top_members = $wpdb->get_results("
                                SELECT m.*, u.user_login, u.display_name,
                                       (SELECT COUNT(*) FROM {$wpdb->prefix}mlm_members WHERE sponsor_id = m.id) as referrals_count,
                                       (SELECT COUNT(*) FROM {$wpdb->prefix}mlm_trees WHERE member_id = m.id AND is_active = 0) as completed_trees
                                FROM {$wpdb->prefix}mlm_members m
                                LEFT JOIN {$wpdb->prefix}users u ON m.user_id = u.ID
                                ORDER BY m.total_commissions DESC
                                LIMIT 5
                            ");
                            
                            if ($top_members):
                                foreach ($top_members as $index => $member):
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($member->display_name ?: $member->user_login); ?></strong>
                                    </td>
                                    <td><?php echo number_format($member->total_commissions, 2); ?> ج.م</td>
                                    <td><?php echo $member->referrals_count; ?></td>
                                    <td><?php echo $member->completed_trees; ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">لا توجد بيانات</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- الصف الثالث: التقارير السريعة -->
        <div class="mlm-row">
            <div class="mlm-col-12">
                <div class="mlm-settings-section">
                    <h3>تقارير سريعة</h3>
                    <div class="mlm-quick-reports">
                        <div class="mlm-report-card">
                            <h4>تقرير العمولات اليومي</h4>
                            <p>عرض العمولات المحققة اليوم</p>
                            <a href="<?php echo admin_url('admin.php?page=mlm-commissions&date=today'); ?>" class="button button-primary">عرض التقرير</a>
                        </div>
                        
                        <div class="mlm-report-card">
                            <h4>تقرير الأعضاء الجدد</h4>
                            <p>الأعضاء المسجلين خلال هذا الشهر</p>
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&date=month'); ?>" class="button button-primary">عرض التقرير</a>
                        </div>
                        
                        <div class="mlm-report-card">
                            <h4>تقرير المكافآت</h4>
                            <p>المكافآت المستحقة والمدفوعة</p>
                            <a href="<?php echo admin_url('admin.php?page=mlm-rewards'); ?>" class="button button-primary">عرض التقرير</a>
                        </div>
                        
                        <div class="mlm-report-card">
                            <h4>تصدير البيانات</h4>
                            <p>تصدير جميع بيانات النظام</p>
                            <a href="<?php echo admin_url('admin.php?page=mlm-reports&tab=export'); ?>" class="button button-primary">التصدير</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mlm-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.mlm-col-6 {
    flex: 1;
}

.mlm-col-12 {
    flex: 100%;
}

.mlm-chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e1e1e1;
}

.mlm-activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.mlm-activity-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
    gap: 15px;
}

.mlm-activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    font-size: 1.5em;
    width: 40px;
    text-align: center;
}

.activity-content {
    flex: 1;
}

.activity-content p {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.activity-date {
    font-size: 0.85em;
    color: #666;
}

.mlm-quick-reports {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.mlm-report-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #e1e1e1;
    text-align: center;
}

.mlm-report-card h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.mlm-report-card p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .mlm-row {
        flex-direction: column;
    }
    
    .mlm-col-6 {
        flex: 100%;
    }
    
    .mlm-quick-reports {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // تحميل مكتبة Charts إذا لم تكن محملة
    if (typeof Chart !== 'undefined') {
        // رسم بياني توزيع الأعضاء
        const membersCtx = document.getElementById('membersChart').getContext('2d');
        new Chart(membersCtx, {
            type: 'doughnut',
            data: {
                labels: ['نشطين', 'غير نشطين'],
                datasets: [{
                    data: [
                        <?php echo $stats['active_members']; ?>,
                        <?php echo $stats['total_members'] - $stats['active_members']; ?>
                    ],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tahoma, Arial, sans-serif'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'توزيع الأعضاء',
                        font: {
                            family: 'Tahoma, Arial, sans-serif'
                        }
                    }
                }
            }
        });

        // رسم بياني العمولات الشهرية
        const commissionsCtx = document.getElementById('commissionsChart').getContext('2d');
        new Chart(commissionsCtx, {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'العمولات (ج.م)',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tahoma, Arial, sans-serif'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'العمولات الشهرية',
                        font: {
                            family: 'Tahoma, Arial, sans-serif'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                family: 'Tahoma, Arial, sans-serif'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Tahoma, Arial, sans-serif'
                            }
                        }
                    }
                }
            }
        });
    }

    // تحديث الإحصائيات كل 30 ثانية
    function updateStats() {
        $.ajax({
            url: mlm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mlm_admin_action',
                mlm_action: 'get_dashboard_stats',
                nonce: mlm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // تحديث الإحصائيات في الواجهة
                    $('.stat-number').each(function() {
                        const statType = $(this).closest('.mlm-stat-card').find('h3').text().trim();
                        const newValue = response.data[getStatKey(statType)];
                        if (newValue !== undefined) {
                            $(this).text(formatStatValue(statType, newValue));
                        }
                    });
                }
            }
        });
    }

    function getStatKey(statText) {
        const map = {
            'إجمالي الأعضاء': 'total_members',
            'الأعضاء النشطين': 'active_members',
            'إجمالي العمولات': 'total_commissions',
            'عمولات معلقة': 'pending_commissions',
            'إجمالي المكافآت': 'total_rewards',
            'مكافآت معلقة': 'pending_rewards',
            'الأشجار المكتملة': 'completed_trees'
        };
        return map[statText] || statText;
    }

    function formatStatValue(statType, value) {
        if (statType.includes('عمولات') || statType.includes('مكافآت')) {
            return Number(value).toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م';
        } else {
            return Number(value).toLocaleString('ar-EG');
        }
    }

    // تحديث الإحصائيات كل 30 ثانية
    setInterval(updateStats, 30000);

    // تحديث عند تحميل الصفحة
    updateStats();
});
</script>