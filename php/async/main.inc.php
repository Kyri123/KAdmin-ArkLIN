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

ini_set('display_errors', $display_error);
ini_set('display_startup_errors', $display_error);

session_start();

date_default_timezone_set('Europe/Amsterdam');

include('php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

include('php/class/helper.class.inc.php');
include('php/class/user.class.inc.php');
include('php/class/steamAPI.class.inc.php');
include('php/class/savefile_reader.class.inc.php');
include('php/class/Template.class.inc.php');
include('php/class/rcon.class.inc.php');
include('php/class/server.class.inc.php');
include('php/class/alert.class.inc.php');
include('php/functions/allg.func.inc.php');

$steamapi = new steamapi();
$helper = new helper();
$user = new userclass();
$user->setid($_SESSION["id"]);
$jhelper = new player_json_helper();
$alert = new alert();
?>
