<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'guestbook';
$page_title = '留言板';

$messages = DB::query('SELECT * FROM ' . DB::table('messages') . ' ORDER BY CASE WHEN id = 1 THEN 0 ELSE 1 END, id DESC LIMIT 100')->fetchAll();

// 记住的昵称
$lastNick = (is_array($pref) && isset($pref['nick']) && is_string($pref['nick'])) ? $pref['nick'] : '';

// 查看该昵称的所有留言：根据留言 ID 从库里取出昵称，再按昵称查询
$filterMsgs = [];
$filterNick = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nick_id'])) {
    csrf_check();
    $id = (int)$_POST['nick_id'];
    $row = DB::query('SELECT nickname FROM ' . DB::table('messages') . ' WHERE id=?', [$id])->fetch();
    if ($row) {
        $filterNick = $row['nickname'];
        // 参数化查询，防止 SQL 注入
        $filterMsgs = DB::query(
            'SELECT nickname,content,created_at FROM ' . DB::table('messages') . ' WHERE nickname=? ORDER BY id DESC',
            [$filterNick]
        )->fetchAll();
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('chat') ?>留言板</h1>
        <p>所有输出经 htmlspecialchars(ENT_QUOTES) 转义，并拦截脚本/事件特征。尝试 XSS 将被拦截。</p>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head"><h3><?= icon('chat') ?>发表留言</h3></div>
            <div class="card-body">
                <form id="guest-form" method="post" action="<?= BASE_URL ?>guestbook/submit.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>昵称</label>
                        <input class="form-control" type="text" name="nickname" maxlength="80" value="<?= e($lastNick) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>内容</label>
                        <textarea class="form-control" name="content" maxlength="200" required></textarea>
                        <div class="form-hint">内容不超过 200 字，两次发言间隔至少 10 秒。</div>
                    </div>
                    <button class="btn" type="submit"><?= icon('chat', 16) ?>提交</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h3><?= icon('code') ?>防护代码</h3></div>
            <div class="card-body">
                <div class="code-box"><span class="c-com">// 输出时完整转义单双引号</span>
<span class="c-key">echo</span> htmlspecialchars(<span class="c-key">$msg</span>, ENT_QUOTES, <span class="c-str">'UTF-8'</span>);

<span class="c-com">// 输入时拦截 XSS 特征</span>
<span class="c-key">if</span> (preg_match(<span class="c-str">'/&lt;script|onerror=|javascript:/i'</span>, <span class="c-key">$msg</span>)) {
    <span class="c-key">waf_block</span>(<span class="c-str">'xss'</span>, <span class="c-key">$msg</span>);
}</div>
            </div>
        </div>
    </div>

    <?php if ($filterNick !== ''): ?>
    <div class="card">
        <div class="card-head"><h3><?= icon('log') ?>检索结果：<?= e($filterNick) ?> 的留言（<?= count($filterMsgs) ?>）</h3></div>
        <div class="card-body">
            <?php if (!$filterMsgs): ?>
                <div class="empty">没有找到该昵称的留言。</div>
            <?php else: ?>
            <div class="scroll" style="max-height:300px">
                <?php foreach ($filterMsgs as $m): ?>
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
    <?php endif; ?>

    <div class="card">
        <div class="card-head"><h3><?= icon('log') ?>全部留言（<?= count($messages) ?>）</h3><span class="extra">点「TA的留言」查看该昵称全部留言</span></div>
        <div class="card-body">
            <?php if (!$messages): ?>
                <div class="empty">暂无留言。</div>
            <?php else: ?>
            <div class="scroll" style="max-height:520px">
                <?php foreach ($messages as $m): ?>
                <div class="msg-item">
                    <div class="msg-head">
                        <span class="msg-nick"><?= e($m['nickname']) ?></span>
                        <span class="msg-time"><?= e($m['created_at']) ?></span>
                        <form method="post" style="display:inline;margin-left:auto">
                            <?= csrf_field() ?>
                            <input type="hidden" name="nick_id" value="<?= (int)$m['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-ghost">TA的留言</button>
                        </form>
                    </div>
                    <div class="msg-content" title="<?= e($m['content']) ?>"><?= e($m['content']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
