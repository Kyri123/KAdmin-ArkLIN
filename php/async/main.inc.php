<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

chdir('../../../');
include("php/functions/php70-72.inc.php");
include('php/inc/config.inc.php');
include('php/class/helper.class.inc.php');

$helper = new helper();
$ckonfig = $helper->file_to_json('php/inc/custom_konfig.json', true);

ini_set('display_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
ini_set('display_startup_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
//error_reporting(E_ALL);

// Starte Session
session_start();

include('php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Importiere Funktionen
include('php/functions/allg.func.inc.php');
include('php/functions/check.func.inc.php');
include('php/functions/modify.func.inc.php');
include('php/functions/traffic.func.inc.php');
include('php/functions/util.func.inc.php');

// Importiere Klassen
include('php/class/user.class.inc.php');
include('php/class/steamAPI.class.inc.php');
include('php/class/savefile_reader.class.inc.php');
include('php/class/Template.class.inc.php');
include('php/class/rcon.class.inc.php');
include('php/class/server.class.inc.php');
include('php/class/alert.class.inc.php');

// include inz
include('php/inc/template_preinz.inc.php');

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$steamapi = new steamapi();
$user = new userclass();
$user->setid($_SESSION["id"]);
$jhelper = new player_json_helper();
$alert = new alert();

// Allgemein SteamAPI Arrays
$steamapi_mods = (file_exists("app/json/steamapi/mods.json")) ? $helper->file_to_json("app/json/steamapi/mods.json", true) : array();
$steamapi_user = (file_exists("app/json/steamapi/user.json")) ? $helper->file_to_json("app/json/steamapi/user.json", true) : array();
?>
