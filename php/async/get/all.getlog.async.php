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
    echo '<tr><td class="p-2 text-green">Logzeit: <b>'.date ("d.m.Y H:i:s", filemtime($file)).'</b> | Zeigt maximal: <b>'.$max.'</b> | Verstecken: <b>'.$type.'</b></td></tr>';
    while ($i--) {
        $array[$i] = str_replace("\"", null, $array[$i]);
        $array[$i] = str_replace("\n", null, $array[$i]);
        $array[$i] = str_replace("\r", null, $array[$i]);
        if ($array[$i] != "" && $array[$i] != ' ') {
            $laenge = strlen($array[$i]);
            $z++;
            if ($laenge < 300 && $type == true) {
                $content .= '<tr><td class="p-2"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</td></tr>';
            }
            elseif ($hide == false) {
                $content .= '<tr><td class="p-2"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</td></tr>';
            } else {
                $content .= '<tr><td class="p-2"><b class="text-info">'.($i+1).': </b> <b>{::lang::php::async::get::all::getlog::toolarge}</b></td></tr>';
            }
        }
        if ($z == $max) break;
    }


} else {
    $content = '<tr><td>{::lang::php::async::get::all::getlog::no_log_found}</i></td></tr>';
}

$tpl = new Template("content.htm", "app/template/universally/default/");
$tpl->load();
$tpl->r("content", $content);
$tpl->echo();


?>