<?php

date_default_timezone_set('Europe/Amsterdam');
chdir('../../');

include('inc/class/helper.class.inc.php');
include('inc/class/user.class.inc.php');
include('inc/class/steamAPI.class.inc.php');
include('inc/class/savefile_reader.class.inc.php');
include('inc/class/Template.class.inc.php');
include('inc/class/server.class.inc.php');
include('inc/class/rcon.class.inc.php');
include('inc/func/allg.func.inc.php');
$steamapi = new steamapi();
$helper = new helper();
$user = new userclass();
$jhelper = new player_json_helper();


?>
