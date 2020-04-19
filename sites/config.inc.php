<?php

// Vars
$tpl_dir = 'tpl/konfig/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "Konfiguration";
$urltop = '<li class="breadcrumb-item">Konfiguration</li>';

$ppath = "inc/custom_konfig.json";
$apath = "remote/arkmanager/arkmanager.cfg";
$array = $helper->file_to_json($ppath, true);
if(!isset($array["clusterestart"])) $array["clusterestart"] = 0;
if(!isset($array["uninstall_mod"])) $array["clusterestart"] = 0;
if(!isset($array["install_mod"])) $array["clusterestart"] = 0;
if(!isset($array["servlocdir"])) $array["clusterestart"] = 0;
if(!isset($array["arklocdir"])) $array["arklocdir"] = null;
if(!isset($array["apikey"])) $array["apikey"] = null;
$helper->savejson_exsists($array, $ppath);

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

// Arkmanager.cfg
if(isset($_POST["savearkmanager"])) {
    $content = ini_save_rdy($_POST["text"]);
    if(file_put_contents($apath, $content)) {
        $resp = meld('success', 'Arkmanager Konfiguration gespeichert.', 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Arkmanager Konfiguration <b>NICHT</b> gespeichert.', 'Fehler!', null);
    }
}


//Panel CFG
if(isset($_POST["savepanel"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");

    for($i=0;$i<count($a_key);$i++) {
        if(in_array($a_key[$i], $filter_bool) && $a_value[$i] == "1") $a_value[$i] = 1;
        if(in_array($a_key[$i], $filter_bool) && $a_value[$i] == "0") $a_value[$i] = 0;
        if(in_array($a_key[$i], $filter_link)) {
            if($a_key[$i] == "servlocdir" && readlink("remote/serv") != $a_value[$i]) {
                $loc = "remote/serv";
                if(file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            elseif($a_key[$i] == "arklocdir" && readlink("remote/arkmanager") != $a_value[$i]) {
                $loc = "remote/arkmanager";
                if(file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            $json[$a_key[$i]] = $a_value[$i];
        }
        else {
            $json[$a_key[$i]] = $a_value[$i];
        }
    }

    $json_str = $helper->json_to_str($json);
    if(file_put_contents($ppath, $json_str)) {
        $resp = meld('success', 'Panel Konfiguration gespeichert.', 'Erfolgreich!', null);
    }
    else {
        $resp = meld('danger', 'Panel Konfiguration <b>NICHT</b> gespeichert.', 'Fehler!', null);
    }
}

$panelconfig = $helper->file_to_json($ppath, true);
$option_panel = null;
foreach($panelconfig as $key => $value) {
    $list = new Template("opt.htm", $tpl_dir);
    $list->load();

    $find = array(
        "uninstall_mod",
        "install_mod",
        "clusterestart",
        "servlocdir",
        "arklocdir",
        "apikey"
    );
    $repl = array(
        "Deinstalliere Mods beim Entfernen",
        "Installiere Mods beim Hinzufügen",
        "Starte Clusterserver neu wenn Optionen geändert werden",
        "Server-verzeichnis",
        "Arkmanager-verzeichnis",
        "Steam-API Key <a href='https://steamcommunity.com/dev/apikey' target='_blank'>(finde ich Hier)</a>"
    );

    $bool = array("uninstall_mod", "install_mod", "clusterestart");
    if(in_array($key, $bool)) {
        $list->replif("ifbool", true);
        $list->replif("ifnum", false);
        $list->replif("iftxt", false);
        if($value == 1) {
            $list->repl("true", "selected");
        }
        else {
            $list->repl("true", "null");
        }
        $list->repl("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->repl("keym", $key);
    }
    elseif(is_numeric($value)) {
        $list->replif("ifbool", false);
        $list->replif("ifnum", true);
        $list->replif("iftxt", false);
        $list->repl("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->repl("keym", $key);
        $list->repl("value", $value);
    }
    else {
        $list->replif("ifbool", false);
        $list->replif("ifnum", false);
        $list->replif("iftxt", true);
        $list->repl("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->repl("keym", $key);
        $list->repl("value", $value);
    }
    $option_panel .= $list->loadin();
}

$content_arkmanager = file_get_contents($apath);
$tpl->repl("arkmanager", $content_arkmanager);
$tpl->repl("option_panel", $option_panel);
$tpl->repl("resp", $resp);

$content = $tpl->loadin();
$pageicon = "<i class=\"fa fa-edit\" aria-hidden=\"true\"></i>";
$btns = null;
?>