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
$tpl_dir = 'app/template/core/konfig/';
$tpl_dir_all = 'app/template/all/';
$setsidebar = false;
$cfglist = null;
$pagename = "{::lang::php::config::pagename}";
$urltop = "<li class=\"breadcrumb-item\">$pagename</li>";
$syslpath = "remote/steamcmd";
$workshop = "$syslpath/steamapps/workshop/appworkshop_346110.acf";
$limit = $helper->file_to_json("app/json/panel/aas_min.json", true);
$maxi = $helper->file_to_json("app/json/panel/aas_max.json", true);

$ppath = "php/inc/custom_konfig.json";
$apath = "remote/arkmanager/arkmanager.cfg";
$wpath = 'arkadmin_server/config/server.json';
$array = $helper->file_to_json($ppath, true);
if (!isset($array["clusterestart"])) $array["clusterestart"] = 0;
if (!isset($array["uninstall_mod"])) $array["uninstall_mod"] = 0;
if (!isset($array["install_mod"])) $array["install_mod"] = 0;
if (!isset($array["servlocdir"])) $array["servlocdir"] = 0;
if (!isset($array["arklocdir"])) $array["arklocdir"] = null;
if (!isset($array["apikey"])) $array["apikey"] = null;
if (!isset($array["show_err"])) $array["show_err"] = 0;
if (!isset($array["steamcmddir"])) $array["steamcmddir"] = "/home/steam/Steam/";
$helper->savejson_exsists($array, $ppath);

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

// Arkmanager.cfg
if (isset($_POST["savearkmanager"])) {
    $content = ini_save_rdy($_POST["text"]);
    if (file_put_contents($apath, $content)) {
        $alert->code = 102;
        $resp .= $alert->re();
    } else {
        $alert->code = 1;
        $resp .= $alert->re();
    }
}

//remove cache
if (isset($url[2]) && isset($url[3]) && $url[2] == 'clear' && $url[3] == 'steamcmd') {
    if(file_exists($workshop)) {
        file_put_contents($workshop, "\"AppWorkshop\" {}");
        header("Location: /config"); exit;
    }
    else {
        header("Location: /config"); exit;
    }
}

// save Webhelper
if (isset($_POST["savewebhelper"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");

    $allok = true;
    for ($i=0;$i<count($a_key);$i++) {
        if(isset($limit[$a_key[$i]])) {
            if(!(intval($limit[$a_key[$i]]) <= intval($a_value[$i]))) $allok = false;
        }
        $jsons[$a_key[$i]] = $a_value[$i];
    }

    $json_str = $helper->json_to_str($jsons);
    if($allok) {
        if (file_put_contents($wpath, $json_str)) {
            $resp .= $alert->rd(102);
        } else {
            $resp .= $alert->rd(1);
        }
    }
    else {
        $resp .= $alert->rd(2);
    }
}

//Panel CFG
if (isset($_POST["savepanel"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir","steamcmddir");

    for ($i=0;$i<count($a_key);$i++) {
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "1") $a_value[$i] = 1;
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "0") $a_value[$i] = 0;
        if (in_array($a_key[$i], $filter_link)) {
            if ($a_key[$i] == "servlocdir" && readlink("remote/serv") != $a_value[$i]) {
                $loc = "remote/serv";
                if (is_link($loc) || file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            elseif ($a_key[$i] == "arklocdir" && readlink("remote/arkmanager") != $a_value[$i]) {
                $loc = "remote/arkmanager";
                if (is_link($loc) || file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            elseif ($a_key[$i] == "steamcmddir" && (readlink("remote/steamcmd") != $a_value[$i] || !file_exists("remote/steamcmd"))) {
                $loc = "remote/steamcmd";
                if (is_link($loc) || file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            $json[$a_key[$i]] = $a_value[$i];
        } else {
            $json[$a_key[$i]] = $a_value[$i];
        }
    }

    $json_str = $helper->json_to_str($json);
    if (file_put_contents($ppath, $json_str)) {
        $alert->code = 102;
        $resp .= $alert->re();
    } else {
        $alert->code = 1;
        $resp .= $alert->re();
    }
}

$panelconfig = $helper->file_to_json($ppath, true);
$option_panel = null;
foreach($panelconfig as $key => $value) {
    $list = new Template("opt.htm", $tpl_dir);
    $list->load();

    $bool = array("uninstall_mod", "install_mod", "clusterestart", "expert", "show_err");
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
        $list->r("keym", "panel::$key");
    }
    elseif (is_numeric($value)) {
        $list->rif ("ifbool", false);
        $list->rif ("ifnum", true);
        $list->rif ("iftxt", false);
        $list->r("key", $key);
        $list->r("keym", "panel::$key");
        $list->r("value", $value);
    } else {
        $list->rif ("ifbool", false);
        $list->rif ("ifnum", false);
        $list->rif ("iftxt", true);
        $list->r("key", $key);
        $list->r("keym", "panel::$key");
        $list->r("value", $value);
    }
    $option_panel .= $list->load_var();
}

$servercfg = $helper->file_to_json($wpath, true);
if(!isset($servercfg["port"])) $servercfg["port"] = 30000;
if(!isset($servercfg["autorestart"])) $servercfg["autorestart"] = 1;
if(!isset($servercfg["autoupdater_active"])) $servercfg["autoupdater_active"] = 0;
if(!isset($servercfg["autoupdater_branch"])) $servercfg["autoupdater_branch"] = "master";
if(!isset($servercfg["autoupdater_intervall"])) $servercfg["autoupdater_intervall"] = 60000;
if(!isset($servercfg["autorestart_intervall"])) $servercfg["autorestart_intervall"] = 1800000;

$option_server = null;
foreach($servercfg as $key => $value) {
    $list = new Template("opt.htm", $tpl_dir);
    $list->load();
    $list->rif ("ifbool", false);
    $list->rif ("ifnum", is_numeric($value));
    $list->rif ("iftxt", !is_numeric($value));
    $list->rif("ifmin", isset($limit[$key]));
    $list->rif("ifmax", isset($maxi[$key]));
    $list->r("key", $key);
    $list->r("keym", "aa::$key");
    $list->r("value", $value);
    $list->r("min", ((isset($limit[$key])) ? $limit[$key] : 0));
    $list->r("max", ((isset($maxi[$key])) ? $maxi[$key] : 0));
    $option_server .= $list->load_var();
}


// steamcmd
$cachelink = $cachetext = null;
if(file_exists($syslpath) && is_link($syslpath)) {
    $steamcmd_exsists = true;
    $steamcmd_workshop_exsists = file_exists($workshop);
    if($steamcmd_workshop_exsists) {
        $cachelink = '<a href="#spoiler" data-toggle="collapse" data-target="#cache" aria-expanded="false" aria-controls="cache">' . converttime(filemtime($workshop ))  . ' ({::lang::servercenter::config::steamcmd::show})</a>';
        $cachetext = file_get_contents($workshop);
    }
    else {
        $cachelink = '{::lang::servercenter::config::steamcmd::cache_not_exsists}';
    }
}
else {
    $steamcmd_exsists = false;
}


$content_arkmanager = file_get_contents($apath);
$tpl->r("steamcmd_info", (($steamcmd_exsists) ? null : $alert->rd(306, 3, 0, 0, 0, 0)));
$tpl->r("info_CMD", $alert->rd(307, 3, 0, 0, 0, 0));
$tpl->rif("steamcmdsys", $steamcmd_exsists);
$tpl->rif("steamfile", $steamcmd_workshop_exsists);
$tpl->r("cache_link", $cachelink);
$tpl->r("cache_text", $cachetext);
$tpl->r("arkmanager", $content_arkmanager);
$tpl->r("option_panel", $option_panel);
$tpl->r('webhelper', $option_server);
$tpl->r("resp", $resp);

$content = $tpl->load_var();
$pageicon = "<i class=\"fa fa-edit\" aria-hidden=\"true\"></i>";
$btns = null;
?>