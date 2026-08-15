<?php
/**
 * 网络安全攻防演练靶场 v1.1
 * 作者：高先生笔记
 * 网站：www.gxsnote.cn
 * 联系方式：QQ 67031002
 * 最后更新：2026-08-15
 */

// 站点与数据库配置
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => '1',
        'user'    => '1',
        'pass'    => 'Hs5yJSwK8CRW4Szn',
        'charset' => 'utf8mb4',
        'prefix'  => 'gxs_',
    ],
    'site' => [
        'name'       => '网络安全攻防演练靶场',
        'version'    => '1.1',
        'company'    => '安全攻防实验室',
        'h_dir'      => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'H',
        'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads',
    ],
];
