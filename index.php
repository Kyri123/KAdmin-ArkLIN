<?php /** @noinspection ALL */
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

define("__ADIR__", __DIR__);

// hide errors
$stime = microtime(true);
include(__ADIR__.'/php/inc/config.inc.php');
include(__ADIR__.'/php/class/helper.class.inc.php');
$helper = new helper();
$ckonfig = $helper->file_to_json(__ADIR__.'/php/inc/custom_konfig.json', true);
$site_name = $content = null;

// Deaktiviere Error anzeige
ini_set('display_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
ini_set('display_startup_errors', ((isset($ckonfig["show_err"])) ? $ckonfig["show_err"] : 0));
if(isset($ckonfig["show_err"])) error_reporting(E_ALL);

//check install
if (!file_exists(__ADIR__."/app/check/subdone")) {
    header('Location: /install.php');
    exit;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar = $g_alert_bool = false;

//start SessionS
session_start();

// Löse URL auf
$ROOT           = str_replace("index.php", null, $_SERVER["SCRIPT_NAME"]);
$ROOT           = substr($ROOT, 0, -1);

$REQUEST_URI    = str_replace($ROOT, null, $_SERVER["REQUEST_URI"]);
$ROOT_TPL       = __DIR__."/app/template";

$PAGE_EXP       = $REQUEST_URI != "" ? explode("/", $REQUEST_URI) : array();

$page           = isset($PAGE_EXP[0]) ? $PAGE_EXP[0] : "home";

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

$check_json = $helper->file_to_json(__ADIR__."/app/data/sql_check.json");
if($mycon->is && !$check_json["checked"]) include(__ADIR__.'/php/inc/auto_update_sql_DB.inc.php');

// Include functions
include(__ADIR__.'/php/functions/allg.func.inc.php');
include(__ADIR__.'/php/functions/check.func.inc.php');
include(__ADIR__.'/php/functions/modify.func.inc.php');
include(__ADIR__.'/php/functions/traffic.func.inc.php');
include(__ADIR__.'/php/functions/util.func.inc.php');

// include classes
include(__ADIR__.'/php/class/xml_helper.class.php');
include(__ADIR__.'/php/class/Template.class.inc.php');
include(__ADIR__.'/php/class/alert.class.inc.php');
include(__ADIR__.'/php/class/rcon.class.inc.php');
include(__ADIR__.'/php/class/savefile_reader.class.inc.php');
include(__ADIR__.'/php/class/user.class.inc.php');
include(__ADIR__.'/php/class/steamAPI.class.inc.php');
include(__ADIR__.'/php/class/server.class.inc.php');
include(__ADIR__.'/php/class/jobs.class.inc.php');

// include inz
include(__ADIR__.'/php/inc/template_preinz.inc.php');

//create class_var
$alert = new alert();
$steamapi = new steamapi();
$user = new userclass();
if(isset($_SESSION["id"])) {
    $user->setid($_SESSION['id']);

    //Prüfe ob der Benutzer gebant ist
    if ($user->read("ban") > 0) {
        $query = "DELETE FROM `ArkAdmin_user_cookies` WHERE (`userid`='".$_SESSION["id"]."')";
        $mycon->query($query);
        session_destroy();
    }

    // Prüfe ob der Nutzer noch exsistiert
    $query = "SELECT * FROM `ArkAdmin_users` WHERE (`id`='".$_SESSION["id"]."')";
    if (!($mycon->query($query)->numRows() > 0)) {
        session_destroy();
        header("Location: /login");
        exit;
    }

    // Erfasse IP
    $path = __ADIR__."/app/json/user/".md5($_SESSION["id"]).".json";
    if(!file_exists($path)) file_put_contents($path, "{}") ? null : null;
    if(file_exists($path)) {
        $json = $helper->file_to_json($path, true);
        $json["ip"] = getRealIpAddr();
        $helper->savejson_create($json, $path);
    }
}

// Allgemein SteamAPI Arrays
$steamapi_mods = (file_exists(__ADIR__."/app/json/steamapi/mods.json")) ? $helper->file_to_json(__ADIR__."/app/json/steamapi/mods.json", true) : array();
$steamapi_user = (file_exists(__ADIR__."/app/json/steamapi/user.json")) ? $helper->file_to_json(__ADIR__."/app/json/steamapi/user.json", true) : array();

// include util
include(__ADIR__.'/php/inc/session.inc.php');

//create globals vars
$API_Key = $ckonfig['apikey'];
$servlocdir = $ckonfig['servlocdir'];
$expert = $user->expert();
$jobs = new jobs();
$all = $helper->file_to_json(__ADIR__."/app/json/serverinfo/all.json");

// lade Permissions
$permissions_default = $helper->file_to_json(__ADIR__."/app/json/user/permissions.tpl.json");
if(
    !file_exists(__ADIR__."/app/json/user/".(isset($_SESSION["id"]) ? md5($_SESSION["id"]) : null).".permissions.json") &&
    isset($_SESSION["id"])
) $helper->savejson_create($permissions_default, __ADIR__."/app/json/user/".md5($_SESSION["id"]).".permissions.json");
$permissions = (isset($_SESSION["id"]) && file_exists(__ADIR__."/app/json/user/".md5($_SESSION["id"]).".permissions.json")) ? $helper->file_to_json(__ADIR__."/app/json/user/".md5($_SESSION["id"]).".permissions.json") : $helper->file_to_json(__ADIR__."/app/json/user/permissions.tpl.json");
$permissions = array_replace_recursive($permissions_default, $permissions);

// gehe Rechte der Server durch
$servers_perm = array();
$file = __ADIR__.'/app/json/serverinfo/all.json';
$server = $all["cfgs_only_name"];
foreach ($server as $item) {
    $perm_file = file_get_contents(__ADIR__."/app/json/user/permissions_servers.tpl.json");
    $perm_file = str_replace("{cfg}", $item, $perm_file);
    $default = $helper->str_to_json($perm_file);
    if(isset($permissions["server"][$item])) {
        $permissions["server"][$item] = array_replace_recursive($default[$item], $permissions["server"][$item]);
    }
    else {
        $permissions["server"] += $default;
    }
}

if (isset($_SESSION["id"])) {
    $query = 'UPDATE `ArkAdmin_users` SET `lastlogin`=\''.time().'\' WHERE `id`=\''.$_SESSION["id"].'\'';
    $mycon->query($query);
}

// Define default page
$page = $url[1];

if (file_exists(__ADIR__.'/php/page/'.$page.'.inc.php')) {
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
if ($page == "login" || $page == "registration") {
    $pagename = '{::lang::php::index::pagename_reg}';
    if ($page == "login") $pagename = '{::lang::php::index::pagename_login}';
}

if($user->perm("all/manage_aas")) $btns .= '
    <a href="http://'.$ip.':'.$webserver['config']['port'].'/update/'.md5($ip).'?md5='.md5($_SESSION["id"]).'" target="_blank" class="btn btn-outline-secondary rounded-0" id="force_update" data-toggle="popover_action" title="" data-content="{::lang::allg::force_update_text}" data-original-title="{::lang::allg::force_update}">
        <span class="icon text-white-50">
            <i class="fa fa-cloud-download"></i>
        </span>
    </a>
    <a href="http://'.$ip.':'.$webserver['config']['port'].'/restart/'.md5($ip).'?md5='.md5($_SESSION["id"]).'" target="_blank" class="btn btn-outline-secondary rounded-0" id="force_update" data-toggle="popover_action" title="" data-content="{::lang::allg::force_restart_text}" data-original-title="{::lang::allg::force_restart}">
        <span class="icon text-white-50">
            <i class="fas fa-undo"></i>
        </span>
    </a>
    <a href="http://'.$ip.':'.$webserver['config']['port'].'/log?md5='.md5($_SESSION["id"]).'" target="_blank" class="btn btn-outline-secondary rounded-0" id="force_update" data-toggle="popover_action" title="" data-content="{::lang::allg::show_as_logs_text}" data-original-title="{::lang::allg::show_as_logs}">
        <span class="icon text-white-50">
            <i class="fas fa-list"></i>
        </span>
    </a>
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
$tpl_b->r('aa_version', $version);
$tpl_b->r('lastcheck_webhelper', converttime(((file_exists($path_webhelper)) ? intval(file_get_contents($path_webhelper)) : time()), true));
$tpl_b->r('user', $user->read("username"));
$tpl_b->r('content', $content);
$tpl_b->r('site_name', $site_name);
$tpl_b->r('btns', "<div class=\"d-sm-inline-block\">$btns</div>");
$tpl_b->r('urltop', $urltop);
$tpl_b->r('g_alert', $g_alert);
$tpl_b->rif ('if_g_alert', $g_alert_bool);
$tpl_b->r("langlist", get_lang_list());
$tpl_b->r("maxserver", $maxpanel_server);
$tpl_b->r("rank", "<span class='text-".((!$user->perm("allg/is_admin")) ? "success" : "danger")."'>{::lang::php::userpanel::".((!$user->perm("allg/is_admin")) ? "user" : "admin")."}</span>");

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
if ($page != "login" && $page != "registration" && $page != "crontab" && isset($_SESSION['id']) && file_exists(__ADIR__."/app/check/done")) {
    $tpl_h->echo();
    $tpl_b->echo();
    $tpl_f->echo();
} else {

    // Login
    if ($page == "login" && file_exists(__ADIR__."/app/check/done") && !isset($_SESSION['id'])) {
        if (isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_h->echo();
        $tpl_login->r("ROOT", $ROOT);
        $tpl_login->r("langlist", get_lang_list());
        $tpl_login->echo();
    }

    // Registration
    elseif ($page == "registration" && file_exists(__ADIR__."/app/check/subdone") && !isset($_SESSION['id'])) {
        if (isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_h->echo();
        $tpl_register->r("ROOT", $ROOT);
        $tpl_register->r("langlist", get_lang_list());
        $tpl_register->echo();
    }

    // Crontab
    elseif ($page == "crontab" && file_exists(__ADIR__."/app/check/done")) {
        $tpl_h->echo();
        $tpl_crontab->echo();
    }

    // Forward installer
    elseif (!file_exists(__ADIR__."/app/check/subdone")) {
        header('Location: /install.php');
        exit;
    } else {
        // Forward installer (registration)
        if (file_exists(__ADIR__."/app/check/subdone") && !file_exists(__ADIR__."/app/check/done")) {
            header('Location: /registration');
            exit;
        }

        // Forward not loggedin
        elseif (file_exists(__ADIR__."/app/check/done")) {
            header('Location: /login');
            exit;
        }

        // Forward not installed
        else {
            header('Location: /install.php');
            exit;
        }
    }
}

//close mysql
$mycon->close();