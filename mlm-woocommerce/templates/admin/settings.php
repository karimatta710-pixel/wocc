<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap mlm-admin-wrap">
    <div class="mlm-header">
        <h1><span class="dashicons dashicons-admin-settings"></span> إعدادات نظام العمولات المتعددة</h1>
        <p>إدارة إعدادات النظام والمستويات والعمولات والمكافآت</p>
    </div>

    <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
        <div class="mlm-notice">
            <p>تم حفظ الإعدادات بنجاح.</p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field('mlm_save_settings'); ?>
        
        <div class="mlm-nav-tabs">
            <a href="#general" class="nav-tab nav-tab-active">الإعدادات العامة</a>
            <a href="#commissions" class="nav-tab">العمولات</a>
            <a href="#tree" class="nav-tab">هيكل الشجرة</a>
            <a href="#rewards" class="nav-tab">المكافآت</a>
            <a href="#advanced" class="nav-tab">متقدم</a>
        </div>

        <div class="mlm-content">
            <!-- الإعدادات العامة -->
            <div id="general" class="mlm-tab-content active">
                <div class="mlm-settings-section">
                    <h3>الإعدادات الأساسية</h3>
                    
                    <div class="mlm-form-group">
                        <label for="min_purchase_amount">الحد الأدنى للشراء للانضمام (ج.م)</label>
                        <input type="number" id="min_purchase_amount" name="min_purchase_amount" 
                               value="<?php echo esc_attr($settings['min_purchase_amount']); ?>" 
                               min="0" step="100" required>
                        <p class="description">الحد الأدنى لقيمة الشراء ليصبح العميل عضواً في النظام</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="auto_join">الانضمام التلقائي</label>
                        <select id="auto_join" name="auto_join">
                            <option value="yes" <?php selected($settings['auto_join'], 'yes'); ?>>مفعل</option>
                            <option value="no" <?php selected($settings['auto_join'], 'no'); ?>>معطل</option>
                        </select>
                        <p class="description">انضمام العملاء تلقائياً عند تحقيق شراء بالحد الأدنى</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="currency">العملة</label>
                        <select id="currency" name="currency">
                            <option value="EGP" selected>جنيه مصري (ج.م)</option>
                            <option value="USD">دولار أمريكي ($)</option>
                            <option value="EUR">يورو (€)</option>
                            <option value="SAR">ريال سعودي (ر.س)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- إعدادات العمولات -->
            <div id="commissions" class="mlm-tab-content">
                <div class="mlm-settings-section">
                    <h3>نسب العمولات للمستويات</h3>
                    
                    <div class="mlm-form-group">
                        <label for="commission_level_1">نسبة العمولة - المستوى الأول (%)</label>
                        <input type="number" id="commission_level_1" name="commission_level_1" 
                               value="<?php echo esc_attr($settings['commission_levels'][1]); ?>" 
                               min="0" max="100" step="0.1" class="commission-rate-input" required>
                        <p class="description">نسبة العمولة للأعضاء المباشرين في المستوى الأول</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="commission_level_2">نسبة العمولة - المستوى الثاني (%)</label>
                        <input type="number" id="commission_level_2" name="commission_level_2" 
                               value="<?php echo esc_attr($settings['commission_levels'][2]); ?>" 
                               min="0" max="100" step="0.1" class="commission-rate-input" required>
                        <p class="description">نسبة العمولة لأعضاء المستوى الثاني</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="commission_level_3">نسبة العمولة - المستوى الثالث (%)</label>
                        <input type="number" id="commission_level_3" name="commission_level_3" 
                               value="<?php echo esc_attr($settings['commission_levels'][3]); ?>" 
                               min="0" max="100" step="0.1" class="commission-rate-input" required>
                        <p class="description">نسبة العمولة لأعضاء المستوى الثالث</p>
                    </div>

                    <div class="commission-preview">
                        <!-- سيتم ملؤها بواسطة JavaScript -->
                    </div>
                </div>

                <div class="mlm-settings-section">
                    <h3>إعدادات الدفع</h3>
                    
                    <div class="mlm-form-group">
                        <label for="payout_method">طريقة الدفع</label>
                        <select id="payout_method" name="payout_method">
                            <option value="manual">يدوي</option>
                            <option value="wallet">محفظة إلكترونية</option>
                            <option value="bank">تحويل بنكي</option>
                        </select>
                    </div>

                    <div class="mlm-form-group">
                        <label for="min_payout">الحد الأدنى للسحب (ج.م)</label>
                        <input type="number" id="min_payout" name="min_payout" value="100" min="0" step="10">
                    </div>

                    <div class="mlm-form-group">
                        <label for="payout_schedule">موعد الدفع</label>
                        <select id="payout_schedule" name="payout_schedule">
                            <option value="daily">يومي</option>
                            <option value="weekly" selected>أسبوعي</option>
                            <option value="monthly">شهري</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- هيكل الشجرة -->
            <div id="tree" class="mlm-tab-content">
                <div class="mlm-settings-section">
                    <h3>هيكل الشجرة الشبكية</h3>
                    
                    <div class="mlm-form-group">
                        <label for="level1_count">عدد الأعضاء في المستوى الأول</label>
                        <input type="number" id="level1_count" name="level1_count" 
                               value="<?php echo esc_attr($settings['tree_structure']['level1_count']); ?>" 
                               min="1" max="10" required>
                        <p class="description">عدد الأعضاء المباشرين تحت كل عضو</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="level2_count">عدد الأعضاء في المستوى الثاني</label>
                        <input type="number" id="level2_count" name="level2_count" 
                               value="<?php echo esc_attr($settings['tree_structure']['level2_count']); ?>" 
                               min="1" max="20" required>
                        <p class="description">إجمالي الأعضاء في المستوى الثاني</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="level3_count">عدد الأعضاء في المستوى الثالث</label>
                        <input type="number" id="level3_count" name="level3_count" 
                               value="<?php echo esc_attr($settings['tree_structure']['level3_count']); ?>" 
                               min="1" max="50" required>
                        <p class="description">إجمالي الأعضاء في المستوى الثالث</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="max_levels">الحد الأقصى للمستويات</label>
                        <select id="max_levels" name="max_levels">
                            <option value="3" selected>3 مستويات</option>
                            <option value="4">4 مستويات</option>
                            <option value="5">5 مستويات</option>
                            <option value="6">6 مستويات</option>
                        </select>
                        <p class="description">الحد الأقصى لمستويات العمولة في النظام</p>
                    </div>
                </div>

                <div class="mlm-settings-section">
                    <h3>معاينة هيكل الشجرة</h3>
                    <div class="mlm-tree-preview">
                        <div class="mlm-tree-level">
                            <h4>المستوى الأول: <?php echo esc_html($settings['tree_structure']['level1_count']); ?> أعضاء</h4>
                        </div>
                        <div class="mlm-tree-level">
                            <h4>المستوى الثاني: <?php echo esc_html($settings['tree_structure']['level2_count']); ?> أعضاء</h4>
                        </div>
                        <div class="mlm-tree-level">
                            <h4>المستوى الثالث: <?php echo esc_html($settings['tree_structure']['level3_count']); ?> أعضاء</h4>
                        </div>
                        <div class="mlm-tree-total">
                            <strong>الإجمالي: <?php echo array_sum($settings['tree_structure']); ?> عضو في الشجرة الكاملة</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- نظام المكافآت -->
            <div id="rewards" class="mlm-tab-content">
                <div class="mlm-settings-section">
                    <h3>هيكل المكافآت</h3>
                    <p class="description">تعريف المكافآت بناءً على عدد الأشجار المكتملة</p>
                    
                    <div class="mlm-rewards-container">
                        <?php foreach ($settings['reward_structure'] as $trees => $amount): ?>
                            <div class="mlm-reward-row">
                                <input type="number" name="reward_trees[]" placeholder="عدد الأشجار" 
                                       value="<?php echo esc_attr($trees); ?>" min="1" required>
                                <input type="number" name="reward_amounts[]" placeholder="المبلغ" 
                                       value="<?php echo esc_attr($amount); ?>" min="0" step="0.01" required>
                                <button type="button" class="button button-secondary mlm-remove-reward">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" class="button button-primary mlm-add-reward">+ إضافة مكافأة</button>
                </div>

                <div class="mlm-settings-section">
                    <h3>معاينة جدول المكافآت</h3>
                    <table class="mlm-table">
                        <thead>
                            <tr>
                                <th>عدد الأشجار</th>
                                <th>المكافأة (ج.م)</th>
                                <th>المكافأة التراكمية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cumulative = 0;
                            ksort($settings['reward_structure']);
                            foreach ($settings['reward_structure'] as $trees => $amount):
                                $cumulative += $amount;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($trees); ?> شجرة</td>
                                    <td><?php echo number_format($amount, 2); ?> ج.م</td>
                                    <td><?php echo number_format($cumulative, 2); ?> ج.م</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- الإعدادات المتقدمة -->
            <div id="advanced" class="mlm-tab-content">
                <div class="mlm-settings-section">
                    <h3>إعدادات الأمان</h3>
                    
                    <div class="mlm-form-group">
                        <label for="ip_restriction">تقييد IP</label>
                        <select id="ip_restriction" name="ip_restriction">
                            <option value="no">معطل</option>
                            <option value="yes">مفعل</option>
                        </select>
                        <p class="description">منع تسجيلات متعددة من نفس عنوان IP</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="cookie_duration">مدة صلاحية رابط الإحالة (أيام)</label>
                        <input type="number" id="cookie_duration" name="cookie_duration" value="30" min="1" max="365">
                    </div>
                </div>

                <div class="mlm-settings-section">
                    <h3>الصيانة والنظافة</h3>
                    
                    <div class="mlm-form-group">
                        <label for="auto_cleanup">التنظيف التلقائي</label>
                        <select id="auto_cleanup" name="auto_cleanup">
                            <option value="no">معطل</option>
                            <option value="yes">مفعل</option>
                        </select>
                        <p class="description">حذف السجلات القديمة تلقائياً</p>
                    </div>

                    <div class="mlm-form-group">
                        <label for="keep_records">الاحتفاظ بالسجلات (أيام)</label>
                        <input type="number" id="keep_records" name="keep_records" value="365" min="30" max="1095">
                    </div>

                    <div class="mlm-form-group">
                        <label for="delete_data_on_uninstall">حذف البيانات عند إلغاء التثبيت</label>
                        <select id="delete_data_on_uninstall" name="delete_data_on_uninstall">
                            <option value="no" selected>لا</option>
                            <option value="yes">نعم</option>
                        </select>
                        <p class="description">تحذير: هذا الإجراء لا يمكن التراجع عنه</p>
                    </div>
                </div>

                <div class="mlm-settings-section">
                    <h3>التقارير والإشعارات</h3>
                    
                    <div class="mlm-form-group">
                        <label for="email_notifications">الإشعارات البريدية</label>
                        <select id="email_notifications" name="email_notifications">
                            <option value="yes" selected>مفعلة</option>
                            <option value="no">معطلة</option>
                        </select>
                    </div>

                    <div class="mlm-form-group">
                        <label for="report_frequency">تكرار التقارير</label>
                        <select id="report_frequency" name="report_frequency">
                            <option value="daily">يومي</option>
                            <option value="weekly" selected>أسبوعي</option>
                            <option value="monthly">شهري</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- زر الحفظ -->
            <div class="mlm-form-group">
                <input type="submit" name="save_settings" class="button button-primary mlm-save-settings" value="حفظ الإعدادات">
                <button type="button" class="button button-secondary mlm-reset-settings">استعادة الإفتراضيات</button>
            </div>
        </div>
    </form>
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

    // إضافة صف مكافأة جديد
    $('.mlm-add-reward').on('click', function() {
        const $container = $('.mlm-rewards-container');
        const index = $container.find('.mlm-reward-row').length;
        
        const html = `
            <div class="mlm-reward-row">
                <input type="number" name="reward_trees[]" placeholder="عدد الأشجار" min="1" required>
                <input type="number" name="reward_amounts[]" placeholder="المبلغ" min="0" step="0.01" required>
                <button type="button" class="button button-secondary mlm-remove-reward">حذف</button>
            </div>
        `;
        
        $container.append(html);
    });

    // حذف صف مكافأة
    $('.mlm-rewards-container').on('click', '.mlm-remove-reward', function() {
        if ($('.mlm-reward-row').length > 1) {
            $(this).closest('.mlm-reward-row').remove();
        } else {
            alert('يجب أن يكون هناك على الأقل صف واحد للمكافآت');
        }
    });

    // استعادة الإعدادات الافتراضية
    $('.mlm-reset-settings').on('click', function() {
        if (confirm('هل أنت متأكد من استعادة الإعدادات الافتراضية؟ سيتم فقدان جميع الإعدادات الحالية.')) {
            // إعادة تعيين النموذج
            document.querySelector('form').reset();
        }
    });
});
</script>