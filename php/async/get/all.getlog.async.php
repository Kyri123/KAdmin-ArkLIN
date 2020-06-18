<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_GET['cfg'];
$file = $_GET['file'];
$max = $_GET['max'];
$type = $_GET['type'];
if ($max == 'NaN') $max = '&#8734;';
if ($type == 'Ja') $hide = true;
if ($type == 'Nein') $hide = false;

$content = null;
if (file_exists($file)) {
$z = 1;

    $array = array();
    $array = file($file);
    $i = sizeof($array);
    echo '<li class="list-group-item list-group-item-mod text-primary">Logzeit: '.date ("d.m.Y H:i:s", filemtime($file)).' | Zeigt maximal: <b>'.$max.'</b> | Verstecken: <b>'.$type.'</b></li>';
    while ($i--) {
        $array[$i] = str_replace("\"", null, $array[$i]);
        $array[$i] = str_replace("\n", null, $array[$i]);
        $array[$i] = str_replace("\r", null, $array[$i]);
        if ($array[$i] != "" && $array[$i] != ' ') {
            $laenge = strlen($array[$i]);
            $z++;
            if ($laenge < 300 && $type == true) {
                $content .= '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</li>';
            }
            elseif ($hide == false) {
                $content .= '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</li>';
            } else {
                $content .= '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b> <b>{::lang::php::async::get::all::getlog::toolarge}</b></li>';
            }
        }
        if ($z == $max) break;
    }


} else {
    $content = '<li class="list-group-item list-group-item-mod">{::lang::php::async::get::all::getlog::no_log_found}</i></li>';
}

$tpl = new Template("content.htm", "app/template/default/");
$tpl->load();
$tpl->r("content", $content);
$tpl->echo();


?>