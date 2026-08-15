<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

require __DIR__ . '/../includes/init.php';
$active = 'upload';
$page_title = '文件上传';
$flash = null;
$uploaded = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    csrf_check();
    $file = $_FILES['file'];
    $originName = $file['name'];

    // 10 秒上传间隔限制
    $now = time();
    $lastUpload = (int)($_SESSION['last_upload'] ?? 0);
    if ($now - $lastUpload < 10) {
        $flash = ['type' => 'error', 'msg' => '上传太频繁，请 ' . (10 - ($now - $lastUpload)) . ' 秒后再试。'];
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $flash = ['type' => 'error', 'msg' => '上传失败，错误码：' . $file['error']];
    } else {
        // 固定最高安全等级：白名单后缀 + MIME + getimagesize + 重命名
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'pdf', 'zip'];
        $ext = strtolower(pathinfo($originName, PATHINFO_EXTENSION));

        // 拦截危险后缀特征
        if (preg_match('/\.(php|phtml|php3|php5|phar|asp|aspx|jsp|cer|asax?)(\.|$)/i', $originName)) {
            waf_block('upload', $originName, 'upload');
        }

        if (!in_array($ext, $allowed, true)) {
            $flash = ['type' => 'error', 'msg' => '仅允许 ' . implode(' / ', $allowed) . ' 类型文件。'];
        } else {
            // MIME 检测：优先 finfo，其次 mime_content_type，都没有则用客户端提供的类型
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            } elseif (function_exists('mime_content_type')) {
                $mime = mime_content_type($file['tmp_name']);
            } else {
                $mime = $file['type'] ?? '';
            }
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'text/plain', 'application/pdf', 'application/zip'];
            if (!in_array($mime, $allowedMime, true)) {
                $flash = ['type' => 'error', 'msg' => '文件 MIME 类型不被允许：' . $mime];
            } else {
                // 图片类型必须通过 getimagesize 校验
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $imgInfo = @getimagesize($file['tmp_name']);
                    if ($imgInfo === false) {
                        $flash = ['type' => 'error', 'msg' => '图片文件内容校验失败，疑似伪造图片。'];
                    }
                }
                if (!$flash) {
                    $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = config('site.upload_dir') . DIRECTORY_SEPARATOR . $newName;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $uploaded = ['name' => $newName, 'origin' => $originName, 'size' => $file['size'], 'mime' => $mime];
                        $_SESSION['last_upload'] = time();
                        log_action('upload_ok', 'upload', $originName . ' -> ' . $newName, false);
                        $flash = ['type' => 'success', 'msg' => '上传成功：' . $newName];
                    } else {
                        $flash = ['type' => 'error', 'msg' => '移动文件失败，请检查 uploads 目录权限。'];
                    }
                }
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <h1><?= icon('upload') ?>文件上传</h1>
        <p>白名单后缀 + MIME 校验 + getimagesize 图片校验 + 随机重命名。尝试上传脚本文件将被拦截。</p>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-head"><h3><?= icon('upload') ?>上传文件</h3></div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>选择文件</label>
                        <input class="form-control" type="file" name="file" required>
                        <div class="form-hint">允许：jpg / png / gif / webp / txt / pdf / zip</div>
                    </div>
                    <button class="btn" type="submit"><?= icon('upload', 16) ?>上传</button>
                </form>
                <?php if ($uploaded): ?>
                <div style="margin-top:16px;padding:12px;background:#000;border:1px solid var(--border)">
                    <div style="color:var(--green);font-size:12px;margin-bottom:6px">// 上传结果</div>
                    <div style="font-size:12px;color:var(--text-dim)">原始名：<?= e($uploaded['origin']) ?></div>
                    <div style="font-size:12px;color:var(--text-dim)">保存名：<?= e($uploaded['name']) ?></div>
                    <div style="font-size:12px;color:var(--text-dim)">访问路径：<span style="color:var(--green)"><?= e(BASE_URL) ?>uploads/<?= e($uploaded['name']) ?></span></div>
                    <div style="font-size:12px;color:var(--text-dim)">MIME：<?= e($uploaded['mime']) ?></div>
                    <div style="font-size:12px;color:var(--text-dim)">大小：<?= number_format($uploaded['size']) ?> bytes</div>
                    <?php if (in_array(pathinfo($uploaded['name'], PATHINFO_EXTENSION), ['jpg','jpeg','png','gif','webp'])): ?>
                    <img src="<?= BASE_URL ?>uploads/<?= e($uploaded['name']) ?>" style="max-width:100%;margin-top:10px;border:1px solid var(--border)">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h3><?= icon('code') ?>防护代码</h3></div>
            <div class="card-body">
                <div class="code-box"><span class="c-com">// 1. 白名单后缀</span>
<span class="c-key">$allowed</span> = [<span class="c-str">'jpg','png','gif','txt','pdf'</span>];
<span class="c-com">// 2. MIME 校验</span>
<span class="c-key">$mime</span> = mime_content_type(<span class="c-key">$tmp</span>);
<span class="c-com">// 3. 图片内容校验</span>
getimagesize(<span class="c-key">$tmp</span>);
<span class="c-com">// 4. 随机重命名，不可预测路径</span>
<span class="c-key">$name</span> = bin2hex(random_bytes(4)).<span class="c-str">'.jpg'</span>;</div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
