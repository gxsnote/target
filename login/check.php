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
    redirect(BASE_URL);
}

// CSRF 校验
csrf_check();

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');
$ip = get_ip();

// 防爆破检查
if (!login_rate_check($ip)) {
    log_action('login_blocked', 'login', $username, true);
    $failUrl = BASE_URL . 'login/?err=2';
    redirect($failUrl);
}

if ($username === '' || $password === '') {
    redirect(BASE_URL . 'login/?err=1');
}

// 固定最高安全等级：PDO 预处理 + 攻击特征拦截
$sig = '/(union\s+select|select\s+.+\s+from|information_schema|into\s+outfile|load_file|--|#|\/\*|\bor\b|\band\b|\bsleep\s*\(|\bbenchmark\s*\(|\|\|)/i';
if (preg_match($sig, $username) || preg_match($sig, $password)) {
    waf_block('sql', $username, 'login');
}

$stmt = DB::query(
    'SELECT * FROM ' . DB::table('users') . ' WHERE username = ? AND password = ?',
    [$username, md5($password)]
);
$user = $stmt->fetch();

if ($user) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
    ];
    login_rate_reset($ip);
    log_action('login_success', 'normal', $username, false);
    redirect(BASE_URL . 'board/?welcome=1');
}

login_rate_fail($ip);
log_action('login_fail', 'sql', $username, false);
redirect(BASE_URL . 'login/?err=1');
