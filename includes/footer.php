</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col">
            <div class="footer-brand">
                <span class="brand-logo"><?= icon('shield', 16) ?></span>
                <strong><?= e(config('site.name')) ?></strong>
            </div>
            <p>面向授权安全测试与教学的攻防演练平台，覆盖 SQL 注入、XSS、文件上传等常见 Web 漏洞场景。所有攻击行为均被记录。</p>
        </div>
        <div class="footer-col">
            <h4>导航</h4>
            <a href="<?= e(BASE_URL) ?>login/">注入练习</a>
            <a href="<?= e(BASE_URL) ?>board/">胜利榜</a>
            <a href="<?= e(BASE_URL) ?>graveyard/">战败榜</a>
            <a href="<?= e(BASE_URL) ?>logs/">操作日志</a>
        </div>
        <div class="footer-col">
            <h4>模块</h4>
            <a href="<?= e(BASE_URL) ?>guestbook/">留言板 XSS</a>
            <a href="<?= e(BASE_URL) ?>upload/">文件上传</a>
            <a href="<?= e(BASE_URL) ?>about/">渗透规则</a>
        </div>
        <div class="footer-col">
            <h4>授权说明</h4>
            <p>本平台已授权所有注册用户在遵守渗透规则的前提下进行安全测试。</p>
            <p style="color: #ff4444; font-weight: bold; margin-top: 6px;">严禁篡改、删除、破坏任何非 board 目录下的文件或页面。违规者永久封禁并保留追究法律责任的权利。</p>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>Copyright &copy; <?= date('Y') ?> 高先生笔记 保留所有权利</span>
            <span class="beian">
                <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener">蒙ICP备2022001900号</a>
                <span class="sep">|</span>
                <a href="http://www.beian.gov.cn/" target="_blank" rel="noopener">蒙公网安备15060202000456号</a>
            </span>
        </div>
    </div>
    <div style="background: #1a0000; border-top: 2px solid #ff4444; padding: 8px 0; text-align: center; font-size: 13px; color: #ff6666;">
        <div class="container">
            <strong>严正声明：</strong>本平台仅允许在 board 目录上传黑页以示胜利。禁止篡改任何其他页面或文件，本站已进行公安联网违规者将追究法律责任。
        </div>
    </div>
</footer>
<script src="<?= e(BASE_URL) ?>assets/js/main.js"></script>
</body>
</html>