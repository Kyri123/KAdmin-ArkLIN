SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `ArkAdmin_reg_code`;
CREATE TABLE `ArkAdmin_reg_code` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`code` text,`used` int(11) DEFAULT NULL,`time` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
INSERT INTO `ArkAdmin_reg_code` VALUES ('0000000001', '7c90c6595f7cb4d2aa0e', '1', '0');
DROP TABLE IF EXISTS `ArkAdmin_users`;
CREATE TABLE `ArkAdmin_users` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`username` text,`email` text,`password` text,`lastlogin` double DEFAULT NULL,`registerdate` double DEFAULT NULL,`rang` int(11) DEFAULT NULL,`ban` int(11) DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
INSERT INTO `ArkAdmin_users` VALUES ('0000000001', 'Gast', 'none', 'a', '0', '0', '0', '0');