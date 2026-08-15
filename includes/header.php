<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 公共页眉，各页面在业务逻辑后 require
$page_title = $page_title ?? config('site.name');
$active = $active ?? '';
$siteStats = get_stats();
$nav = [
    'home'      => ['首页', BASE_URL, 'home'],
    'login'     => ['注入练习', BASE_URL . 'login/', 'term'],
    'board'     => ['胜利榜', BASE_URL . 'board/', 'trophy'],
    'graveyard' => ['战败榜', BASE_URL . 'graveyard/', 'skull'],
    'logs'      => ['操作日志', BASE_URL . 'logs/', 'log'],
    'guestbook' => ['留言板', BASE_URL . 'guestbook/', 'chat'],
    'upload'    => ['文件上传', BASE_URL . 'upload/', 'upload'],
    'register'  => ['注册', BASE_URL . 'register/', 'user'],
    'help'      => ['帮助文档', BASE_URL . 'page.php', 'help'],
    'about'     => ['渗透规则', BASE_URL . 'about/', 'about'],
];
?><!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?> | <?= e(config('site.name')) ?></title>
<link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/style.css">
<script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <button class="nav-toggle" id="navToggle" aria-label="菜单">
            <span></span><span></span><span></span>
        </button>
        <a class="brand" href="<?= e(BASE_URL) ?>">
            <span class="brand-logo"><?= icon('shield', 20) ?></span>
            <span class="brand-text">
                <strong><?= e(config('site.name')) ?></strong>
                <em>v<?= e(config('site.version')) ?></em>
            </span>
        </a>
        <nav class="main-nav" id="mainNav">
            <div class="nav-side-head">
                <span class="brand-logo"><?= icon('shield', 18) ?></span>
                <strong>导航菜单</strong>
            </div>
            <?php foreach ($nav as $key => $item): ?>
                <a href="<?= e($item[1]) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                    <?= icon($item[2], 15) ?><span><?= e($item[0]) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="nav-overlay" id="navOverlay"></div>
        <div class="header-right">
            <!--<button class="icon-btn" id="themeToggle" title="切换主题" aria-label="切换主题"><?= icon('sun', 16) ?></button>-->
            <!--<span class="secure-badge"><span>SECURE</span></span>-->
            <?php if (is_login()): ?>
                <span class="user-chip"><?= icon('user', 14) ?><?= e(current_user()['username']) ?></span>
                <a class="btn btn-sm btn-ghost" href="<?= e(BASE_URL) ?>profile/">改密</a>
                <a class="btn btn-sm btn-ghost" href="<?= e(BASE_URL) ?>login/logout.php">退出</a>
            <?php else: ?>
                <a class="btn btn-sm" href="<?= e(BASE_URL) ?>login/">登录</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="stats-bar">
        <div class="container stats-inner">
            <span class="stat-item"><?= icon('user', 13) ?>总访客 <b><?= $siteStats['visitors'] ?></b></span>
            <span class="stat-item"><?= icon('users', 13) ?>用户 <b><?= $siteStats['users'] ?></b></span>
            <span class="stat-item"><?= icon('globe', 13) ?>在线 <b class="online"><?= $siteStats['online'] ?></b></span>
            <span class="stat-item"><?= icon('skull', 13) ?>拦截 <b class="blocked"><?= $siteStats['blocked'] ?></b></span>
            <span class="stat-item"><?= icon('trophy', 13) ?>成功 <b class="success"><?= $siteStats['winners'] ?></b></span>
        </div>
    </div>
</header>
<main class="site-main">
<?php if (!empty($flash)): ?>
    <div class="container"><div class="alert alert-<?= e($flash['type']) ?>"><?= icon($flash['type']==='success'?'check':'warn', 16) ?><?= e($flash['msg']) ?></div></div>
<?php endif; ?>
