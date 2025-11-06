<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mlm-admin-wrap">
    <div class="mlm-header">
        <h1><span class="dashicons dashicons-groups"></span> إدارة الأعضاء - نظام العمولات</h1>
        <p>عرض وإدارة أعضاء نظام العمولات المتعددة</p>
    </div>

    <?php if ($_GET['action'] ?? '' === 'view'): ?>
        <!-- عرض تفاصيل العضو -->
        <div class="mlm-member-details">
            <h2>تفاصيل العضو: <?php echo esc_html($user->display_name); ?></h2>
            
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
                    <span class="mlm-info-value"><?php echo esc_html($member->referral_code); ?></span>
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
                    <span class="mlm-info-label">العمولات الإجمالية</span>
                    <span class="mlm-info-value"><?php echo number_format($member->total_commissions, 2); ?> ج.م</span>
                </div>
            </div>

            <div class="mlm-action-buttons">
                <a href="<?php echo admin_url('admin.php?page=mlm-members'); ?>" class="button button-secondary">العودة للقائمة</a>
                <button class="button button-primary mlm-view-tree" data-id="<?php echo $member->id; ?>">عرض الشجرة</button>
                <?php if ($member->status === 'active'): ?>
                    <button class="button button-secondary mlm-deactivate-member" data-id="<?php echo $member->id; ?>">إلغاء التفعيل</button>
                <?php else: ?>
                    <button class="button button-primary mlm-activate-member" data-id="<?php echo $member->id; ?>">تفعيل</button>
                <?php endif; ?>
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
        </div>

        <!-- قائمة العمولات -->
        <div class="mlm-section">
            <h3>سجل العمولات</h3>
            <table class="mlm-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>المبلغ</th>
                        <th>النسبة</th>
                        <th>المستوى</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($commissions)): ?>
                        <?php foreach ($commissions as $commission): ?>
                            <tr>
                                <td>#<?php echo esc_html($commission->order_id); ?></td>
                                <td><?php echo number_format($commission->commission_amount, 2); ?> ج.م</td>
                                <td><?php echo esc_html($commission->commission_rate); ?>%</td>
                                <td>المستوى <?php echo esc_html($commission->level); ?></td>
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
                            <td colspan="6" style="text-align: center;">لا توجد عمولات حتى الآن</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- قائمة المكافآت -->
        <div class="mlm-section">
            <h3>سجل المكافآت</h3>
            <table class="mlm-table">
                <thead>
                    <tr>
                        <th>عدد الأشجار</th>
                        <th>المكافأة</th>
                        <th>الإجمالي التراكمي</th>
                        <th>تاريخ التحقيق</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rewards)): ?>
                        <?php foreach ($rewards as $reward): ?>
                            <tr>
                                <td><?php echo esc_html($reward->trees_completed); ?> شجرة</td>
                                <td><?php echo number_format($reward->reward_amount, 2); ?> ج.م</td>
                                <td><?php echo number_format($reward->total_rewards, 2); ?> ج.م</td>
                                <td><?php echo date('Y-m-d H:i', strtotime($reward->achieved_date)); ?></td>
                                <td>
                                    <span class="mlm-badge mlm-badge-<?php echo $reward->status === 'paid' ? 'paid' : 'pending'; ?>">
                                        <?php echo $reward->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($reward->status === 'pending'): ?>
                                        <button class="mlm-action-btn mlm-action-btn-success mlm-pay-reward" data-id="<?php echo $reward->id; ?>">دفع</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">لا توجد مكافآت حتى الآن</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- قائمة جميع الأعضاء -->
        <div class="mlm-search-box">
            <form method="get">
                <input type="hidden" name="page" value="mlm-members">
                <input type="text" name="s" placeholder="البحث في الأعضاء..." value="<?php echo esc_attr($search); ?>" class="mlm-search-input">
                <button type="submit" class="button button-primary">بحث</button>
                <?php if ($search): ?>
                    <a href="<?php echo admin_url('admin.php?page=mlm-members'); ?>" class="button button-secondary">إعادة تعيين</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="mlm-filter-section">
            <select class="mlm-filter-select" data-column="5">
                <option value="all">جميع الحالات</option>
                <option value="نشط">نشط</option>
                <option value="غير نشط">غير نشط</option>
            </select>
        </div>

        <table class="mlm-table">
            <thead>
                <tr>
                    <th>العضو</th>
                    <th>البريد الإلكتروني</th>
                    <th>كود الإحالة</th>
                    <th>الراعي</th>
                    <th>تاريخ الانضمام</th>
                    <th>الحالة</th>
                    <th>العمولات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <?php
                        $sponsor = $member->sponsor_id ? MLM_Core::get_instance()->get_member_by_id($member->sponsor_id) : null;
                        $sponsor_user = $sponsor ? get_userdata($sponsor->user_id) : null;
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($member->display_name ?: $member->user_login); ?></strong>
                            </td>
                            <td><?php echo esc_html($member->user_email); ?></td>
                            <td><code><?php echo esc_html($member->referral_code); ?></code></td>
                            <td>
                                <?php if ($sponsor_user): ?>
                                    <?php echo esc_html($sponsor_user->display_name ?: $sponsor_user->user_login); ?>
                                <?php else: ?>
                                    <span style="color: #999;">لا يوجد</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($member->join_date)); ?></td>
                            <td>
                                <span class="mlm-badge mlm-badge-<?php echo $member->status === 'active' ? 'active' : 'pending'; ?>">
                                    <?php echo $member->status === 'active' ? 'نشط' : 'غير نشط'; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($member->total_commissions, 2); ?> ج.م</td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $member->id); ?>" 
                                   class="mlm-action-btn mlm-action-btn-primary">عرض</a>
                                <button class="mlm-action-btn mlm-view-tree" data-id="<?php echo $member->id; ?>">الشجرة</button>
                                <?php if ($member->status === 'active'): ?>
                                    <button class="mlm-action-btn mlm-action-btn-danger mlm-deactivate-member" data-id="<?php echo $member->id; ?>">إلغاء التفعيل</button>
                                <?php else: ?>
                                    <button class="mlm-action-btn mlm-action-btn-success mlm-activate-member" data-id="<?php echo $member->id; ?>">تفعيل</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">لا توجد أعضاء</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_members > $per_page): ?>
            <div class="mlm-pagination">
                <?php
                $total_pages = ceil($total_members / $per_page);
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $page
                ));
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>