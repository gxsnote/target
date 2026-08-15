<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'graveyard';
$page_title = '战败榜';

// 按 IP 聚合攻击次数（一个 IP 一行，累计该 IP 所有被拦截的攻击）
$page = max(1, (int)($_GET['p'] ?? 1));
$size = 20;

$totalIp = (int)DB::query(
    'SELECT COUNT(DISTINCT ip) FROM ' . DB::table('logs') . ' WHERE is_blocked=1'
)->fetchColumn();
$pages = max(1, (int)ceil($totalIp / $size));
$page = min($page, $pages);
$offset = ($page - 1) * $size;

$rows = DB::query(
    'SELECT ip,
            COUNT(*) AS total,
            SUM(attack_type = ?) AS sql_cnt,
            SUM(attack_type = ?) AS xss_cnt,
            SUM(attack_type = ?) AS upload_cnt,
            MAX(created_at) AS last_time
     FROM ' . DB::table('logs') . '
     WHERE is_blocked = 1
     GROUP BY ip
     ORDER BY total DESC, last_time DESC
     LIMIT ' . $offset . ',' . $size,
    ['sql', 'xss', 'upload']
)->fetchAll();

$totalBlocked = (int)DB::query(
    'SELECT COUNT(*) FROM ' . DB::table('logs') . ' WHERE is_blocked=1'
)->fetchColumn();
$today = (int)DB::query(
    'SELECT COUNT(*) FROM ' . DB::table('logs') . ' WHERE is_blocked=1 AND DATE(created_at)=CURDATE()'
)->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('skull') ?>战败榜</h1>
        <p>按攻击者 IP 聚合排名，同一 IP 的所有攻击累计到一条。共 <?= $totalIp ?> 个攻击者 IP，<?= $totalBlocked ?> 次拦截，今日 <?= $today ?> 次。</p>
    </div>

    <div class="grid grid-3" style="margin-bottom:16px">
        <div class="card" style="margin-bottom:0">
            <div class="card-body" style="text-align:center">
                <div style="font-size:22px;color:var(--red)"><?= $totalBlocked ?></div>
                <div style="font-size:10.5px;color:var(--text-mute);letter-spacing:1px">总拦截次数</div>
            </div>
        </div>
        <div class="card" style="margin-bottom:0">
            <div class="card-body" style="text-align:center">
                <div style="font-size:22px;color:var(--red)"><?= $totalIp ?></div>
                <div style="font-size:10.5px;color:var(--text-mute);letter-spacing:1px">攻击者 IP 数</div>
            </div>
        </div>
        <div class="card" style="margin-bottom:0">
            <div class="card-body" style="text-align:center">
                <div style="font-size:22px;color:var(--red)"><?= $today ?></div>
                <div style="font-size:10.5px;color:var(--text-mute);letter-spacing:1px">今日拦截</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('log') ?>攻击排行榜</h3><span class="extra">按总攻击次数降序</span></div>
        <div class="card-body">
            <?php if (!$rows): ?>
                <div class="empty"><?= icon('shield', 32) ?>暂无被拦截的攻击记录。</div>
            <?php else: ?>
            <div class="table-wrap scroll" style="max-height:560px">
            <table class="data-table">
                <thead><tr>
                    <th>排名</th>
                    <th>挑战者</th>
                    <th>IP 地址</th>
                    <th>总攻击</th>
                    <th>SQL</th>
                    <th>XSS</th>
                    <th>上传</th>
                    <th>最近攻击</th>
                </tr></thead>
                <tbody>
                <?php foreach ($rows as $i => $r): $rank = $offset + $i + 1; ?>
                    <tr>
                        <td class="rank-cell <?= $rank <= 3 ? 'top' : '' ?>"><?= $rank ?></td>
                        <td>匿名挑战者</td>
                        <td class="ip-cell"><?= e($r['ip']) ?></td>
                        <td class="num-cell"><?= (int)$r['total'] ?></td>
                        <td class="num-cell"><?= (int)$r['sql_cnt'] ?></td>
                        <td class="num-cell"><?= (int)$r['xss_cnt'] ?></td>
                        <td class="num-cell"><?= (int)$r['upload_cnt'] ?></td>
                        <td style="white-space:nowrap;color:var(--text-dim)"><?= e($r['last_time']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php if ($pages > 1): ?>
            <div style="margin-top:14px;text-align:center">
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
