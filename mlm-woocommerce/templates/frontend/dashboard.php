<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-frontend-container">
    <div class="mlm-dashboard-header">
        <h1>مرحباً بك في نظام العمولات</h1>
        <p>إدارة عمولاتك ومكافآتك وشبكتك من مكان واحد</p>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="mlm-stats-cards">
        <div class="mlm-stat-card">
            <h3>رصيد العمولات</h3>
            <span class="stat-number"><?php echo number_format($member->pending_commissions, 2); ?> ج.م</span>
            <div class="stat-desc">قابل للسحب</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>إجمالي العمولات</h3>
            <span class="stat-number"><?php echo number_format($member->total_commissions, 2); ?> ج.م</span>
            <div class="stat-desc">منذ الانضمام</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>الأعضاء المُحالون</h3>
            <span class="stat-number"><?php echo count($tree_structure['level1'] ?? []); ?></span>
            <div class="stat-desc">في شبكتك</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>الأشجار المكتملة</h3>
            <span class="stat-number"><?php echo MLM_Rewards::get_instance()->count_completed_trees($member->id); ?></span>
            <div class="stat-desc">شجرة مكتملة</div>
        </div>
    </div>

    <!-- رابط الإحالة -->
    <div class="mlm-referral-section">
        <h2>رابط الإحالة الخاص بك</h2>
        <p>شارك هذا الرابط مع أصدقائك واحصل على عمولات عند انضمامهم وشرائهم</p>
        
        <div class="mlm-referral-link-container">
            <input type="text" value="<?php echo esc_url(add_query_arg('ref', $member->referral_code, home_url())); ?>" 
                   readonly class="mlm-referral-link">
            <button class="mlm-copy-btn">نسخ الرابط</button>
        </div>
        
        <div class="mlm-share-buttons">
            <button class="mlm-share-btn" data-platform="whatsapp">مشاركة على واتساب</button>
            <button class="mlm-share-btn" data-platform="facebook">مشاركة على فيسبوك</button>
            <button class="mlm-share-btn" data-platform="twitter">مشاركة على تويتر</button>
        </div>
    </div>

    <!-- التبويبات -->
    <div class="mlm-tabs">
        <button class="mlm-tab active" data-tab="dashboard">لوحة التحكم</button>
        <button class="mlm-tab" data-tab="commissions">العمولات</button>
        <button class="mlm-tab" data-tab="tree">شبكتي</button>
        <button class="mlm-tab" data-tab="rewards">المكافآت</button>
        <button class="mlm-tab" data-tab="profile">الملف الشخصي</button>
    </div>

    <!-- محتوى التبويبات -->
    <div class="mlm-tab-content active" id="dashboard">
        <!-- نظرة عامة -->
        <div class="mlm-section">
            <h2>النشاط الحديث</h2>
            
            <div class="mlm-activity-list">
                <?php if (!empty($commissions)): ?>
                    <?php foreach (array_slice($commissions, 0, 5) as $commission): ?>
                        <div class="mlm-activity-item">
                            <div class="activity-icon">💰</div>
                            <div class="activity-content">
                                <p>عمولة جديدة بقيمة <?php echo number_format($commission->commission_amount, 2); ?> ج.م</p>
                                <span class="activity-date"><?php echo human_time_diff(strtotime($commission->created_date), current_time('timestamp')); ?> مضت</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #666;">لا يوجد نشاط حديث</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- تقدم المكافآت -->
        <div class="mlm-progress-section">
            <h2>تقدمك towards المكافآت</h2>
            
            <div class="mlm-progress-bar">
                <div class="mlm-progress-fill" style="width: <?php echo ($reward_progress['completed_trees'] / 25) * 100; ?>%"></div>
            </div>
            
            <div class="progress-text">
                <?php echo $reward_progress['completed_trees']; ?> من 25 شجرة مكتملة
            </div>

            <?php if ($reward_progress['next_reward']): ?>
                <div class="mlm-next-reward">
                    <h4>🎯 المكافأة التالية</h4>
                    <p>بعد إكمال <?php echo $reward_progress['next_reward']['trees_needed']; ?> شجرة إضافية</p>
                    <p><strong><?php echo number_format($reward_progress['next_reward']['reward_amount'], 2); ?> ج.م</strong></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- تبويب العمولات -->
    <div class="mlm-tab-content" id="commissions">
        <div class="mlm-section">
            <h2>سجل العمولات</h2>
            
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
                <tbody class="mlm-commissions-list">
                    <?php if (!empty($commissions)): ?>
                        <?php foreach ($commissions as $commission): ?>
                            <tr>
                                <td>#<?php echo $commission->order_id; ?></td>
                                <td><?php echo number_format($commission->commission_amount, 2); ?> ج.م</td>
                                <td><?php echo $commission->commission_rate; ?>%</td>
                                <td>المستوى <?php echo $commission->level; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($commission->created_date)); ?></td>
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

            <?php if (count($commissions) > 10): ?>
                <button class="button mlm-load-more" data-page="1">تحميل المزيد</button>
            <?php endif; ?>
        </div>

        <div class="mlm-section">
            <h2>إحصائيات العمولات</h2>
            
            <div class="mlm-stats-cards">
                <div class="mlm-stat-card">
                    <h3>عمولات هذا الشهر</h3>
                    <span class="stat-number">
                        <?php
                        $month_commissions = array_filter($commissions, function($c) {
                            return date('Y-m', strtotime($c->created_date)) === date('Y-m');
                        });
                        $month_total = array_sum(array_column($month_commissions, 'commission_amount'));
                        echo number_format($month_total, 2);
                        ?> ج.م
                    </span>
                </div>
                
                <div class="mlm-stat-card">
                    <h3>متوسط العمولة</h3>
                    <span class="stat-number">
                        <?php
                        $avg = count($commissions) > 0 ? $member->total_commissions / count($commissions) : 0;
                        echo number_format($avg, 2);
                        ?> ج.م
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- تبويب الشجرة -->
    <div class="mlm-tab-content" id="tree">
        <div class="mlm-section">
            <h2>شبكتك الشبكية</h2>
            <p>عرض هيكل شبكتك وأعضاء فريقك</p>
            
            <button class="button button-primary mlm-refresh-tree">تحديث البيانات</button>
        </div>

        <div class="mlm-tree-view">
            <!-- سيتم ملؤها بواسطة JavaScript -->
            <div class="mlm-loading">جاري تحميل بيانات الشجرة...</div>
        </div>
    </div>

    <!-- تبويب المكافآت -->
    <div class="mlm-tab-content" id="rewards">
        <div class="mlm-section">
            <h2>المكافآت والإنجازات</h2>
            
            <table class="mlm-table">
                <thead>
                    <tr>
                        <th>عدد الأشجار</th>
                        <th>المكافأة</th>
                        <th>الإجمالي التراكمي</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody class="mlm-rewards-list">
                    <?php if (!empty($rewards)): ?>
                        <?php foreach ($rewards as $reward): ?>
                            <tr class="<?php echo $reward->status === 'pending' ? 'mlm-new-reward' : ''; ?>">
                                <td><?php echo $reward->trees_completed; ?> شجرة</td>
                                <td><?php echo number_format($reward->reward_amount, 2); ?> ج.م</td>
                                <td><?php echo number_format($reward->total_rewards, 2); ?> ج.م</td>
                                <td><?php echo date('Y-m-d', strtotime($reward->achieved_date)); ?></td>
                                <td>
                                    <span class="mlm-badge mlm-badge-<?php echo $reward->status === 'paid' ? 'paid' : 'pending'; ?>">
                                        <?php echo $reward->status === 'paid' ? 'تم الدفع' : 'معلق'; ?>
                                    </span>
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

        <div class="mlm-section">
            <h2>جدول المكافآت</h2>
            
            <table class="mlm-table">
                <thead>
                    <tr>
                        <th>الإنجاز</th>
                        <th>المكافأة</th>
                        <th>المكافأة التراكمية</th>
                        <th>حالتك</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reward_structure = MLM_Database::get_setting('reward_structure', array());
                    ksort($reward_structure);
                    $cumulative = 0;
                    
                    foreach ($reward_structure as $trees => $amount):
                        $cumulative += $amount;
                        $achieved = $reward_progress['completed_trees'] >= $trees;
                    ?>
                        <tr>
                            <td><?php echo $trees; ?> شجرة</td>
                            <td><?php echo number_format($amount, 2); ?> ج.م</td>
                            <td><?php echo number_format($cumulative, 2); ?> ج.م</td>
                            <td>
                                <?php if ($achieved): ?>
                                    <span class="mlm-badge mlm-badge-paid">مكتمل</span>
                                <?php else: ?>
                                    <span class="mlm-badge mlm-badge-pending">قيد التقدم</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- تبويب الملف الشخصي -->
    <div class="mlm-tab-content" id="profile">
        <div class="mlm-section">
            <h2>الملف الشخصي</h2>
            
            <div class="mlm-profile-info">
                <div class="profile-field">
                    <label>اسم المستخدم:</label>
                    <span><?php echo esc_html(wp_get_current_user()->display_name); ?></span>
                </div>
                
                <div class="profile-field">
                    <label>البريد الإلكتروني:</label>
                    <span><?php echo esc_html(wp_get_current_user()->user_email); ?></span>
                </div>
                
                <div class="profile-field">
                    <label>كود الإحالة:</label>
                    <span><code><?php echo esc_html($member->referral_code); ?></code></span>
                </div>
                
                <div class="profile-field">
                    <label>تاريخ الانضمام:</label>
                    <span><?php echo date('Y-m-d', strtotime($member->join_date)); ?></span>
                </div>
                
                <div class="profile-field">
                    <label>الحالة:</label>
                    <span>
                        <span class="mlm-badge mlm-badge-<?php echo $member->status === 'active' ? 'active' : 'pending'; ?>">
                            <?php echo $member->status === 'active' ? 'نشط' : 'غير نشط'; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="mlm-section">
            <h2>إعدادات الحساب</h2>
            
            <form class="mlm-profile-form">
                <div class="mlm-form-group">
                    <label for="display_name">اسم العرض</label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr(wp_get_current_user()->display_name); ?>">
                </div>
                
                <div class="mlm-form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" placeholder="أدخل رقم هاتفك">
                </div>
                
                <div class="mlm-form-group">
                    <label for="payment_method">طريقة الدفع المفضلة</label>
                    <select id="payment_method" name="payment_method">
                        <option value="bank">تحويل بنكي</option>
                        <option value="wallet">محفظة إلكترونية</option>
                    </select>
                </div>
                
                <button type="submit" class="button button-primary">حفظ التغييرات</button>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // نسخ رابط الإحالة
    $('.mlm-copy-btn').on('click', function() {
        const $input = $('.mlm-referral-link');
        $input.select();
        document.execCommand('copy');
        
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text('تم النسخ!').addClass('copied');
        
        setTimeout(() => {
            $btn.text(originalText).removeClass('copied');
        }, 2000);
    });

    // مشاركة الرابط
    $('.mlm-share-btn').on('click', function() {
        const platform = $(this).data('platform');
        const url = encodeURIComponent($('.mlm-referral-link').val());
        const text = encodeURIComponent('انضم إلى نظام العمولات من خلال رابط الإحالة الخاص بي!');
        
        let shareUrl = '';
        
        switch (platform) {
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${text} ${url}`;
                break;
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;
                break;
        }
        
        window.open(shareUrl, '_blank', 'width=600,height=400');
    });

    // تبديل التبويبات
    $('.mlm-tab').on('click', function() {
        $('.mlm-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.mlm-tab-content').removeClass('active');
        $('#' + $(this).data('tab')).addClass('active');
    });
});
</script>