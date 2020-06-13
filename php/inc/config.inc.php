<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

#Mysql
include('pconfig.inc.php');

#allgmeine
$sitename = "ArkAdmin";
$sitename_short = "AA";
$version = '0.7.1';
$ip = $_SERVER['SERVER_ADDR'];

#Webserver
$webserver['url'] = 'http://data.chiraya.de/';
$webserver['changelog'] = $webserver['url'].'changelog.json';
$webserver['version'] = $webserver['url'].'version.json';

#Actions
$action_opt = array(
    "install",
    "start",
    "update",
    "restart",
    "stop",
    "backup",
    "checkupdate",
    "checkmodupdate",
    "installmods",
    "uninstallmods",
    "saveworld",
    "status"
);
$action_str = array(
    "{::lang::php::cfg::action::_0}",
    "{::lang::php::cfg::action::_1}",
    "{::lang::php::cfg::action::_2}",
    "{::lang::php::cfg::action::_3}",
    "{::lang::php::cfg::action::_4}",
    "{::lang::php::cfg::action::_5}",
    "{::lang::php::cfg::action::_6}",
    "{::lang::php::cfg::action::_7}",
    "{::lang::php::cfg::action::_8}",
    "{::lang::php::cfg::action::_9}",
    "{::lang::php::cfg::action::_10}",
    "{::lang::php::cfg::action::_11}"
);
$clustertype = array(
  "Slave",
  "Master"
);
?>