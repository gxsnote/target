<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 应用初始化入口，所有页面顶部 require 本文件
define('ROOT', dirname(__DIR__));
define('BASE_URL', '/'); // 如部署在子目录请改为 /bachang/

date_default_timezone_set('Asia/Shanghai');

// 全局安全响应头
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session 安全配置（必须在 session_start 之前设置）
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Lax');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}
session_start();

require ROOT . '/includes/functions.php';
require ROOT . '/includes/db.php';
require ROOT . '/includes/Cache.php';
require ROOT . '/config/waf.php';

// 仅允许中国大陆 IP 访问
require ROOT . '/includes/ip_guard.php';
ip_guard_check();

// 读取用户偏好（记住昵称等），JSON 存储于 Cookie（禁止 unserialize 反序列化用户输入）
$pref = [];
if (isset($_COOKIE['gx_pref'])) {
    $decoded = json_decode($_COOKIE['gx_pref'], true);
    if (is_array($decoded)) {
        $pref = $decoded;
    }
}

// CSRF Token 生成
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** 校验 CSRF Token */
function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token 验证失败，请返回重试。');
    }
}

/** 输出隐藏的 CSRF input */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

/** 登录失败次数检查与记录（防爆破） */
function login_rate_check(string $ip): bool
{
    $key = 'login_fail_' . md5($ip);
    $fails = $_SESSION[$key] ?? ['count' => 0, 'time' => 0];
    // 15 分钟内最多失败 5 次
    if ($fails['count'] >= 5 && (time() - $fails['time']) < 900) {
        return false;
    }
    if ((time() - $fails['time']) >= 900) {
        $fails = ['count' => 0, 'time' => 0];
    }
    $_SESSION[$key] = $fails;
    return true;
}

function login_rate_fail(string $ip): void
{
    $key = 'login_fail_' . md5($ip);
    $fails = $_SESSION[$key] ?? ['count' => 0, 'time' => 0];
    $fails['count']++;
    $fails['time'] = time();
    $_SESSION[$key] = $fails;
}

function login_rate_reset(string $ip): void
{
    $key = 'login_fail_' . md5($ip);
    unset($_SESSION[$key]);
}

// 兼容旧版：访客表不存在则自动创建
try {
    DB::pdo()->exec("CREATE TABLE IF NOT EXISTS `" . config('db.prefix') . "visitors` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `ip` VARCHAR(45) NOT NULL,
        `session_id` VARCHAR(64) NOT NULL,
        `first_seen` DATETIME NOT NULL,
        `last_active` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_session` (`session_id`),
        KEY `idx_last` (`last_active`),
        KEY `idx_ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {
}

// 记录访客在线
track_visitor();
