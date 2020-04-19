<?php

#Mysql
include('pconfig.inc.php');

#allgmeine
$sitename = "ArkAdmin";
$sitename_short = "AA";
$version = '0.6.1';
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
    "Installieren",
    "Starten",
    "Update",
    "Neustarten",
    "Stoppen",
    "Backup",
    "Checkupdate",
    "Checkmodupdate",
    "Installmods",
    "Uninstallmods",
    "Speichern",
    "Status"
);
$clustertype = array(
  "Slave",
  "Master"
);



// TODO: remove

$txt_alert = "Achtung: Dies ist eine Frühe version des Cluster Systemes. Es kann zu Fehlern & Problemen kommen da es tiefer in die Verwaltung eingreifen wird.
Sollten euch Fehler auffallen Meldet diese bitte im ArkForum.de oder bei Github! (Links beide links)";
?>