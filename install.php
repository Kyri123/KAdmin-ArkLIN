<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

$url = $_SERVER["REQUEST_URI"];
$url = explode("/", $url);

if(!file_exists("app/check")) mkdir("app/check");
if(!file_exists("app/cache")) mkdir("app/cache");

include('php/class/helper.class.inc.php');
include('php/class/xml_helper.class.php');
include('php/class/Template.class.inc.php'); 
include('php/class/alert.class.inc.php'); 
include("php/functions/allg.func.inc.php");
include("php/class/mysql.class.inc.php");
include("install/func.inc.php");

$alert = new alert();
$helper = new helper();
$tpl_dir = "install/";

$tpl = new Template("main.htm", $tpl_dir);
$tpl->load();

$step = 0;
if (isset($url[2])) $step = $url[2];

for ($i=0;$i<20;$i++) {
    if ($step == $i) {
        include("install/page/step".$i.".inc.php");
    }
}

$tpl->r("langlist", get_lang_list());
$tpl->r("stepid", ($step+1));
$tpl->r("title", $title);
$tpl->r("pagename", "Installer");
$tpl->r("time", time());
$tpl->r("content", $content);
$tpl->echo();
?>