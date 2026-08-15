<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// WAF 拦截与日志统一入口

/**
 * 触发拦截：记录战败日志并显示拦截页
 */
function waf_block(string $attackType, string $payload, string $action = 'request'): void
{
    log_action($action, $attackType, $payload, true);
    http_response_code(403);
    $typeName = attack_type_name($attackType);
    $ip = get_ip();
    $level = level_meta(current_level());
    $title = '请求已被拦截';
    require ROOT . '/includes/header.php';
    echo '<div class="blocked-wrap">
        <div class="blocked-card">
            <div class="blocked-icon">' . icon('shield', 48) . '</div>
            <h1>请求已被安全防护拦截</h1>
            <p class="blocked-sub">检测到疑似' . e($typeName) . '攻击行为，本次操作已记录至战败榜。</p>
            <div class="blocked-meta">
                <div><span>拦截类型</span><strong>' . e($typeName) . '</strong></div>
                <div><span>来源IP</span><strong>' . e($ip) . '</strong></div>
                <div><span>安全等级</span><strong>' . e($level['name']) . '</strong></div>
                <div><span>拦截时间</span><strong>' . date('Y-m-d H:i:s') . '</strong></div>
            </div>
            <div class="blocked-actions">
                <a class="btn btn-primary" href="' . e(BASE_URL) . '">返回首页</a>
                <a class="btn btn-ghost" href="' . e(BASE_URL) . 'graveyard/">查看战败榜</a>
            </div>
        </div>
    </div>';
    require ROOT . '/includes/footer.php';
    exit;
}
