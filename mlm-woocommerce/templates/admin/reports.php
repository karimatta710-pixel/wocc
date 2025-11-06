<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mlm-admin-wrap">
    <div class="mlm-header">
        <h1><span class="dashicons dashicons-chart-bar"></span> التقارير والإحصائيات</h1>
        <p>تقارير أداء نظام العمولات المتعددة</p>
    </div>

    <div class="mlm-stats-grid">
        <div class="mlm-stat-card">
            <h3>إجمالي الأعضاء</h3>
            <div class="stat-number"><?php echo number_format($stats['total_members']); ?></div>
            <div class="stat-desc">إجمالي المسجلين في النظام</div>
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

    <div class="mlm-nav-tabs">
        <a href="#financial" class="nav-tab nav-tab-active">التقارير المالية</a>
        <a href="#members" class="nav-tab">تقارير الأعضاء</a>
        <a href="#performance" class="nav-tab">أداء النظام</a>
        <a href="#export" class="nav-tab">تصدير البيانات</a>
    </div>

    <div class="mlm-content">
        <!-- التقارير المالية -->
        <div id="financial" class="mlm-tab-content active">
            <div class="mlm-settings-section">
                <h3>إحصائيات العمولات</h3>
                
                <div class="mlm-form-group">
                    <label for="report_period">الفترة الزمنية</label>
                    <select id="report_period" name="report_period">
                        <option value="today">اليوم</option>
                        <option value="week">هذا الأسبوع</option>
                        <option value="month" selected>هذا الشهر</option>
                        <option value="quarter">هذا الربع</option>
                        <option value="year">هذه السنة</option>
                        <option value="custom">مخصص</option>
                    </select>
                </div>

                <div id="custom_dates" style="display: none;">
                    <div class="mlm-form-group">
                        <label for="start_date">من تاريخ</label>
                        <input type="date" id="start_date" name="start_date">
                    </div>
                    <div class="mlm-form-group">
                        <label for="end_date">إلى تاريخ</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                </div>

                <button class="button button-primary mlm-generate-report" data-type="commissions">توليد التقرير</button>
            </div>

            <div class="mlm-report-results">
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>الفترة</th>
                            <th>عدد العمولات</th>
                            <th>إجمالي العمولات</th>
                            <th>متوسط العمولة</th>
                            <th>عمولات معلقة</th>
                            <th>عمولات مدفوعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>هذا الشهر</td>
                            <td>150</td>
                            <td>45,000 ج.م</td>
                            <td>300 ج.م</td>
                            <td>15,000 ج.م</td>
                            <td>30,000 ج.م</td>
                        </tr>
                        <tr>
                            <td>الشهر الماضي</td>
                            <td>120</td>
                            <td>36,000 ج.م</td>
                            <td>300 ج.م</td>
                            <td>12,000 ج.م</td>
                            <td>24,000 ج.م</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mlm-settings-section">
                <h3>توزيع العمولات حسب المستويات</h3>
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>المستوى</th>
                            <th>عدد العمولات</th>
                            <th>إجمالي القيمة</th>
                            <th>النسبة</th>
                            <th>المتوسط</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>المستوى الأول</td>
                            <td>50</td>
                            <td>25,000 ج.م</td>
                            <td>55.6%</td>
                            <td>500 ج.م</td>
                        </tr>
                        <tr>
                            <td>المستوى الثاني</td>
                            <td>30</td>
                            <td>12,000 ج.م</td>
                            <td>26.7%</td>
                            <td>400 ج.م</td>
                        </tr>
                        <tr>
                            <td>المستوى الثالث</td>
                            <td>20</td>
                            <td>8,000 ج.م</td>
                            <td>17.8%</td>
                            <td>400 ج.م</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- تقارير الأعضاء -->
        <div id="members" class="mlm-tab-content">
            <div class="mlm-settings-section">
                <h3>إحصائيات الأعضاء</h3>
                
                <div class="mlm-form-group">
                    <label for="member_report_type">نوع التقرير</label>
                    <select id="member_report_type" name="member_report_type">
                        <option value="registration">تسجيلات جديدة</option>
                        <option value="activity">النشاط</option>
                        <option value="performance">الأداء</option>
                        <option value="tree_completion">إكمال الأشجار</option>
                    </select>
                </div>

                <button class="button button-primary mlm-generate-report" data-type="members">توليد التقرير</button>
            </div>

            <div class="mlm-report-results">
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>الفترة</th>
                            <th>أعضاء جدد</th>
                            <th>نسبة النمو</th>
                            <th>أعضاء نشطين</th>
                            <th>معدل النشاط</th>
                            <th>متوسط العمولة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>هذا الشهر</td>
                            <td>45</td>
                            <td>+15%</td>
                            <td>38</td>
                            <td>84.4%</td>
                            <td>320 ج.م</td>
                        </tr>
                        <tr>
                            <td>الشهر الماضي</td>
                            <td>39</td>
                            <td>+13%</td>
                            <td>32</td>
                            <td>82.1%</td>
                            <td>310 ج.م</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mlm-settings-section">
                <h3>أفضل الأعضاء أداءً</h3>
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>العضو</th>
                            <th>إجمالي العمولات</th>
                            <th>عدد العمولات</th>
                            <th>الأعضاء المُحالون</th>
                            <th>الأشجار المكتملة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>أحمد محمد</td>
                            <td>12,500 ج.م</td>
                            <td>42</td>
                            <td>15</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>محمد علي</td>
                            <td>9,800 ج.م</td>
                            <td>35</td>
                            <td>12</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>فاطمة أحمد</td>
                            <td>8,200 ج.م</td>
                            <td>28</td>
                            <td>10</td>
                            <td>2</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- أداء النظام -->
        <div id="performance" class="mlm-tab-content">
            <div class="mlm-settings-section">
                <h3>مؤشرات أداء النظام</h3>
                
                <div class="mlm-stats-grid">
                    <div class="mlm-stat-card">
                        <h3>معدل التحويل</h3>
                        <div class="stat-number">18.5%</div>
                        <div class="stat-desc">زوار إلى أعضاء</div>
                    </div>
                    
                    <div class="mlm-stat-card">
                        <h3>متوسط قيمة الطلب</h3>
                        <div class="stat-number">1,250 ج.م</div>
                        <div class="stat-desc">لكل عملية شراء</div>
                    </div>
                    
                    <div class="mlm-stat-card">
                        <h3>معدل الإحالة</h3>
                        <div class="stat-number">2.3</div>
                        <div class="stat-desc">عضو لكل مُحيل</div>
                    </div>
                    
                    <div class="mlm-stat-card">
                        <h3>وقت إكمال الشجرة</h3>
                        <div class="stat-number">45 يوم</div>
                        <div class="stat-desc">المتوسط</div>
                    </div>
                </div>
            </div>

            <div class="mlm-settings-section">
                <h3>اتجاهات النمو</h3>
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>الشهر</th>
                            <th>أعضاء جدد</th>
                            <th>عمولات</th>
                            <th>مكافآت</th>
                            <th>معدل النمو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>يناير</td>
                            <td>25</td>
                            <td>15,000 ج.م</td>
                            <td>5,000 ج.م</td>
                            <td>+25%</td>
                        </tr>
                        <tr>
                            <td>فبراير</td>
                            <td>32</td>
                            <td>22,000 ج.م</td>
                            <td>6,500 ج.م</td>
                            <td>+28%</td>
                        </tr>
                        <tr>
                            <td>مارس</td>
                            <td>45</td>
                            <td>35,000 ج.م</td>
                            <td>8,200 ج.م</td>
                            <td>+40%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- تصدير البيانات -->
        <div id="export" class="mlm-tab-content">
            <div class="mlm-settings-section">
                <h3>تصدير البيانات والتقارير</h3>
                
                <div class="mlm-form-group">
                    <label for="export_type">نوع التقرير</label>
                    <select id="export_type" name="export_type">
                        <option value="commissions">العمولات</option>
                        <option value="members">الأعضاء</option>
                        <option value="rewards">المكافآت</option>
                        <option value="trees">الأشجار</option>
                        <option value="financial">التقارير المالية</option>
                    </select>
                </div>

                <div class="mlm-form-group">
                    <label for="export_format">صيغة الملف</label>
                    <select id="export_format" name="export_format">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <div class="mlm-form-group">
                    <label for="export_period">الفترة</label>
                    <select id="export_period" name="export_period">
                        <option value="all">الكل</option>
                        <option value="today">اليوم</option>
                        <option value="week">هذا الأسبوع</option>
                        <option value="month">هذا الشهر</option>
                        <option value="year">هذه السنة</option>
                        <option value="custom">مخصص</option>
                    </select>
                </div>

                <button class="button button-primary mlm-export-btn" data-type="report">تصدير التقرير</button>
                <button class="button button-secondary mlm-export-btn" data-type="full">تصدير كافة البيانات</button>
            </div>

            <div class="mlm-settings-section">
                <h3>التقارير المجدولة</h3>
                
                <table class="mlm-table">
                    <thead>
                        <tr>
                            <th>نوع التقرير</th>
                            <th>التكرار</th>
                            <th>آخر تشغيل</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>تقرير العمولات الأسبوعي</td>
                            <td>أسبوعي</td>
                            <td>2024-01-15</td>
                            <td><span class="mlm-badge mlm-badge-active">نشط</span></td>
                            <td>
                                <button class="mlm-action-btn">تعطيل</button>
                                <button class="mlm-action-btn">تشغيل الآن</button>
                            </td>
                        </tr>
                        <tr>
                            <td>تقرير الأعضاء الشهري</td>
                            <td>شهري</td>
                            <td>2024-01-01</td>
                            <td><span class="mlm-badge mlm-badge-active">نشط</span></td>
                            <td>
                                <button class="mlm-action-btn">تعطيل</button>
                                <button class="mlm-action-btn">تشغيل الآن</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button class="button button-primary">إضافة تقرير مجدول</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // تبديل التبويبات
    $('.mlm-nav-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        $('.mlm-nav-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.mlm-tab-content').removeClass('active');
        $($(this).attr('href')).addClass('active');
    });

    // إظهار/إخفاء التواريخ المخصصة
    $('#report_period').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom_dates').show();
        } else {
            $('#custom_dates').hide();
        }
    });

    // تصدير التقارير
    $('.mlm-export-btn').on('click', function() {
        const type = $(this).data('type');
        const format = $('#export_format').val();
        const reportType = $('#export_type').val();
        const period = $('#export_period').val();
        
        // تنفيذ عملية التصدير
        alert(`جاري تصدير ${type === 'report' ? 'التقرير' : 'كافة البيانات'} بصيغة ${format}`);
        
        // في التطبيق الحقيقي، سيتم توجيه المستخدم لرابط التحميل
        window.open(`admin-ajax.php?action=mlm_export&type=${reportType}&format=${format}&period=${period}`, '_blank');
    });

    // توليد التقارير
    $('.mlm-generate-report').on('click', function() {
        const type = $(this).data('type');
        const period = $('#report_period').val();
        
        // إظهار تحميل
        $(this).html('<span class="mlm-loading"></span> جاري توليد التقرير...');
        
        // محاكاة عملية التوليد
        setTimeout(() => {
            $(this).text('توليد التقرير');
            alert('تم توليد التقرير بنجاح');
        }, 2000);
    });
});
</script>