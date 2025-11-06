<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-member-details">
    <div class="mlm-member-header">
        <div class="member-avatar">
            <?php echo get_avatar($user->ID, 80); ?>
        </div>
        <div class="member-info">
            <h2><?php echo esc_html($user->display_name); ?></h2>
            <p class="member-email"><?php echo esc_html($user->user_email); ?></p>
            <p class="member-id">رقم العضو: <?php echo $member->id; ?></p>
        </div>
        <div class="member-actions">
            <a href="<?php echo admin_url('admin.php?page=mlm-members'); ?>" class="button button-secondary">العودة للقائمة</a>
            <button class="button button-primary mlm-view-tree" data-id="<?php echo $member->id; ?>">عرض الشجرة</button>
            <a href="<?php echo get_edit_user_link($user->ID); ?>" class="button button-secondary" target="_blank">تحرير المستخدم</a>
            <?php if ($member->status === 'active'): ?>
                <button class="button button-secondary mlm-deactivate-member" data-id="<?php echo $member->id; ?>">إلغاء التفعيل</button>
            <?php else: ?>
                <button class="button button-primary mlm-activate-member" data-id="<?php echo $member->id; ?>">تفعيل</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="mlm-member-info-grid">
        <div class="mlm-info-item">
            <span class="mlm-info-label">اسم المستخدم</span>
            <span class="mlm-info-value"><?php echo esc_html($user->user_login); ?></span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">البريد الإلكتروني</span>
            <span class="mlm-info-value"><?php echo esc_html($user->user_email); ?></span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">كود الإحالة</span>
            <span class="mlm-info-value">
                <code><?php echo esc_html($member->referral_code); ?></code>
                <button class="button button-small copy-code" data-code="<?php echo esc_attr($member->referral_code); ?>">نسخ</button>
            </span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">تاريخ الانضمام</span>
            <span class="mlm-info-value"><?php echo date('Y-m-d H:i', strtotime($member->join_date)); ?></span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">الحالة</span>
            <span class="mlm-info-value">
                <span class="mlm-badge mlm-badge-<?php echo $member->status === 'active' ? 'active' : 'pending'; ?>">
                    <?php echo $member->status === 'active' ? 'نشط' : 'غير نشط'; ?>
                </span>
            </span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">الراعي</span>
            <span class="mlm-info-value">
                <?php
                $sponsor = $member->sponsor_id ? MLM_Core::get_instance()->get_member_by_id($member->sponsor_id) : null;
                if ($sponsor):
                    $sponsor_user = get_userdata($sponsor->user_id);
                ?>
                    <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $sponsor->id); ?>">
                        <?php echo esc_html($sponsor_user->display_name ?: $sponsor_user->user_login); ?>
                    </a>
                <?php else: ?>
                    <span style="color: #999;">لا يوجد</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">العمولات الإجمالية</span>
            <span class="mlm-info-value"><?php echo number_format($member->total_commissions, 2); ?> ج.م</span>
        </div>
        <div class="mlm-info-item">
            <span class="mlm-info-label">العمولات المعلقة</span>
            <span class="mlm-info-value"><?php echo number_format($member->pending_commissions, 2); ?> ج.م</span>
        </div>
    </div>
</div>

<!-- إحصائيات العضو -->
<div class="mlm-stats-grid">
    <div class="mlm-stat-card">
        <h3>العمولات المعلقة</h3>
        <div class="stat-number"><?php echo number_format($member->pending_commissions, 2); ?> ج.م</div>
        <div class="stat-desc">في انتظار الدفع</div>
    </div>
    
    <div class="mlm-stat-card">
        <h3>العمولات المدفوعة</h3>
        <div class="stat-number"><?php echo number_format($member->paid_commissions, 2); ?> ج.م</div>
        <div class="stat-desc">تم دفعها</div>
    </div>
    
    <div class="mlm-stat-card">
        <h3>الأعضاء المُحالون</h3>
        <div class="stat-number"><?php echo count($tree_structure['level1'] ?? []); ?></div>
        <div class="stat-desc">في المستوى الأول</div>
    </div>
    
    <div class="mlm-stat-card">
        <h3>الأشجار المكتملة</h3>
        <div class="stat-number"><?php echo MLM_Rewards::get_instance()->count_completed_trees($member->id); ?></div>
        <div class="stat-desc">شجرة مكتملة</div>
    </div>
    
    <div class="mlm-stat-card">
        <h3>إجمالي المكافآت</h3>
        <div class="stat-number">
            <?php 
            $total_rewards = 0;
            foreach ($rewards as $reward) {
                $total_rewards += $reward->reward_amount;
            }
            echo number_format($total_rewards, 2); 
            ?> ج.م
        </div>
        <div class="stat-desc">المكافآت المستحقة</div>
    </div>
    
    <div class="mlm-stat-card">
        <h3>معدل العمولة</h3>
        <div class="stat-number">
            <?php
            $avg_commission = count($commissions) > 0 ? $member->total_commissions / count($commissions) : 0;
            echo number_format($avg_commission, 2);
            ?> ج.م
        </div>
        <div class="stat-desc">متوسط العمولة</div>
    </div>
</div>

<div class="mlm-row">
    <!-- قائمة العمولات -->
    <div class="mlm-col-6">
        <div class="mlm-section">
            <h3>سجل العمولات</h3>
            
            <div class="mlm-table-container">
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>المبلغ</th>
                            <th>المستوى</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($commissions)): ?>
                            <?php foreach (array_slice($commissions, 0, 10) as $commission): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo wc_get_order($commission->order_id) ? wc_get_order($commission->order_id)->get_view_order_url() : '#'; ?>" 
                                           target="_blank" class="order-link">
                                            #<?php echo $commission->order_id; ?>
                                        </a>
                                    </td>
                                    <td class="amount-cell"><?php echo number_format($commission->commission_amount, 2); ?> ج.م</td>
                                    <td>
                                        <span class="level-badge level-<?php echo $commission->level; ?>">
                                            المستوى <?php echo $commission->level; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($commission->created_date)); ?></td>
                                    <td>
                                        <span class="mlm-badge mlm-badge-<?php echo $commission->status === 'paid' ? 'paid' : 'pending'; ?>">
                                            <?php echo $commission->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">لا توجد عمولات حتى الآن</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (count($commissions) > 10): ?>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=mlm-commissions&member_id=' . $member->id); ?>" class="button button-secondary">
                        عرض جميع العمولات (<?php echo count($commissions); ?>)
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- قائمة المكافآت -->
    <div class="mlm-col-6">
        <div class="mlm-section">
            <h3>سجل المكافآت</h3>
            
            <div class="mlm-table-container">
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>عدد الأشجار</th>
                            <th>المكافأة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rewards)): ?>
                            <?php foreach ($rewards as $reward): ?>
                                <tr>
                                    <td><?php echo $reward->trees_completed; ?> شجرة</td>
                                    <td><?php echo number_format($reward->reward_amount, 2); ?> ج.م</td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($reward->achieved_date)); ?></td>
                                    <td>
                                        <span class="mlm-badge mlm-badge-<?php echo $reward->status === 'paid' ? 'paid' : 'pending'; ?>">
                                            <?php echo $reward->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($reward->status === 'pending'): ?>
                                            <button class="mlm-action-btn mlm-action-btn-success mlm-pay-reward" data-id="<?php echo $reward->id; ?>">دفع</button>
                                        <?php else: ?>
                                            <span class="mlm-action-btn" style="background: #f8f9fa; color: #666;">تم الدفع</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">لا توجد مكافآت حتى الآن</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- هيكل الشجرة المصغر -->
        <div class="mlm-section">
            <h3>هيكل الشجرة</h3>
            
            <div class="mlm-tree-mini">
                <div class="tree-level">
                    <h4>المستوى الأول: <?php echo count($tree_structure['level1'] ?? []); ?> عضو</h4>
                    <div class="level-members">
                        <?php if (!empty($tree_structure['level1'])): ?>
                            <?php foreach (array_slice($tree_structure['level1'], 0, 3) as $member_data): ?>
                                <div class="mini-member">
                                    <?php
                                    $level1_member = MLM_Core::get_instance()->get_member_by_id($member_data['member_id']);
                                    $level1_user = $level1_member ? get_userdata($level1_member->user_id) : null;
                                    ?>
                                    <div class="mini-avatar">👤</div>
                                    <div class="mini-info">
                                        <div class="mini-name"><?php echo $level1_user ? esc_html($level1_user->display_name) : 'عضو #' . $member_data['member_id']; ?></div>
                                        <div class="mini-stats"><?php echo number_format($level1_member->total_commissions, 0); ?> ج.م</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($tree_structure['level1']) > 3): ?>
                                <div class="mini-member more-members">
                                    +<?php echo count($tree_structure['level1']) - 3; ?> أعضاء آخرين
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-members">لا يوجد أعضاء</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 15px;">
                <button class="button button-primary mlm-view-full-tree" data-id="<?php echo $member->id; ?>">عرض الشجرة الكاملة</button>
            </div>
        </div>
    </div>
</div>

<style>
.mlm-member-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e1e1e1;
}

.member-avatar img {
    border-radius: 50%;
}

.member-info h2 {
    margin: 0 0 5px 0;
    font-size: 1.8em;
}

.member-email {
    margin: 0 0 5px 0;
    color: #666;
    font-size: 1.1em;
}

.member-id {
    margin: 0;
    color: #999;
    font-size: 0.9em;
}

.member-actions {
    margin-left: auto;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.mlm-table-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
}

.mlm-tree-mini {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.tree-level {
    margin-bottom: 15px;
}

.tree-level h4 {
    margin: 0 0 10px 0;
    font-size: 1em;
    color: #333;
}

.level-members {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
}

.mini-member {
    background: white;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e1e1e1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mini-avatar {
    font-size: 1.2em;
}

.mini-info {
    flex: 1;
}

.mini-name {
    font-weight: 500;
    font-size: 0.9em;
    margin-bottom: 2px;
}

.mini-stats {
    font-size: 0.8em;
    color: #27ae60;
}

.more-members {
    justify-content: center;
    text-align: center;
    color: #666;
    font-style: italic;
}

.no-members {
    text-align: center;
    color: #999;
    font-style: italic;
    padding: 20px;
}

.level-badge {
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: bold;
}

.level-1 { background: #e8f5e8; color: #27ae60; }
.level-2 { background: #e8f4fd; color: #3498db; }
.level-3 { background: #fef5e7; color: #f39c12; }

.order-link {
    color: #0073aa;
    text-decoration: none;
}

.order-link:hover {
    text-decoration: underline;
}

.amount-cell {
    font-weight: bold;
    color: #27ae60;
}

@media (max-width: 768px) {
    .mlm-member-header {
        flex-direction: column;
        text-align: center;
    }
    
    .member-actions {
        margin-left: 0;
        justify-content: center;
    }
    
    .mlm-row {
        flex-direction: column;
    }
    
    .mlm-col-6 {
        flex: 100%;
    }
    
    .level-members {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // نسخ كود الإحالة
    $('.copy-code').on('click', function() {
        const code = $(this).data('code');
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(code).select();
        document.execCommand('copy');
        $temp.remove();
        
        $(this).text('تم النسخ!');
        setTimeout(() => {
            $(this).text('نسخ');
        }, 2000);
    });

    // عرض الشجرة الكاملة
    $('.mlm-view-full-tree, .mlm-view-tree').on('click', function() {
        const memberId = $(this).data('id');
        window.open('<?php echo admin_url('admin.php?page=mlm-members'); ?>&action=view_tree&member_id=' + memberId, '_blank');
    });

    // دفع المكافأة
    $('.mlm-pay-reward').on('click', function() {
        const rewardId = $(this).data('id');
        const $btn = $(this);
        
        if (confirm('هل تريد دفع هذه المكافأة؟')) {
            $btn.html('<span class="mlm-loading"></span>').prop('disabled', true);
            
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'pay_reward',
                    reward_id: rewardId,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.replaceWith('<span class="mlm-badge mlm-badge-paid">تم الدفع</span>');
                    } else {
                        alert('حدث خطأ: ' + response.data);
                        $btn.text('دفع').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('حدث خطأ في الاتصال');
                    $btn.text('دفع').prop('disabled', false);
                }
            });
        }
    });

    // إلغاء تفعيل العضو
    $('.mlm-deactivate-member').on('click', function() {
        const memberId = $(this).data('id');
        if (confirm('هل أنت متأكد من إلغاء تفعيل هذا العضو؟')) {
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'deactivate_member',
                    member_id: memberId,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('حدث خطأ: ' + response.data);
                    }
                }
            });
        }
    });

    // تفعيل العضو
    $('.mlm-activate-member').on('click', function() {
        const memberId = $(this).data('id');
        if (confirm('هل أنت متأكد من تفعيل هذا العضو؟')) {
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'activate_member',
                    member_id: memberId,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('حدث خطأ: ' + response.data);
                    }
                }
            });
        }
    });
});
</script>