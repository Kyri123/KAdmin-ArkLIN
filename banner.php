<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

define("__ADIR__", __DIR__);

// hide errors
$stime = microtime(true);
include(__ADIR__.'/php/inc/config.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');
$helper = new helper();
$ckonfig = $helper->fileToJson(__ADIR__.'/php/inc/custom_konfig.json', true);
$site_name = $content = null;

// Deaktiviere Error anzeige
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

//check install
if (!file_exists(__ADIR__."/app/check/subdone")) {
    header('Location: /install.php');
    exit;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar = $g_alert_bool = false;

// Connent to MYSQL
include(__ADIR__.'/php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Include functions
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// include classes
include(__ADIR__.'/php/class/xml_helper.class.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/user.class.inc.php');
include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/jobs.class.inc.php');

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');

// API

// Prüfe auf berechtigung der API abfrage
$API_path           = __ADIR__."/php/inc/api.json";
$API_array          = $helper->fileToJson($API_path);
$API_active         = boolval($API_array["active"]);
$API_key            = $API_array["key"];

if($_GET["key"] == $API_key && $API_active) {
    $mapjson = $helper->fileToJson(__ADIR__."/app/json/panel/maps.json");

    $serv = new server($_GET["server"]);

    $tpl = new Template("", __ADIR__."/app/template/universally/default/banner.htm");
    $tpl->load();

    $state_info = $serv->status();

    $tpl->r("width"         , $_GET["width"]);
    $tpl->r("bg"            , $_GET["bg"]);
    $tpl->r("a"             , $_GET["a"]);
    $tpl->r("txt"           , $_GET["txt"]);
    $tpl->r("border"        , $_GET["border"]);

    $tpl->r("statecolor"    , $serv->statecode() == 2 ? "green" : "red");

    $tpl->r("statetxt"      , $_GET["border"]);
    $tpl->r("curr"          , $serv->statecode() == 2 ? count($state_info->aplayersarr) : 0);
    $tpl->r("ip"            , $_GET["ip"].":".$serv->cfg_read(ark_QueryPort));
    $tpl->r("max"           , $serv->cfg_read("ark_MaxPlayers"));
    $tpl->r("mapstr"        , isset($mapjson[$serv->cfg_read("serverMap")]) ? $mapjson[$serv->cfg_read("serverMap")]["name"] : $serv->cfg_read("serverMap"));
    $tpl->r("headurl"       , $state_info->ARKServers);
    $tpl->r("servername"    , $serv->cfg_read("ark_SessionName"));

    $tpl->echo();
}
else {
    echo '{"permissions": false}';
}


//close mysql
$mycon->close();