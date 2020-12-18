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

define("__ADIR__", __DIR__);

$ROOT           = str_replace(["install.php"], null, $_SERVER["SCRIPT_NAME"]);
$ROOT           = substr($ROOT, 0, -1);
$complete       = false;

// Standart Vars
$title = $modal = $modals = $content = null;

// Setzte Error auf 0
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// werte URL aus
$url    = $_SERVER["REQUEST_URI"];
$url    = explode("/", $url);

// Erstelle bestimmt Ordner
if(!file_exists(__ADIR__."/app/check")) mkdir(__ADIR__."/app/check");
if(!file_exists(__ADIR__."/app/cache")) mkdir(__ADIR__."/app/cache");

// Hole alle benötigten Klassen
include(__ADIR__.'/php/class/KUtil.class.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');
$helper = new helper();
include(__ADIR__.'/php/inc/template_preinz.inc.php');
include(__ADIR__.'/php/class/xml_helper.class.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');

// Include functions
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// MySQL
include(__ADIR__."/php/class/mysql.class.inc.php");

// Installer Klassen
include(__ADIR__."/install//php/class/check.class.inc.php");

// Erstelle hauptverzeichnise und Klassen
$check = new check(__ADIR__."/install//data/check.json");
$alert = new alert();

// Verzeichnisse
$dirs["main"]       = __ADIR__."/install/";
$dirs["tpl"]        = $dirs["main"]."template/";
$dirs["data"]       = $dirs["main"]."data/";
$dirs["php"]        = $dirs["main"]."/php/";
$dirs["class"]      = $dirs["php"]."class/";
$dirs["function"]   = $dirs["php"]."function/";
$dirs["include"]    = $dirs["php"]."include/";

// erstelle Templates
$tpl = new Template("main.htm", $dirs["tpl"]);
$tpl->load();
$resp = null;

// verarbeite
$step = 0;
if (isset($url[2]) && is_numeric($url[2])) $step = $url[2];
for ($i=0;$i<20;$i++) {
    if ($step == $i) {
        include($dirs["include"]."step_$i.inc.php");
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
$tpl->r("ROOT", $ROOT);
$tpl->r("__ADIR__", __ADIR__);
$tpl->echo();

if($complete) {
    // Abschluss
    $KUTIL->createFile(__ADIR__."/app/check/done", "true");

    $KUTIL->mkdir(__ADIR__."/app/json/saves");
    $KUTIL->mkdir(__ADIR__."/app/data/serv");
    $KUTIL->mkdir(__ADIR__."/app/data/config");
    $KUTIL->mkdir(__ADIR__."/app/cache");

    $KUTIL->removeFile(__ADIR__."/install");
}