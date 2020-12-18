<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

$tpl_crontab        = new Template('crontab.htm', __ADIR__.'/app/template/core/system/');
$tpl_crontab->load();
$re                 = null;
$pagename           = 'Crontab';
$job                = isset($url[2]) ? $url[2] : null;
$re                 = null;
$time               = time();
$subpage            = __ADIR__."/php/subpage/crontab";

$timediff['shell']  = 20;
$timediff['player'] = 8;
$KUTIL->filePutContents(__ADIR__."/app/data/checked", "checked");

// Dir_creater
$dir = dirToArray(__ADIR__.'/remote/arkmanager/instances/');
include("$subpage/allgemein/dir_create.inc.php");

// Für Spielerliste
if ($job == "player") {
    include("$subpage/player/player_check.inc.php");
}

// Auswertung von Server Status & Chatlog
elseif ($job == "status") {
    include("$subpage/status/chatlog.inc.php");
    include("$subpage/status/status.inc.php");
}

// Syncronisiere Clusterinformationen
include("$subpage/allgemein/cluster.inc.php");

// Lade alle Dateien aus der SteamAPI
include("$subpage/allgemein/steamapi.inc.php");

$tpl_crontab->r('re', $re);