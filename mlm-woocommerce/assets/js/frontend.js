jQuery(document).ready(function($) {
    'use strict';

    // نظام العمولات المتعددة - الواجهة الأمامية
    const MLM_Frontend = {
        currentTab: 'dashboard',
        init: function() {
            this.bindEvents();
            this.loadInitialData();
            this.initCharts();
        },

        bindEvents: function() {
            // نسخ رابط الإحالة
            $('.mlm-copy-btn').on('click', this.copyReferralLink.bind(this));

            // تبديل التبويبات
            $('.mlm-tab').on('click', this.switchTab.bind(this));

            // تحديث بيانات الشجرة
            $('.mlm-refresh-tree').on('click', this.refreshTreeData.bind(this));

            // تحميل المزيد من العمولات
            $('.mlm-load-more').on('click', this.loadMoreCommissions.bind(this));

            // مشاركة رابط الإحالة
            $('.mlm-share-btn').on('click', this.shareReferralLink.bind(this));

            // عرض تفاصيل المكافأة
            $('.mlm-reward-details').on('click', this.showRewardDetails.bind(this));
        },

        loadInitialData: function() {
            this.loadCommissions();
            this.loadTreeData();
            this.loadRewardsProgress();
        },

        initCharts: function() {
            // تهيئة الرسوم البيانية إذا كانت مكتبة Charts موجودة
            if (typeof Chart !== 'undefined') {
                this.initCommissionsChart();
                this.initTreeChart();
            }
        },

        copyReferralLink: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const $input = $btn.siblings('input');
            
            $input.select();
            document.execCommand('copy');
            
            const originalText = $btn.text();
            $btn.text('تم النسخ!');
            $btn.addClass('copied');
            
            setTimeout(() => {
                $btn.text(originalText);
                $btn.removeClass('copied');
            }, 2000);
        },

        switchTab: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const tabName = $btn.data('tab');
            
            // تحديث التبويب النشط
            $('.mlm-tab').removeClass('active');
            $btn.addClass('active');
            
            // إخفاء جميع المحتويات
            $('.mlm-tab-content').removeClass('active');
            
            // عرض المحتوى المطلوب
            $(`#${tabName}`).addClass('active');
            
            // حفظ التبويب الحالي
            this.currentTab = tabName;
            
            // تحميل بيانات التبويب إذا لزم الأمر
            this.loadTabData(tabName);
        },

        loadTabData: function(tabName) {
            switch (tabName) {
                case 'commissions':
                    this.loadCommissions();
                    break;
                case 'tree':
                    this.loadTreeData();
                    break;
                case 'rewards':
                    this.loadRewards();
                    break;
            }
        },

        refreshTreeData: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const $container = $('.mlm-tree-members');
            
            this.showLoading($btn, 'جاري التحديث...');
            $container.html('<div class="mlm-loading">جاري تحميل بيانات الشجرة</div>');

            this.loadTreeData().then(() => {
                this.hideLoading($btn, 'تحديث الشجرة');
            });
        },

        loadTreeData: function() {
            return $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_tree_data',
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayTreeData(response.data);
                    } else {
                        $('.mlm-tree-members').html('<div class="mlm-error">فشل في تحميل البيانات</div>');
                    }
                },
                error: () => {
                    $('.mlm-tree-members').html('<div class="mlm-error">حدث خطأ في الاتصال</div>');
                }
            });
        },

        displayTreeData: function(treeData) {
            let html = '';
            
            // المستوى الأول
            if (treeData.level1 && treeData.level1.length > 0) {
                html += '<div class="mlm-tree-level"><h3>المستوى الأول</h3><div class="mlm-tree-members">';
                treeData.level1.forEach(member => {
                    html += this.generateMemberCard(member, 1);
                });
                html += '</div></div>';
            } else {
                html += '<div class="mlm-tree-level"><h3>المستوى الأول</h3><div class="mlm-tree-members"><div class="mlm-tree-member">لا يوجد أعضاء</div></div></div>';
            }
            
            // المستوى الثاني
            if (treeData.level2 && treeData.level2.length > 0) {
                html += '<div class="mlm-tree-level"><h3>المستوى الثاني</h3><div class="mlm-tree-members">';
                treeData.level2.forEach(member => {
                    html += this.generateMemberCard(member, 2);
                });
                html += '</div></div>';
            } else {
                html += '<div class="mlm-tree-level"><h3>المستوى الثاني</h3><div class="mlm-tree-members"><div class="mlm-tree-member">لا يوجد أعضاء</div></div></div>';
            }
            
            // المستوى الثالث
            if (treeData.level3 && treeData.level3.length > 0) {
                html += '<div class="mlm-tree-level"><h3>المستوى الثالث</h3><div class="mlm-tree-members">';
                treeData.level3.forEach(member => {
                    html += this.generateMemberCard(member, 3);
                });
                html += '</div></div>';
            } else {
                html += '<div class="mlm-tree-level"><h3>المستوى الثالث</h3><div class="mlm-tree-members"><div class="mlm-tree-member">لا يوجد أعضاء</div></div></div>';
            }
            
            $('.mlm-tree-view').html(html);
        },

        generateMemberCard: function(member, level) {
            // في التطبيق الحقيقي، ستأتي هذه البيانات من الخادم
            const memberInfo = {
                name: `عضو #${member.member_id}`,
                joinDate: '2024-01-01',
                status: 'نشط',
                purchases: Math.floor(Math.random() * 5) + 1
            };
            
            return `
                <div class="mlm-tree-member">
                    <div class="member-name">${memberInfo.name}</div>
                    <div class="member-info">
                        <div>المستوى: ${level}</div>
                        <div>الحالة: ${memberInfo.status}</div>
                        <div>المشتريات: ${memberInfo.purchases}</div>
                    </div>
                </div>
            `;
        },

        loadCommissions: function() {
            $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_commissions',
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayCommissions(response.data);
                    }
                }
            });
        },

        displayCommissions: function(commissions) {
            let html = '';
            
            if (commissions.length > 0) {
                commissions.forEach(commission => {
                    html += this.generateCommissionRow(commission);
                });
            } else {
                html = '<tr><td colspan="6" style="text-align: center;">لا توجد عمولات حتى الآن</td></tr>';
            }
            
            $('.mlm-commissions-list').html(html);
        },

        generateCommissionRow: function(commission) {
            const statusBadge = commission.status === 'paid' ? 
                '<span class="mlm-badge mlm-badge-paid">تم الدفع</span>' :
                '<span class="mlm-badge mlm-badge-pending">معلق</span>';
            
            const date = new Date(commission.date).toLocaleDate('ar-EG');
            
            return `
                <tr>
                    <td>#${commission.order_id}</td>
                    <td>${parseFloat(commission.amount).toFixed(2)} ج.م</td>
                    <td>${commission.rate}%</td>
                    <td>المستوى ${commission.level}</td>
                    <td>${date}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        },

        loadMoreCommissions: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const page = parseInt($btn.data('page')) || 1;
            
            this.showLoading($btn, 'جاري التحميل...');

            $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_commissions',
                    page: page + 1,
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    this.hideLoading($btn, 'تحميل المزيد');
                    
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(commission => {
                            $('.mlm-commissions-list').append(this.generateCommissionRow(commission));
                        });
                        $btn.data('page', page + 1);
                    } else {
                        $btn.hide();
                    }
                },
                error: () => {
                    this.hideLoading($btn, 'تحميل المزيد');
                }
            });
        },

        loadRewards: function() {
            $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_rewards',
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayRewards(response.data);
                    }
                }
            });
        },

        displayRewards: function(rewards) {
            let html = '';
            
            if (rewards.length > 0) {
                rewards.forEach(reward => {
                    html += this.generateRewardRow(reward);
                });
            } else {
                html = '<tr><td colspan="4" style="text-align: center;">لا توجد مكافآت حتى الآن</td></tr>';
            }
            
            $('.mlm-rewards-list').html(html);
        },

        generateRewardRow: function(reward) {
            const statusBadge = reward.status === 'paid' ? 
                '<span class="mlm-badge mlm-badge-paid">تم الدفع</span>' :
                '<span class="mlm-badge mlm-badge-pending">معلق</span>';
            
            const date = new Date(reward.achieved_date).toLocaleDate('ar-EG');
            
            return `
                <tr>
                    <td>${reward.trees_completed} شجرة</td>
                    <td>${parseFloat(reward.reward_amount).toFixed(2)} ج.م</td>
                    <td>${date}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        },

        loadRewardsProgress: function() {
            $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_rewards_progress',
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.displayRewardsProgress(response.data);
                    }
                }
            });
        },

        displayRewardsProgress: function(progress) {
            // تحديث شريط التقدم
            const percentage = (progress.completed_trees / 25) * 100; // افتراض 25 كحد أقصى
            $('.mlm-progress-fill').css('width', percentage + '%');
            
            $('.progress-text').text(`${progress.completed_trees} من 25 شجرة مكتملة`);
            
            // عرض المكافأة التالية
            if (progress.next_reward) {
                $('.mlm-next-reward').html(`
                    <h4>المكافأة التالية</h4>
                    <p>بعد إكمال ${progress.next_reward.trees_needed} شجرة إضافية</p>
                    <p><strong>${progress.next_reward.reward_amount.toFixed(2)} ج.م</strong></p>
                `).show();
            } else {
                $('.mlm-next-reward').hide();
            }
        },

        shareReferralLink: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const referralLink = $('.mlm-referral-link').val();
            
            // مشاركة عبر الشبكات الاجتماعية
            if (navigator.share) {
                navigator.share({
                    title: 'انضم إلى نظام العمولات',
                    text: 'انضم من خلال رابط الإحالة الخاص بي واحصل على مزايا حصرية!',
                    url: referralLink
                });
            } else {
                // عرض خيارات المشاركة البديلة
                this.showShareOptions(referralLink);
            }
        },

        showShareOptions: function(link) {
            const shareHtml = `
                <div class="mlm-share-options">
                    <h4>مشاركة رابط الإحالة</h4>
                    <div class="share-buttons">
                        <button class="share-whatsapp" data-link="${link}">WhatsApp</button>
                        <button class="share-facebook" data-link="${link}">Facebook</button>
                        <button class="share-telegram" data-link="${link}">Telegram</button>
                    </div>
                </div>
            `;
            
            // تنفيذ منطق المشاركة
            alert('رابط الإحالة: ' + link);
        },

        showRewardDetails: function(e) {
            e.preventDefault();
            
            const $btn = $(e.target);
            const rewardId = $btn.data('id');
            
            // عرض تفاصيل المكافأة
            $.ajax({
                url: mlm_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'mlm_frontend_ajax',
                    mlm_action: 'get_reward_details',
                    reward_id: rewardId,
                    nonce: mlm_frontend.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.showRewardModal(response.data);
                    }
                }
            });
        },

        showRewardModal: function(rewardData) {
            // تنفيذ عرض تفاصيل المكافأة في نافذة منبثقة
            alert(`تفاصيل المكافأة: ${rewardData.trees_completed} شجرة - ${rewardData.reward_amount} ج.م`);
        },

        initCommissionsChart: function() {
            const ctx = document.getElementById('commissionsChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                        datasets: [{
                            label: 'العمولات',
                            data: [1200, 1900, 3000, 2500, 2200, 3000],
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                rtl: true,
                                labels: {
                                    font: {
                                        family: 'Tahoma'
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },

        initTreeChart: function() {
            const ctx = document.getElementById('treeChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['المستوى الأول', 'المستوى الثاني', 'المستوى الثالث'],
                        datasets: [{
                            data: [2, 4, 8],
                            backgroundColor: [
                                '#667eea',
                                '#764ba2',
                                '#f093fb'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                rtl: true,
                                labels: {
                                    font: {
                                        family: 'Tahoma'
                                    }
                                }
                            }
                        }
                    }
                });
            }
        },

        showLoading: function($element, text = 'جاري المعالجة...') {
            $element.data('original-text', $element.text());
            $element.html(`<span class="mlm-loading"></span> ${text}`);
            $element.prop('disabled', true);
        },

        hideLoading: function($element, originalText = null) {
            $element.text(originalText || $element.data('original-text'));
            $element.prop('disabled', false);
        }
    };

    // تهيئة النظام
    MLM_Frontend.init();
});