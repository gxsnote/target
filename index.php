<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/includes/init.php';

// 安装检测
try {
    DB::pdo()->query('SELECT 1 FROM ' . DB::table('users') . ' LIMIT 1');
} catch (Throwable $e) {
    header('Location: ' . BASE_URL . 'install/install.php');
    exit;
}

$active = 'home';
$page_title = '首页';
$stats = get_stats();

// 最近战败记录
$blockedLogs = DB::query('SELECT * FROM ' . DB::table('logs') . ' WHERE is_blocked=1 ORDER BY id DESC LIMIT 8')->fetchAll();
// 最近操作日志
$recentLogs = DB::query('SELECT * FROM ' . DB::table('logs') . ' ORDER BY id DESC LIMIT 10')->fetchAll();
// 最近留言
$messages = DB::query(
    'SELECT * FROM ' . DB::table('messages') . ' 
     ORDER BY CASE WHEN id = 1 THEN 0 ELSE 1 END, id DESC 
     LIMIT 5'
)->fetchAll();
// 胜利榜
$winners = array_slice(get_winners(), 0, 8);

$loginError = isset($_GET['err']) ? '登录失败：用户名或密码错误，或输入被安全规则拦截。' : '';

// 留言搜索（参数化查询，结果转义输出）
$q = trim($_GET['q'] ?? '');
$searchResults = [];
if ($q !== '') {
    $searchResults = DB::query(
        'SELECT nickname,content,created_at FROM ' . DB::table('messages') . ' WHERE content LIKE ? ORDER BY id DESC LIMIT 20',
        ['%' . $q . '%']
    )->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<div class="container">

    <!-- Hero -->
    <section class="hero">
        <h1>网络安全攻防演练靶场<span class="cursor"></span></h1>
        <p>基于 PHP 8.0 + MySQL 5.7 构建的 Web 安全教学平台，固定最高安全等级，覆盖 SQL 注入、XSS、文件上传等典型漏洞场景。所有攻击行为均被记录，可在战败榜复盘。</p>
        <div class="hero-stats">
            <div class="hero-stat"><div class="num"><?= $stats['total'] ?></div><div class="label">操作总次数</div></div>
            <div class="hero-stat"><div class="num"><?= $stats['blocked'] ?></div><div class="label">被拦截次数</div></div>
            <div class="hero-stat"><div class="num"><?= $stats['winners'] ?></div><div class="label">胜利页面</div></div>
            <div class="hero-stat"><div class="num"><?= $stats['messages'] ?></div><div class="label">留言条数</div></div>
        </div>
    </section>

    <!-- 项目状态提示 -->
    <div class="vuln-notice" style="border: 1px solid #ff4444; background: rgba(255, 68, 68, 0.06); padding: 10px 16px; border-radius: 6px;">
        <span class="vuln-dot" style="background: #ff4444;"></span>
        <span style="color: #ff6666; font-weight: 500;">
            项目已停止，点击查看详情 →
            <a href="javascript:void(0);" onclick="document.getElementById('vulnModal').style.display='flex';" style="color: #ff4444; font-weight: 600; text-decoration: underline;">
                公告说明
            </a>
        </span>
    </div>

    <!-- 搜索 -->
    <section class="card">
        <div class="card-head"><h3><?= icon('chat') ?>留言搜索</h3><span class="extra">关键词检索留言内容</span></div>
        <div class="card-body">
            <form method="get" action="" style="display:flex;gap:8px;flex-wrap:wrap">
                <input class="form-control" type="text" name="q" value="<?= e($q) ?>" placeholder="输入关键词搜索留言……" style="flex:1;min-width:200px">
                <button class="btn" type="submit"><?= icon('log', 16) ?>搜索</button>
            </form>
            <?php if ($q !== ''): ?>
            <div style="margin-top:14px;font-size:12px;color:var(--text-dim)">
                关键词 <span style="color:var(--green)"><?= e($q) ?></span> 共找到 <?= count($searchResults) ?> 条留言：
            </div>
            <?php if ($searchResults): ?>
            <div class="scroll" style="max-height:260px;margin-top:8px">
                <?php foreach ($searchResults as $m): ?>
                <div class="msg-item">
                    <div class="msg-head"><span class="msg-nick"><?= e($m['nickname']) ?></span><span class="msg-time"><?= e($m['created_at']) ?></span></div>
                    <div class="msg-content"><?= e($m['content']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty" style="padding:16px">没有匹配的留言。</div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- 注入区 -->
    <section class="card">
        <div class="card-head">
            <h3><?= icon('term') ?>注入练习区 · 登录认证</h3>
            <span class="extra">SECURE MODE</span>
        </div>
        <div class="login-panel">
            <div class="login-form-side">
                <h3>系统登录</h3>
                <p class="sub">尝试通过构造输入绕过认证。含注入特征的请求将被直接拦截。</p>
                <?php if ($loginError): ?>
                    <div class="alert alert-error"><?= icon('warn', 16) ?><?= e($loginError) ?></div>
                <?php endif; ?>
                <?php if (is_login()): ?>
                    <div class="alert alert-success"><?= icon('check', 16) ?>已登录：<?= e(current_user()['username']) ?>（<?= e(current_user()['role']) ?>），<a href="<?= BASE_URL ?>login/logout.php">退出</a><?php if (is_admin()): ?> · <a href="<?= BASE_URL ?>board/">前往胜利榜写黑页</a><?php else: ?> · <a href="<?= BASE_URL ?>board/">查看胜利榜</a><?php endif; ?></div>
                <?php else: ?>
                <form method="post" action="<?= BASE_URL ?>login/check.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>用户名</label>
                        <input class="form-control" type="text" name="username" placeholder="请输入用户名" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input class="form-control" type="password" name="password" placeholder="请输入密码" autocomplete="off">
                    </div>
                    <button class="btn btn-block" type="submit"><?= icon('unlock', 16) ?>登 录</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="login-info-side">
                <h3 style="color:var(--green);margin-bottom:8px"><?= icon('code') ?>防护代码</h3>
                <div class="code-box"><span class="c-com">// PDO 预处理，参数化查询</span>
<span class="c-key">$sql</span> = <span class="c-str">"SELECT * FROM gxs_users
        WHERE username = ? AND password = ?"</span>;
<span class="c-key">$stmt</span> = <span class="c-key">$pdo</span>->prepare(<span class="c-key">$sql</span>);
<span class="c-key">$stmt</span>->execute([<span class="c-key">$username</span>, md5(<span class="c-key">$password</span>)]);
</div>
            </div>
        </div>
    </section>

    <!-- 胜利榜 / 战败榜 -->
    <section class="grid grid-2">
        <div class="card">
            <div class="card-head">
                <h3><?= icon('trophy') ?>胜利榜</h3>
                <a class="extra" href="<?= BASE_URL ?>board/">查看全部</a>
            </div>
            <div class="card-body">
                <?php if (!$winners): ?>
                    <div class="empty"><?= icon('flag', 32) ?>暂无胜利记录，等待第一位攻破者。</div>
                <?php else: ?>
                <div class="scroll" style="max-height:340px">
                    <?php foreach ($winners as $w): ?>
                    <div class="winner-item">
                        <div class="winner-icon"><?= icon('trophy', 18) ?></div>
                        <div class="winner-info">
                            <a href="<?= BASE_URL ?>H/<?= e($w['name']) ?>" target="_blank"><?= e($w['name']) ?></a>
                            <div class="winner-meta"><?= date('Y-m-d H:i:s', $w['time']) ?> &middot; <?= number_format($w['size']) ?> bytes</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3><?= icon('skull') ?>战败榜</h3>
                <a class="extra" href="<?= BASE_URL ?>graveyard/">查看全部</a>
            </div>
            <div class="card-body">
                <?php if (!$blockedLogs): ?>
                    <div class="empty"><?= icon('shield', 32) ?>暂无被拦截的攻击记录。</div>
                <?php else: ?>
                <div class="table-wrap scroll" style="max-height:340px">
                <table class="data-table">
                    <thead><tr><th>IP</th><th>类型</th><th>时间</th></tr></thead>
                    <tbody>
                    <?php foreach ($blockedLogs as $l): ?>
                        <tr>
                            <td class="ip-cell"><?= e($l['ip']) ?></td>
                            <td><span class="tag tag-<?= e($l['attack_type']) ?>"><?= e(attack_type_name($l['attack_type'])) ?></span></td>
                            <td><?= e($l['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- 操作日志 / 留言板 -->
    <section class="grid grid-2">
        <div class="card">
            <div class="card-head">
                <h3><?= icon('log') ?>操作日志</h3>
                <a class="extra" href="<?= BASE_URL ?>logs/">完整日志</a>
            </div>
            <div class="card-body">
                <?php if (!$recentLogs): ?>
                    <div class="empty">暂无操作记录。</div>
                <?php else: ?>
                <div class="scroll">
                    <?php foreach ($recentLogs as $l): ?>
                    <div class="log-item">
                        <span class="log-dot <?= $l['is_blocked'] ? 'blocked' : 'pass' ?>"></span>
                        <div class="log-main">
                            <div class="line">
                                <span class="tag tag-<?= e($l['attack_type']) ?>"><?= e(attack_type_name($l['attack_type'])) ?></span>
                                <?= $l['is_blocked'] ? '<span class="tag tag-block">已拦截</span>' : '<span class="tag tag-pass">通过</span>' ?>
                                <?= e($l['action']) ?>
                            </div>
                            <div class="meta"><?= e($l['ip']) ?> &middot; <?= e($l['created_at']) ?><?= $l['payload'] ? ' &middot; ' . e(mb_substr($l['payload'],0,60)) : '' ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h3><?= icon('chat') ?>留言板</h3>
                <a class="extra" href="<?= BASE_URL ?>guestbook/">更多留言</a>
            </div>
            <div class="card-body">
                <form id="guest-form" method="post" action="<?= BASE_URL ?>guestbook/submit.php" style="margin-bottom:16px">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <input class="form-control" type="text" name="nickname" placeholder="昵称" maxlength="80" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="content" placeholder="说点什么……" maxlength="200" required></textarea>
                    </div>
                    <button class="btn" type="submit"><?= icon('chat', 16) ?>发表留言</button>
                </form>
                <?php if (!$messages): ?>
                    <div class="empty">暂无留言。</div>
                <?php else: ?>
                <div class="scroll" style="max-height:340px">
                    <?php foreach ($messages as $m): ?>
                    <div class="msg-item">
                        <div class="msg-head">
                            <span class="msg-nick"><?= e($m['nickname']) ?></span>
                            <span class="msg-time"><?= e($m['created_at']) ?></span>
                        </div>
                        <div class="msg-content"><?= e($m['content']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

</div>

<!-- 项目停止公告弹窗 -->
<div class="vuln-modal" id="vulnModal">
    <div class="vuln-modal-card">
        <div class="vuln-modal-head">
            <span class="vuln-blink" style="color: #ff4444;">⏸</span> 项目状态通知
        </div>
        <div class="vuln-modal-body">

            <!-- 核心通知 -->
            <p style="margin: 0 0 12px 0; padding: 10px 12px; background: rgba(255, 68, 68, 0.12); border: 2px solid #ff4444; border-radius: 4px; color: #ff6666; text-align: center; font-weight: bold; font-size: 16px;">
                ⚠️ 靶场项目已全权停止
            </p>

            <!-- 说明 -->
            <p style="margin: 12px 0; color: #ccc; line-height: 1.8; font-size: 14px;">
                感谢各位研究员长期以来的支持与参与。<br><br>
                后续相关活动将在 <strong style="color: #00d4ff;">抖音</strong> 开放，敬请关注。
            </p>

            <!-- 开源信息 -->
            <div style="margin: 12px 0; padding: 12px 14px; background: rgba(0, 212, 255, 0.05); border: 1px solid #00d4ff; border-radius: 4px;">
                <p style="margin: 0 0 6px 0; color: #00d4ff; font-weight: 600; font-size: 14px;">📦 本次靶场代码已开源</p>
                <p style="margin: 0; color: #aaa; font-size: 13px; word-break: break-all;">
                    下载地址：<a href="https://github.com/yourname/target" target="_blank" rel="noopener" style="color: #00d4ff; text-decoration: underline;">https://github.com/yourname/target</a>（请替换为实际仓库地址）
                </p>
            </div>

            <!-- 提醒 -->
            <p style="margin-top: 12px; padding: 8px 12px; background: rgba(255, 68, 68, 0.06); border-left: 3px solid #ff4444; color: #ff8888; font-size: 13px;">
                ⚠️ 即日起不再接受新的渗透测试提交，胜利榜与战败榜将保持只读状态。
            </p>

        </div>
        <div class="vuln-modal-foot">
            <button class="btn" id="vulnClose">我知道了</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>