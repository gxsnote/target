<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'about';
$page_title = '渗透规则';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('about') ?>渗透规则与授权说明</h1>
        <p>使用本平台前请仔细阅读以下规则。进入平台即视为同意并遵守全部条款。</p>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('shield') ?>授权声明</h3></div>
        <div class="card-body">
            <div class="auth-box">
                本平台（<?= e(config('site.name')) ?>）正式授权所有注册用户在遵守本页所列渗透规则的前提下，对平台提供的练习模块（包括但不限于 SQL 注入、XSS 跨站、文件上传等）进行安全渗透测试。用户在本平台练习模块内发起的注入、跨站、上传等攻击尝试，均属于平台明确授权范围，不构成非法入侵。<br><br>
                本授权仅限于本平台自身提供的靶场环境，<strong style="color:var(--red)">不包含</strong>平台所在服务器上的其他站点、同网段其他主机，以及互联网上的任何第三方系统。用户利用本平台技术或工具攻击未授权目标的，平台不承担任何责任，并保留追究权利。
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('warn') ?>渗透规则（禁止事项）</h3></div>
        <div class="card-body">
            <ul class="rule-list">
                <li>禁止对本平台及相关服务器发起 DDoS、CC、SYN Flood 等任何形式的流量攻击或压力测试。</li>
                <li>禁止篡改平台首页或其他系统页面；胜利榜黑页功能仅限写入 H 目录下的独立页面，不得覆盖 index.php 等核心文件。</li>
                <li>留言板禁止发布涉及党政机关、国家领导人、政治敏感内容，禁止辱骂、人身攻击、色情、暴力及违法信息。</li>
                <li>禁止删除、篡改他人留言、黑页或日志数据，禁止 DROP/TRUNCATE 数据表等破坏性操作。</li>
                <li>禁止上传 WebShell、木马、后门、挖矿程序等恶意文件至服务器并持久化控制；上传练习仅用于验证漏洞。</li>
                <li>禁止使用可能导致服务器宕机或资源耗尽的 payload（如大并发 sleep 注入、死循环、超大文件）。</li>
                <li>禁止尝试提权、横向移动、攻击服务器上其他站点或内网主机。</li>
                <li>禁止对平台以外的任何系统发起攻击，禁止将本平台作为跳板攻击第三方。</li>
                <li>禁止分享、传播从平台获取的任何真实敏感数据；本平台数据均为测试数据。</li>
                <li>发现高危漏洞或可逃逸出靶场环境的问题，请及时联系管理员，请勿公开扩散或利用。</li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('zap') ?>平台说明</h3></div>
        <div class="card-body">
            <div class="table-wrap">
            <table class="data-table">
                <tr><td style="width:130px">技术栈</td><td>PHP 8.0 + MySQL 5.7（PDO 预处理），表前缀 gxs_</td></tr>
                <tr><td>安全模式</td><td>固定最高安全等级（SECURE MODE），所有攻击尝试记录至战败榜</td></tr>
                <tr><td>练习模块</td><td>SQL 注入（/login/）、XSS（/guestbook/）、文件上传（/upload/）、黑页（/board/）</td></tr>
                <tr><td>密码存储</td><td>MD5（仅为靶场演示，生产环境请使用 password_hash）</td></tr>
                <tr><td>版权</td><td>高先生笔记</td></tr>
            </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('flag') ?>违规处理</h3></div>
        <div class="card-body">
            <p style="color:var(--text-dim);font-size:12.5px;line-height:1.9">
                违反上述规则者，平台有权立即封禁其 IP、清除相关数据并终止服务；
                如行为涉嫌违法，将配合相关部门追究法律责任。请在授权范围内、以学习为目的使用本平台。
            </p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
