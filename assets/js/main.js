// 靶场前端交互
document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;

    // 主题切换
    var themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        function paintThemeIcon() {
            var dark = root.getAttribute('data-theme') === 'dark';
            // 暗色时显示太阳（点击切到浅色），浅色时显示月亮
            themeBtn.innerHTML = dark
                ? '<svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>'
                : '<svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
        }
        paintThemeIcon();
        themeBtn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            paintThemeIcon();
        });
    }

    // 侧边栏
    var navToggle = document.getElementById('navToggle');
    var overlay = document.getElementById('navOverlay');
    var nav = document.getElementById('mainNav');
    function closeNav() { document.body.classList.remove('nav-open'); }
    if (navToggle) {
        navToggle.addEventListener('click', function () {
            document.body.classList.toggle('nav-open');
        });
    }
    if (overlay) overlay.addEventListener('click', closeNav);
    if (nav) {
        nav.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeNav); });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeNav();
    });

    // 留言长度校验
    var guestForm = document.getElementById('guest-form');
    if (guestForm) {
        guestForm.addEventListener('submit', function (e) {
            var content = guestForm.querySelector('[name=content]').value.trim();
            if (content.length < 2) {
                e.preventDefault();
                alert('留言内容不能少于 2 个字符');
            }
        });
    }

    // 黑页代码编辑器：Tab 缩进
    var deface = document.getElementById('deface-code');
    if (deface) {
        deface.addEventListener('keydown', function (e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                var s = this.selectionStart, en = this.selectionEnd;
                this.value = this.value.substring(0, s) + '    ' + this.value.substring(en);
                this.selectionStart = this.selectionEnd = s + 4;
            }
        });
    }

    // 首页漏洞弹窗（每次打开都弹出）
    var vulnModal = document.getElementById('vulnModal');
    var vulnClose = document.getElementById('vulnClose');
    if (vulnModal) {
        vulnModal.classList.add('show');
    }
    if (vulnClose) {
        vulnClose.addEventListener('click', function () {
            vulnModal.classList.remove('show');
        });
    }
    if (vulnModal) {
        vulnModal.addEventListener('click', function (e) {
            if (e.target === vulnModal) vulnModal.classList.remove('show');
        });
    }
});

