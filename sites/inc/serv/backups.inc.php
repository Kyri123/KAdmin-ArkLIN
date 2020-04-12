<?php

$page_tpl = new Template('backup.htm', 'tpl/serv/sites/');
$page_tpl->load();
$page_tpl->debug(true);
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Backups</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->repl('cfg' ,$url[2]);
$page_tpl->repl('SESSION_USERNAME' ,$user->name());

$dir_array = dirToArray($serv->get_backup_dir());
$y = 0; $list = null;

if(isset($url[4]) && isset($url[5]) && $url[4] == "removemain") {
    $key = $url[5];
    $path = $serv->get_backup_dir()."/".$key;
    if(file_exists($path)) {
        if(del_dir($path)) {
            $resp = meld('success', $filename. " & alles darin wurde entfernt.", 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konnte Ordner nicht entfernen.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Konnte Ordner nicht finden.', 'Fehler!', null);
    }
}

if(isset($url[4]) && isset($url[5]) && isset($url[6]) && $url[4] == "remove") {
    $key = $url[5];
    $i = $url[6];
    $path = $serv->get_backup_dir()."/".$key."/".$dir_array[$key][$i];
    $filename = $dir_array[$key][$i];
    if(file_exists($path)) {
        if(unlink($path)) {
            $resp = meld('success', $filename. " wurde entfernt.", 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konnte Datei nicht entfernen.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Konnte Datei nicht finden.', 'Fehler!', null);
    }
}

if(isset($_POST["playthisin"])) {
    $key = $_POST["key"];
    $i = $_POST["i"];
    $opt = array();
    if(isset($_POST["opt"])) $opt = $_POST["opt"];
    $path = $serv->get_backup_dir()."/".$key."/".$dir_array[$key][$i];
    $spath = $serv->get_save_dir();
    $state = $serv->get_state();
    $cpath = $spath."/../Config/LinuxServer/";
    try {
        $phar = new PharData($path);
        $phar->extractTo('cache');
        $cont = true;
    } catch (Exception $e) {
        $resp = meld('danger', 'Konnte das Backup nicht einspielen', 'Fataler Fehler!', null);
        $cont = false;
    }
    if($cont && $state == 0) {
        $find = array($serv->show_name().".", ".tar.bz2");
        $replace = array(null, null);
        $filename = str_replace($find, $replace, $dir_array[$key][$i]);
        $path = "cache/".$filename."/";
        $dir_array = dirToArray($path);

        if(in_array("remall", $opt)) {
            del_dir($spath);
            mkdir($spath);
        }

        for($i=0;$i<count($dir_array);$i++) {
            $info = pathinfo($path.$dir_array[$i]);
            $ending = $info["extension"];

            if(in_array("world", $opt) && $ending == "ark") {
                if(file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                rename($path.$dir_array[$i], $spath."/".$dir_array[$i]);
            }
            elseif (!(in_array("world", $opt)) && $ending == "ark") {
                unlink($path.$dir_array[$i]);
            }

            if(in_array("ini", $opt) && $ending == "ini") {
                if(file_exists($cpath.$dir_array[$i])) unlink($cpath.$dir_array[$i]);
                rename($path.$dir_array[$i], $cpath.$dir_array[$i]);
            }
            elseif (!(in_array("ini", $opt)) && $ending == "ini") {
                unlink($path.$dir_array[$i]);
            }

            if(in_array("playerandtribes", $opt) && ($ending == "arkprofile" || $ending == "arktribe")) {
                if(file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                rename($path.$dir_array[$i], $spath."/".$dir_array[$i]);
            }
            elseif (!(in_array("playerandtribes", $opt)) && ($ending == "arkprofile" || $ending == "arktribe")) {
                unlink($path.$dir_array[$i]);
            }
        }
        rmdir($path);
        $resp = meld('success', $filename. " wurde Einspielt.", 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', $filename. " konnte nicht eingespielt werden... Server Online (Status muss Offline sein) oder Datei konnte nicht entpackt werden.", 'Fehler!', null);
    }
}


$dir_array = dirToArray($serv->get_backup_dir());
foreach ($dir_array as $key => $value) {
    $list2 = null;
    $listtpl = new Template('list_backup.htm', 'tpl/serv/sites/list/');
    $listtpl->load();
    $rndb = rndbit(50);
    $listtpl->repl("rndb", $rndb);
    $listtpl->repl("title", $key);
    $listtpl->repl("i", count($dir_array[$key]));
    $listtpl->repl("y", $y);
    for($i=0;$i<count($dir_array[$key]);$i++) {
        $list2tpl = new Template('list2_backup.htm', 'tpl/serv/sites/list/');
        $list2tpl->load();
        $rndb = "modal".$y.$i;
        $list2tpl->repl("rndb", $rndb);

        $durl = $serv->get_backup_dir()."/".$key."/".$dir_array[$key][$i];

        $list2tpl->repl("i", $i);
        $list2tpl->repl("key", $key);
        $list2tpl->repl("durl", $durl);
        $list2tpl->repl("title", $dir_array[$key][$i]);
        $list2tpl->repl("filesize", bitrechner(filesize($durl)));

        $list2 .= $list2tpl->loadin();
    }
    $listtpl->repl("list", $list2);
    $listtpl->repl("cfg", $serv->show_name());
    $listtpl->repl("key", $key);
    $listtpl->replif("ifemtpy", false);
    if(count($dir_array[$key]) > 0) $list .= $listtpl->loadin();
    if(count($dir_array[$key]) > 0) $y++;

}
if($list == null) {
    $listtpl = new Template('list_backup.htm', 'tpl/serv/sites/list/');
    $listtpl->load();
    $listtpl->replif("ifemtpy", true);
    $list .= $listtpl->loadin();
}

$page_tpl->repl("backups_liste", $list);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>