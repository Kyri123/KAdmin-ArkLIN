DROP TABLE IF EXISTS `ArkAdmin_user_cookies`;
CREATE TABLE `ArkAdmin_user_cookies` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`md5id` text,`validate` text,`userid` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4;