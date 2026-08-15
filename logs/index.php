<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';

// 操作日志仅管理员可查看
if (!is_admin()) {
    redirect(BASE_URL);
}

$active = 'logs';
$page_title = '操作日志';

$page = max(1, (int)($_GET['p'] ?? 1));
$size = 30;
$total = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('logs'))->fetchColumn();
$pages = max(1, (int)ceil($total / $size));
$page = min($page, $pages);
$offset = ($page - 1) * $size;

$logs = DB::query(
    'SELECT * FROM ' . DB::table('logs') . ' ORDER BY id DESC LIMIT ' . $offset . ',' . $size
)->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('log') ?>操作日志</h1>
        <p>记录全部操作与攻击尝试，共 <?= $total ?> 条。</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (!$logs): ?>
                <div class="empty">暂无操作记录。</div>
            <?php else: ?>
            <div class="table-wrap scroll" style="max-height:620px">
            <table class="data-table">
                <thead><tr><th>ID</th><th>时间</th><th>IP</th><th>类型</th><th>动作</th><th>结果</th><th>Payload</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td><?= (int)$l['id'] ?></td>
                        <td style="white-space:nowrap"><?= e($l['created_at']) ?></td>
                        <td class="ip-cell"><?= e($l['ip']) ?></td>
                        <td><span class="tag tag-<?= e($l['attack_type']) ?>"><?= e(attack_type_name($l['attack_type'])) ?></span></td>
                        <td><?= e($l['action']) ?></td>
                        <td><?= $l['is_blocked'] ? '<span class="tag tag-block">已拦截</span>' : '<span class="tag tag-pass">通过</span>' ?></td>
                        <td class="payload-cell" title="<?= e($l['payload']) ?>"><?= e($l['payload']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if ($pages > 1): ?>
            <div style="margin-top:16px;text-align:center">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a class="btn btn-sm <?= $i===$page?'':'btn-ghost' ?>" href="?p=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
