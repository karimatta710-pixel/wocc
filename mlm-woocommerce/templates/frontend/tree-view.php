<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mlm-frontend-container">
    <div class="mlm-dashboard-header">
        <h1>شبكتك الشبكية</h1>
        <p>عرض هيكل فريقك وأعضاء شبكتك</p>
    </div>

    <div class="mlm-stats-cards">
        <div class="mlm-stat-card">
            <h3>إجمالي الأعضاء</h3>
            <span class="stat-number">
                <?php
                $total_members = count($tree_structure['level1'] ?? []) + 
                               count($tree_structure['level2'] ?? []) + 
                               count($tree_structure['level3'] ?? []);
                echo $total_members;
                ?>
            </span>
            <div class="stat-desc">في شبكتك</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>المستوى الأول</h3>
            <span class="stat-number"><?php echo count($tree_structure['level1'] ?? []); ?></span>
            <div class="stat-desc">أعضاء مباشرين</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>المستوى الثاني</h3>
            <span class="stat-number"><?php echo count($tree_structure['level2'] ?? []); ?></span>
            <div class="stat-desc">أعضاء غير مباشرين</div>
        </div>
        
        <div class="mlm-stat-card">
            <h3>المستوى الثالث</h3>
            <span class="stat-number"><?php echo count($tree_structure['level3'] ?? []); ?></span>
            <div class="stat-desc">أعضاء في المستوى الثالث</div>
        </div>
    </div>

    <div class="mlm-section">
        <h2>هيكل الشجرة</h2>
        
        <div class="mlm-tree-controls">
            <button class="button button-primary mlm-refresh-tree">🔄 تحديث</button>
            <button class="button button-secondary mlm-expand-all">➕ توسيع الكل</button>
            <button class="button button-secondary mlm-collapse-all">➖ طي الكل</button>
        </div>

        <!-- الشجرة الرئيسية -->
        <div class="mlm-tree-visualization">
            <!-- المستوى 0 - أنت -->
            <div class="mlm-tree-level-0">
                <div class="mlm-tree-node mlm-tree-node-main">
                    <div class="node-content">
                        <div class="node-avatar">👤</div>
                        <div class="node-info">
                            <div class="node-name">أنت</div>
                            <div class="node-details">صاحب الشجرة</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- المستوى 1 -->
            <div class="mlm-tree-level-1">
                <h3>المستوى الأول (الأعضاء المباشرون)</h3>
                <div class="mlm-tree-members">
                    <?php if (!empty($tree_structure['level1'])): ?>
                        <?php foreach ($tree_structure['level1'] as $index => $member): ?>
                            <div class="mlm-tree-member" data-level="1" data-index="<?php echo $index; ?>">
                                <div class="member-card">
                                    <div class="member-avatar">👥</div>
                                    <div class="member-info">
                                        <div class="member-name">عضو #<?php echo $member['member_id']; ?></div>
                                        <div class="member-stats">
                                            <span>عمولات: 0 ج.م</span>
                                            <span>أعضاء: 0</span>
                                        </div>
                                    </div>
                                    <div class="member-actions">
                                        <button class="view-subtree" data-member="<?php echo $member['member_id']; ?>">عرض الفريق</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mlm-tree-member empty">
                            <div class="member-card">
                                <div class="member-avatar">➕</div>
                                <div class="member-info">
                                    <div class="member-name">لا يوجد أعضاء</div>
                                    <div class="member-desc">ادعُ أصدقاءك للانضمام</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- المستوى 2 -->
            <div class="mlm-tree-level-2">
                <h3>المستوى الثاني</h3>
                <div class="mlm-tree-members">
                    <?php if (!empty($tree_structure['level2'])): ?>
                        <?php foreach ($tree_structure['level2'] as $index => $member): ?>
                            <div class="mlm-tree-member" data-level="2" data-index="<?php echo $index; ?>">
                                <div class="member-card">
                                    <div class="member-avatar">👥</div>
                                    <div class="member-info">
                                        <div class="member-name">عضو #<?php echo $member['member_id']; ?></div>
                                        <div class="member-stats">
                                            <span>عمولات: 0 ج.م</span>
                                            <span>أعضاء: 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mlm-tree-member empty">
                            <div class="member-card">
                                <div class="member-avatar">🔒</div>
                                <div class="member-info">
                                    <div class="member-name">غير متاح</div>
                                    <div class="member-desc">يتطلب أعضاء في المستوى الأول</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- المستوى 3 -->
            <div class="mlm-tree-level-3">
                <h3>المستوى الثالث</h3>
                <div class="mlm-tree-members">
                    <?php if (!empty($tree_structure['level3'])): ?>
                        <?php foreach ($tree_structure['level3'] as $index => $member): ?>
                            <div class="mlm-tree-member" data-level="3" data-index="<?php echo $index; ?>">
                                <div class="member-card">
                                    <div class="member-avatar">👥</div>
                                    <div class="member-info">
                                        <div class="member-name">عضو #<?php echo $member['member_id']; ?></div>
                                        <div class="member-stats">
                                            <span>عمولات: 0 ج.م</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="mlm-tree-member empty">
                            <div class="member-card">
                                <div class="member-avatar">🔒</div>
                                <div class="member-info">
                                    <div class="member-name">غير متاح</div>
                                    <div class="member-desc">يتطلب أعضاء في المستوى الثاني</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mlm-section">
        <h2>إحصائيات مفصلة</h2>
        
        <table class="mlm-table">
            <thead>
                <tr>
                    <th>المستوى</th>
                    <th>عدد الأعضاء</th>
                    <th>إجمالي العمولات</th>
                    <th>متوسط العمولة</th>
                    <th>نسبة الإكمال</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>المستوى الأول</td>
                    <td><?php echo count($tree_structure['level1'] ?? []); ?></td>
                    <td>
                        <?php
                        $level1_commissions = array_sum(array_map(function($m) use ($commissions) {
                            return array_sum(array_column(array_filter($commissions, function($c) use ($m) {
                                return $c->member_id == $m['member_id'];
                            }), 'commission_amount'));
                        }, $tree_structure['level1'] ?? []));
                        echo number_format($level1_commissions, 2); 
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $avg1 = count($tree_structure['level1'] ?? []) > 0 ? 
                            $level1_commissions / count($tree_structure['level1'] ?? []) : 0;
                        echo number_format($avg1, 2);
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $required1 = MLM_Database::get_setting('tree_structure', array())['level1_count'] ?? 2;
                        $completion1 = count($tree_structure['level1'] ?? []) / $required1 * 100;
                        echo number_format($completion1, 1); 
                        ?>%
                    </td>
                </tr>
                <tr>
                    <td>المستوى الثاني</td>
                    <td><?php echo count($tree_structure['level2'] ?? []); ?></td>
                    <td>
                        <?php
                        $level2_commissions = array_sum(array_map(function($m) use ($commissions) {
                            return array_sum(array_column(array_filter($commissions, function($c) use ($m) {
                                return $c->member_id == $m['member_id'];
                            }), 'commission_amount'));
                        }, $tree_structure['level2'] ?? []));
                        echo number_format($level2_commissions, 2); 
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $avg2 = count($tree_structure['level2'] ?? []) > 0 ? 
                            $level2_commissions / count($tree_structure['level2'] ?? []) : 0;
                        echo number_format($avg2, 2);
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $required2 = MLM_Database::get_setting('tree_structure', array())['level2_count'] ?? 4;
                        $completion2 = count($tree_structure['level2'] ?? []) / $required2 * 100;
                        echo number_format($completion2, 1); 
                        ?>%
                    </td>
                </tr>
                <tr>
                    <td>المستوى الثالث</td>
                    <td><?php echo count($tree_structure['level3'] ?? []); ?></td>
                    <td>
                        <?php
                        $level3_commissions = array_sum(array_map(function($m) use ($commissions) {
                            return array_sum(array_column(array_filter($commissions, function($c) use ($m) {
                                return $c->member_id == $m['member_id'];
                            }), 'commission_amount'));
                        }, $tree_structure['level3'] ?? []));
                        echo number_format($level3_commissions, 2); 
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $avg3 = count($tree_structure['level3'] ?? []) > 0 ? 
                            $level3_commissions / count($tree_structure['level3'] ?? []) : 0;
                        echo number_format($avg3, 2);
                        ?> ج.م
                    </td>
                    <td>
                        <?php
                        $required3 = MLM_Database::get_setting('tree_structure', array())['level3_count'] ?? 8;
                        $completion3 = count($tree_structure['level3'] ?? []) / $required3 * 100;
                        echo number_format($completion3, 1); 
                        ?>%
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.mlm-tree-visualization {
    margin: 30px 0;
}

.mlm-tree-level-0,
.mlm-tree-level-1,
.mlm-tree-level-2,
.mlm-tree-level-3 {
    margin: 40px 0;
    text-align: center;
}

.mlm-tree-level-0 {
    margin-bottom: 60px;
}

.mlm-tree-node-main {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.node-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.node-avatar {
    font-size: 2em;
}

.node-info {
    text-align: right;
}

.node-name {
    font-size: 1.3em;
    font-weight: bold;
    margin-bottom: 5px;
}

.node-details {
    opacity: 0.9;
    font-size: 0.9em;
}

.mlm-tree-members {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.member-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    text-align: right;
    transition: all 0.3s ease;
    position: relative;
}

.member-card:hover {
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.member-avatar {
    font-size: 2em;
    margin-bottom: 10px;
}

.member-name {
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 1.1em;
}

.member-stats {
    display: flex;
    justify-content: space-between;
    font-size: 0.85em;
    color: #7f8c8d;
    margin-bottom: 10px;
}

.member-stats span {
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
}

.member-actions {
    margin-top: 10px;
}

.view-subtree {
    background: #667eea;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background 0.3s ease;
}

.view-subtree:hover {
    background: #5a6fd8;
}

.mlm-tree-member.empty .member-card {
    background: #f8f9fa;
    border-style: dashed;
    border-color: #bdc3c7;
    color: #7f8c8d;
}

.mlm-tree-controls {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* الخطوط الواصلة بين المستويات */
.mlm-tree-level-1::before,
.mlm-tree-level-2::before,
.mlm-tree-level-3::before {
    content: '';
    display: block;
    width: 2px;
    height: 40px;
    background: #bdc3c7;
    margin: 0 auto 20px auto;
}

@media (max-width: 768px) {
    .mlm-tree-members {
        grid-template-columns: 1fr;
    }
    
    .node-content {
        flex-direction: column;
        text-align: center;
    }
    
    .node-info {
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // تحديث بيانات الشجرة
    $('.mlm-refresh-tree').on('click', function() {
        const $btn = $(this);
        const $treeView = $('.mlm-tree-visualization');
        
        $btn.html('<span class="mlm-loading"></span> جاري التحديث...');
        $treeView.html('<div class="mlm-loading">جاري تحميل بيانات الشجرة...</div>');
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    });

    // توسيع/طي الكل
    $('.mlm-expand-all').on('click', function() {
        $('.mlm-tree-member').slideDown();
    });
    
    $('.mlm-collapse-all').on('click', function() {
        $('.mlm-tree-level-2 .mlm-tree-member, .mlm-tree-level-3 .mlm-tree-member').slideUp();
    });

    // عرض الفريق الفرعي
    $('.view-subtree').on('click', function() {
        const memberId = $(this).data('member');
        alert('عرض فريق العضو #' + memberId + ' - هذه الميزة قيد التطوير');
    });

    // تفاعلات بطاقات الأعضاء
    $('.member-card').on('click', function() {
        $(this).toggleClass('active');
    });
});
</script>