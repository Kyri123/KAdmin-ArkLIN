<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Setzte Error auf 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// werte URL aus
$url = $_SERVER["REQUEST_URI"];
$url = explode("/", $url);

// Erstelle bestimmt Dateien
if(!file_exists("app/check")) mkdir("app/check");
if(!file_exists("app/cache")) mkdir("app/cache");

// Hole alle benötigten Klassen
include('php/class/helper.class.inc.php');
include('php/class/xml_helper.class.php');
include('php/class/Template.class.inc.php'); 
include('php/class/alert.class.inc.php');

// Include functions
include('php/functions/allg.func.inc.php');
include('php/functions/check.func.inc.php');
include('php/functions/modify.func.inc.php');
include('php/functions/traffic.func.inc.php');
include('php/functions/util.func.inc.php');

// MySQL
include("php/class/mysql.class.inc.php");

// Installer Klassen
include("install/php/class/check.class.inc.php");

// Erstelle hauptverzeichnise und Klassen
$check = new check("install/data/check.json");
$alert = new alert();
$helper = new helper();

// Verzeichnisse
$dirs["main"] = "install/";
$dirs["tpl"] = $dirs["main"]."template/";
$dirs["data"] = $dirs["main"]."data/";
$dirs["php"] = $dirs["main"]."php/";
$dirs["class"] = $dirs["php"]."class/";
$dirs["function"] = $dirs["php"]."function/";
$dirs["include"] = $dirs["php"]."include/";

// erstelle Templates
$tpl = new Template("main.htm", $dirs["tpl"]);
$tpl->load();
$resp = null;

// verarbeite
$step = 0;
if (isset($url[2])) $step = $url[2];
for ($i=0;$i<20;$i++) {
    if ($step == $i) {
        include($dirs["include"]."step_".$i.".inc.php");
    }
}

$tpl->r("langlist", get_lang_list());
$tpl->r("steptid", $step);
$tpl->r("stepid", ($step+1));
$tpl->r("title", $title);
$tpl->r("resp", $resp);
$tpl->r("pagename", "Installer");
$tpl->r("time", time());
$tpl->r("content", $content);
$tpl->r("code", "7c90c6595f7cb4d2aa0e");
$tpl->echo();
?>