<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$tpl_crontab = new Template('crontab.htm', 'app/template/system/');
$tpl_crontab->load();
$re = null;
$root_dir = $_SERVER['DOCUMENT_ROOT'];
chdir($root_dir);
$pagename = 'Crontab';
$job = $url[2];
$re = null;
$time = time();
$subpage = "php/subpage/crontab";

$timediff['shell'] = 20;
$timediff['player'] = 5;

//function
function filter_end ($str) {
    if (strpos($str, 'Yes') !== false) {
        return 'Yes';
    } else {
        return 'No';
    }
}

// Dir_creater
$dir = dirToArray('remote/arkmanager/instances/');
include("$subpage/allgemein/dir_create.inc.php");

if (!file_exists("app/data/checked")) file_put_contents("app/data/checked", "checked"); // Schreibe dass der Crontab abgerufen wurde

//Für Serverstatus
if ($job == "status") {
    include("$subpage/status/shell.inc.php");
}


//Für Spielerliste & Auswertung von Server Status
elseif ($job == "player") {
    include("$subpage/crontab/player/player_check.inc.php");
}

//jobs
elseif ($job == "jobs") {
    include("$subpage/jobs/jobs.inc.php"); // Allgemein Jobs (Auto-Update, Auto-Backup & Custom Jobs)
}


include("$subpage/allgemein/chat_fixer.inc.php");
include("$subpage/allgemein/cluster.inc.php");

$tpl_crontab->r('re', $re);
?>