<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-search-box">
    <form method="get">
        <input type="hidden" name="page" value="mlm-rewards">
        <input type="text" name="s" placeholder="البحث في المكافآت..." class="mlm-search-input">
        <button type="submit" class="button button-primary">بحث</button>
    </form>
</div>

<div class="mlm-filter-section">
    <select class="mlm-filter-select" data-column="4">
        <option value="all">جميع الحالات</option>
        <option value="معلق">معلق</option>
        <option value="تم الدفع">تم الدفع</option>
    </select>
    
    <select class="mlm-filter-select" id="trees_filter">
        <option value="all">جميع الأشجار</option>
        <option value="1">1 شجرة</option>
        <option value="2">2 شجرة</option>
        <option value="4">4 شجرة</option>
        <option value="6">6 شجرة</option>
        <option value="8">8 شجرة</option>
        <option value="10">10 شجرة</option>
        <option value="15">15 شجرة</option>
        <option value="20">20 شجرة</option>
        <option value="25">25 شجرة</option>
    </select>
</div>

<div class="mlm-rewards-summary">
    <div class="summary-card">
        <h4>إجمالي المكافآت</h4>
        <div class="summary-amount"><?php echo number_format($stats['total_rewards'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>مكافآت معلقة</h4>
        <div class="summary-amount pending"><?php echo number_format($stats['pending_rewards'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>مكافآت مدفوعة</h4>
        <div class="summary-amount paid"><?php echo number_format($stats['paid_rewards'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>أعلى مكافأة</h4>
        <div class="summary-amount"><?php echo number_format($stats['max_reward'], 2); ?> ج.م</div>
    </div>
</div>

<table class="mlm-table">
    <thead>
        <tr>
            <th>رقم المكافأة</th>
            <th>العضو</th>
            <th>عدد الأشجار</th>
            <th>مبلغ المكافأة</th>
            <th>المكافأة التراكمية</th>
            <th>تاريخ التحقيق</th>
            <th>تاريخ الدفع</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rewards)): ?>
            <?php foreach ($rewards as $reward): ?>
                <tr data-status="<?php echo $reward->status; ?>" 
                    data-trees="<?php echo $reward->trees_completed; ?>">
                    <td>#<?php echo $reward->id; ?></td>
                    <td>
                        <?php
                        $member = MLM_Core::get_instance()->get_member_by_id($reward->member_id);
                        $user = $member ? get_userdata($member->user_id) : null;
                        if ($user):
                        ?>
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $reward->member_id); ?>">
                                <?php echo esc_html($user->display_name ?: $user->user_login); ?>
                            </a>
                        <?php else: ?>
                            عضو #<?php echo $reward->member_id; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="trees-badge">
                            <?php echo $reward->trees_completed; ?> شجرة
                        </span>
                    </td>
                    <td class="amount-cell"><?php echo number_format($reward->reward_amount, 2); ?> ج.م</td>
                    <td><?php echo number_format($reward->total_rewards, 2); ?> ج.م</td>
                    <td><?php echo date('Y-m-d H:i', strtotime($reward->achieved_date)); ?></td>
                    <td>
                        <?php echo $reward->paid_date ? date('Y-m-d H:i', strtotime($reward->paid_date)) : '-'; ?>
                    </td>
                    <td>
                        <span class="mlm-badge mlm-badge-<?php echo $reward->status === 'paid' ? 'paid' : 'pending'; ?>">
                            <?php echo $reward->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="mlm-action-buttons">
                            <button class="mlm-action-btn view-details" data-id="<?php echo $reward->id; ?>" title="عرض التفاصيل">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            
                            <?php if ($reward->status === 'pending'): ?>
                                <button class="mlm-action-btn mlm-action-btn-success mlm-pay-reward" 
                                        data-id="<?php echo $reward->id; ?>" title="دفع المكافأة">
                                    <span class="dashicons dashicons-yes"></span>
                                </button>
                            <?php endif; ?>
                            
                            <button class="mlm-action-btn mlm-export-reward" 
                                    data-id="<?php echo $reward->id; ?>" title="تصدير">
                                <span class="dashicons dashicons-download"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px;">
                    <div style="color: #666;">
                        <span class="dashicons dashicons-awards" style="font-size: 48px; display: block; margin-bottom: 15px;"></span>
                        <h3>لا توجد مكافآت</h3>
                        <p>لم يتم تحقيق أي مكافآت في النظام بعد.</p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_rewards > $per_page): ?>
    <div class="mlm-pagination">
        <?php
        $total_pages = ceil($total_rewards / $per_page);
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => '&laquo; السابق',
            'next_text' => 'التالي &raquo;',
            'total' => $total_pages,
            'current' => $page
        ));
        ?>
    </div>
<?php endif; ?>

<style>
.mlm-rewards-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.trees-badge {
    background: #e8f4fd;
    color: #3498db;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.amount-cell {
    font-weight: bold;
    color: #f39c12;
}

@media (max-width: 768px) {
    .mlm-rewards-summary {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // تصفية المكافآت
    function filterRewards() {
        const statusFilter = $('.mlm-filter-select[data-column="4"]').val();
        const treesFilter = $('#trees_filter').val();
        
        $('.mlm-table tbody tr').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const trees = $row.data('trees');
            
            let show = true;
            
            // تصفية حسب الحالة
            if (statusFilter !== 'all') {
                const statusText = status === 'paid' ? 'تم الدفع' : 'معلق';
                if (statusText !== statusFilter) {
                    show = false;
                }
            }
            
            // تصفية حسب عدد الأشجار
            if (treesFilter !== 'all' && trees != treesFilter) {
                show = false;
            }
            
            $row.toggle(show);
        });
    }

    $('.mlm-filter-select, #trees_filter').on('change', filterRewards);

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
                        const $row = $btn.closest('tr');
                        $row.find('.mlm-badge')
                            .removeClass('mlm-badge-pending')
                            .addClass('mlm-badge-paid')
                            .text('تم الدفع');
                            
                        $row.find('td').eq(6).text(new Date().toLocaleDateString('ar-EG'));
                        $btn.remove();
                        updateSummaryStats();
                    } else {
                        alert('حدث خطأ: ' + response.data);
                        $btn.html('<span class="dashicons dashicons-yes"></span>').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('حدث خطأ في الاتصال');
                    $btn.html('<span class="dashicons dashicons-yes"></span>').prop('disabled', false);
                }
            });
        }
    });

    // عرض تفاصيل المكافأة
    $('.view-details').on('click', function() {
        const rewardId = $(this).data('id');
        
        $.ajax({
            url: mlm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mlm_admin_action',
                mlm_action: 'get_reward_details',
                reward_id: rewardId,
                nonce: mlm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showRewardModal(response.data);
                } else {
                    alert('حدث خطأ في تحميل البيانات');
                }
            }
        });
    });

    // تصدير المكافأة
    $('.mlm-export-reward').on('click', function() {
        const rewardId = $(this).data('id');
        window.open(mlm_admin.ajax_url + '?action=mlm_export_reward&reward_id=' + rewardId + '&nonce=' + mlm_admin.nonce, '_blank');
    });

    function showRewardModal(data) {
        const modalHtml = `
            <div id="mlm-reward-modal" class="mlm-modal">
                <div class="mlm-modal-content">
                    <div class="mlm-modal-header">
                        <h3>تفاصيل المكافأة #${data.id}</h3>
                        <span class="mlm-modal-close">&times;</span>
                    </div>
                    <div class="mlm-modal-body">
                        <div class="reward-details">
                            <div class="detail-row">
                                <label>العضو:</label>
                                <span>${data.member_name}</span>
                            </div>
                            <div class="detail-row">
                                <label>عدد الأشجار:</label>
                                <span>${data.trees_completed} شجرة</span>
                            </div>
                            <div class="detail-row">
                                <label>مبلغ المكافأة:</label>
                                <span>${data.reward_amount} ج.م</span>
                            </div>
                            <div class="detail-row">
                                <label>المكافأة التراكمية:</label>
                                <span>${data.total_rewards} ج.م</span>
                            </div>
                            <div class="detail-row">
                                <label>الحالة:</label>
                                <span class="mlm-badge mlm-badge-${data.status === 'paid' ? 'paid' : 'pending'}">
                                    ${data.status === 'paid' ? 'تم الدفع' : 'معلق'}
                                </span>
                            </div>
                            <div class="detail-row">
                                <label>تاريخ التحقيق:</label>
                                <span>${data.achieved_date}</span>
                            </div>
                            ${data.paid_date ? `
                            <div class="detail-row">
                                <label>تاريخ الدفع:</label>
                                <span>${data.paid_date}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        
        $('.mlm-modal-close, #mlm-reward-modal').on('click', function(e) {
            if (e.target === this) {
                $('#mlm-reward-modal').remove();
            }
        });
    }

    function updateSummaryStats() {
        // تحديث إحصائيات الملخص
        $.ajax({
            url: mlm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mlm_admin_action',
                mlm_action: 'get_rewards_stats',
                nonce: mlm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.summary-amount').eq(0).text(response.data.total + ' ج.م');
                    $('.summary-amount.pending').text(response.data.pending + ' ج.م');
                    $('.summary-amount.paid').text(response.data.paid + ' ج.م');
                    $('.summary-amount').eq(3).text(response.data.max + ' ج.م');
                }
            }
        });
    }

    // تحديث الإحصائيات كل 30 ثانية
    setInterval(updateSummaryStats, 30000);
});
</script>