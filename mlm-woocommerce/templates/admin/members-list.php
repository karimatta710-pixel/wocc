<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

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
    
    <select class="mlm-filter-select" data-column="6">
        <option value="all">جميع العمولات</option>
        <option value="high">عالية (> 1000 ج.م)</option>
        <option value="medium">متوسطة (100-1000 ج.م)</option>
        <option value="low">منخفضة (< 100 ج.م)</option>
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
                        <br>
                        <small style="color: #666;">ID: <?php echo $member->id; ?></small>
                    </td>
                    <td><?php echo esc_html($member->user_email); ?></td>
                    <td>
                        <code style="background: #f5f5f5; padding: 2px 5px; border-radius: 3px;">
                            <?php echo esc_html($member->referral_code); ?>
                        </code>
                        <button class="button button-small copy-code" data-code="<?php echo esc_attr($member->referral_code); ?>" style="margin-right: 5px;">
                            نسخ
                        </button>
                    </td>
                    <td>
                        <?php if ($sponsor_user): ?>
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $sponsor->id); ?>">
                                <?php echo esc_html($sponsor_user->display_name ?: $sponsor_user->user_login); ?>
                            </a>
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
                    <td>
                        <div style="font-weight: bold; color: #27ae60;">
                            <?php echo number_format($member->total_commissions, 2); ?> ج.م
                        </div>
                        <small style="color: #666;">
                            معلق: <?php echo number_format($member->pending_commissions, 2); ?> ج.م
                        </small>
                    </td>
                    <td>
                        <div class="mlm-action-buttons">
                            <a href="<?php echo admin_url('admin.php?page=mlm-members&action=view&member_id=' . $member->id); ?>" 
                               class="mlm-action-btn mlm-action-btn-primary" title="عرض التفاصيل">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            
                            <button class="mlm-action-btn mlm-view-tree" data-id="<?php echo $member->id; ?>" title="عرض الشجرة">
                                <span class="dashicons dashicons-networking"></span>
                            </button>
                            
                            <a href="<?php echo get_edit_user_link($member->user_id); ?>" 
                               class="mlm-action-btn" title="تحرير المستخدم" target="_blank">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            
                            <?php if ($member->status === 'active'): ?>
                                <button class="mlm-action-btn mlm-action-btn-danger mlm-deactivate-member" 
                                        data-id="<?php echo $member->id; ?>" title="إلغاء التفعيل">
                                    <span class="dashicons dashicons-no"></span>
                                </button>
                            <?php else: ?>
                                <button class="mlm-action-btn mlm-action-btn-success mlm-activate-member" 
                                        data-id="<?php echo $member->id; ?>" title="تفعيل">
                                    <span class="dashicons dashicons-yes"></span>
                                </button>
                            <?php endif; ?>
                            
                            <button class="mlm-action-btn mlm-send-message" 
                                    data-user-id="<?php echo $member->user_id; ?>" title="إرسال رسالة">
                                <span class="dashicons dashicons-email"></span>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px;">
                    <div style="color: #666;">
                        <span class="dashicons dashicons-groups" style="font-size: 48px; display: block; margin-bottom: 15px;"></span>
                        <h3>لا توجد أعضاء</h3>
                        <p>لم يتم تسجيل أي أعضاء في النظام بعد.</p>
                    </div>
                </td>
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
            'prev_text' => '&laquo; السابق',
            'next_text' => 'التالي &raquo;',
            'total' => $total_pages,
            'current' => $page
        ));
        ?>
    </div>
<?php endif; ?>

<style>
.mlm-action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.mlm-action-buttons .mlm-action-btn {
    padding: 6px;
    min-width: 32px;
    text-align: center;
}

.button-small {
    padding: 2px 8px;
    font-size: 11px;
    height: auto;
    line-height: 1.5;
}

.copy-code {
    font-size: 11px;
    padding: 1px 5px;
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

    // تصفية الجدول
    $('.mlm-filter-select').on('change', function() {
        const column = $(this).data('column');
        const value = $(this).val();
        
        $('.mlm-table tbody tr').each(function() {
            const $row = $(this);
            let show = true;
            
            if (column === 5) { // تصفية حسب الحالة
                if (value !== 'all') {
                    const statusText = $row.find('td').eq(5).text().trim();
                    if (statusText !== value) {
                        show = false;
                    }
                }
            } else if (column === 6) { // تصفية حسب العمولات
                if (value !== 'all') {
                    const commissionText = $row.find('td').eq(6).find('div').text();
                    const commission = parseFloat(commissionText.replace(/[^\d.]/g, ''));
                    
                    switch (value) {
                        case 'high':
                            if (commission <= 1000) show = false;
                            break;
                        case 'medium':
                            if (commission < 100 || commission > 1000) show = false;
                            break;
                        case 'low':
                            if (commission >= 100) show = false;
                            break;
                    }
                }
            }
            
            $row.toggle(show);
        });
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

    // إرسال رسالة
    $('.mlm-send-message').on('click', function() {
        const userId = $(this).data('user-id');
        const message = prompt('أدخل الرسالة التي تريد إرسالها:');
        
        if (message) {
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'send_message',
                    user_id: userId,
                    message: message,
                    nonce: mlm_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('تم إرسال الرسالة بنجاح');
                    } else {
                        alert('حدث خطأ في إرسال الرسالة: ' + response.data);
                    }
                }
            });
        }
    });
});
</script>