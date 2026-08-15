<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'register';
$page_title = '注册账号';
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    // 10 秒注册间隔限制
    $now = time();
    $lastReg = (int)($_SESSION['last_register'] ?? 0);
    if ($now - $lastReg < 10) {
        $flash = ['type' => 'error', 'msg' => '操作太频繁，请 ' . (10 - ($now - $lastReg)) . ' 秒后再试。'];
    } elseif ($u === '' || $p === '') {
        $flash = ['type' => 'error', 'msg' => '用户名和密码不能为空。'];
    } elseif (mb_strlen($u) > 32) {
        $flash = ['type' => 'error', 'msg' => '用户名不超过 32 个字符。'];
    } elseif (strlen($p) < 6) {
        $flash = ['type' => 'error', 'msg' => '密码至少 6 位。'];
    } else {
        try {
            // PDO 参数化，入库安全
            DB::query(
                'INSERT INTO ' . DB::table('users') . ' (username,password,role,created_at) VALUES (?,?,?,NOW())',
                [$u, md5($p), 'user']
            );
            $_SESSION['last_register'] = time();
            log_action('register', 'normal', $u, false);
            $flash = ['type' => 'success', 'msg' => '注册成功，请登录。'];
        } catch (PDOException $e) {
            $flash = ['type' => 'error', 'msg' => '注册失败，用户名可能已存在。'];
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:460px">
    <div class="page-head">
        <h1><?= icon('user') ?>注册账号</h1>
        <p>注册后可登录并修改密码。</p>
    </div>
    <div class="card">
        <div class="card-body" style="padding:20px">
            <form method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>用户名</label>
                    <input class="form-control" type="text" name="username" maxlength="32" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>密码</label>
                    <input class="form-control" type="password" name="password" required autocomplete="off">
                </div>
                <button class="btn btn-block" type="submit"><?= icon('check', 16) ?>注 册</button>
            </form>
            <p style="margin-top:14px;font-size:12px;color:var(--text-mute)">已有账号？<a href="<?= BASE_URL ?>login/">去登录</a></p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
