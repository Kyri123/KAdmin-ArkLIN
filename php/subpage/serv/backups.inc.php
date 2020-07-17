<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::backup::pagename}';
$page_tpl = new Template('backup.htm', 'app/template/sub/serv/');
$page_tpl->load();
$page_tpl->debug(true);
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::backup::urltop}</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('SESSION_USERNAME' ,$user->name());

$dir_array = dirToArray($serv->dir_backup());
$y = 0; $list = null;

if (isset($url[4]) && isset($url[5]) && $url[4] == "removemain") {
    $key = $url[5];
    $path = $serv->dir_backup()."/".$key;
    if (file_exists($path)) {
        if (del_dir($path)) {
            $alert->code = 101;
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

if (isset($url[4]) && isset($url[5]) && isset($url[6]) && $url[4] == "remove") {
    $key = $url[5];
    $i = $url[6];
    $path = $serv->dir_backup()."/".$key."/".$dir_array[$key][$i];
    $filename = $dir_array[$key][$i];
    if (file_exists($path)) {
        if (unlink($path)) {
            $alert->code = 101;
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

if (isset($_POST["playthisin"])) {
    $key = $_POST["key"];
    $i = $_POST["i"];
    $opt = array();
    if (isset($_POST["opt"])) $opt = $_POST["opt"];
    $path = $serv->dir_backup()."/".$key."/".$dir_array[$key][$i];
    $spath = $serv->dir_save();
    $state = $serv->statecode();
    $cpath = $serv->dir_save(true)."/Config/LinuxServer/";
    try {
        $phar = new PharData($path);
        $phar->extractTo('cache');
        $cont = true;
    } catch (Exception $e) {
        $alert->code = 17;
        $resp = $alert->re();
        $cont = false;
    }
    if ($cont && $state == 0) {
        $find = array($serv->name().".", ".tar.bz2");
        $replace = array(null, null);
        $filename = str_replace($find, $replace, $dir_array[$key][$i]);
        $path = "cache/".$filename."/";
        $dir_array = dirToArray($path);

        if (in_array("remall", $opt)) {
            del_dir($spath);
            mkdir($spath);
        }

        for ($i=0;$i<count($dir_array);$i++) {
            $info = pathinfo($path.$dir_array[$i]);
            $ending = $info["extension"];

            if (in_array("world", $opt) && $ending == "ark") {
                if (file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                rename($path.$dir_array[$i], $spath."/".$dir_array[$i]);
            }
            elseif (!(in_array("world", $opt)) && $ending == "ark") {
                unlink($path.$dir_array[$i]);
            }

            if (in_array("ini", $opt) && $ending == "ini") {
                if (file_exists($cpath.$dir_array[$i])) unlink($cpath.$dir_array[$i]);
                rename($path.$dir_array[$i], $cpath.$dir_array[$i]);
            }
            elseif (!(in_array("ini", $opt)) && $ending == "ini") {
                unlink($path.$dir_array[$i]);
            }

            if (in_array("playerandtribes", $opt) && ($ending == "arkprofile" || $ending == "arktribe")) {
                if (file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                rename($path.$dir_array[$i], $spath."/".$dir_array[$i]);
            }
            elseif (!(in_array("playerandtribes", $opt)) && ($ending == "arkprofile" || $ending == "arktribe")) {
                unlink($path.$dir_array[$i]);
            }
        }
        rmdir($path);
        $alert->code = 106;
        $resp = $alert->re();
    } else {
        $alert->code = 7;
        $resp = $alert->re();
    }
}


$dir_array = dirToArray($serv->dir_backup());
foreach ($dir_array as $key => $value) {
    $list2 = null;
    $listtpl = new Template('backup.htm', 'app/template/lists/serv/backups/');
    $listtpl->load();
    $rndb = rndbit(50);
    $listtpl->r("rndb", $rndb);
    $listtpl->r("title", $key);
    $listtpl->r("i", count($dir_array[$key]));
    $listtpl->r("y", $y);
    for ($i=0;$i<count($dir_array[$key]);$i++) {
        $list2tpl = new Template('backup_sub.htm', 'app/template/lists/serv/backups/');
        $list2tpl->load();
        $rndb = "modal".$y.$i;
        $list2tpl->r("rndb", $rndb);

        $durl = $serv->dir_backup()."/".$key."/".$dir_array[$key][$i];

        $list2tpl->r("i", $i);
        $list2tpl->r("key", $key);
        $list2tpl->r("durl", $durl);
        $list2tpl->r("title", $dir_array[$key][$i]);
        $list2tpl->r("filesize", bitrechner(filesize($durl)));

        $list2 .= $list2tpl->load_var();
    }
    $listtpl->r("list", $list2);
    $listtpl->r("cfg", $serv->name());
    $listtpl->r("key", $key);
    $listtpl->rif ("ifemtpy", false);
    if (count($dir_array[$key]) > 0) $list .= $listtpl->load_var();
    if (count($dir_array[$key]) > 0) $y++;

}
if ($list == null) {
    $listtpl = new Template('backup.htm', 'app/template/lists/serv/backups/');
    $listtpl->load();
    $listtpl->rif ("ifemtpy", true);
    $list .= $listtpl->load_var();
}

$page_tpl->r("backups_liste", $list);
$page_tpl->session();
$panel = $page_tpl->load_var();


?>