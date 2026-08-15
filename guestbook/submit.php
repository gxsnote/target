<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'guestbook/');
}

// CSRF 校验
csrf_check();

$nickname = trim($_POST['nickname'] ?? '');
$content  = trim($_POST['content'] ?? '');

// 10 秒发言间隔限制
$now = time();
$last = (int)($_SESSION['last_guestbook'] ?? 0);
if ($now - $last < 10) {
    $flash = ['type' => 'error', 'msg' => '发言太频繁，请 ' . (10 - ($now - $last)) . ' 秒后再试。'];
} elseif ($nickname === '' || $content === '') {
    $flash = ['type' => 'error', 'msg' => '昵称和内容不能为空。'];
} elseif (mb_strlen($nickname) > 80 || mb_strlen($content) > 200) {
    $flash = ['type' => 'error', 'msg' => '昵称不超过 80 字，留言内容不超过 200 字。'];
} elseif (strcasecmp($nickname, 'admin') === 0 && !is_admin()) {
    $flash = ['type' => 'error', 'msg' => '该昵称已被保留，请勿使用。'];
} else {
    // XSS 特征拦截
    $sig = '/(<\s*script|<\s*iframe|onerror\s*=|onload\s*=|onmouseover\s*=|onclick\s*=|onfocus\s*=|javascript:|vbscript:|<\s*img[^>]+onerror)/i';
    if (preg_match($sig, $nickname) || preg_match($sig, $content)) {
        waf_block('xss', $content, 'guestbook');
    }

    DB::query(
        'INSERT INTO ' . DB::table('messages') . ' (nickname, content, created_at) VALUES (?,?,NOW())',
        [$nickname, $content]
    );
    $_SESSION['last_guestbook'] = $now;
    // 记住昵称，JSON 存入 Cookie（禁止 serialize 反序列化用户输入）
    setcookie('gx_pref', json_encode(['nick' => $nickname], JSON_UNESCAPED_UNICODE), time() + 86400 * 30, '/');
    log_action('guestbook_post', 'normal', mb_substr($content, 0, 50), false);
    $flash = ['type' => 'success', 'msg' => '留言成功。'];
}

$active = 'guestbook';
$page_title = '留言板';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <p><a class="btn" href="<?= BASE_URL ?>guestbook/">返回留言板</a></p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
