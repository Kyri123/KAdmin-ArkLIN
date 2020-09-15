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
$version = '1.1.0';
$ip = $_SERVER['SERVER_ADDR'];
$maxpanel_server = 10;

#Webserver
$webserver['url'] = 'https://data.chiraya.de/';
$webserver['changelog'] = $webserver['url'].'changelog.json';
$webserver['config'] = json_decode(file_get_contents("arkadmin_server/config/server.json") ,true);
$webserver['config']["port"] = (isset($webserver['config']["port"])) ? $webserver['config']["port"] : 30000;

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

$clustertype = array(
  "Slave",
  "Master"
);
