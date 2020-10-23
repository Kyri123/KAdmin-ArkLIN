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
include(__ADIR__.'/php/inc/config.inc.php');

ini_set('display_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
ini_set('display_startup_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));

session_start();

date_default_timezone_set('Europe/Amsterdam');

include(__ADIR__.'/php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

include(__ADIR__.'/php/class/helper.class.inc.php');
include(__ADIR__.'/php/class/user.class.inc.php');
include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/functions/allg.func.inc.php');


$steamapi = new steamapi();
$helper = new helper();

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');
$user = new userclass();
$user->setid($_SESSION["id"]);
$jhelper = new player_json_helper();
$alert = new alert();

$tpl = new Template("content.htm", __ADIR__."/app/template/universally/default/");
$tpl->load();
$tpl->r("content", (file_exists(__ADIR__."/app/data/checked")) ? "<a href='$ROOT/install.php/5' target='_blank' class='btn btn-success rounded-0' style='width: 100%'>{::lang::install::allg::done}</a>" : null);
$tpl->echo();


