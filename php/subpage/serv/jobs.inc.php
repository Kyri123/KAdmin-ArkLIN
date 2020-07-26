<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::jobs::pagename}';
$page_tpl = new Template('jobs.htm', 'app/template/sub/serv/');
$page_tpl->load();
$page_tpl->debug(true);
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::jobs::urltop}</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->r('cfg' ,$url[2]);
$page_tpl->r('SESSION_USERNAME' ,$user->name());
$cpath = "app/json/servercfg/jobs_" . $serv->name() . ".json";

// erstelle ggf Verzeichnis und Datei.
if (!file_exists($cpath)) {
    $array = array(
        "option" =>  array(
            "backup" => array(
                "active" => "false",
                "para" => null,
                "intervall" => 1800,
                "datetime" => time(),
            ),
            "update" => array(
                "active" => "false",
                "para" => null,
                "intervall" => 1800,
                "datetime" => time(),
            )
        )
    );
    file_put_contents($cpath, $helper->json_to_str($array));
}

// Cronjobs aktualisieren (AutoUpdate/AutoBackup)
if (isset($_POST['set'])) {
    $key = $_POST['key'];
    $intervall = $_POST['intervall'];
    $datetime = $_POST['time'];
    $datetime = strtotime($datetime);
    $json = $helper->file_to_json($cpath, true);
    if ($intervall != null && is_numeric($intervall) && $intervall > 0) {
        if ($datetime != null && $datetime > time()) {
            $json['option'][$key]['active'] = $_POST['active'];
            $json['option'][$key]['datetime'] = $datetime;
            $json['option'][$key]['intervall'] = $intervall;
            $json['option'][$key]['para'] = $_POST['parameter'];
            if ($helper->savejson_exsists($json, $cpath)) {
                $alert->code = 102;
                $resp = $alert->re();
            } else {
                $alert->code = 1;
                $resp = $alert->re();
            }
        } else {
            $alert->code = 15;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 14;
        $resp = $alert->re();
    }
}

// Cronjob erstellen
if (isset($_POST['addjob'])) {
    $json = $helper->file_to_json($cpath, true);
    $i = 0;
    while(true) {
        if (!isset($json['jobs'][$i])) break; $i++;
    }
    $i = intval($i);
    $name = $_POST['name'];
    $action = $_POST['action'];
    $parameter = $_POST['parameter'];
    $intervall = $_POST['intervall'];
    $datetime = $_POST['time'];
    $datetime = strtotime($datetime);
    if ($name != null) {
        if ($action != null) {
            if ($intervall != null && is_numeric($intervall) && $intervall > 0) {
                if ($datetime != null) {
                    $query = "INSERT INTO `ArkAdmin_jobs` 
                    (
                        `job`, 
                        `parm`, 
                        `time`, 
                        `intervall`, 
                        `active`, 
                        `server`, 
                        `name`
                    ) VALUES (
                        '$action', 
                        '$parameter', 
                        '$datetime', 
                        '$intervall', 
                        '1',  
                        '".$serv->name()."',
                        '$name'
                    )";
                    if ($mycon->query($query)) {
                        $alert->code = 100;
                        $resp = $alert->re();
                    }
                    else {
                        $alert->code = 3;
                        $resp = $alert->re();
                    }
                }
                else {
                    $alert->code = 15;
                    $resp = $alert->re();
                }
            } else {
                $alert->code = 14;
                $resp = $alert->re();
            }
        } else {
            $alert->code = 2;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 2;
        $resp = $alert->re();
    }
}

//Remove Jobs
if (isset($url[4]) && isset($url[5]) && $url[4] == "delete") {
    $i = $url[5];
    $i = intval($i);
    $query = 'SELECT * FROM `ArkAdmin_jobs` WHERE `id` = \''.$i.'\'';
    if($mycon->query($query)->numRows() > 0) {
        $query = 'DELETE FROM `ArkAdmin_jobs` WHERE `id` = \''.$i.'\'';
        if ($mycon->query($query)) {
            $alert->code = 101;
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 16;
        $resp = $alert->re();
    }
}

if (isset($url[4]) && isset($url[5]) && $url[4] == "toggle") {
    $i = $url[5];
    $i = intval($i);
    $query = 'SELECT * FROM `ArkAdmin_jobs` WHERE `id` = \''.$i.'\'';
    if($mycon->query($query)->numRows() > 0) {
        $arr = $mycon->query($query)->fetchArray();
        if ($arr['active'] == 1) {
            $set = 0;
            $txt = "{::lang::php::sc::page::jobs::job_active}";
        } else {
            $set = 1;
            $txt = "{::lang::php::sc::page::jobs::job_disturb}";
        }

        echo $query = 'UPDATE `ArkAdmin_jobs` SET `active` = \''.$set.'\' WHERE `id` = \''.$i.'\'';
        if ($mycon->query($query)) {
            $alert->code = 100;
            $alert->overwrite_text = $txt;
            $resp = $alert->re();
        } else {
            $alert->code = 1;
            $resp = $alert->re();
        }
    } else {
        $alert->code = 16;
        $resp = $alert->re();
    }
}


$json = null; $jobs = null;
$query = 'SELECT * FROM `ArkAdmin_jobs` WHERE `server` = \''.$serv->name().'\'';
if($mycon->query($query)->numRows() > 0) {
    $json = $mycon->query($query)->fetchAll();
    foreach($json as $key => $value) {
        $list = new Template('jobs.htm', 'app/template/lists/serv/jobs/');
        $list->load();
        $list->rif ('empty', true);
        if ($value['active'] == 0) {
            $toggle_icon = 'fa fa-check';
            $toggle_btn_color = 'success';
            $toggle_icon_color = 'danger';
            $toggle_tooltip = '{::lang::php::sc::page::jobs::tool_active}';
        } else {
            $toggle_icon = 'fa fa-times';
            $toggle_btn_color = 'danger';
            $toggle_icon_color = 'success';
            $toggle_tooltip = '{::lang::php::sc::page::jobs::tool_disturb}';
        }
        $list->r('toggle_tooltip', $toggle_tooltip);
        $list->r('toggle_icon', $toggle_icon);
        $list->r('toggle_btn_color', $toggle_btn_color);
        $list->r('toggle_icon_color', $toggle_icon_color);
        $list->r('title', $value['name']);
        $list->r('action', $value['job']);
        $list->r('parameter', $value['pram']);
        $list->r('intervall', $value['intervall']);
        $list->r('cfg', $serv->name());
        $list->r('i', $value["id"]);
        $list->r('datetime', date('d.m.Y - H:i', $value['time']));
        $jobs .= $list->load_var();
    } 
}

if ($jobs == null) {
    $list = new Template('jobs.htm', 'app/template/lists/serv/jobs/');
    $list->load();
    $list->rif ('empty', false);
    $list->r('title', '{::lang::php::sc::page::jobs::nothing}');
    $jobs .= $list->load_var();
}
$stamp = $json['option']['backup']['datetime'];
for ($i=0;$i<6;$i++) {
    $page_tpl->r("datetime_backup[$i]", $t[$i]);
}
if ($json['option']['backup']['active'] == "true") $page_tpl->r('true_backup', 'Selected'); $page_tpl->r('true_backup', null);
$page_tpl->r('para_backup', $json['option']['backup']['para']);
$page_tpl->r('datetime_backup', date('Y-m-d H:i', $stamp));
$page_tpl->r('intervall_backup', $json['option']['backup']['intervall']);


$stamp = $json['option']['update']['datetime'];
for ($i=0;$i<6;$i++) {
    $page_tpl->r("datetime_update[$i]", $t[$i]);
}
if ($json['option']['update']['active'] == "true") $page_tpl->r('true_update', 'Selected'); $page_tpl->r('true_update', null);
$page_tpl->r('para_update', $json['option']['update']['para']);
$page_tpl->r('datetime_update', date('Y-m-d H:i', $stamp));
$page_tpl->r('intervall_update', $json['option']['update']['intervall']);

$page_tpl->r('list', $jobs);
$page_tpl->session();
$panel = $page_tpl->load_var();


?>