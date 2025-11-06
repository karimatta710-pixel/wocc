<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-frontend-container">
    <div class="mlm-dashboard-header">
        <h1>إدارة العمولات</h1>
        <p>تتبع عمولاتك وتاريخ دفعاتك</p>
    </div>

    <!-- ملخص سريع -->
    <div class="mlm-stats-cards">
        <div class="mlm-stat-card">
            <h3>الرصيد الحالي</h3>
            <span class="stat-number"><?php echo number_format($member->pending_commissions, 2); ?> ج.م</span>
            <div class="stat-desc">قابل للسحب</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>إجمالي العمولات</h3>
            <span class="stat-number"><?php echo number_format($member->total_commissions, 2); ?> ج.م</span>
            <div class="stat-desc">منذ الانضمام</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>عمولات هذا الشهر</h3>
            <span class="stat-number">
                <?php
                $current_month = date('Y-m');
                $month_commissions = array_filter($commissions, function($c) use ($current_month) {
                    return date('Y-m', strtotime($c->created_date)) === $current_month;
                });
                $month_total = array_sum(array_column($month_commissions, 'commission_amount'));
                echo number_format($month_total, 2);
                ?> ج.م
            </span>
            <div class="stat-desc">شهر <?php echo date('F'); ?></div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>آخر عمولة</h3>
            <span class="stat-number">
                <?php
                $last_commission = !empty($commissions) ? $commissions[0]->commission_amount : 0;
                echo number_format($last_commission, 2);
                ?> ج.م
            </span>
            <div class="stat-desc">آخر عملية</div>
        </div>
    </div>

    <!-- طلب سحب -->
    <div class="mlm-section">
        <h2>طلب ستح الأموال</h2>
        
        <div class="mlm-withdrawal-form">
            <div class="mlm-form-group">
                <label for="withdrawal_amount">المبلغ المطلوب سحبه (ج.م)</label>
                <input type="number" id="withdrawal_amount" name="withdrawal_amount" 
                       min="50" max="<?php echo $member->pending_commissions; ?>" 
                       step="10" value="<?php echo min(100, $member->pending_commissions); ?>">
                <p class="description">الحد الأدنى للسحب: 50 ج.م | الرصيد المتاح: <?php echo number_format($member->pending_commissions, 2); ?> ج.م</p>
            </div>
            
            <div class="mlm-form-group">
                <label for="payment_method">طريقة السحب</label>
                <select id="payment_method" name="payment_method">
                    <option value="bank">تحويل بنكي</option>
                    <option value="wallet">محفظة إلكترونية</option>
                </select>
            </div>
            
            <div class="mlm-form-group">
                <label for="account_details">تفاصيل الحساب</label>
                <textarea id="account_details" name="account_details" placeholder="أدخل تفاصيل حسابك البنكي أو محفظتك الإلكترونية..." rows="3"></textarea>
            </div>
            
            <button type="button" class="button button-primary mlm-request-withdrawal">طلب السحب</button>
        </div>
    </div>

    <!-- تصفية العمولات -->
    <div class="mlm-section">
        <div class="mlm-filter-controls">
            <select id="commission_filter" class="mlm-filter-select">
                <option value="all">جميع العمولات</option>
                <option value="pending">معلقة فقط</option>
                <option value="paid">مدفوعة فقط</option>
            </select>
            
            <select id="time_filter" class="mlm-filter-select">
                <option value="all">كل الفترات</option>
                <option value="today">اليوم</option>
                <option value="week">هذا الأسبوع</option>
                <option value="month">هذا الشهر</option>
                <option value="year">هذه السنة</option>
            </select>
            
            <input type="text" id="search_commissions" placeholder="البحث في العمولات..." class="mlm-search-input">
        </div>
    </div>

    <!-- قائمة العمولات -->
    <div class="mlm-section">
        <h2>سجل العمولات</h2>
        
        <table class="mlm-table">
            <thead>
                <tr>
                    <th>رقم الطلب</th>
                    <th>المبلغ</th>
                    <th>النسبة</th>
                    <th>المستوى</th>
                    <th>العضو</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody id="commissions_list">
                <?php if (!empty($commissions)): ?>
                    <?php foreach ($commissions as $commission): ?>
                        <tr data-status="<?php echo $commission->status; ?>" 
                            data-date="<?php echo date('Y-m-d', strtotime($commission->created_date)); ?>">
                            <td>
                                <a href="<?php echo wc_get_order($commission->order_id) ? wc_get_order($commission->order_id)->get_view_order_url() : '#'; ?>" 
                                   target="_blank" class="order-link">
                                    #<?php echo $commission->order_id; ?>
                                </a>
                            </td>
                            <td class="amount-cell"><?php echo number_format($commission->commission_amount, 2); ?> ج.م</td>
                            <td><?php echo $commission->commission_rate; ?>%</td>
                            <td>
                                <span class="level-badge level-<?php echo $commission->level; ?>">
                                    المستوى <?php echo $commission->level; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $member_user = get_userdata($commission->member_id);
                                echo $member_user ? esc_html($member_user->display_name) : 'عضو #' . $commission->member_id;
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($commission->created_date)); ?></td>
                            <td>
                                <span class="mlm-badge mlm-badge-<?php echo $commission->status === 'paid' ? 'paid' : 'pending'; ?>">
                                    <?php echo $commission->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="mlm-action-btn view-details" data-id="<?php echo $commission->id; ?>">تفاصيل</button>
                                <?php if ($commission->status === 'pending'): ?>
                                    <button class="mlm-action-btn mlm-action-btn-primary request-payment" data-id="<?php echo $commission->id; ?>">طلب الدفع</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">
                            <div style="padding: 40px; color: #666;">
                                <div style="font-size: 3em; margin-bottom: 20px;">💸</div>
                                <h3>لا توجد عمولات حتى الآن</h3>
                                <p>ادعُ أصدقاءك للانضمام وابدأ في كسب العمولات!</p>
                                <a href="<?php echo esc_url(add_query_arg('ref', $member->referral_code, home_url())); ?>" 
                                   class="button button-primary" target="_blank">
                                    مشاركة رابط الإحالة
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (count($commissions) > 10): ?>
            <div style="text-align: center; margin-top: 20px;">
                <button class="button button-secondary mlm-load-more" data-page="1">تحميل المزيد من العمولات</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- إحصائيات العمولات -->
    <div class="mlm-section">
        <h2>إحصائيات العمولات</h2>
        
        <div class="mlm-stats-grid">
            <div class="mlm-stat-card">
                <h3>أعلى عمولة</h3>
                <span class="stat-number">
                    <?php
                    $max_commission = !empty($commissions) ? max(array_column($commissions, 'commission_amount')) : 0;
                    echo number_format($max_commission, 2);
                    ?> ج.م
                </span>
            </div>
            
            <div class="mlm-stat-card">
                <h3>متوسط العمولة</h3>
                <span class="stat-number">
                    <?php
                    $avg_commission = !empty($commissions) ? array_sum(array_column($commissions, 'commission_amount')) / count($commissions) : 0;
                    echo number_format($avg_commission, 2);
                    ?> ج.م
                </span>
            </div>
            
            <div class="mlm-stat-card">
                <h3>عمولات المستوى الأول</h3>
                <span class="stat-number">
                    <?php
                    $level1_commissions = array_sum(array_column(array_filter($commissions, function($c) {
                        return $c->level == 1;
                    }), 'commission_amount'));
                    echo number_format($level1_commissions, 2);
                    ?> ج.م
                </span>
            </div>
            
            <div class="mlm-stat-card">
                <h3>عمولات المستوى الثاني</h3>
                <span class="stat-number">
                    <?php
                    $level2_commissions = array_sum(array_column(array_filter($commissions, function($c) {
                        return $c->level == 2;
                    }), 'commission_amount'));
                    echo number_format($level2_commissions, 2);
                    ?> ج.م
                </span>
            </div>
        </div>

        <!-- رسم بياني بسيط للعمولات -->
        <div class="mlm-chart-section">
            <h3>توزيع العمولات خلال الشهر</h3>
            <div class="mlm-simple-chart">
                <?php
                $month_days = date('t');
                $daily_commissions = array_fill(1, $month_days, 0);
                
                foreach ($commissions as $commission) {
                    $day = (int) date('j', strtotime($commission->created_date));
                    if ($day <= $month_days) {
                        $daily_commissions[$day] += $commission->commission_amount;
                    }
                }
                
                $max_day = max($daily_commissions) ?: 1;
                ?>
                
                <div class="chart-bars">
                    <?php for ($day = 1; $day <= $month_days; $day++): ?>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: <?php echo ($daily_commissions[$day] / $max_day) * 100; ?>%"
                                 title="<?php echo $day; ?>: <?php echo number_format($daily_commissions[$day], 2); ?> ج.م">
                            </div>
                            <span class="chart-label"><?php echo $day; ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mlm-withdrawal-form {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border: 2px dashed #dee2e6;
}

.mlm-filter-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.mlm-filter-select,
.mlm-search-input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.order-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.order-link:hover {
    text-decoration: underline;
}

.amount-cell {
    font-weight: bold;
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

.mlm-chart-section {
    margin-top: 30px;
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.mlm-simple-chart {
    margin-top: 20px;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 150px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 30px;
}

.chart-bar-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.chart-bar {
    width: 100%;
    background: linear-gradient(to top, #667eea, #764ba2);
    border-radius: 2px 2px 0 0;
    transition: all 0.3s ease;
    min-height: 2px;
}

.chart-bar:hover {
    opacity: 0.8;
}

.chart-label {
    margin-top: 5px;
    font-size: 11px;
    color: #7f8c8d;
}

@media (max-width: 768px) {
    .mlm-filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .mlm-filter-select,
    .mlm-search-input {
        width: 100%;
    }
    
    .chart-bars {
        gap: 2px;
        height: 120px;
    }
    
    .chart-label {
        font-size: 9px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // طلب سحب الأموال
    $('.mlm-request-withdrawal').on('click', function() {
        const amount = parseFloat($('#withdrawal_amount').val());
        const method = $('#payment_method').val();
        const details = $('#account_details').val();
        const available = parseFloat('<?php echo $member->pending_commissions; ?>');
        
        if (!amount || amount < 50) {
            alert('الحد الأدنى للسحب هو 50 ج.م');
            return;
        }
        
        if (amount > available) {
            alert('المبلغ المطلوب exceeds رصيدك المتاح');
            return;
        }
        
        if (!details.trim()) {
            alert('يرجى إدخال تفاصيل الحساب');
            return;
        }
        
        if (confirm(`هل تريد تأكيد طلب سحب مبلغ ${amount} ج.م؟`)) {
            // محاكاة إرسال الطلب
            $(this).html('<span class="mlm-loading"></span> جاري المعالجة...').prop('disabled', true);
            
            setTimeout(() => {
                alert('تم إرسال طلب السحب بنجاح. سيتم معالجته خلال 24-48 ساعة.');
                $(this).text('طلب السحب').prop('disabled', false);
                $('#withdrawal_amount').val('');
                $('#account_details').val('');
            }, 2000);
        }
    });

    // تصفية العمولات
    $('#commission_filter, #time_filter').on('change', filterCommissions);
    $('#search_commissions').on('keyup', filterCommissions);
    
    function filterCommissions() {
        const statusFilter = $('#commission_filter').val();
        const timeFilter = $('#time_filter').val();
        const searchTerm = $('#search_commissions').val().toLowerCase();
        const today = new Date();
        
        $('#commissions_list tr').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const date = new Date($row.data('date'));
            const text = $row.text().toLowerCase();
            
            let show = true;
            
            // تصفية حسب الحالة
            if (statusFilter !== 'all' && status !== statusFilter) {
                show = false;
            }
            
            // تصفية حسب الوقت
            if (timeFilter !== 'all') {
                const timeDiff = today - date;
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);
                
                switch (timeFilter) {
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
            
            // تصفية حسب البحث
            if (searchTerm && text.indexOf(searchTerm) === -1) {
                show = false;
            }
            
            $row.toggle(show);
        });
    }

    // تحميل المزيد من العمولات
    $('.mlm-load-more').on('click', function() {
        const $btn = $(this);
        const page = parseInt($btn.data('page')) + 1;
        
        $btn.html('<span class="mlm-loading"></span> جاري التحميل...').prop('disabled', true);
        
        // محاكاة تحميل المزيد
        setTimeout(() => {
            $btn.text('تحميل المزيد').prop('disabled', false).data('page', page);
            alert('تم تحميل المزيد من العمولات');
        }, 1500);
    });

    // عرض تفاصيل العمولة
    $('.view-details').on('click', function() {
        const commissionId = $(this).data('id');
        alert('عرض تفاصيل العمولة #' + commissionId + ' - هذه الميزة قيد التطوير');
    });

    // طلب دفع عمولة
    $('.request-payment').on('click', function() {
        const commissionId = $(this).data('id');
        if (confirm('هل تريد طلب دفع هذه العمولة؟')) {
            $(this).html('<span class="mlm-loading"></span> جاري الطلب...').prop('disabled', true);
            
            setTimeout(() => {
                alert('تم إرسال طلب الدفع بنجاح');
                $(this).text('تم الطلب').prop('disabled', true).removeClass('mlm-action-btn-primary');
            }, 1000);
        }
    });
});
</script>