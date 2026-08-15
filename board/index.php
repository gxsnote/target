<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'board';
$page_title = '胜利榜';
$flash = null;

// 默认黑页模板
$defaultDeface = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>Hacked By You</title>
<style>
body{margin:0;background:#000;color:#00ff41;font-family:Consolas,monospace;
     display:flex;align-items:center;justify-content:center;min-height:100vh;
     text-align:center;text-shadow:0 0 20px #00ff41}
.box{padding:40px}
h1{font-size:48px;letter-spacing:4px;margin:0 0 16px}
p{color:#5a7a5e;letter-spacing:2px}
</style>
</head>
<body>
<div class="box">
  <h1>HACKED</h1>
  <p>System has been compromised.</p>
  <p>// your name here</p>
</div>
</body>
</html>
HTML;

// 提交黑页（仅管理员）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do']) && $_POST['do'] === 'deface') {
    csrf_check();
    if (!is_admin()) {
        $flash = ['type' => 'error', 'msg' => '仅管理员可写入黑页。'];
    } else {
    $title = trim($_POST['title'] ?? '');
    $code  = $_POST['code'] ?? '';

    // 标题作为文件名：仅允许字母数字下划线横线中文，防止目录穿越
    $safeTitle = preg_replace('/[^\p{Han}a-zA-Z0-9_\-]/u', '', $title);
    if ($safeTitle === '') {
        $safeTitle = 'win_' . date('YmdHis');
    }
    if (mb_strlen($code) < 10) {
        $flash = ['type' => 'error', 'msg' => '黑页代码不能为空且至少 10 个字符。'];
    } elseif (preg_match('/<\?php|<\?=|<\?\s|<\%|<%/i', $code)) {
        // 纵深防御：禁止 PHP 标签（H 目录已通过 .htaccess 禁止 PHP 执行）
        $flash = ['type' => 'error', 'msg' => '黑页仅允许 HTML/CSS/JS，禁止包含 PHP 代码。'];
    } else {
        // 同名覆盖：加时间戳后缀避免冲突
        $fileBase = $safeTitle;
        $path = config('site.h_dir') . DIRECTORY_SEPARATOR . $fileBase . '.html';
        if (file_exists($path)) {
            $fileBase = $safeTitle . '_' . date('His');
            $path = config('site.h_dir') . DIRECTORY_SEPARATOR . $fileBase . '.html';
        }
        // 黑页代码原样写入，允许 HTML/CSS/JS 执行
        if (@file_put_contents($path, $code) !== false) {
            log_action('deface', 'normal', $fileBase . '.html', false);
            $flash = ['type' => 'success', 'msg' => '黑页已生成：' . $fileBase . '.html'];
        } else {
            $flash = ['type' => 'error', 'msg' => '写入失败，请检查 H 目录权限。'];
        }
    }
    }
}

$winners = get_winners();
$welcome = isset($_GET['welcome']);
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('trophy') ?>胜利榜</h1>
        <p>自动读取 H 目录下的 .html 文件。成功登录后可在此写入自定义黑页（支持完整 HTML/CSS/JS）。</p>
    </div>

    <?php if ($welcome && is_admin()): ?>
    <div class="alert alert-success"><?= icon('check', 16) ?>认证已绕过，欢迎 <?= e(current_user()['username']) ?>。可在下方写入你的黑页。</div>
    <?php endif; ?>

    <?php if (is_admin()): ?>
    <div class="card">
        <div class="card-head"><h3><?= icon('flag') ?>写入黑页</h3><span class="extra">仅管理员可写，标题即文件名，代码原样保存为 .html</span>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="do" value="deface">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>标题（文件名）</label>
                    <input class="form-control" type="text" name="title" maxlength="40" placeholder="例如：HackedByXxx（仅字母数字中文下划线）" required>
                    <div class="form-hint">文件名仅保留中文、字母、数字、下划线和横线，自动追加 .html。</div>
                </div>
                <div class="form-group">
                    <label>黑页代码（HTML）</label>
                    <textarea id="deface-code" class="form-control deface-editor" name="code" spellcheck="false" required><?= e($defaultDeface) ?></textarea>
                    <div class="form-hint">支持完整 HTML/CSS/JavaScript，保存后访问即渲染执行。可按 Tab 缩进。</div>
                </div>
                <button class="btn" type="submit"><?= icon('flag', 16) ?>生成黑页</button>
                <a class="btn btn-ghost" href="<?= BASE_URL ?>H/welcome.html" target="_blank">预览示例</a>
            </form>
        </div>
    </div>
    <?php elseif (is_login()): ?>
    <div class="alert alert-error"><?= icon('lock', 16) ?>写黑页仅限管理员账号，普通用户可查看下方胜利记录。</div>
    <?php else: ?>
    <div class="alert alert-info"><?= icon('user', 16) ?>登录后可查看胜利记录，写黑页仅限管理员。<a href="<?= BASE_URL ?>login/">前往登录</a></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-head">
            <h3><?= icon('trophy') ?>全部胜利记录（<?= count($winners) ?>）</h3>
            <span class="extra">目录：H/</span>
        </div>
        <div class="card-body">
            <?php if (!$winners): ?>
                <div class="empty"><?= icon('flag', 32) ?>暂无胜利记录。</div>
            <?php else: ?>
            <div class="scroll" style="max-height:460px">
                <?php foreach ($winners as $w): ?>
                <div class="winner-item">
                    <div class="winner-icon"><?= icon('trophy', 18) ?></div>
                    <div class="winner-info">
                        <a href="<?= BASE_URL ?>H/<?= e($w['name']) ?>" target="_blank"><?= e($w['name']) ?></a>
                        <div class="winner-meta"><?= date('Y-m-d H:i:s', $w['time']) ?> &middot; <?= number_format($w['size']) ?> bytes</div>
                    </div>
                    <a class="btn btn-sm btn-cyan" href="<?= BASE_URL ?>H/<?= e($w['name']) ?>" target="_blank">访问</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
