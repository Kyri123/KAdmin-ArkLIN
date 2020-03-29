<?php
require('js_inz.inc.php');
$cfg = $_GET['cfg'];
$file = $_GET['file'];
$max = $_GET['max'];
$type = $_GET['type'];
if($max == 'NaN') $max = '&#8734;';
if($type == 'Ja') $hide = true;
if($type == 'Nein') $hide = false;


if(file_exists($file)) {
$z = 1;

    $array = array();
    $array = file($file);
    $i = sizeof($array);
    echo '<li class="list-group-item list-group-item-mod text-primary">Logzeit: '.date ("d.m.Y H:i:s", filemtime($file)).' | Zeigt maximal: <b>'.$max.'</b> | Verstecken: <b>'.$type.'</b></li>';
    while ($i--) {
        $array[$i] = str_replace("\"", null, $array[$i]);
        $array[$i] = str_replace("\n", null, $array[$i]);
        $array[$i] = str_replace("\r", null, $array[$i]);
        if($array[$i] != "" && $array[$i] != ' ') {
            $laenge = strlen($array[$i]);
            $z++;
            if($laenge < 300 && $type == true) {
                echo '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</li>';
            }
            elseif($hide == false) {
                echo '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b>'.filtersh(alog($array[$i])).'</li>';
            }
            else {
                echo '<li class="list-group-item list-group-item-mod"><b class="text-info">'.($i+1).': </b> <b>Zulang wird verborgen....</b></li>';
            }
        }
        if($z == $max) die;
    }


}
else {
    echo '<li class="list-group-item list-group-item-mod"><b class="text-info">ACHTUNG: </b> Kein log gefunden!<i></i></li>';
}



?>