<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// hide errors
$stime = microtime(true);
include('php/inc/config.inc.php');
include('php/class/helper.class.inc.php');
$helper = new helper();
$ckonfig = $helper->file_to_json('php/inc/custom_konfig.json', true);
$site_name = $content = null;

// Deaktiviere Error anzeige
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

//check install
if (!file_exists("app/check/subdone")) {
    header('Location: /install.php');
    exit;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar = $g_alert_bool = false;

// Connent to MYSQL
include('php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Include functions
include('php/functions/allg.func.inc.php');
include('php/functions/check.func.inc.php');
include('php/functions/modify.func.inc.php');
include('php/functions/traffic.func.inc.php');
include('php/functions/util.func.inc.php');

// include classes
include('php/class/xml_helper.class.php');
include('php/class/Template.class.inc.php');
include('php/class/alert.class.inc.php');
include('php/class/rcon.class.inc.php');
include('php/class/savefile_reader.class.inc.php');
include('php/class/user.class.inc.php');
include('php/class/steamAPI.class.inc.php');
include('php/class/server.class.inc.php');
include('php/class/jobs.class.inc.php');

// include inz
include('php/inc/template_preinz.inc.php');

// API

// Prüfe auf berechtigung der API abfrage
if(!isset($_GET["response"]) && !isset($_GET["key"]) || isset($_GET["key"]) && $_GET["key"] != $ckonfig["api_key"]) {
    echo '{"permissions": false}';
}
else {
    if($_GET["key"] == $ckonfig["api_key"]) {

    }
}


//close mysql
$mycon->close();