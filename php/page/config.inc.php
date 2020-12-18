<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if(!$session_user->perm("config/show")) {
    header("Location: /401"); exit;
}

// Vars
$tpl_dir            = __ADIR__.'/app/template/core/konfig/';
$tpl_dir_all        = __ADIR__.'/app/template/all/';
$setsidebar         = false;
$cfglist            = null;
$pagename           = "{::lang::php::config::pagename}";
$urltop             = "<li class=\"breadcrumb-item\">$pagename</li>";
$syslpath           = __ADIR__."/remote/steamcmd";
$workshop           = "$syslpath/steamapps/workshop/appworkshop_346110.acf";
$resp               = null;
$limit              = $helper->fileToJson(__ADIR__."/app/json/panel/aas_min.json", true);
$maxi               = $helper->fileToJson(__ADIR__."/app/json/panel/aas_max.json", true);

$API_path           = __ADIR__."/php/inc/api.json";
$ppath              = __ADIR__."/php/inc/custom_konfig.json";
$apath              = __ADIR__."/remote/arkmanager/arkmanager.cfg";
$wpath              = __ADIR__.'/arkadmin_server/config/server.json';
$tpath              = __ADIR__.'/app/data/template.cfg';
$array              = $helper->fileToJson($ppath, true);

if (!isset($array["clusterestart"]))    $array["clusterestart"]     = 0;
if (!isset($array["uninstall_mod"]))    $array["uninstall_mod"]     = 0;
if (!isset($array["install_mod"]))      $array["install_mod"]       = 0;
if (!isset($array["servlocdir"]))       $array["servlocdir"]        = 0;
if (!isset($array["arklocdir"]))        $array["arklocdir"]         = null;
if (!isset($array["apikey"]))           $array["apikey"]            = null;
if (!isset($array["show_err"]))         $array["show_err"]          = 0;
if (!isset($array["steamcmddir"]))      $array["steamcmddir"]       = "/home/steam/Steam/";
$helper->saveFile($array, $ppath);

//Erstelle wenn nicht vorhanden API Datei mit inhalt
if(!file_exists($API_path)) {
    $API_array = array(
        "active" => 0,
        "key" => md5(rndbit(20))
    );
    $helper->saveFile($API_array, $API_path);
}
else {
    $API_array = $helper->fileToJson($API_path, true);
}

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();

// Arkmanager.cfg
if (isset($_POST["savearkmanager"]) && $session_user->perm("config/am_save")) {
    $content    = ini_save_rdy($_POST["text"]);
    if ($KUTIL->filePutContents($apath, $content)) {
        $resp   .= $alert->rd(102);
    } else {
        $resp   .= $alert->rd(1);
    }
}
elseif(isset($_POST["savearkmanager"])) {
    $resp .= $alert->rd(99);
}

// Template.cfg
if (isset($_POST["savetemplate"]) && $session_user->perm("config/edit_default")) {
    $content    = ini_save_rdy($_POST["text"]);
    if ($KUTIL->filePutContents($tpath, $content)) {
        $resp   .= $alert->rd(102);
    } else {
        $resp   .= $alert->rd(1);
    }
}
elseif(isset($_POST["savetemplate"])) {
    $resp .= $alert->rd(99);
}

//remove cache
if (isset($url[3]) && $url[2] == 'clear' && $url[3] == 'steamcmd' && $session_user->perm("config/scmd_clear")) {
    if(@file_exists($workshop)) {
        $KUTIL->filePutContents($workshop, "\"AppWorkshop\" {}");
        header("Location: /config"); exit;
    }
    else {
        header("Location: /config"); exit;
    }
}
elseif(isset($url[3]) && $url[2] == 'clear' && $url[3] == 'steamcmd') {
    $resp .= $alert->rd(99);
}

// save Webhelper
if (isset($_POST["savewebhelper"]) && $session_user->perm("config/aa_save")) {
    $a_key          = $_POST["key"];
    $a_value        = $_POST["value"];
    $filter_bool    = [
        "install_mod",
        "uninstall_mod"
    ];
    $filter_link    = [
        "servlocdir",
        "arklocdir"
    ];

    // Prüfe minimalwerte
    $allok = true;
    for ($i=0;$i<count($a_key);$i++) {
        if(isset($limit[$a_key[$i]])) if(!(intval($limit[$a_key[$i]]) <= intval($a_value[$i]))) $allok = false;
        $jsons[$a_key[$i]] = $a_value[$i];
    }

    // Teste Endwerte
    $check = array(
        "WebPath",
        "AAPath",
        "ServerPath",
        "SteamPath"
    );
    foreach ($check as $ITEM) if(substr($jsons[$ITEM], -1) == "/") $jsons[$ITEM] = substr($jsons[$ITEM], 0, -1);
    if(substr($jsons["HTTP"], -1) != "/") $jsons["HTTP"] .= "/";

    // Speichern
    $json_str       = $helper->jsonToString($jsons);
    if($allok) {
        if ($KUTIL->filePutContents($wpath, $json_str)) {
            $resp   .= $alert->rd(102);
        } else {
            $resp   .= $alert->rd(1);
        }
    }
    else {
        $resp       .= $alert->rd(2);
    }
}
elseif(isset($_POST["savewebhelper"])) {
    $resp           .= $alert->rd(99);
}

//Panel CFG
if (isset($_POST["savepanel"]) && $session_user->perm("config/panel_save")) {
    $a_key          = $_POST["key"];
    $a_value        = $_POST["value"];
    $filter_bool    = [
        "install_mod",
        "uninstall_mod"
    ];
    $filter_link = [
        "servlocdir",
        "arklocdir",
        "steamcmddir"
    ];
    $json           = $helper->fileToJson($ppath, true);
    $check          = [
        "servlocdir",
        "arklocdir",
        "steamcmddir"
    ];

    for ($i=0;$i<count($a_key);$i++) {
        if (in_array($a_key[$i], $check))if(substr($a_value[$i], -1) != "/")  $a_value[$i]    .= "/";
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "1")              $a_value[$i]    = 1;
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "0")              $a_value[$i]    = 0;
        if (in_array($a_key[$i], $filter_link)) {
            if ($a_key[$i] == "servlocdir" && readlink(__ADIR__."/remote/serv") != $a_value[$i]) {
                $loc = __ADIR__."/remote/serv";
                if (is_link($loc) || @file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            elseif ($a_key[$i] == "arklocdir" && readlink(__ADIR__."/remote/arkmanager") != $a_value[$i]) {
                $loc = __ADIR__."/remote/arkmanager";
                if (is_link($loc) || @file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            elseif ($a_key[$i] == "steamcmddir" && (readlink(__ADIR__."/remote/steamcmd") != $a_value[$i] || !file_exists(__ADIR__."/remote/steamcmd"))) {
                $loc = __ADIR__."/remote/steamcmd";
                if (is_link($loc) || @file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                $resp .= (!symlink($target, $loc)) ? $alert->rd(30, 1) : null;
            }
            $json[$a_key[$i]]   = $a_value[$i];
        } else {
            $json[$a_key[$i]]   = $a_value[$i];
        }
    }

    $json_str = $helper->jsonToString($json);
    $resp .= $alert->rd($KUTIL->filePutContents($ppath, $json_str) ? 102 : 1);
}
elseif(isset($_POST["savepanel"])) {
    $resp .= $alert->rd(99);
}

$panelconfig = $helper->fileToJson($ppath, true);
$option_panel = null;
foreach($panelconfig as $key => $value) {
    $list   = new Template("opt.htm", $tpl_dir);
    $list->load();

    $ro     = null;
    $bool   = [
        "uninstall_mod",
        "install_mod",
        "clusterestart",
        "expert",
        "show_err"
    ];

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
    $list->r("readonly", $ro);
    $option_panel .= $list->load_var();
}

$servercfg = $helper->fileToJson($wpath, true);
if(!isset($servercfg["port"]))                  $servercfg["port"]                      = 30000;
if(!isset($servercfg["autorestart"]))           $servercfg["autorestart"]               = 1;
if(!isset($servercfg["autoupdater_active"]))    $servercfg["autoupdater_active"]        = 0;
if(!isset($servercfg["autoupdater_branch"]))    $servercfg["autoupdater_branch"]        = "master";
if(!isset($servercfg["autoupdater_intervall"])) $servercfg["autoupdater_intervall"]     = 60000;
if(!isset($servercfg["autorestart_intervall"])) $servercfg["autorestart_intervall"]     = 1800000;
if(!isset($servercfg["screen"]))                $servercfg["screen"]                    = "ArkAdmin";

$option_server = null;
foreach($servercfg as $key => $value) {
    $list   = new Template("opt.htm", $tpl_dir);
    $list->load();

    $ro     = null;
    if($key == "WebPath") {
        $value  = __ADIR__;
        $ro     = "readonly";
    }

    $list->rif ("ifbool", false);
    $list->rif ("ifnum", is_numeric($value));
    $list->rif ("iftxt", !is_numeric($value));
    $list->rif("ifmin", isset($limit[$key]));
    $list->rif("ifmax", isset($maxi[$key]));
    $list->r("readonly", $ro);
    $list->r("key", $key);
    $list->r("keym", "aa::$key");
    $list->r("value", $value);
    $list->r("min", ((isset($limit[$key])) ? $limit[$key] : 0));
    $list->r("max", ((isset($maxi[$key])) ? $maxi[$key] : 0));
    $option_server .= $list->load_var();
}


// steamcmd
$cachelink = $cachetext = null;
if(@file_exists($syslpath) && is_link($syslpath)) {
    $steamcmd_exsists           = true;
    $steamcmd_workshop_exsists  = @file_exists($workshop);
    if($steamcmd_workshop_exsists) {
        $cachelink              = '<a href="#spoiler" data-toggle="collapse" data-target="#cache" aria-expanded="false" aria-controls="cache">' . converttime(filemtime($workshop ))  . ' ({::lang::servercenter::config::steamcmd::show})</a>';
        $cachetext              = $KUTIL->fileGetContents($workshop);
    }
    else {
        $cachelink              = '{::lang::servercenter::config::steamcmd::cache_not_exsists}';
    }
}
else {
    $steamcmd_exsists           = false;
}

$tpl->r("steamcmd_info", (($steamcmd_exsists) ? null : $alert->rd(306, 3, 0, 0, 0, 0)));
$tpl->r("info_CMD", $alert->rd(307, 3, 0, 0, 0, 0));
$tpl->r("cache_link", $cachelink);
$tpl->r("cache_text", $cachetext);
$tpl->r("arkmanager", (@file_exists($apath)) ? $KUTIL->fileGetContents($apath) : "");
$tpl->r("templatecfg", (@file_exists($tpath)) ? $KUTIL->fileGetContents($tpath) : "");
$tpl->r("option_panel", $option_panel);
$tpl->r('webhelper', $option_server);
$tpl->r("resp", $resp);
$tpl->r("API_KEY", $API_array["key"]);
$tpl->r("website", (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST']);



$tpl->rif("API_ACTIVE", boolval($API_array["active"]));
$tpl->rif("steamcmdsys", $steamcmd_exsists);
$tpl->rif("steamfile", $steamcmd_workshop_exsists);

$content = $tpl->load_var();
$pageicon = "<i class=\"fa fa-edit\" aria-hidden=\"true\"></i>";
$btns = null;