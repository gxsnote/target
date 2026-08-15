<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
if (!is_login()) {
    redirect(BASE_URL . 'login/');
}
$active = 'profile';
$page_title = '修改密码';
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = $_POST['oldpassword'] ?? '';
    $new = $_POST['newpassword'] ?? '';
    $user = current_user();

    // 旧密码校验使用参数化查询
    $row = DB::query(
        'SELECT id,password FROM ' . DB::table('users') . ' WHERE username=?',
        [$user['username']]
    )->fetch();

    if (!$row || $row['password'] !== md5($old)) {
        $flash = ['type' => 'error', 'msg' => '旧密码错误。'];
    } elseif (strlen($new) < 6) {
        $flash = ['type' => 'error', 'msg' => '新密码至少 6 位。'];
    } else {
        // 参数化更新
        DB::query(
            "UPDATE " . DB::table('users') . " SET password=MD5(?) WHERE username=?",
            [$new, $user['username']]
        );
        $_SESSION['user']['password'] = md5($new);
        log_action('change_password', 'normal', '', false);
        $flash = ['type' => 'success', 'msg' => '密码修改成功。'];
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:460px">
    <div class="page-head">
        <h1><?= icon('lock') ?>修改密码</h1>
        <p>当前登录：<?= e(current_user()['username']) ?></p>
    </div>
    <div class="card">
        <div class="card-body" style="padding:20px">
            <form method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>旧密码</label>
                    <input class="form-control" type="password" name="oldpassword" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>新密码</label>
                    <input class="form-control" type="password" name="newpassword" required autocomplete="off">
                </div>
                <button class="btn btn-block" type="submit"><?= icon('check', 16) ?>确认修改</button>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
