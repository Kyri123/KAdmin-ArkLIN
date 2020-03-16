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

if(isset($_POST['setbackup'])) {
    $intervall = $_POST['intervall'];
    $datetime = strtotime($_POST['datetime']);
    $json = $helper->file_to_json($cpath, true);
    if($intervall != null && is_numeric($intervall) && $intervall > 0) {
        if($datetime != null && $datetime > time()) {
            $json['option']['backup']['active'] = $_POST['active'];
            $json['option']['backup']['para'] = $_POST['para'];
            $json['option']['backup']['datetime'] = $datetime;
            $json['option']['backup']['intervall'] = $intervall;
            $json['option']['backup']['para'] = $_POST['setbackup'];
            if($helper->savejson_exsists($json, $cpath)) {
                $resp = meld('success', 'Backups Aktualisiert.', 'Erfolgreich!', null);
            }
            else {
                $resp = meld('danger', 'Konnte Datei nicht speichern.', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Intervall hat kein gültiges Format oder ist Leer.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Intervall hat kein gültiges Format, Ist kleiner als 0 oder ist Leer.', 'Fehler!', null);
    }
}

if(isset($_POST['setupdate'])) {
    $intervall = $_POST['intervall'];
    $datetime = strtotime($_POST['datetime']);
    $json = $helper->file_to_json($cpath, true);
    if($intervall != null && is_numeric($intervall) && $intervall > 0) {
        if($datetime != null && $datetime > time()) {
            $json['option']['update']['active'] = $_POST['active'];
            $json['option']['update']['para'] = $_POST['para'];
            $json['option']['update']['datetime'] = $datetime;
            $json['option']['update']['intervall'] = $intervall;
            $json['option']['update']['para'] = $_POST['setbackup'];
            if($helper->savejson_exsists($json, $cpath)) {
                $resp = meld('success', 'Updates Aktualisiert.', 'Erfolgreich!', null);
            }
            else {
                $resp = meld('danger', 'Konnte Datei nicht speichern.', 'Fehler!', null);
            }
        }
        else {
            $resp = meld('danger', 'Intervall hat kein gültiges Format oder ist Leer.', 'Fehler!', null);
        }
    }
    else {
        $resp = meld('danger', 'Intervall hat kein gültiges Format, Ist kleiner als 0 oder ist Leer.', 'Fehler!', null);
    }
}

if(isset($_POST['addjob'])) {
    $json = $helper->file_to_json($cpath, true);
    $i = count($json['jobs']);
    $name = $_POST['name'];
    $action = $_POST['action'];
    $parameter = $_POST['parameter'];
    $intervall = $_POST['intervall'];
    $datetime = $_POST['datetime'];
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
    // $resp = meld('success mb-4', 'Arkmanger.cfg wurde gespeichert!', 'Gespeichert', 'fas fa-check', 'fas fa-exclamation-circle');
}
if(isset($url[4]) && isset($url[5]) && $url[4] == "delete") {
    $i = $url[5];
    $json = $helper->file_to_json($cpath, true);
    if(isset($json['jobs'][$i])) {
        unset($json['jobs'][$i]);
        if($helper->savejson_exsists($json, $cpath)) {
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


$json = null; $jobs = null;
$json = $helper->file_to_json($cpath, true);
if(count($json['jobs']) > 0) {
    for($z=0;$z<count($json['jobs']);$z++) {
        $list = new Template('list_jobs.htm', 'tpl/serv/sites/list/');
        $list->load();
        $list->replif('empty', true);
        $list->repl('title', $json['jobs'][$z]['name']);
        $list->repl('action', $json['jobs'][$z]['action']);
        $list->repl('parameter', $json['jobs'][$z]['parameter']);
        $list->repl('intervall', $json['jobs'][$z]['intervall']);
        $list->repl('cfg', $serv->show_name());
        $list->repl('i', $z);
        $list->repl('datetime', date('d.m.Y - H:i', $json['jobs'][$z]['datetime']));
        $jobs .= $list->loadin();
    }
}
else {
    $list = new Template('list_jobs.htm', 'tpl/serv/sites/list/');
    $list->load();
    $list->replif('empty', false);
    $list->repl('title', 'Keine Jobs gefunden!');
    $jobs .= $list->loadin();
}

if($json['option']['backup']['active'] == "true") $page_tpl->repl('true_backup', 'Selected'); $page_tpl->repl('true_backup', null);
$page_tpl->repl('para_backup', $json['option']['backup']['para']);
$page_tpl->repl('datetime_backup', date('Y-m-d\TH:i', $json['option']['backup']['datetime']));
$page_tpl->repl('intervall_backup', $json['option']['backup']['intervall']);
if($json['option']['update']['active'] == "true") $page_tpl->repl('true_backup', 'Selected'); $page_tpl->repl('true_backup', null);
$page_tpl->repl('para_update', $json['option']['update']['para']);
$page_tpl->repl('datetime_backup', date('Y-m-d\TH:i', $json['option']['update']['datetime']));
$page_tpl->repl('intervall_update', $json['option']['update']['intervall']);

$page_tpl->repl('list', $jobs);
$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>