<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

define("__ADIR__", __DIR__."/../..");
chdir("../../");
$ROOT = str_replace($_SERVER["DOCUMENT_ROOT"], null, __DIR__);
$ROOT = str_replace("/php/async", null, $ROOT);

include(__ADIR__.'/php/inc/config.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');

$helper = new helper();
$ckonfig = $helper->file_to_json(__ADIR__.'/php/inc/custom_konfig.json', true);

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
$permissions = $user->permissions;

// Allgemein SteamAPI Arrays
$steamapi_mods = (file_exists(__ADIR__."/app/json/steamapi/mods.json")) ? $helper->file_to_json(__ADIR__."/app/json/steamapi/mods.json", true) : array();
$steamapi_user = (file_exists(__ADIR__."/app/json/steamapi/user.json")) ? $helper->file_to_json(__ADIR__."/app/json/steamapi/user.json", true) : array();

