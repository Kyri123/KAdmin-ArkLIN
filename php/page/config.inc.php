<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Vars
$tpl_dir = 'app/template/konfig/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "{::lang::php::config::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";

$ppath = "php/inc/custom_konfig.json";
$apath = "remote/arkmanager/arkmanager.cfg";
$wpath = 'java/config.properties';
$array = $helper->file_to_json($ppath, true);
if (!isset($array["clusterestart"])) $array["clusterestart"] = 0;
if (!isset($array["uninstall_mod"])) $array["uninstall_mod"] = 0;
if (!isset($array["install_mod"])) $array["install_mod"] = 0;
if (!isset($array["servlocdir"])) $array["servlocdir"] = 0;
if (!isset($array["arklocdir"])) $array["arklocdir"] = null;
if (!isset($array["apikey"])) $array["apikey"] = null;
$helper->savejson_exsists($array, $ppath);

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

// Arkmanager.cfg
if (isset($_POST["savearkmanager"])) {
    $content = ini_save_rdy($_POST["text"]);
    if (file_put_contents($apath, $content)) {
        $alert->code = 102;
        $resp = $alert->re();
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

// Arkmanager.cfg
if (isset($_POST["savewebhelper"])) {
    $content = ini_save_rdy($_POST["text"]);
    if (file_put_contents($wpath, $content)) {
        $alert->code = 102;
        $resp = $alert->re();
    } else {
        $alert->code = 1;
        $resp = $alert->re();
    }
}

//Panel CFG
if (isset($_POST["savepanel"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");

    for ($i=0;$i<count($a_key);$i++) {
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "1") $a_value[$i] = 1;
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "0") $a_value[$i] = 0;
        if (in_array($a_key[$i], $filter_link)) {
            if ($a_key[$i] == "servlocdir" && readlink("remote/serv") != $a_value[$i]) {
                $loc = "remote/serv";
                if (file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            elseif ($a_key[$i] == "arklocdir" && readlink("remote/arkmanager") != $a_value[$i]) {
                $loc = "remote/arkmanager";
                if (file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            $json[$a_key[$i]] = $a_value[$i];
        } else {
            $json[$a_key[$i]] = $a_value[$i];
        }
    }

    $json_str = $helper->json_to_str($json);
    if (file_put_contents($ppath, $json_str)) {
        $alert->code = 102;
        $resp = $alert->re();
    } else {
        $alert->code = 1;
        $resp = $alert->re();
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
        "{::lang::php::config::key::uninstallmod}",
        "{::lang::php::config::key::installmod}",
        "{::lang::php::config::key::clusterestart}",
        "{::lang::php::config::key::servlocdir}",
        "{::lang::php::config::key::arklocdir}",
        "Steam-API Key <a href='https://steamcommunity.com/dev/apikey' target='_blank'>({::lang::php::config::key::apikey_found_here})</a>"
    );

    $bool = array("uninstall_mod", "install_mod", "clusterestart", "expert");
    if (in_array($key, $bool)) {
        $list->rif ("ifbool", true);
        $list->rif ("ifnum", false);
        $list->rif ("iftxt", false);
        if ($value == 1) {
            $list->r("true", "selected");
        } else {
            $list->r("true", "null");
        }
        $list->r("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->r("keym", $key);
    }
    elseif (is_numeric($value)) {
        $list->rif ("ifbool", false);
        $list->rif ("ifnum", true);
        $list->rif ("iftxt", false);
        $list->r("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->r("keym", $key);
        $list->r("value", $value);
    } else {
        $list->rif ("ifbool", false);
        $list->rif ("ifnum", false);
        $list->rif ("iftxt", true);
        $list->r("key", $key);
        $key = str_replace($find, $repl, $key);
        $list->r("keym", $key);
        $list->r("value", $value);
    }
    $option_panel .= $list->load_var();
}

$content_arkmanager = file_get_contents($apath);
$tpl->r("arkmanager", $content_arkmanager);
$tpl->r("option_panel", $option_panel);
$tpl->r('webhelper', file_get_contents($wpath));
$tpl->r("resp", $resp);

$content = $tpl->load_var();
$pageicon = "<i class=\"fa fa-edit\" aria-hidden=\"true\"></i>";
$btns = null;
?>