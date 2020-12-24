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

// Loadtime
$stime = microtime(true);
define("__ADIR__", __DIR__);

// start SessionS
session_start();

// check install
if(isset($_GET["mod_rewrite"])) {
    echo array_key_exists('HTTP_MOD_REWRITE', $_SERVER) ? '{"HTTP_MOD_REWRITE":"on"}' : '{}';
    exit;
}
if (!file_exists(__ADIR__."/app/check/done")) {
    header('Location: /install.php');
    exit;
}

// Konfig
include(__ADIR__.'/php/inc/config.inc.php');

// Inz KUTIL
include(__ADIR__.'/php/class/KUtil.class.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');

$helper     = new helper();
$ckonfig    = $helper->fileToJson(__ADIR__.'/php/inc/custom_konfig.json', true);

// Deaktiviere Error anzeige
ini_set('display_errors',           ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
ini_set('display_startup_errors',   ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
if(isset($ckonfig["show_err"]))           error_reporting(E_ALL);

// Einige Einstellungen KUTIL und default VARS
$site_name                  = $content = null;
$KUTIL->replacePathFrom     = [
    __ADIR__."/remote/serv/",
    __ADIR__."/remote/arkmanager/",
    __ADIR__."/remote/steamcmd/"
];
$KUTIL->replacePathTo   = [
    $ckonfig["servlocdir"],
    $ckonfig["arklocdir"],
    $ckonfig["steamcmddir"]
];

// Entfernt Installer wenn der Vorgang abgeschlossen ist
if(@file_exists(__ADIR__."/app/check/done")) $KUTIL->removeFile(__ADIR__."/install.php", false);
if(@file_exists(__ADIR__."/app/check/done")) $KUTIL->removeFile(__ADIR__."/install");

// erzeuge Default Permissions FILE
$all                            = $helper->fileToJson(__ADIR__."/app/json/serverinfo/all.json");
$D_PERM_ARRAY                   = $helper->fileToJson(__ADIR__."/app/json/user/permissions.tpl.json");
$server                         = $all["cfgs_only_name"];
foreach ($server as $item) {
    $perm_file                  = $KUTIL->fileGetContents(__ADIR__."/app/json/user/permissions_servers.tpl.json");
    $perm_file                  = str_replace("{cfg}", $item, $perm_file);
    $default                    = $helper->stringToJson($perm_file);
    $D_PERM_ARRAY["server"]     += $default;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename       = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar     = $g_alert_bool = false;

// Löse URL auf
$ROOT           = str_replace("index.php", null, $_SERVER["SCRIPT_NAME"]);
$ROOT           = substr($ROOT, 0, -1);

$REQUEST_URI    = str_replace($ROOT, null, $_SERVER["REQUEST_URI"]);
$ROOT_TPL       = __DIR__."/app/template";

$PAGE_EXP       = $REQUEST_URI != "" ? explode("/", $REQUEST_URI) : array();
$page           = isset($url[1]) ? $url[1] : "home";


// API
$API_path       = __ADIR__."/php/inc/api.json";
$API_array      = $helper->fileToJson($API_path);
$API_ACTIVE     = boolval($API_array["active"]);
$API_KEY        = $API_array["key"];

// read URL
$surl           = $_SERVER["REQUEST_URI"];
$url            = $PAGE_EXP;
if ($url[1] == "" || $url[1] == "favicon.ico") {
    header("Location: $ROOT/home");
    exit;
}

// Connent to MYSQL
include(__ADIR__.'/php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);

// Update last login
if (isset($_SESSION["id"])) {
    $mycon->query('UPDATE `ArkAdmin_users` SET `lastlogin`=? WHERE `id`=?', time(), $_SESSION["id"]);
}

// Logge aus wenn nötig
if($url[1] == "logout") {
    if (isset($_COOKIE["id"]) && isset($_COOKIE["validate"])) {
        $query = "DELETE FROM `ArkAdmin_user_cookies` WHERE (`validate`=?)";
        $mycon->query($query, $_COOKIE["validate"]);
        setcookie("id", "", time() - 3600);
        setcookie("validate", "", time() - 3600);
    }

    session_destroy();
    header("Location: $ROOT/login");
    exit;
}

// Updater
$check_json = $helper->fileToJson(__ADIR__."/app/data/sql_check.json");
if($mycon->is && !$check_json["checked"]) include(__ADIR__.'/php/inc/auto_update_sql_DB.inc.php');

// Include functions
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// include classes
include(__ADIR__.'/php/class/xml_helper.class.php');
include(__ADIR__.'/php/class/user.class.inc.php');

$session_user = new userclass();
if (isset($_SESSION["id"])) {
    $session_user->setid($_SESSION["id"]);
}

include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/jobs.class.inc.php');

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');

//create class_var
$alert      = new alert();
$steamapi   = new steamapi();
$user       = new userclass();
if(isset($_SESSION["id"])) {
    $user->setid($_SESSION['id']);

    //Prüfe ob der Benutzer gebant ist
    if ($user->read("ban") > 0) {
        $query = "DELETE FROM `ArkAdmin_user_cookies` WHERE (`userid`=?)";
        $mycon->query($query, $_SESSION["id"]);
        session_destroy();
    }

    // Prüfe ob der Nutzer noch exsistiert
    $query = "SELECT * FROM `ArkAdmin_users` WHERE (`id`=?)";
    if (!($mycon->query($query, $_SESSION["id"])->numRows() > 0)) {
        session_destroy();
        header("Location: /login");
        exit;
    }

    // Erfasse IP
    $path = __ADIR__."/app/json/user/".md5($_SESSION["id"]).".json";
    $KUTIL->createFile($path, "{}") ? null : null;
    if(@file_exists($path)) {
        $json           = $helper->fileToJson($path, true);
        $json["ip"]     = getRealIpAddr();
        $json["id"]     = $session_user->read("id");
        $helper->saveFile($json, $path);
    }
}

// Allgemein SteamAPI Arrays
$steamapi_mods  = (@file_exists(__ADIR__."/app/json/steamapi/mods.json")) ? $helper->fileToJson(__ADIR__."/app/json/steamapi/mods.json", true) : array();
$steamapi_user  = (@file_exists(__ADIR__."/app/json/steamapi/user.json")) ? $helper->fileToJson(__ADIR__."/app/json/steamapi/user.json", true) : array();

// include util
include(__ADIR__.'/php/inc/session.inc.php');

//create globals vars
$API_Key        = $ckonfig['apikey'];
$servlocdir     = $ckonfig['servlocdir'];
$expert         = isset($_SESSION["id"]) ? $user->expert() : false;
$jobs           = new jobs();

// Define default page
$page           = $url[1];

if (@file_exists(__ADIR__.'/php/page/'.$page.'.inc.php')) {
    include(__ADIR__.'/php/page/'.$page.'.inc.php');
} else {
    header("Location: /404");
    exit;
}

// Website
// Load template
$tpl_h = new Template("head.htm", __ADIR__."/app/template/core/index/");
$tpl_h->load();

$tpl_b = new Template("body.htm", __ADIR__."/app/template/core/index/");
$tpl_b->load();

$tpl_f = new Template("foother.htm", __ADIR__."/app/template/core/index/");
$tpl_f->load();

// lade Global_Alerts
include(__ADIR__.'/php/inc/global_alert.inc.php');

// Include
include(__ADIR__.'/php/inc/server.inc.php');
include(__ADIR__.'/php/inc/nav_curr.inc.php');

// Define pagename for login & registration
if($page == "login" || $page == "registration") $pagename = ($page == "login") ? '{::lang::php::index::pagename_login}' : '{::lang::php::index::pagename_reg}';
if($session_user->perm("all/manage_aas")) $btns .= '
    <div class="dropdown d-inline">
        <a class="btn btn-outline-secondary rounded-0 dropdown-toggle " href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <i class="fa fa-server"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
            <a href="http://'.$ip.':'.$webserver['config']['port'].'/update/'.md5($ip).'?md5='.md5($_SESSION["id"]).'" target="_blank" class="dropdown-item text-dark">
                <span class="icon">
                    <i class="fa fa-cloud-download"></i>
                </span>
                <span class="pl-2">{::lang::allg::nav::btn_update}</span>
            </a>
            <a href="http://'.$ip.':'.$webserver['config']['port'].'/restart/'.md5($ip).'?md5='.md5($_SESSION["id"]).'" target="_blank" class="dropdown-item text-dark">
                <span class="icon">
                    <i class="fas fa-undo"></i>
                </span>
                <span class="pl-2">{::lang::allg::nav::btn_restart}</span>
            </a>
            <a href="http://'.$ip.':'.$webserver['config']['port'].'/log?md5='.md5($_SESSION["id"]).'" target="_blank" class="dropdown-item text-dark">
                <span class="icon">
                    <i class="fas fa-list"></i>
                </span>
                <span class="pl-2">{::lang::allg::nav::btn_log}</span>
            </a>
        </div>
    </div>
';

// replace
$tpl_h->r('time', time());

$tpl_h->r("ROOT", $ROOT);
$tpl_b->r("ROOT", $ROOT);
$tpl_f->r("ROOT", $ROOT);

$path_webhelper = __ADIR__."/app/check/webhelper";
$tpl_b->r('pagename', $pagename);
$tpl_b->r('pageicon', $pageicon);
$tpl_h->r('pagename', $pagename);
$tpl_h->rif('darkmode', isset($_COOKIE["style"]) ? $_COOKIE["style"] == "dark" : false);
$tpl_b->r('aa_version', $version);
$tpl_b->r('lastcheck_webhelper', converttime(((@file_exists($path_webhelper)) ? intval($KUTIL->fileGetContents($path_webhelper)) : time()), true));
$tpl_b->r('user', isset($_SESSION["id"]) ? $user->read("username") : "Not logged in");
$tpl_b->r('content', $content);
$tpl_b->r('site_name', $site_name);
$tpl_b->r('btns', "<div class=\"d-sm-inline-block\">$btns</div>");
$tpl_b->r('urltop', $urltop);
$tpl_b->r('g_alert', $g_alert);
$tpl_b->rif ('if_g_alert', $g_alert_bool);
$tpl_b->r("langlist", get_lang_list());
$tpl_b->r("maxserver", $maxpanel_server);
$tpl_b->r("rank", "<span class='text-".((!$session_user->perm("allg/is_admin")) ? "success" : "danger")."'>{::lang::php::userpanel::".((!$session_user->perm("allg/is_admin")) ? "user" : "admin")."}</span>");

// Server Traffics
$tpl_b->r('count_server', count($all["cfgs"]));
$tpl_b->r('curr_server', $all["onserv"]);
$tpl_b->r('off_server', (count($all["cfgs"]) - $all["onserv"]));
$tpl_b->r('count_server_perc', perc($all["onserv"], count($all["cfgs"])));
$tpl_b->r('cpu_perc', cpu_perc());
$tpl_b->r('free', bitrechner(disk_free_space(__ADIR__."/remote/serv"), "B", "GB"));
$tpl_b->r('free_max', bitrechner(disk_total_space(__ADIR__."/remote/serv"), "B", "GB"));
$tpl_b->r('ram_used', str_replace("MB", "GB", bitrechner(mem_array()[1], "B", "GB")));
$tpl_b->r('ram_max', str_replace("MB", "GB", bitrechner(mem_array()[0], "B", "GB")));
$tpl_b->r('ram_perc', mem_perc());
$tpl_b->r('free_perc', (100 - perc(disk_free_space(__ADIR__."/remote/serv"), disk_total_space(__ADIR__."/remote/serv"))));
$tpl_b->r('perc_on', perc(1, 9));
$ifnot_traffic = false;
$check = array("changelog", "404");
if (in_array($page, $check)) $ifnot_traffic = true;
$tpl_b->rif ("ifchangelog", $ifnot_traffic);

$tpl_b->r("ltime", round((microtime(true) - $stime), 2));

// Site Builder
$isNotLoggedIn = [
    "login",
    "registration",
    "crontab"
];

if($page === "crontab") {
    $tpl_h->echo();
    $tpl_crontab->echo();
    $tpl_f->echo();
}
elseif(in_array($page, $isNotLoggedIn) && isset($_SESSION['id'])) {
    header("location: $ROOT/home");
    exit;
}
elseif(!in_array($page, $isNotLoggedIn) && !isset($_SESSION['id'])) {
    header("location: $ROOT/login");
    exit;
}
elseif(in_array($page, $isNotLoggedIn) && !isset($_SESSION['id'])) {
    $tpl_h->echo();
    if($page == "login") {
        $tpl_login->r("ROOT", $ROOT);
        $tpl_login->r("langlist", get_lang_list());
        $tpl_login->echo();
    }
    elseif($page == "registration") {
        $tpl_register->r("ROOT", $ROOT);
        $tpl_register->r("langlist", get_lang_list());
        $tpl_register->echo();
    }
    else {
        $tpl_h->echo();
        $tpl_crontab->echo();
        $tpl_f->echo();
    }
}
else {
    $tpl_h->echo();
    $tpl_b->echo();
    $tpl_f->echo();
}

//close mysql
$mycon->close();