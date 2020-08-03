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
$version = '0.11.0a';
$ip = $_SERVER['SERVER_ADDR'];

#Webserver
$webserver['url'] = 'http://data.chiraya.de/';
$webserver['changelog'] = $webserver['url'].'changelog.json';

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
    "status",
    "list-mods",
    "restore",
    "getpid",
    "cancelshutdown"
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
    "{::lang::php::cfg::action::_11}",
    "{::lang::php::cfg::action::_12}",
    "{::lang::php::cfg::action::_13}",
    "{::lang::php::cfg::action::_14}",
    "{::lang::php::cfg::action::_15}",
    "{::lang::php::cfg::action::_16}",
    "{::lang::php::cfg::action::_17}",
    "{::lang::php::cfg::action::_18}",
    "{::lang::php::cfg::action::_19}",
    "{::lang::php::cfg::action::_20}"
);
$clustertype = array(
  "Slave",
  "Master"
);
?>