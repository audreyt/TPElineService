-- --------------------------------------------------------
-- 主機:                           127.0.0.1
-- 服務器版本:                        10.1.13-MariaDB - mariadb.org binary distribution
-- 服務器操作系統:                      Win32
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 導出 yourdbname 的資料庫結構
CREATE DATABASE IF NOT EXISTS `yourdbname` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `yourdbname`;


-- 導出  表 yourdbname.dataset_to_display 結構
DROP TABLE IF EXISTS `dataset_to_display`;
CREATE TABLE IF NOT EXISTS `dataset_to_display` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '資料集編號',
  `area_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `info_to_show` text COLLATE utf8_unicode_ci COMMENT 'json format',
  `created_at` datetime NOT NULL,
  `changed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='rawdata from crawler';

-- 資料導出被取消選擇。


-- 導出  表 yourdbname.dataset_to_push 結構
DROP TABLE IF EXISTS `dataset_to_push`;
CREATE TABLE IF NOT EXISTS `dataset_to_push` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '資料集編號',
  `area_code` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `info_to_show` text COLLATE utf8_unicode_ci COMMENT 'json format',
  `created_at` datetime NOT NULL,
  `changed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='rawdata from crawler';

-- 資料導出被取消選擇。


-- 導出  表 yourdbname.line_service_token 結構
DROP TABLE IF EXISTS `line_service_token`;
CREATE TABLE IF NOT EXISTS `line_service_token` (
  `access_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refresh_token` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `expired` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 資料導出被取消選擇。


-- 導出  表 yourdbname.member 結構
DROP TABLE IF EXISTS `member`;
CREATE TABLE IF NOT EXISTS `member` (
  `mid` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'pk',
  `display_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `picture_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status_message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='使用者與其訂閱容器';

-- 資料導出被取消選擇。


-- 導出  表 yourdbname.message 結構
DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `msg_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'pk',
  `mid` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'from who',
  `payload` text COLLATE utf8_unicode_ci COMMENT 'message''s content',
  `done` tinyint(3) NOT NULL,
  `send_at` datetime NOT NULL COMMENT 'message send time',
  `created_at` datetime NOT NULL,
  `raw_data` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='lineBC post messages';

-- 資料導出被取消選擇。


-- 導出  表 yourdbname.subscription_container 結構
DROP TABLE IF EXISTS `subscription_container`;
CREATE TABLE IF NOT EXISTS `subscription_container` (
  `mid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `dataset_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `detail` text COLLATE utf8_unicode_ci,
  `is_pushed` tinyint(1) NOT NULL DEFAULT '0',
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_pushed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='資料集與使用者之間的訂閱容器關係';

-- 資料導出被取消選擇。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
