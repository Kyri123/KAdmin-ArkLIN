<?php

$page_tpl = new Template('jobs.htm', 'tpl/serv/sites/');
$page_tpl->load();
$page_tpl->debug(true);
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Jobs (Aufgaben)</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->repl('cfg' ,$url[2]);
$page_tpl->repl('SESSION_USERNAME' ,$user->name());
$cpath = "data/config/jobs_" . $serv->show_name() . ".json";

// erstelle ggf Verzeichnis und Datei.
if(!file_exists('data/config')) mkdir('data/config');
if(!file_exists($cpath)) {
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
        ),
        "jobs" => array()
    );
    file_put_contents($cpath, $helper->json_to_str($array));
}

if(isset($_POST['set'])) {
    $key = $_POST['key'];
    $intervall = $_POST['intervall'];
    $datetime = $_POST['time'][2]."-".$_POST['time'][1]."-".$_POST['time'][0]." ".$_POST['time'][3].":".$_POST['time'][4].":00";
    $datetime = strtotime($datetime);
    $json = $helper->file_to_json($cpath, true);
    if($intervall != null && is_numeric($intervall) && $intervall > 0) {
        if($datetime != null && $datetime > time()) {
            $json['option'][$key]['active'] = $_POST['active'];
            $json['option'][$key]['datetime'] = $datetime;
            $json['option'][$key]['intervall'] = $intervall;
            $json['option'][$key]['para'] = $_POST['parameter'];
            if($helper->savejson_exsists($json, $cpath)) {
                $resp = meld('success', 'Aktualisiert.', 'Erfolgreich!', null);
            }
            else {
                $resp = meld('danger', 'Konnte Datei nicht speichern.', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Zeit muss gößer der aktuellen Zeit sein & das Format muss eingehalten werden', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Intervall hat kein gültiges Format, Ist kleiner als 0 oder ist Leer.', 'Fehler!', null);
    }
}


if(isset($_POST['addjob'])) {
    $json = $helper->file_to_json($cpath, true);
    $i = 0;
    while(true) {
        if(!isset($json['jobs'][$i])) break; $i++;
    }
    $i = intval($i);
    $name = $_POST['name'];
    $action = $_POST['action'];
    $parameter = $_POST['parameter'];
    $intervall = $_POST['intervall'];
    $datetime = $_POST['time'][2]."-".$_POST['time'][1]."-".$_POST['time'][0]." ".$_POST['time'][3].":".$_POST['time'][4].":00";
    $datetime = strtotime($datetime);
    if($name != null) {
        if($action != null) {
            if($intervall != null && is_numeric($intervall) && $intervall > 0) {
                if($datetime != null && $datetime > time()) {
                    $json['jobs'][$i]['name'] = $name;
                    $json['jobs'][$i]['action'] = $action;
                    $json['jobs'][$i]['intervall'] = $intervall;
                    $json['jobs'][$i]['parameter'] = $parameter;
                    $json['jobs'][$i]['datetime'] = $datetime;
                    $helper->savejson_exsists($json, $cpath);
                    $resp = meld('success', 'Aufgabe erstellt.', 'Erfolgreich!', null);
                }
                else {
                    $resp = meld('danger', 'Datum & Zeit sind kleiner als die aktuelle Zeit oder sind falsch eingeben.', 'Fehler!', null);
                }
            }
            else {
                $resp = meld('danger', 'Intervall hat kein gültiges Format, Ist kleiner als 0 oder ist Leer.', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Aufgabe wurde nicht gewählt.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Bezeichnung ist leer', 'Fehler!', null);
    }
}

//Remove Jobs
if(isset($url[4]) && isset($url[5]) && $url[4] == "delete") {
    $i = $url[5];
    $i = intval($i);
    $json = $helper->file_to_json($cpath);
    if(isset($json["jobs"][$i])) {
        unset($json["jobs"][$i]);
        if($helper->savejson_create($json, $cpath)) {
            $resp = meld('success', 'Aufgabe entfernt.', 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konnte Datei nicht speichern.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Aufgabe gibt es nicht.', 'Fehler!', null);
    }
}

if(isset($url[4]) && isset($url[5]) && $url[4] == "toggle") {
    $i = $url[5];
    $i = intval($i);
    $json = $helper->file_to_json($cpath, true);
    if(isset($json['jobs'][$i])) {
        if($json['jobs'][$i]['active'] == "true") {
            $json['jobs'][$i]['active'] = "false";
            $txt = "Aufgabe wurde aktiviert";
        }
        else {
            $json['jobs'][$i]['active'] = "true";
            $txt = "Aufgabe wurde deaktiviert";
        }
        if($helper->savejson_exsists($json, $cpath)) {
            $resp = meld('success', $txt, 'Erfolgreich!', null);
        }
        else {
            $resp = meld('danger', 'Konnte Datei nicht speichern.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Aufgabe gibt es nicht.', 'Fehler!', null);
    }
}


$json = null; $jobs = null;
$json = $helper->file_to_json($cpath, true);

foreach($json['jobs'] as $key => $value) {
    $list = new Template('list_jobs.htm', 'tpl/serv/sites/list/');
    $list->load();
    $list->replif('empty', true);
    if($json['jobs'][$key]['active'] == "true") {
        $toggle_icon = 'fa fa-check';
        $toggle_btn_color = 'btn-success';
        $toggle_tooltip = 'Deaktivieren';
    }
    else {
        $toggle_icon = 'fa fa-times';
        $toggle_btn_color = 'btn-danger';
        $toggle_tooltip = 'Aktivieren';
    }
    $list->repl('toggle_tooltip', $toggle_tooltip);
    $list->repl('toggle_icon', $toggle_icon);
    $list->repl('toggle_btn_color', $toggle_btn_color);
    $list->repl('title', $json['jobs'][$key]['name']);
    $list->repl('action', $json['jobs'][$key]['action']);
    $list->repl('parameter', $json['jobs'][$key]['parameter']);
    $list->repl('intervall', $json['jobs'][$key]['intervall']);
    $list->repl('cfg', $serv->show_name());
    $list->repl('i', $key);
    $list->repl('datetime', date('d.m.Y - H:i', $json['jobs'][$key]['datetime']));
    $jobs .= $list->loadin();
}

if($jobs == null) {
    $list = new Template('list_jobs.htm', 'tpl/serv/sites/list/');
    $list->load();
    $list->replif('empty', false);
    $list->repl('title', 'Keine Jobs gefunden!');
    $jobs .= $list->loadin();
}
$stamp = $json['option']['backup']['datetime'];
$t[0] = date("Y", $stamp);
$t[1] = date("m", $stamp);
$t[2] = date("d", $stamp);
$t[3] = date("H", $stamp);
$t[4] = date("i", $stamp);
for($i=0;$i<6;$i++) {
    $page_tpl->repl("datetime_backup[$i]", $t[$i]);
}
if($json['option']['backup']['active'] == "true") $page_tpl->repl('true_backup', 'Selected'); $page_tpl->repl('true_backup', null);
$page_tpl->repl('para_backup', $json['option']['backup']['para']);
$page_tpl->repl('datetime_backup', date('Y-m-d\TH:i', $json['option']['backup']['datetime']));
$page_tpl->repl('intervall_backup', $json['option']['backup']['intervall']);


$stamp = $json['option']['update']['datetime'];
$t[0] = date("Y", $stamp);
$t[1] = date("m", $stamp);
$t[2] = date("d", $stamp);
$t[3] = date("H", $stamp);
$t[4] = date("i", $stamp);
for($i=0;$i<6;$i++) {
    $page_tpl->repl("datetime_update[$i]", $t[$i]);
}
if($json['option']['update']['active'] == "true") $page_tpl->repl('true_update', 'Selected'); $page_tpl->repl('true_update', null);
$page_tpl->repl('para_update', $json['option']['update']['para']);
$page_tpl->repl('datetime_update', date('Y-m-d\TH:i', $json['option']['update']['datetime']));
$page_tpl->repl('intervall_update', $json['option']['update']['intervall']);

$page_tpl->repl('list', $jobs);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>