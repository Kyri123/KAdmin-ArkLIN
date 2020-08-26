SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `ArkAdmin_jobs`;
CREATE TABLE `ArkAdmin_jobs` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`job` text,`parm` text,`time` int(11) DEFAULT NULL,`intervall` int(11) DEFAULT NULL,`active` int(11) DEFAULT NULL,`server` text,`name` text,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `ArkAdmin_reg_code`;
CREATE TABLE `ArkAdmin_reg_code` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`code` text,`used` int(11) DEFAULT NULL,`time` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;
INSERT INTO `ArkAdmin_reg_code` VALUES ('0000000001', '7c90c6595f7cb4d2aa0e', '0', '0');
DROP TABLE IF EXISTS `ArkAdmin_shell`;
CREATE TABLE `ArkAdmin_shell` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`server` text,`command` text,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=4102 DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `ArkAdmin_user_cookies`;
CREATE TABLE `ArkAdmin_user_cookies` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`md5id` text,`validate` text,`userid` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4;
DROP TABLE IF EXISTS `ArkAdmin_users`;
CREATE TABLE `ArkAdmin_users` (`id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,`username` text,`email` text,`password` text,`lastlogin` double DEFAULT NULL,`registerdate` double DEFAULT NULL,`rang` int(11) DEFAULT NULL,`ban` int(11) DEFAULT '0',PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
INSERT INTO `ArkAdmin_users` VALUES ('0000000001', 'Gast', 'none', 'a', '0', '0', '0', '0');
DROP TABLE IF EXISTS `ArkAdmin_players`;
create table ArkAdmin_players (total_id int auto_increment, server text null, id bigint null, SteamId bigint null, SteamName text null, CharacterName text null, Level bigint null, ExperiencePoints bigint null, TotalEngramPoints bigint null, FirstSpawned boolean null, FileCreated bigint null, FileUpdated bigint null, TribeId text null, TribeName longtext null, constraint ArkAdmin_players_pk primary key (total_id));
DROP TABLE IF EXISTS `ArkAdmin_tribe`;
create table ArkAdmin_tribe (total_id int auto_increment, server text null, Id bigint null, tribeName text null, OwnerId bigint null, FileCreated bigint null, FileUpdated bigint null, Members longtext null, constraint ArkAdmin_tribe_pk primary key (total_id));