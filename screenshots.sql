/*
Navicat MySQL Data Transfer

Source Server         : tbspace.de
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : screenshots

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-02-18 22:10:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for screenshots
-- ----------------------------
DROP TABLE IF EXISTS `screenshots`;
CREATE TABLE `screenshots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(100) NOT NULL,
  `filename` text DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `hidden` tinyint(1) unsigned DEFAULT 0,
  `tags` text DEFAULT NULL,
  `fulltext` longtext DEFAULT NULL,
  `ocr` tinyint(1) unsigned DEFAULT 0,
  PRIMARY KEY (`id`,`url`),
  UNIQUE KEY `name` (`url`) USING BTREE,
  FULLTEXT KEY `Tags` (`tags`),
  FULLTEXT KEY `fulltext` (`fulltext`)
) ENGINE=MyISAM AUTO_INCREMENT=20699 DEFAULT CHARSET=utf8mb4;
