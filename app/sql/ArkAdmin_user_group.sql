create table `ArkAdmin_user_group` (`id` int unsigned zerofill auto_increment primary key, `name` text null, `editform` int null, `time` bigint null, `permissions` longtext null, `canadd` longtext null);
INSERT INTO `ArkAdmin_user_group` (`id`, `name`, `editform`, `time`, `permissions`, `canadd`) VALUES (1, 'Superadmin', 1, 0, '{"all":{"is_admin":1}}', '[1]');