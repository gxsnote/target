<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// IP 地域封锁：仅允许中国大陆 IP 访问
// 使用 ip2region 离线库，不依赖外部服务

function ip_guard_check(): void
{
    // 封锁场景只信任直连 IP，不采信可伪造的代理头
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // 放行内网 / 本地 / 保留地址
    if ($ip === 'unknown' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return;
    }

    // IPv6 暂放行（离线库主要覆盖 IPv4）
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return;
    }

    $xdb = ROOT . '/includes/ip2region/ip2region.xdb';
    if (!is_file($xdb)) {
        return; // 数据库缺失不拦截，避免误伤
    }

    try {
        require_once ROOT . '/includes/ip2region/XdbSearcher.php';
        $searcher = XdbSearcher::newWithFileOnly($xdb);
        $region = $searcher->search($ip);
        // 格式：国家|区域|省份|城市|ISP
        if ($region !== '' && strpos($region, '中国') !== 0) {
            http_response_code(403);
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 访问被拒绝</title>
<style>body{background:#0a0a0a;color:#00ff41;font-family:Consolas,Monaco,monospace;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}
.box{text-align:center;padding:40px;border:1px solid #00ff41;max-width:500px}
h1{font-size:20px;margin:0 0 16px}p{font-size:13px;color:#888;line-height:1.8;margin:0}</style></head>
<body><div class="box"><h1>[ 403 FORBIDDEN ]</h1><p>本站仅允许中国大陆 IP 访问。<br>Access restricted to mainland China IPs only.</p></div></body></html>';
            exit;
        }
    } catch (Throwable $e) {
        // 查询异常不拦截
    }
}
