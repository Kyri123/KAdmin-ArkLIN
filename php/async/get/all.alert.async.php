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

require('../main.inc.php');

if(isset($_GET["code"]))                                                    $alert->code                = intval($_GET["code"]);
if(isset($_GET["overwrite_color"]))                                         $alert->overwrite_color     = $_GET["overwrite_color"];
if(isset($_GET["overwrite_text"]))                                          $alert->overwrite_text      = $_GET["overwrite_text"];
if(isset($_GET["overwrite_icon"]))                                          $alert->overwrite_icon      = $_GET["overwrite_icon"];
if(isset($_GET["overwrite_title"]))                                         $alert->overwrite_title     = $_GET["overwrite_title"];
if(isset($_GET["overwrite_style"]) && is_int(($_GET["overwrite_style"])))   $alert->overwrite_style     = $_GET["overwrite_style"];
if(isset($_GET["replace"]))                                                 $replace                    = $_GET["replace"];

if(isset($replace) && is_countable($replace)) foreach($replace as $k => $v) $alert->r($v[0], $v[1]);

echo $alert->re();
$mycon->close();