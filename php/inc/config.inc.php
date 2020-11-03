<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Mysql
if(file_exists(__ADIR__.'/php/inc/pconfig.inc.php')) include(__ADIR__.'/php/inc/pconfig.inc.php');

// allgmeine
$sitename = "ArkAdmin2";
$version = '2.0.1';
$ip = $_SERVER['SERVER_ADDR'];
$maxpanel_server = 12;
$buildid = 201.000;

// Webserver
$webserver['url'] = 'https://data.chiraya.de/';
$webserver['changelog'] = $webserver['url'].'changelog.json';
$webserver['sendin'] = $webserver['url'].'sendin.php';
$webserver['config'] = json_decode(file_get_contents(__ADIR__."/arkadmin_server/config/server.json") ,true);
$webserver['config']["port"] = (isset($webserver['config']["port"])) ? $webserver['config']["port"] : 30000;

// Webserver Platzhalter

// Actions
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

// Cluster Typen
$clustertype = array(
  "Slave",
  "Master"
);

$ignore_perm = array(
    "force_update",
    "force_restart",
    "gus",
    "game",
    "engine"
);

$head_img = array(
    "min" => 0,
    "max" => 6,
    "img" => array(
        "/app/dist/img/backgrounds/bg.jpg",
        "/app/dist/img/backgrounds/side.jpg",
        "/app/dist/img/backgrounds/4.jpg",
        "/app/dist/img/backgrounds/5.jpg",
        "/app/dist/img/backgrounds/6.jpg",
        "/app/dist/img/backgrounds/7.jpg",
        "/app/dist/img/backgrounds/8.jpg"
    )
);