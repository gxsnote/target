<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'login';
$page_title = '注入练习';
$loginError = isset($_GET['err']) ? ($_GET['err'] === '2' ? '登录失败次数过多，请 15 分钟后再试。' : '登录失败：用户名或密码错误，或输入被安全规则拦截。') : '';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('term') ?>注入练习区</h1>
        <p>登录认证采用 PDO 预处理 + 攻击特征拦截。尝试构造 SQL 注入，观察防护效果。</p>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('lock') ?>系统登录</h3><span class="extra">SECURE MODE</span></div>
        <div class="login-panel">
            <div class="login-form-side">
                <h3 style="color:var(--green);margin-bottom:6px">认证入口</h3>
                <p class="sub">含注入特征的请求将被直接拦截并记录到战败榜。</p>
                <?php if ($loginError): ?>
                    <div class="alert alert-error"><?= icon('warn', 16) ?><?= e($loginError) ?></div>
                <?php endif; ?>
                <?php if (is_login()): ?>
                    <div class="alert alert-success"><?= icon('check', 16) ?>已登录：<?= e(current_user()['username']) ?>，<a href="<?= BASE_URL ?>board/">前往胜利榜</a> · <a href="<?= BASE_URL ?>login/logout.php">退出</a></div>
                <?php else: ?>
                <form method="post" action="<?= BASE_URL ?>login/check.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>用户名</label>
                        <input class="form-control" type="text" name="username" placeholder="请输入用户名" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input class="form-control" type="password" name="password" placeholder="请输入密码" autocomplete="off">
                    </div>
                    <button class="btn btn-block" type="submit"><?= icon('unlock', 16) ?>登 录</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="login-info-side">
                <h3 style="color:var(--green);margin-bottom:6px"><?= icon('code') ?>防护代码</h3>
                <div class="code-box"><span class="c-com">// PDO 预处理，参数化查询，不可注入</span>
<span class="c-key">$sql</span> = <span class="c-str">"SELECT * FROM gxs_users
        WHERE username = ? AND password = ?"</span>;
<span class="c-key">$stmt</span> = <span class="c-key">$pdo</span>->prepare(<span class="c-key">$sql</span>);
<span class="c-key">$stmt</span>->execute([<span class="c-key">$username</span>, md5(<span class="c-key">$password</span>)]);

<span class="c-com">// 攻击特征实时拦截</span>
<span class="c-key">if</span> (preg_match(<span class="c-str">'/union\s+select|--|\bor\b|.../'</span>, <span class="c-key">$username</span>)) {
    <span class="c-key">waf_block</span>(<span class="c-str">'sql'</span>, <span class="c-key">$username</span>);
}</div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
