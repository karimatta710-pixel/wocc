<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-search-box">
    <form method="get">
        <input type="hidden" name="page" value="mlm-commissions">
        <input type="text" name="s" placeholder="البحث في العمولات..." class="mlm-search-input">
        <button type="submit" class="button button-primary">بحث</button>
    </form>
</div>

<div class="mlm-filter-section">
    <select class="mlm-filter-select" data-column="5">
        <option value="all">جميع الحالات</option>
        <option value="معلق">معلق</option>
        <option value="تم الدفع">تم الدفع</option>
        <option value="ملغى">ملغى</option>
    </select>
    
    <select class="mlm-filter-select" data-column="3">
        <option value="all">جميع المستويات</option>
        <option value="1">المستوى 1</option>
        <option value="2">المستوى 2</option>
        <option value="3">المستوى 3</option>
    </select>
    
    <select class="mlm-filter-select" id="date_filter">
        <option value="all">كل الفترات</option>
        <option value="today">اليوم</option>
        <option value="week">هذا الأسبوع</option>
        <option value="month">هذا الشهر</option>
        <option value="year">هذه السنة</option>
    </select>
</div>

<div class="mlm-commissions-summary">
    <div class="summary-card">
        <h4>إجمالي العمولات</h4>
        <div class="summary-amount"><?php echo number_format($stats['total_commissions'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>عمولات معلقة</h4>
        <div class="summary-amount pending"><?php echo number_format($stats['pending_commissions'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>عمولات مدفوعة</h4>
        <div class="summary-amount paid"><?php echo number_format($stats['paid_commissions'], 2); ?> ج.م</div>
    </div>
    <div class="summary-card">
        <h4>متوسط العمولة</h4>
        <div class="summary-amount"><?php echo number_format($stats['avg_commission'], 2); ?> ج.م</div>
    </div>
</div>

<table class="mlm-table">
    <thead>
        <tr>
            <th>رقم العمولة</th>
            <th>رقم الطلب</th>
            <th>العضو</th>
            <th>الراعي</th>
            <th>المستوى</th>
            <th>المبلغ</th>
            <th>النسبة</th>
            <th>تاريخ العمولة</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($commissions)): ?>
            <?php foreach ($commissions as $commission): ?>
                <tr data-status="<?php echo $commission->status; ?>" 
                    data-level="<?php echo $commission->level; ?>"
                    data-date="<?php echo date('Y-m-d', strtotime($commission->created_date)); ?>">
                    <td>#<?php echo $commission->id; ?></td>
                    <td>
                        <a href="<?php echo wc_get_order($commission->order_id) ? wc_get_order($commission->order_id)->get_view_order_url() : '#'; ?>" 
                           target="_blank" class="order-link">
                            #<?php echo $commission->order_id; ?>
                        </a>
                    </td>
                    <td>
                        <?php
                        $member_user = get_userdata($commission->member_id);
                        if ($member_user):
                        ?>
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $commission->member_id); ?>">
                                <?php echo esc_html($member_user->display_name ?: $member_user->user_login); ?>
                            </a>
                        <?php else: ?>
                            عضو #<?php echo $commission->member_id; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $sponsor_user = get_userdata($commission->sponsor_id);
                        if ($sponsor_user):
                        ?>
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $commission->sponsor_id); ?>">
                                <?php echo esc_html($sponsor_user->display_name ?: $sponsor_user->user_login); ?>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="level-badge level-<?php echo $commission->level; ?>">
                            المستوى <?php echo $commission->level; ?>
                        </span>
                    </td>
                    <td class="amount-cell"><?php echo number_format($commission->commission_amount, 2); ?> ج.م</td>
                    <td><?php echo $commission->commission_rate; ?>%</td>
                    <td><?php echo date('Y-m-d H:i', strtotime($commission->created_date)); ?></td>
                    <td>
                        <span class="mlm-badge mlm-badge-<?php echo $commission->status === 'paid' ? 'paid' : 'pending'; ?>">
                            <?php echo $commission->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="mlm-action-buttons">
                            <button class="mlm-action-btn view-details" data-id="<?php echo $commission->id; ?>" title="عرض التفاصيل">
                                <span class="dashicons dashicons-visibility"></span>
                            </button>
                            
                            <?php if ($commission->status === 'pending'): ?>
                                <button class="mlm-action-btn mlm-action-btn-success mlm-pay-commission" 
                                        data-id="<?php echo $commission->id; ?>" title="دفع العمولة">
                                    <span class="dashicons dashicons-yes"></span>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($commission->status !== 'cancelled'): ?>
                                <button class="mlm-action-btn mlm-action-btn-danger mlm-cancel-commission" 
                                        data-id="<?php echo $commission->id; ?>" title="إلغاء العمولة">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            <?php endif; ?>
                            
                            <button class="mlm-action-btn mlm-export-commission" 
                                    data-id="<?php echo $commission->id; ?>" title="تصدير">
                                <span class="dashicons dashicons-download"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" style="text-align: center; padding: 40px;">
                    <div style="color: #666;">
                        <span class="dashicons dashicons-money" style="font-size: 48px; display: block; margin-bottom: 15px;"></span>
                        <h3>لا توجد عمولات</h3>
                        <p>لم يتم تسجيل أي عمولات في النظام بعد.</p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_commissions > $per_page): ?>
    <div class="mlm-pagination">
        <?php
        $total_pages = ceil($total_commissions / $per_page);
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
.mlm-commissions-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.summary-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e1e1e1;
    text-align: center;
}

.summary-card h4 {
    margin: 0 0 10px 0;
    font-size: 0.9em;
    color: #666;
    text-transform: uppercase;
}

.summary-amount {
    font-size: 1.5em;
    font-weight: bold;
    color: #333;
}

.summary-amount.pending {
    color: #e74c3c;
}

.summary-amount.paid {
    color: #27ae60;
}

.level-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
}

.level-1 { background: #e8f5e8; color: #27ae60; }
.level-2 { background: #e8f4fd; color: #3498db; }
.level-3 { background: #fef5e7; color: #f39c12; }

.amount-cell {
    font-weight: bold;
    color: #27ae60;
}

.order-link {
    color: #0073aa;
    text-decoration: none;
}

.order-link:hover {
    text-decoration: underline;
}

.mlm-action-buttons {
    display: flex;
    gap: 5px;
    justify-content: center;
}

@media (max-width: 768px) {
    .mlm-commissions-summary {
        grid-template-columns: 1fr 1fr;
    }
    
    .mlm-action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // تصفية العمولات
    function filterCommissions() {
        const statusFilter = $('.mlm-filter-select[data-column="5"]').val();
        const levelFilter = $('.mlm-filter-select[data-column="3"]').val();
        const dateFilter = $('#date_filter').val();
        const today = new Date();
        
        $('.mlm-table tbody tr').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const level = $row.data('level');
            const date = new Date($row.data('date'));
            
            let show = true;
            
            // تصفية حسب الحالة
            if (statusFilter !== 'all') {
                const statusText = status === 'paid' ? 'تم الدفع' : 'معلق';
                if (statusText !== statusFilter) {
                    show = false;
                }
            }
            
            // تصفية حسب المستوى
            if (levelFilter !== 'all' && level != levelFilter) {
                show = false;
            }
            
            // تصفية حسب التاريخ
            if (dateFilter !== 'all') {
                const timeDiff = today - date;
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);
                
                switch (dateFilter) {
                    case 'today':
                        if (daysDiff >= 1) show = false;
                        break;
                    case 'week':
                        if (daysDiff > 7) show = false;
                        break;
                    case 'month':
                        if (daysDiff > 30) show = false;
                        break;
                    case 'year':
                        if (daysDiff > 365) show = false;
                        break;
                }
            }
            
            $row.toggle(show);
        });
    }

    $('.mlm-filter-select, #date_filter').on('change', filterCommissions);

    // دفع العمولة
    $('.mlm-pay-commission').on('click', function() {
        const commissionId = $(this).data('id');
        const $btn = $(this);
        
        if (confirm('هل تريد دفع هذه العمولة؟')) {
            $btn.html('<span class="mlm-loading"></span>').prop('disabled', true);
            
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'pay_commission',
                    commission_id: commissionId,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').find('.mlm-badge')
                            .removeClass('mlm-badge-pending')
                            .addClass('mlm-badge-paid')
                            .text('تم الدفع');
                            
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

    // إلغاء العمولة
    $('.mlm-cancel-commission').on('click', function() {
        const commissionId = $(this).data('id');
        const $btn = $(this);
        
        if (confirm('هل أنت متأكد من إلغاء هذه العمولة؟ لا يمكن التراجع عن هذا الإجراء.')) {
            $btn.html('<span class="mlm-loading"></span>').prop('disabled', true);
            
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'cancel_commission',
                    commission_id: commissionId,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').find('.mlm-badge')
                            .removeClass('mlm-badge-pending mlm-badge-paid')
                            .addClass('mlm-badge-danger')
                            .text('ملغى');
                            
                        $btn.closest('.mlm-action-buttons').find('.mlm-pay-commission').remove();
                        $btn.remove();
                        updateSummaryStats();
                    } else {
                        alert('حدث خطأ: ' + response.data);
                        $btn.html('<span class="dashicons dashicons-no"></span>').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('حدث خطأ في الاتصال');
                    $btn.html('<span class="dashicons dashicons-no"></span>').prop('disabled', false);
                }
            });
        }
    });

    // عرض تفاصيل العمولة
    $('.view-details').on('click', function() {
        const commissionId = $(this).data('id');
        
        $.ajax({
            url: mlm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'mlm_admin_action',
                mlm_action: 'get_commission_details',
                commission_id: commissionId,
                nonce: mlm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showCommissionModal(response.data);
                } else {
                    alert('حدث خطأ في تحميل البيانات');
                }
            }
        });
    });

    // تصدير العمولة
    $('.mlm-export-commission').on('click', function() {
        const commissionId = $(this).data('id');
        window.open(mlm_admin.ajax_url + '?action=mlm_export_commission&commission_id=' + commissionId + '&nonce=' + mlm_admin.nonce, '_blank');
    });

    function showCommissionModal(data) {
        const modalHtml = `
            <div id="mlm-commission-modal" class="mlm-modal">
                <div class="mlm-modal-content">
                    <div class="mlm-modal-header">
                        <h3>تفاصيل العمولة #${data.id}</h3>
                        <span class="mlm-modal-close">&times;</span>
                    </div>
                    <div class="mlm-modal-body">
                        <div class="commission-details">
                            <div class="detail-row">
                                <label>رقم الطلب:</label>
                                <span>#${data.order_id}</span>
                            </div>
                            <div class="detail-row">
                                <label>المبلغ:</label>
                                <span>${data.commission_amount} ج.م</span>
                            </div>
                            <div class="detail-row">
                                <label>النسبة:</label>
                                <span>${data.commission_rate}%</span>
                            </div>
                            <div class="detail-row">
                                <label>المستوى:</label>
                                <span>${data.level}</span>
                            </div>
                            <div class="detail-row">
                                <label>الحالة:</label>
                                <span class="mlm-badge mlm-badge-${data.status === 'paid' ? 'paid' : 'pending'}">
                                    ${data.status === 'paid' ? 'تم الدفع' : 'معلق'}
                                </span>
                            </div>
                            <div class="detail-row">
                                <label>تاريخ الإنشاء:</label>
                                <span>${data.created_date}</span>
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
        
        $('.mlm-modal-close, #mlm-commission-modal').on('click', function(e) {
            if (e.target === this) {
                $('#mlm-commission-modal').remove();
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
                mlm_action: 'get_commissions_stats',
                nonce: mlm_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.summary-amount').eq(0).text(response.data.total + ' ج.م');
                    $('.summary-amount.pending').text(response.data.pending + ' ج.م');
                    $('.summary-amount.paid').text(response.data.paid + ' ج.م');
                    $('.summary-amount').eq(3).text(response.data.avg + ' ج.م');
                }
            }
        });
    }

    // تحديث الإحصائيات كل 30 ثانية
    setInterval(updateSummaryStats, 30000);
});
</script>