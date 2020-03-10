<?php
chdir($_SERVER['DOCUMENT_ROOT']);
include('inc/class/server.class.inc.php');
include('inc/class/steamAPI.class.inc.php');
include('inc/func/allg.func.inc.php');
$steamapi = new steamapi();
$cfg = $_GET['cfg'];


echo '<li class="list-group-item list-group-item-mod"><b class="text-info">1: </b> Logzeit: '.date ("d.m.Y H:i:s.", filemtime('../../sh/resp/'.$cfg.'/last.log')).'<i></i></li>';
$i = 1;
if($fn = fopen('sh/resp/'.$cfg.'/last.log',"r")) {
    while (!feof($fn)) {#
        $result = fgets($fn);
        if($result != '') {
            $i++;
            echo '<li class="list-group-item list-group-item-mod"><b class="text-info">'.$i.': </b>'.filtersh(llog($result)).'</li>';
        }
    }
    fclose($fn);
}




?>