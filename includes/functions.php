<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 公共函数库

/** 读取配置 */
function config(string $key)
{
    static $c = null;
    if ($c === null) {
        $c = require ROOT . '/config/config.php';
    }
    $v = $c;
    foreach (explode('.', $key) as $k) {
        if (!is_array($v) || !array_key_exists($k, $v)) {
            return null;
        }
        $v = $v[$k];
    }
    return $v;
}

/** HTML 转义输出 */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** 当前安全等级（固定最高级） */
function current_level(): string
{
    return 'high';
}

/** 获取客户端真实 IP（仅信任 REMOTE_ADDR，防止伪造代理头） */
function get_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    // 仅在确认服务器位于可信反向代理后时才信任代理头
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return '0.0.0.0';
    }
    return $ip;
}

/** 记录操作日志 / 攻击日志 */
function log_action(string $action, string $attackType, string $payload, bool $blocked): void
{
    try {
        DB::query(
            'INSERT INTO ' . DB::table('logs') . ' (ip, action, attack_type, payload, is_blocked, level, created_at)
             VALUES (?,?,?,?,?,?,NOW())',
            [get_ip(), $action, $attackType, mb_substr($payload, 0, 500), $blocked ? 1 : 0, current_level()]
        );
    } catch (Throwable $e) {
        // 日志失败不影响主流程
    }
}

/** 攻击类型中文名 */
function attack_type_name(string $t): string
{
    return [
        'sql'    => 'SQL注入',
        'xss'    => 'XSS跨站',
        'php'    => 'PHP代码',
        'upload' => '文件上传',
        'cmd'    => '命令执行',
        'login'  => '登录尝试',
        'normal' => '正常操作',
    ][$t] ?? $t;
}

/** 等级徽章文案 */
function level_meta(string $level): array
{
    return [
        'low'    => ['name' => '低安全等级', 'class' => 'level-low'],
        'medium' => ['name' => '中安全等级', 'class' => 'level-medium'],
        'high'   => ['name' => '高安全等级', 'class' => 'level-high'],
    ][$level] ?? ['name' => $level, 'class' => 'level-high'];
}

/** 扫描 H 目录下的胜利 .html 文件 */
function get_winners(): array
{
    $dir = config('site.h_dir');
    if (!is_dir($dir)) {
        return [];
    }
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.html') ?: [];
    $out = [];
    foreach ($files as $f) {
        $out[] = [
            'name' => basename($f),
            'time' => filemtime($f),
            'size' => filesize($f),
        ];
    }
    usort($out, fn($a, $b) => $a['time'] <=> $b['time']);
    // 教主置顶(硬顶第一)
    $topKey = null;
    foreach ($out as $i => $w) { if ($w['name'] === '教主.html') { $topKey = $i; break; } }
    if ($topKey !== null) { $top = $out[$topKey]; array_splice($out, $topKey, 1); array_unshift($out, $top); }
    return $out;
}

/** 统计数据 */
function get_stats(): array
{
    $total = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('logs'))->fetchColumn();
    $blocked = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('logs') . ' WHERE is_blocked=1')->fetchColumn();
    $winners = count(get_winners());
    $messages = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('messages'))->fetchColumn();
    $users = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('users'))->fetchColumn();
    $visitors = 0;
    $online = 0;
    try {
        $visitors = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('visitors'))->fetchColumn();
        $online = (int)DB::query('SELECT COUNT(*) FROM ' . DB::table('visitors') . ' WHERE last_active > DATE_SUB(NOW(), INTERVAL 5 MINUTE)')->fetchColumn();
    } catch (Throwable $e) {
    }
    return compact('total', 'blocked', 'winners', 'messages', 'users', 'visitors', 'online');
}

/** 记录访客在线（每次访问 upsert） */
function track_visitor(): void
{
    try {
        DB::query(
            'INSERT INTO ' . DB::table('visitors') . ' (ip, session_id, first_seen, last_active)
             VALUES (?,?,NOW(),NOW())
             ON DUPLICATE KEY UPDATE last_active=NOW(), ip=VALUES(ip)',
            [get_ip(), session_id()]
        );
    } catch (Throwable $e) {
    }
}

/** 内联 SVG 图标（国内企业级线性图标风格） */
function icon(string $name, int $size = 18): string
{
    $paths = [
        'home'    => '<path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-7h-6v7H4a1 1 0 0 1-1-1V9.5z"/>',
        'shield'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'term'    => '<path d="M4 17l6-5-6-5"/><path d="M12 19h8"/>',
        'trophy'  => '<path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M7 6H4v2a3 3 0 0 0 3 3"/><path d="M17 6h3v2a3 3 0 0 1-3 3"/>',
        'skull'   => '<path d="M12 2a9 9 0 0 0-5 16.5V21a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-2.5A9 9 0 0 0 12 2z"/><path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M10 16l2-2 2 2"/>',
        'log'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'chat'    => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'upload'  => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
        'about'   => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        'help'    => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
        'lock'    => '<rect x="4" y="11" width="16" height="10" rx="1"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'unlock'  => '<rect x="4" y="11" width="16" height="10" rx="1"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/>',
        'warn'    => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'check'   => '<path d="M20 6L9 17l-5-5"/>',
        'close'   => '<path d="M18 6L6 18"/><path d="M6 6l12 12"/>',
        'user'    => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'time'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'globe'   => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20"/><path d="M12 2a15 15 0 0 0 0 20"/>',
        'zap'     => '<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>',
        'code'    => '<path d="M16 18l6-6-6-6"/><path d="M8 6l-6 6 6 6"/>',
        'flag'    => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22V15"/>',
        'sun'     => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
        'moon'    => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
    ];
    $p = $paths[$name] ?? '';
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $p . '</svg>';
}

/** 跳转 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** 是否登录 */
function is_login(): bool
{
    return !empty($_SESSION['user']);
}

/** 是否管理员 */
function is_admin(): bool
{
    return is_login() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/** 当前登录用户 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}
