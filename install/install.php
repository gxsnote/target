<?php
/**
 * 网络安全攻防演练靶场 v1.1 安装脚本
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 *
 * 使用方法：浏览器访问 /install/install.php，安装完成后请删除本文件！
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT', dirname(__DIR__));
date_default_timezone_set('Asia/Shanghai');

$configFile = ROOT . '/config/config.php';
if (!is_file($configFile)) {
    die('配置文件不存在：config/config.php，请先修改数据库配置。');
}
$config = require $configFile;
$db = $config['db'];
$prefix = $db['prefix'];

$success = [];
$errors = [];

try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $success[] = '数据库连接成功';
} catch (PDOException $e) {
    die('数据库连接失败：' . htmlspecialchars($e->getMessage()));
}

// SQL 建表语句
$sqls = [
    // 用户表
    "CREATE TABLE IF NOT EXISTS `{$prefix}users` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `username` VARCHAR(32) NOT NULL,
        `password` VARCHAR(32) NOT NULL,
        `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // 操作/攻击日志表
    "CREATE TABLE IF NOT EXISTS `{$prefix}logs` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `ip` VARCHAR(45) NOT NULL,
        `action` VARCHAR(100) NOT NULL DEFAULT '',
        `attack_type` VARCHAR(20) NOT NULL DEFAULT 'normal',
        `payload` TEXT,
        `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
        `level` VARCHAR(10) NOT NULL DEFAULT 'high',
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_ip` (`ip`),
        KEY `idx_blocked` (`is_blocked`),
        KEY `idx_type` (`attack_type`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // 留言板表
    "CREATE TABLE IF NOT EXISTS `{$prefix}messages` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `nickname` VARCHAR(80) NOT NULL,
        `content` VARCHAR(200) NOT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // 访客统计表
    "CREATE TABLE IF NOT EXISTS `{$prefix}visitors` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `ip` VARCHAR(45) NOT NULL,
        `session_id` VARCHAR(64) NOT NULL,
        `first_seen` DATETIME NOT NULL,
        `last_active` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_session` (`session_id`),
        KEY `idx_last` (`last_active`),
        KEY `idx_ip` (`ip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        $errors[] = '建表失败：' . $e->getMessage();
    }
}
if (!$errors) {
    $success[] = '数据表创建成功（users / logs / messages / visitors）';
}

// 默认管理员账号：admin / zhuanjiao@123.
$adminPass = 'zhuanjiao@123.';
$adminHash = md5($adminPass);
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO `{$prefix}users` (`username`,`password`,`role`,`created_at`) VALUES (?,?,?,NOW())");
    $stmt->execute(['admin', $adminHash, 'admin']);
    if ($stmt->rowCount() > 0) {
        $success[] = '默认管理员创建成功：admin / ' . $adminPass;
    } else {
        $success[] = '管理员账号已存在，跳过创建';
    }
} catch (PDOException $e) {
    $errors[] = '管理员创建失败：' . $e->getMessage();
}

// 默认欢迎留言
try {
    $pdo->exec("INSERT IGNORE INTO `{$prefix}messages` (`id`,`nickname`,`content`,`created_at`) VALUES
        (1,'高先生笔记','欢迎来到网络安全攻防演练靶场 v1.1，祝各位玩得开心！',NOW())");
    $success[] = '默认留言初始化完成';
} catch (PDOException $e) {
    // 忽略
}

// 创建必要目录及安全文件
$dirs = [
    ROOT . '/uploads' => "php_flag engine off\n<FilesMatch \"\\.(php|php3|php5|phtml|phar|asp|aspx|jsp|cgi|pl|py|sh)$\">\n    Require all denied\n</FilesMatch>\nOptions -Indexes\n",
    ROOT . '/preferences' => "Require all denied\n",
    ROOT . '/H' => "php_flag engine off\n<FilesMatch \"\\.(php|php3|php5|phtml|phar|asp|aspx|jsp|cgi|pl|py|sh)$\">\n    Require all denied\n</FilesMatch>\nOptions -Indexes\n",
];

foreach ($dirs as $dir => $htaccess) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $htFile = $dir . '/.htaccess';
    if (!is_file($htFile)) {
        @file_put_contents($htFile, $htaccess);
    }
}
$success[] = '目录与安全配置文件检查完成（uploads / preferences / H）';

// 安装锁文件
@file_put_contents(ROOT . '/install/installed.lock', date('Y-m-d H:i:s'));

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>靶场 v1.1 安装</title>
<style>
body{background:#0a0a0a;color:#00ff41;font-family:Consolas,Monaco,monospace;max-width:720px;margin:40px auto;padding:0 20px;line-height:1.8}
h1{border-bottom:1px solid #00ff41;padding-bottom:10px}
.ok{color:#00ff41}.err{color:#ff4444}.warn{color:#ffaa00}
.box{border:1px solid #333;padding:16px;margin:16px 0;background:#111}
a{color:#00d4ff}
</style>
</head>
<body>
<h1>[ 网络安全攻防演练靶场 v1.1 安装 ]</h1>

<div class="box">
<?php foreach ($success as $msg): ?>
    <div class="ok">[OK] <?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
<?php foreach ($errors as $msg): ?>
    <div class="err">[ERR] <?= htmlspecialchars($msg) ?></div>
<?php endforeach; ?>
</div>

<?php if (!$errors): ?>
<div class="box">
    <p class="ok">安装完成！</p>
    <p>管理员账号：<strong>admin</strong></p>
    <p>管理员密码：<strong><?= htmlspecialchars($adminPass) ?></strong></p>
    <p class="warn">安全提醒：请立即删除 install 目录！</p>
    <p><a href="../">进入首页</a></p>
</div>
<?php else: ?>
<div class="box">
    <p class="err">安装过程中出现错误，请检查后重试。</p>
</div>
<?php endif; ?>

<p style="color:#555;font-size:12px;margin-top:30px">
    高先生笔记 | www.gxsnote.cn | QQ 67031002
</p>
</body>
</html>
