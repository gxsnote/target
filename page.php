<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 帮助文档页：?p=xxx 加载 pages/ 下的文档
require __DIR__ . '/includes/init.php';

$p = $_GET['p'] ?? 'home';
// 白名单：只允许字母、数字、下划线，杜绝目录穿越
if (!preg_match('/^[a-z0-9_]+$/i', $p)) {
    $p = 'home';
}
$file = __DIR__ . '/pages/' . $p . '.php';
if (!is_file($file)) {
    $file = __DIR__ . '/pages/home.php';
}

$active = 'help';
$page_title = '帮助文档';
require __DIR__ . '/includes/header.php';
include $file;
require __DIR__ . '/includes/footer.php';
