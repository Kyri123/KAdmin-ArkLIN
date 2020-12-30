<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/backup/show")) {
    header("Location: /401");
    exit;
}

$pagename   = '{::lang::php::sc::page::backup::pagename}';
$page_tpl   = new Template('backup.htm', __ADIR__.'/app/template/sub/serv/');
$urltop     = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfgRead('ark_SessionName').'</a></li>';
$urltop     .= '<li class="breadcrumb-item">{::lang::php::sc::page::backup::urltop}</li>';


$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('SESSION_USERNAME' ,$user->read("username"));

$dir_array      = dirToArray($serv->dirBackup());
$y              = 0;
$list           = null;

// entferne ein Backup verzeichnis
if (isset($_POST["removemain"]) && $session_user->perm("$perm/backup/remove")) {
    $key    = $_POST["file"];
    $path   = $KUTIL->path($serv->dirBackup()."/".$key)["/path"];
    if (@file_exists($path)) {
        $resp   .= $alert->rd($KUTIL->removeFile($path) ? 101 : 1);
    } else {
        $resp   .= $alert->rd(1);
    }
}
elseif(isset($_POST["removemain"])) {
    $resp .= $alert->rd(99);
}

// entferne ein Backup
if (isset($_POST["remove"]) && $session_user->perm("$perm/backup/remove")) {
    $key        = $_POST["file"];
    $i          = $_POST["i"];
    $path       = $KUTIL->path($serv->dirBackup()."/".$key."/".$dir_array[$key][$i])["/path"];
    $filename   = $dir_array[$key][$i];
    if (@file_exists($path)) {
        $resp   .= $alert->rd($KUTIL->removeFile($path) ? 101 : 1);
    } else {
        $resp   .= $alert->rd(1);
    }
}
elseif(isset($_POST["remove"])) {
    $resp .= $alert->rd(99);
}

// Spiele Backup ein
if (isset($_POST["playthisin"]) && $session_user->perm("$perm/backup/playin")) {

    $key                            = $_POST["key"];
    $i                              = $_POST["i"];
    $opt                            = [];
    if (isset($_POST["opt"])) $opt  = $_POST["opt"];
    $path                           = $KUTIL->path($serv->dirBackup()."/".$key."/".$dir_array[$key][$i])["/path"];
    $spath                          = $KUTIL->path($serv->dirSavegames())["/path"];
    $state                          = $serv->stateCode();
    $cpath                          = $KUTIL->path($serv->dirSavegames(true)."/Config/LinuxServer/")["/path"];
    $cont                           = false;
    $find                           = array($serv->name().".", ".tar.bz2");
    $replace                        = array(null, null);
    $filename                       = str_replace($find, $replace, $dir_array[$key][$i]);
    $path                           = $KUTIL->path(__ADIR__."/app/cache/".$filename)["/path"];

    $KUTIL->removeFile($path);

    if($state == 0) {
        try {
            // Entpacke Tar
            $phar   = new PharData($KUTIL->path($serv->dirBackup()."/".$key."/".$dir_array[$key][$i])["/path"]);
            $phar->extractTo($KUTIL->path(__ADIR__.'/app/cache')["/path"]);
            $cont   = true;
        } catch (Exception $e) {
            // Datei konnte nicht Entpackt werden
            $resp   .= $alert->rd(17);
        }
    }

    if ($cont) {
        $dir_array  = dirToArray($path);

        if (in_array("remall", $opt)) {
            $KUTIL->removeFile($spath);
            $KUTIL->mkdir($spath);
        }

        for ($i=0;$i<count($dir_array);$i++) if(isset($dir_array[$i])) {
            $info       = pathinfo($path."/".$dir_array[$i]);
            $ending     = $info["extension"];

            // Spiele wenn gewünscht Welten ein
            if (@in_array("world", $opt) && $ending == "ark") {
                $KUTIL->removeFile($spath."/".$dir_array[$i]);
                rename($KUTIL->path($path."/".$dir_array[$i])["/path"], $KUTIL->path($spath."/".$dir_array[$i])["/path"]);
            }
            elseif (!(@in_array("world", $opt)) && $ending == "ark") {
                $KUTIL->removeFile($path."/".$dir_array[$i]);
            }

            // Spiele wenn gewünscht Inis ein
            if (@in_array("ini", $opt) && $ending == "ini") {
                $KUTIL->removeFile($cpath.$dir_array[$i]);
                rename($KUTIL->path($path."/".$dir_array[$i])["/path"], $KUTIL->path($cpath.$dir_array[$i])["/path"]);
            }
            elseif (!(@in_array("ini", $opt)) && $ending == "ini") {
                $KUTIL->removeFile($path."/".$dir_array[$i]);
            }

            // Spiele wenn gewünscht Profile & Stämme ein
            if (@in_array("playerandtribes", $opt) && ($ending == "arkprofile" || $ending == "arktribe")) {
                $KUTIL->removeFile($spath."/".$dir_array[$i]);
                rename($KUTIL->path($path."/".$dir_array[$i])["/path"], $KUTIL->path($spath."/".$dir_array[$i])["/path"]);
            }
            elseif (!(@in_array("playerandtribes", $opt)) && ($ending == "arkprofile" || $ending == "arktribe")) {
                $KUTIL->removeFile($path."/".$dir_array[$i]);
            }
        }
        $KUTIL->removeFile($path, true);
        $resp .= $alert->rd(106);
    } else {
        $resp .= $alert->rd(7);
    }
}
elseif(isset($_POST["playthisin"])) {
    $resp .= $alert->rd(99);
}


// Suche nach Backupordener
$dir_array = dirToArray($KUTIL->path($serv->dirBackup())["/path"]);
foreach ($dir_array as $key => $value) {
    $list2      = null;
    $listtpl    = new Template('backup.htm', __ADIR__.'/app/template/lists/serv/backups/');
    $rndb       = rndbit(50);

    $listtpl->load();
    $listtpl->r("rndb", $rndb);
    $listtpl->r("title", $key);
    $listtpl->r("i", count($dir_array[$key]));
    $listtpl->r("y", $y);

    // Suche nach Backups
    for ($i=0;$i<count($dir_array[$key]);$i++) {
        $list2tpl   = new Template('backup_sub.htm', __ADIR__.'/app/template/lists/serv/backups/');
        $rndb       = "modal".$y.$i;
        $durl       = str_replace(__ADIR__, null, $serv->dirBackup())."/".$key."/".$dir_array[$key][$i];

        $list2tpl->load();
        $list2tpl->r("rndb", $rndb);
        $list2tpl->r("i", $i);
        $list2tpl->r("key", $key);
        $list2tpl->r("durl", $durl);
        $list2tpl->r("title", $dir_array[$key][$i]);
        $list2tpl->r("filesize", bitrechner(filesize($serv->dirBackup()."/".$key."/".$dir_array[$key][$i])));

        $list2      .= $list2tpl->load_var();
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
    $listtpl    = new Template('backup.htm', __ADIR__.'/app/template/lists/serv/backups/');
    $listtpl->load();
    $listtpl->rif ("ifemtpy", true);
    $list       .= $listtpl->load_var();
}

$page_tpl->r("backups_liste", $list);
$panel = $page_tpl->load_var();