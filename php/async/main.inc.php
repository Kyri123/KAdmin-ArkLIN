<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

define("__ADIR__", __DIR__."/../..");
chdir("../../");
$ROOT = str_replace($_SERVER["DOCUMENT_ROOT"], null, __DIR__);
$ROOT = str_replace("/php/async", null, $ROOT);

include(__ADIR__.'/php/inc/config.inc.php');
include(__ADIR__.'/php/class/KUtil.class.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');

$helper = new helper();
$ckonfig = $helper->fileToJson(__ADIR__.'/php/inc/custom_konfig.json', true);

$KUTIL->replacePathFrom = [
    __ADIR__."/remote/serv/",
    __ADIR__."/remote/arkmanager/",
    __ADIR__."/remote/steamcmd/"
];
$KUTIL->replacePathTo   = [
    $ckonfig["servlocdir"],
    $ckonfig["arklocdir"],
    $ckonfig["steamcmddir"]
];


$all = $helper->fileToJson(__ADIR__."/app/json/serverinfo/all.json");
$D_PERM_ARRAY = $helper->fileToJson(__ADIR__."/app/json/user/permissions.tpl.json");
$server = $all["cfgs_only_name"];
foreach ($server as $item) {
    $perm_file = $KUTIL->fileGetContents(__ADIR__."/app/json/user/permissions_servers.tpl.json");
    $perm_file = str_replace("{cfg}", $item, $perm_file);
    $default = $helper->stringToJson($perm_file);
    $D_PERM_ARRAY["server"] += $default;
}

ini_set('display_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
ini_set('display_startup_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
if(isset($ckonfig["show_err"])) error_reporting(E_ALL);

// Starte Session
session_start();

include(__ADIR__.'/php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Importiere Funktionen
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// Importiere Klassen
include(__ADIR__.'/php/class/user.class.inc.php');

$session_user = new userclass();
if (isset($_SESSION["id"])) {
    $session_user->setid($_SESSION["id"]);
}

include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/class/jobs.class.inc.php');

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$steamapi = new steamapi();
$user = new userclass();
$user->setid($_SESSION["id"]);
$jhelper = new player_json_helper();
$alert = new alert();
$jobs = new jobs();
$permissions = $session_user->permissions;

// Allgemein SteamAPI Arrays
$steamapi_mods = (@file_exists(__ADIR__."/app/json/steamapi/mods.json")) ? $helper->fileToJson(__ADIR__."/app/json/steamapi/mods.json", true) : array();
$steamapi_user = (@file_exists(__ADIR__."/app/json/steamapi/user.json")) ? $helper->fileToJson(__ADIR__."/app/json/steamapi/user.json", true) : array();

