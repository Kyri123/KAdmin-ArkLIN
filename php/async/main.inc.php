<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

date_default_timezone_set('Europe/Amsterdam');
chdir('../../../');

include('php/class/helper.class.inc.php');
include('php/class/user.class.inc.php');
include('php/class/steamAPI.class.inc.php');
include('php/class/savefile_reader.class.inc.php');
include('php/class/Template.class.inc.php');
include('php/class/server.class.inc.php');
include('php/class/rcon.class.inc.php');
include('php/class/alert.class.inc.php');
include('php/functions/allg.func.inc.php');
$steamapi = new steamapi();
$helper = new helper();
$user = new userclass();
$jhelper = new player_json_helper();
$alert = new alert();


?>
