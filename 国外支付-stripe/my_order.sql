/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : yltv2_t_bjxcsy_c

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2021-01-26 14:51:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for my_order
-- ----------------------------
DROP TABLE IF EXISTS `my_order`;
CREATE TABLE `my_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dd` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `createtime` int(11) NOT NULL,
  `kcid` int(11) DEFAULT NULL COMMENT '课程id',
  `type` int(11) DEFAULT NULL COMMENT '1 会员，0 课程',
  `huiyuantime` varchar(255) DEFAULT NULL COMMENT '会员时长',
  `status` int(11) DEFAULT '0' COMMENT '[''未支付'', ''待支付'', ''已支付'']',
  `payment_order_id` varchar(255) DEFAULT NULL,
  `payment_order_data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
