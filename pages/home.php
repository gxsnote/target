<div class="container">
    <div class="page-head">
        <h1><?= icon('help') ?>帮助文档</h1>
        <p>本站是一个合法的网络安全攻防演练靶场，所有练习均在本站服务器内进行。请先阅读<a href="<?= BASE_URL ?>about/">渗透规则</a>。</p>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('term') ?>功能模块</h3></div>
        <div class="card-body">
            <div class="code-box"><span class="c-com">// 注入练习（SQL 注入登录绕过）</span>
<a href="<?= BASE_URL ?>login/">/login/</a>

<span class="c-com">// 胜利榜（成功攻陷后写黑页留名，仅管理员可写）</span>
<a href="<?= BASE_URL ?>board/">/board/</a>

<span class="c-com">// 战败榜（按攻击者 IP 聚合的攻击记录）</span>
<a href="<?= BASE_URL ?>graveyard/">/graveyard/</a>

<span class="c-com">// 操作日志（所有请求与拦截记录）</span>
<a href="<?= BASE_URL ?>logs/">/logs/</a>

<span class="c-com">// 留言板（输入输出练习）</span>
<a href="<?= BASE_URL ?>guestbook/">/guestbook/</a>

<span class="c-com">// 文件上传（白名单 + MIME + 图片校验）</span>
<a href="<?= BASE_URL ?>upload/">/upload/</a>

<span class="c-com">// 注册账号 / 修改密码</span>
<a href="<?= BASE_URL ?>register/">/register/</a> &nbsp; <a href="<?= BASE_URL ?>profile/">/profile/</a></div>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head"><h3><?= icon('shield') ?>防护机制</h3></div>
            <div class="card-body">
                <ul style="margin:0;padding-left:18px;font-size:12.5px;line-height:2;color:var(--text-dim)">
                    <li>固定最高安全等级，不可切换</li>
                    <li>SQL 关键字与注释符 WAF 拦截</li>
                    <li>XSS 脚本/事件特征拦截</li>
                    <li>上传白名单后缀 + MIME + getimagesize</li>
                    <li>危险后缀（php/phtml/asp/jsp 等）直接拦截</li>
                    <li>上传文件随机重命名，不可预测路径</li>
                    <li>输出统一 htmlspecialchars(ENT_QUOTES) 转义</li>
                    <li>所有攻击尝试记录 IP 与 payload</li>
                </ul>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><h3><?= icon('trophy') ?>攻陷目标</h3></div>
            <div class="card-body">
                <ul style="margin:0;padding-left:18px;font-size:12.5px;line-height:2;color:var(--text-dim)">
                    <li>发现并利用本站隐藏的漏洞</li>
                    <li>获取管理员权限或在服务器执行命令</li>
                    <li>登录管理员账号后可在胜利榜写黑页留名</li>
                    <li>黑页仅保存在 H 目录，不得篡改其他文件</li>
                    <li>发现漏洞请遵守渗透规则，勿影响他人</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h3><?= icon('code') ?>关于本页</h3></div>
        <div class="card-body">
            <p style="color:var(--text-dim);font-size:12.5px;line-height:1.9;margin:0">
                帮助文档通过 <code>page.php?p=文档名</code> 加载，例如 <code>page.php?p=home</code>。
                本站保留了若干未公开漏洞，在遵守<a href="<?= BASE_URL ?>about/">渗透规则</a>的前提下自行挖掘。
            </p>
        </div>
    </div>
</div>
