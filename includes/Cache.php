<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 简单文件缓存：把数据写入缓存文件
// 安全加固：仅允许写入指定缓存目录，禁止目录穿越和任意路径写入
class Cache
{
    private $file;
    private $data;

    /** 允许写入的根目录（绝对路径） */
    private static function allowedDir(): string
    {
        return ROOT . DIRECTORY_SEPARATOR . 'preferences';
    }

    public function __construct($file = '', $data = '')
    {
        $this->file = $file;
        $this->data = $data;
    }

    public function __destruct()
    {
        if ($this->file === '' || $this->data === '') {
            return;
        }

        // 仅允许纯文件名（不含路径分隔符），防止目录穿越
        $base = basename($this->file);
        if ($base === '' || $base !== $this->file || strpos($base, '..') !== false) {
            return;
        }

        $dir = self::allowedDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // 二次校验：realpath 必须在允许目录内
        $fullPath = $dir . DIRECTORY_SEPARATOR . $base;
        $realDir = realpath($dir);
        if ($realDir === false) {
            return;
        }
        $resolved = $realDir . DIRECTORY_SEPARATOR . $base;

        // 禁止写入 PHP 等可执行脚本
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        $blocked = ['php', 'phtml', 'php3', 'php5', 'php7', 'phar', 'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'sh'];
        if (in_array($ext, $blocked, true)) {
            return;
        }

        @file_put_contents($resolved, $this->data);
    }
}
