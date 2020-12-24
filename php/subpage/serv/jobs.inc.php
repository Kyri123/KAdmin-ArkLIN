<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/jobs/show")) {
    header("Location: /401");
    exit;
}

$pagename   = '{::lang::php::sc::page::jobs::pagename}';
$page_tpl   = new Template('jobs.htm', __ADIR__.'/app/template/sub/serv/');
$urltop     = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfgRead('ark_SessionName').'</a></li>';
$urltop     .= '<li class="breadcrumb-item">{::lang::php::sc::page::jobs::urltop}</li>';

$page_tpl->load();
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('SESSION_USERNAME' ,$user->read("username"));

// Cronjob erstellen
if (isset($_POST['addjob']) && $session_user->perm("$perm/jobs/add")) {
    $name       = $_POST['name'];
    $action     = $_POST['action'];
    $parameter  = $_POST['parameter'];
    $intervall  = $_POST['intervall'];
    $datetime   = $_POST['time'];
    $datetime   = strtotime($datetime);
    if ($name !== null && $action !== null) {
        if ($intervall != null && is_numeric($intervall) && $intervall > 0) {
            if ($datetime != null) {
                $query = "INSERT INTO `ArkAdmin_jobs` (`job`, `parm`, `time`, `intervall`, `active`, `server`, `name`) VALUES (?, ?, ?, ?, '1', ?, ?)";
                $resp .= $alert->rd($mycon->query($query, $action, $parameter, $datetime, $intervall, $serv->name(), $name) ? 100 : 3);
            }
            else {
                $resp .= $alert->rd(15);
            }
        } else {
            $resp .= $alert->rd(14);
        }
    } else {
        $resp .= $alert->rd(2);
    }
}
elseif(isset($_POST['addjob'])) {
    $reps = $alert->rd(99);
}

// Cronjob Bearbeiten
if (isset($_POST['edit']) && $session_user->perm("$perm/jobs/edit")) {
    $id         = $_POST['id'];
    $name       = $_POST['name'];
    $action     = $_POST['action'];
    $parameter  = $_POST['parameter'];
    $intervall  = $_POST['intervall'];
    $datetime   = $_POST['time'];
    $datetime   = strtotime($datetime);
    if ($name !== null && $action !== null) {
        if ($intervall != null && is_numeric($intervall) && $intervall > 0) {
            if ($datetime != null) {
                $query = "UPDATE `ArkAdmin_jobs` SET `job` = ?, `parm` = ?, `time` = ?, `intervall` = ?, `name` = ? WHERE `id` = ?;";
                $resp .= $alert->rd($mycon->query($query, $action, $parameter, $datetime, $intervall, $name, $id) ? 102 : 3);
            }
            else {
                $resp .= $alert->rd(15);
            }
        } else {
            $resp .= $alert->rd(14);
        }
    } else {
        $resp .= $alert->rd(2);
    }
}
elseif(isset($_POST['edit'])) {
    $reps = $alert->rd(99);
}

// Entferne Jobs
if (isset($_POST['delete']) && $session_user->perm("$perm/jobs/remove")) {
    $i      = intval($_POST['i']);
    $query  = 'SELECT * FROM `ArkAdmin_jobs` WHERE `id` = ?';
    if($mycon->query($query, $i)->numRows() > 0) {
        $query = 'DELETE FROM `ArkAdmin_jobs` WHERE `id` = ?';
        $resp .= $alert->rd($mycon->query($query, $i) ? 101 : 1);
    } else {
        $resp .= $alert->rd(16);
    }
}
elseif(isset($_POST['delete'])) {
    $reps = $alert->rd(99);
}

//Erstelle Job (Update & Backup btn)
if (isset($url[5]) && $url[4] == "create" && $session_user->perm("$perm/jobs/add")) {
    $type = $url[5];
    $query  = 'SELECT * FROM `ArkAdmin_jobs` WHERE `job` = ? AND `server` = ?';
    if(($type == "update" || $type == "backup") && $mycon->query($query, $type, $serv->name())->numRows() === 0) {
        if ($mycon->query(
            "INSERT INTO `ArkAdmin_jobs` (`job`, `parm`, `time`, `intervall`, `active`, `server`, `name`) VALUES (?, ?, ?, ?, ?, ?, ?)",
            $type,
            $type == "update" ? "--update-mods --warn --saveworld" : "--allmaps",
            time(),
            1800,
            1,
            $serv->name(),
            $type == "update" ? "Auto Update" : "Auto Backup"
        )) {
            header("location: /servercenter/".$serv->name()."/jobs/");
            exit;
        }
        else {
            // Melde Datenbank Fehler
            $resp .= $alert->rd(3);
        }
    }
}
elseif(isset($url[5]) && $url[4] == "create") {
    $reps = $alert->rd(99);
}

// (De-)Aktivieren von Jobs
if (isset($url[5]) && $url[4] == "toggle" && $session_user->perm("$perm/jobs/toggle")) {
    $i      = intval($url[5]);
    $query  = 'SELECT * FROM `ArkAdmin_jobs` WHERE `id` = ?';
    if($mycon->query($query, $i)->numRows() > 0) {
        $arr    = $mycon->query($query, $i)->fetchArray();
        $set    = $arr['active'] == 1 ? 0 : 1;
        $txt    = $arr['active'] == 1 ? "{::lang::php::sc::page::jobs::job_active}" : "{::lang::php::sc::page::jobs::job_disturb}";

        $query = 'UPDATE `ArkAdmin_jobs` SET `active` = ? WHERE `id` = ?';
        if ($mycon->query($query, $set, $i)) {
            $alert->code = 100;
            $alert->overwrite_text = $txt;
            $resp .= $alert->re();
        } else {
            $resp .= $alert->rd(1);
        }
    } else {
        $resp .= $alert->rd(16);
    }
}
elseif(isset($url[5]) && $url[4] == "toggle") {
    $reps = $alert->rd(99);
}

// Lese Jobs und liste sie
$commands   = array("start", "restart", "stop", "installmods", "uninstallmods", "saveworld", "update", "backup");
$json       = $jobs = $jobs_modal = null;
$query      = 'SELECT * FROM `ArkAdmin_jobs` WHERE `server` = ?';
if($mycon->query($query, $serv->name())->numRows() > 0) {
    $json   = $mycon->query($query, $serv->name())->fetchAll();
    foreach($json as $key => $value) {
        $list   = new Template('jobs.htm', __ADIR__.'/app/template/lists/serv/jobs/');
        $list->load();
        $list->rif ('empty', true);

        $toggle_icon        = intval($value['active']) == 0 ? 'fa fa-check'                                 : 'fa fa-times';
        $toggle_btn_color   = intval($value['active']) == 0 ? 'success'                                     : 'danger';
        $toggle_icon_color  = intval($value['active']) == 0 ? 'danger'                                      : 'success';
        $toggle_tooltip     = intval($value['active']) == 0 ? '{::lang::php::sc::page::jobs::tool_active}'  : '{::lang::php::sc::page::jobs::tool_disturb}';

        for($i=0;$i<count($commands);$i++) $list->r("__".$commands[$i], (($commands[$i] == $value['job'] ? "selected" : null)));

        // List
        $list->r('toggle_tooltip', $toggle_tooltip);
        $list->r('toggle_icon', $toggle_icon);
        $list->r('toggle_btn_color', $toggle_btn_color);
        $list->r('toggle_icon_color', $toggle_icon_color);
        $list->r('title', $value['name']);
        $list->r('action', $value['job']);
        $list->r('parameter', $value['parm']);
        $list->r('intervall', $value['intervall']);
        $list->r('cfg', $serv->name());
        $list->r('i', $value["id"]);
        $list->r('rnd', md5($value["id"]));
        $list->r('datetime', date('d.m.Y - H:i', $value['time']));
        $list->r('datetime_edit', date('Y-m-d H:i', $value['time']));
        $list->rif('modal', false);
        $list->rif('update', $value['job'] == "update");
        $list->rif('backup', $value['job'] == "backup");
        $jobs .= $list->load_var();
    } 
} 

$query_bu = 'SELECT * FROM `ArkAdmin_jobs` WHERE `server` = ? AND `job` = ?';

$page_tpl->rif("update_btn", $mycon->query($query_bu, $serv->name(), "update")->numRows() == 0);
$page_tpl->rif("backup_btn", $mycon->query($query_bu, $serv->name(), "backup")->numRows() == 0);
$page_tpl->r('update_url', "/servercenter/".$serv->name()."/jobs/create/update/");
$page_tpl->r('backup_url', "/servercenter/".$serv->name()."/jobs/create/backup/");
$page_tpl->r('listmodal', $jobs_modal);
$page_tpl->r('listmodal', $jobs_modal);
$page_tpl->r('list', $jobs);
$panel = $page_tpl->load_var();