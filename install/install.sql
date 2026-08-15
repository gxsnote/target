-- ============================================================
-- 网络安全攻防演练靶场 v1.1 数据库安装脚本
-- 作者：高先生笔记
-- 网站：www.gxsnote.cn
-- 联系方式：QQ 67031002
-- 最后更新：2026-08-15
--
-- 使用方法：
--   1. 先在 config/config.php 中修改数据库连接信息
--   2. 将下方 gxs_ 前缀替换为你在 config.php 中设置的前缀
--   3. 在 phpMyAdmin 或 MySQL 命令行中执行本脚本
--   4. 默认管理员：admin / zhuanjiao@123.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- 用户表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `gxs_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(32) NOT NULL,
  `password` VARCHAR(32) NOT NULL COMMENT 'MD5存储',
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 操作/攻击日志表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `gxs_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `action` VARCHAR(100) NOT NULL DEFAULT '',
  `attack_type` VARCHAR(20) NOT NULL DEFAULT 'normal',
  `payload` TEXT,
  `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  `level` VARCHAR(10) NOT NULL DEFAULT 'high',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip`),
  KEY `idx_blocked` (`is_blocked`),
  KEY `idx_type` (`attack_type`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 留言板表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `gxs_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(80) NOT NULL,
  `content` VARCHAR(200) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 访客统计表
-- ----------------------------
CREATE TABLE IF NOT EXISTS `gxs_visitors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `session_id` VARCHAR(64) NOT NULL,
  `first_seen` DATETIME NOT NULL,
  `last_active` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_session` (`session_id`),
  KEY `idx_last` (`last_active`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- 默认管理员账号：admin / zhuanjiao@123.
-- MD5(zhuanjiao@123.) = f5b3ef04903402cf05297b89bcbce9da
-- ----------------------------
INSERT IGNORE INTO `gxs_users` (`username`, `password`, `role`, `created_at`)
VALUES ('admin', 'f5b3ef04903402cf05297b89bcbce9da', 'admin', NOW());

-- ----------------------------
-- 默认欢迎留言
-- ----------------------------
INSERT IGNORE INTO `gxs_messages` (`id`, `nickname`, `content`, `created_at`)
VALUES (1, '高先生笔记', '欢迎来到网络安全攻防演练靶场 v1.1，祝各位玩得开心！', NOW());

SET FOREIGN_KEY_CHECKS = 1;
