jQuery(document).ready(function($) {
    'use strict';

    // نظام العمولات المتعددة - واجهة الإدارة
    const MLM_Admin = {
        init: function() {
            this.bindEvents();
            this.initDataTables();
            this.initSettingsForm();
        },

        bindEvents: function() {
            // دفع العمولة
            $(document).on('click', '.mlm-pay-commission', this.payCommission.bind(this));

            // دفع المكافأة
            $(document).on('click', '.mlm-pay-reward', this.payReward.bind(this));

            // إضافة صف مكافأة جديد
            $(document).on('click', '.mlm-add-reward', this.addRewardRow.bind(this));

            // حذف صف مكافأة
            $(document).on('click', '.mlm-remove-reward', this.removeRewardRow.bind(this));

            // عرض شجرة العضو
            $(document).on('click', '.mlm-view-tree', this.viewMemberTree.bind(this));

            // تصدير التقارير
            $(document).on('click', '.mlm-export-btn', this.exportReport.bind(this));

            // البحث والتصفية
            $(document).on('keyup', '.mlm-search-input', this.debounce(this.handleSearch.bind(this), 300));
            $(document).on('change', '.mlm-filter-select', this.handleFilter.bind(this));

            // حفظ الإعدادات
            $(document).on('click', '.mlm-save-settings', this.saveSettings.bind(this));
        },

        initDataTables: function() {
            // يمكن إضافة DataTables إذا كانت موجودة
            if ($.fn.DataTable) {
                $('.mlm-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Arabic.json'
                    },
                    pageLength: 25,
                    responsive: true
                });
            }
        },

        initSettingsForm: function() {
            // تهيئة نماذج الإعدادات
            this.updateCommissionPreview();
            $(document).on('input', '.commission-rate-input', this.updateCommissionPreview.bind(this));
        },

        payCommission: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const commissionId = $btn.data('id');
            
            if (!confirm(mlm_admin.confirm_pay)) {
                return;
            }

            this.showLoading($btn);

            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'pay_commission',
                    commission_id: commissionId,
                    nonce: mlm_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $btn.replaceWith('<span class="mlm-badge mlm-badge-paid">تم الدفع</span>');
                        this.showNotice(response.data, 'success');
                        this.updateStats();
                    } else {
                        this.showNotice(response.data, 'error');
                        this.hideLoading($btn);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotice('حدث خطأ في الاتصال: ' + error, 'error');
                    this.hideLoading($btn);
                }
            });
        },

        payReward: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const rewardId = $btn.data('id');
            
            if (!confirm(mlm_admin.confirm_pay)) {
                return;
            }

            this.showLoading($btn);

            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'pay_reward',
                    reward_id: rewardId,
                    nonce: mlm_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $btn.replaceWith('<span class="mlm-badge mlm-badge-paid">تم الدفع</span>');
                        this.showNotice(response.data, 'success');
                        this.updateStats();
                    } else {
                        this.showNotice(response.data, 'error');
                        this.hideLoading($btn);
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotice('حدث خطأ في الاتصال: ' + error, 'error');
                    this.hideLoading($btn);
                }
            });
        },

        addRewardRow: function(e) {
            e.preventDefault();
            
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
        },

        removeRewardRow: function(e) {
            e.preventDefault();
            $(e.target).closest('.mlm-reward-row').remove();
        },

        viewMemberTree: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const memberId = $btn.data('id');
            
            this.showLoading($btn);

            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'get_member_tree',
                    member_id: memberId,
                    nonce: mlm_admin.nonce
                },
                success: (response) => {
                    this.hideLoading($btn);
                    
                    if (response.success) {
                        this.displayTreeModal(response.data);
                    } else {
                        this.showNotice(response.data, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.hideLoading($btn);
                    this.showNotice('حدث خطأ في الاتصال: ' + error, 'error');
                }
            });
        },

        displayTreeModal: function(treeData) {
            const modalHtml = `
                <div id="mlm-tree-modal" class="mlm-modal">
                    <div class="mlm-modal-content">
                        <div class="mlm-modal-header">
                            <h3>شجرة العضو</h3>
                            <span class="mlm-modal-close">&times;</span>
                        </div>
                        <div class="mlm-modal-body">
                            ${this.generateTreeHTML(treeData)}
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            
            // إضافة CSS للمودال إذا لم يكن موجوداً
            if (!$('#mlm-modal-css').length) {
                $('head').append(`
                    <style id="mlm-modal-css">
                        .mlm-modal {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0,0,0,0.5);
                            z-index: 9999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .mlm-modal-content {
                            background: white;
                            padding: 20px;
                            border-radius: 8px;
                            max-width: 90%;
                            max-height: 90%;
                            overflow: auto;
                            width: 800px;
                        }
                        .mlm-modal-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 20px;
                            padding-bottom: 15px;
                            border-bottom: 1px solid #ccc;
                        }
                        .mlm-modal-close {
                            font-size: 24px;
                            cursor: pointer;
                            color: #666;
                        }
                        .mlm-modal-close:hover {
                            color: #000;
                        }
                    </style>
                `);
            }
            
            // إغلاق المودال
            $('.mlm-modal-close, #mlm-tree-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#mlm-tree-modal').remove();
                }
            });
        },

        generateTreeHTML: function(treeData) {
            let html = '';
            
            // المستوى الأول
            if (treeData.level1 && treeData.level1.length > 0) {
                html += '<div class="mlm-tree-level"><h4>المستوى الأول (' + treeData.level1.length + ' عضو)</h4><div class="mlm-tree-members">';
                treeData.level1.forEach(member => {
                    html += `<div class="mlm-tree-member">عضو #${member.member_id}</div>`;
                });
                html += '</div></div>';
            }
            
            // المستوى الثاني
            if (treeData.level2 && treeData.level2.length > 0) {
                html += '<div class="mlm-tree-level"><h4>المستوى الثاني (' + treeData.level2.length + ' أعضاء)</h4><div class="mlm-tree-members">';
                treeData.level2.forEach(member => {
                    html += `<div class="mlm-tree-member">عضو #${member.member_id}</div>`;
                });
                html += '</div></div>';
            }
            
            // المستوى الثالث
            if (treeData.level3 && treeData.level3.length > 0) {
                html += '<div class="mlm-tree-level"><h4>المستوى الثالث (' + treeData.level3.length + ' أعضاء)</h4><div class="mlm-tree-members">';
                treeData.level3.forEach(member => {
                    html += `<div class="mlm-tree-member">عضو #${member.member_id}</div>`;
                });
                html += '</div></div>';
            }
            
            if (!html) {
                html = '<p>لا توجد بيانات للشجرة</p>';
            }
            
            return html;
        },

        exportReport: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const reportType = $btn.data('type');
            
            this.showLoading($btn);
            
            // تنفيذ التصدير
            setTimeout(() => {
                this.hideLoading($btn);
                this.showNotice('جاري تحضير التقرير للتحميل...', 'success');
                // هنا سيتم تنفيذ عملية التصدير الفعلية
            }, 1000);
        },

        handleSearch: function(e) {
            const searchTerm = $(e.target).val();
            const $table = $(e.target).closest('.mlm-content').find('.mlm-table');
            
            // تنفيذ البحث على الجدول
            $table.find('tbody tr').each(function() {
                const $row = $(this);
                const text = $row.text().toLowerCase();
                if (text.indexOf(searchTerm.toLowerCase()) > -1) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        },

        handleFilter: function(e) {
            const filterValue = $(e.target).val();
            const $table = $(e.target).closest('.mlm-content').find('.mlm-table');
            const filterColumn = $(e.target).data('column');
            
            // تنفيذ التصفية
            $table.find('tbody tr').each(function() {
                const $row = $(this);
                const cellValue = $row.find('td').eq(filterColumn).text().trim();
                
                if (filterValue === 'all' || cellValue === filterValue) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        },

        saveSettings: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const $form = $btn.closest('form');
            
            this.showLoading($btn);
            
            // يمكن إضافة التحقق من الصحة هنا
            
            $form.submit();
        },

        updateCommissionPreview: function() {
            const rate1 = parseFloat($('#commission_level_1').val()) || 0;
            const rate2 = parseFloat($('#commission_level_2').val()) || 0;
            const rate3 = parseFloat($('#commission_level_3').val()) || 0;
            const sampleAmount = 10000;
            
            const commission1 = (sampleAmount * rate1) / 100;
            const commission2 = (sampleAmount * rate2) / 100;
            const commission3 = (sampleAmount * rate3) / 100;
            const total = commission1 + commission2 + commission3;
            
            $('.commission-preview').html(`
                <div class="mlm-notice">
                    <strong>معاينة العمولات (على أساس طلب بقيمة ${sampleAmount} ج.م):</strong><br>
                    المستوى الأول: ${commission1.toFixed(2)} ج.م (${rate1}%)<br>
                    المستوى الثاني: ${commission2.toFixed(2)} ج.م (${rate2}%)<br>
                    المستوى الثالث: ${commission3.toFixed(2)} ج.م (${rate3}%)<br>
                    <strong>الإجمالي: ${total.toFixed(2)} ج.م</strong>
                </div>
            `);
        },

        updateStats: function() {
            // تحديث الإحصائيات في الخلفية
            $.ajax({
                url: mlm_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_admin_action',
                    mlm_action: 'get_stats',
                    nonce: mlm_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatsDisplay(response.data);
                    }
                }
            });
        },

        updateStatsDisplay: function(stats) {
            // تحديث عرض الإحصائيات
            Object.keys(stats).forEach(stat => {
                $(`.stat-${stat}`).text(stats[stat]);
            });
        },

        showLoading: function($element) {
            $element.data('original-text', $element.text());
            $element.html('<span class="mlm-loading"></span> جاري المعالجة...');
            $element.prop('disabled', true);
        },

        hideLoading: function($element) {
            $element.text($element.data('original-text'));
            $element.prop('disabled', false);
        },

        showNotice: function(message, type = 'success') {
            const noticeClass = type === 'success' ? 'mlm-notice' : 'mlm-notice mlm-notice-error';
            const noticeHtml = `<div class="${noticeClass}">${message}</div>`;
            
            $('.wrap h1').first().after(noticeHtml);
            
            setTimeout(() => {
                $('.mlm-notice').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // تهيئة النظام
    MLM_Admin.init();
});