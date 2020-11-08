<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/backup/show")) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::backup::pagename}';
$page_tpl = new Template('backup.htm', __ADIR__.'/app/template/sub/serv/');
$page_tpl->load();
$page_tpl->debug(true);
$urltop = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::backup::urltop}</li>';

$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('SESSION_USERNAME' ,$user->read("username"));

$dir_array = dirToArray($serv->dir_backup());
$y = 0; $list = null;

// entferne ein Backup verzeichnis
if (isset($_POST["removemain"]) && $session_user->perm("$perm/backup/remove")) {
    $key = $_POST["file"];
    $path = $serv->dir_backup()."/".$key;
    if (file_exists($path)) {
        if (del_dir($path)) {
            // Melde: Abschluss
            $resp .= $alert->rd(101);
        } else {
            // Melde: Lese/Schreib fehler
            $resp .= $alert->rd(1);
        }
    } else {
        // Melde: Lese/Schreib fehler
        $resp .= $alert->rd(1);
    }
}
elseif(isset($_POST["removemain"])) {
    $resp .= $alert->rd(99);
}

// entferne ein Backup
if (isset($_POST["remove"]) && $session_user->perm("$perm/backup/remove")) {
    $key = $_POST["file"];
    $i = $_POST["i"];
    $path = $serv->dir_backup()."/".$key."/".$dir_array[$key][$i];
    $filename = $dir_array[$key][$i];
    if (file_exists($path)) {
        if (unlink($path)) {
            // Melde: Abschluss
            $resp .= $alert->rd(101);
        } else {
            // Melde: Lese/Schreib fehler
            $resp .= $alert->rd(1);
        }
    } else {
        // Melde: Lese/Schreib fehler
        $resp .= $alert->rd(1);
    }
}
elseif(isset($_POST["remove"])) {
    $resp .= $alert->rd(99);
}

// Spiele Backup ein
if (isset($_POST["playthisin"]) && $session_user->perm("$perm/backup/playin")) {
    $key = $_POST["key"];
    $i = $_POST["i"];
    $opt = array();
    if (isset($_POST["opt"])) $opt = $_POST["opt"];

    $path = $serv->dir_backup()."/".$key."/".$dir_array[$key][$i];
    $spath = $serv->dir_save();
    $state = $serv->statecode();
    $cpath = $serv->dir_save(true)."/Config/LinuxServer/";

    $find = array($serv->name().".", ".tar.bz2");
    $replace = array(null, null);
    $filename = str_replace($find, $replace, $dir_array[$key][$i]);
    $path = __ADIR__."/app/cache/".$filename;

    if(file_exists($path)) del_dir($path);

    try {
        // Entpacke Tar
        $phar = new PharData($serv->dir_backup()."/".$key."/".$dir_array[$key][$i]);
        $phar->extractTo(__ADIR__.'/app/cache');
        $cont = true;
    } catch (Exception $e) {
        // Melde: Datei konnte nicht Entpackt werden
        $resp .= $alert->rd(17);
        $cont = false;
    }
    if ($cont && $state == 0) {
        $dir_array = dirToArray($path);

        if (in_array("remall", $opt)) {
            del_dir($spath);
            mkdir($spath);
        }

        for ($i=0;$i<count($dir_array);$i++) {
            if(isset($dir_array[$i])) {
                $info = pathinfo($path."/".$dir_array[$i]);
                $ending = $info["extension"];

                // Spiele wenn gewünscht Welten ein
                if (in_array("world", $opt) && $ending == "ark") {
                    if (file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                    rename($path."/".$dir_array[$i], $spath."/".$dir_array[$i]);
                }
                elseif (!(in_array("world", $opt)) && $ending == "ark") {
                    unlink($path."/".$dir_array[$i]);
                }

                // Spiele wenn gewünscht Inis ein
                if (in_array("ini", $opt) && $ending == "ini") {
                    if (file_exists($cpath.$dir_array[$i])) unlink($cpath.$dir_array[$i]);
                    rename($path."/".$dir_array[$i], $cpath.$dir_array[$i]);
                }
                elseif (!(in_array("ini", $opt)) && $ending == "ini") {
                    unlink($path."/".$dir_array[$i]);
                }

                // Spiele wenn gewünscht Profile & Stämme ein
                if (in_array("playerandtribes", $opt) && ($ending == "arkprofile" || $ending == "arktribe")) {
                    if (file_exists($spath."/".$dir_array[$i])) unlink($spath."/".$dir_array[$i]);
                    rename($path."/".$dir_array[$i], $spath."/".$dir_array[$i]);
                }
                elseif (!(in_array("playerandtribes", $opt)) && ($ending == "arkprofile" || $ending == "arktribe")) {
                    unlink($path."/".$dir_array[$i]);
                }
            }
        }
        del_dir($path);
        // Melde: Backup eingespielt
        $resp .= $alert->rd(106);
    } else {
        // Melde: Server muss Offline sein
        $resp .= $alert->rd(7);
    }
}
elseif(isset($_POST["playthisin"])) {
    $resp .= $alert->rd(99);
}


// Suche nach Backupordener
$dir_array = dirToArray($serv->dir_backup());
foreach ($dir_array as $key => $value) {
    $list2 = null;
    $listtpl = new Template('backup.htm', __ADIR__.'/app/template/lists/serv/backups/');
    $listtpl->load();
    $rndb = rndbit(50);
    $listtpl->r("rndb", $rndb);
    $listtpl->r("title", $key);
    $listtpl->r("i", count($dir_array[$key]));
    $listtpl->r("y", $y);

    // Suche nach Backups
    for ($i=0;$i<count($dir_array[$key]);$i++) {
        $list2tpl = new Template('backup_sub.htm', __ADIR__.'/app/template/lists/serv/backups/');
        $list2tpl->load();
        $rndb = "modal".$y.$i;
        $list2tpl->r("rndb", $rndb);

        $durl = str_replace(__ADIR__, null, $serv->dir_backup())."/".$key."/".$dir_array[$key][$i];

        $list2tpl->r("i", $i);
        $list2tpl->r("key", $key);
        $list2tpl->r("durl", $durl);
        $list2tpl->r("title", $dir_array[$key][$i]);
        $list2tpl->r("filesize", bitrechner(filesize($serv->dir_backup()."/".$key."/".$dir_array[$key][$i])));

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
    // Gebe aus dass keine Backups gefunden wurden
    $listtpl = new Template('backup.htm', __ADIR__.'/app/template/lists/serv/backups/');
    $listtpl->load();
    $listtpl->rif ("ifemtpy", true);
    $list .= $listtpl->load_var();
}

$page_tpl->r("backups_liste", $list);
$panel = $page_tpl->load_var();


