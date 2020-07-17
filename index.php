<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
// hide errors
include('php/inc/config.inc.php');

ini_set('display_errors', $display_error);
ini_set('display_startup_errors', $display_error);
//error_reporting(E_ALL);

// kleiner fix für PHP 70-72
include("php/functions/php70-72.inc.php");

//check install
if (!file_exists("app/check/subdone")) {
    header('Location: /install.php');
    exit;
}

// Define vars
date_default_timezone_set('Europe/Amsterdam');
$pagename = $pageimg = $titlename = $sidebar = $btns = $urltop = $g_alert = $pageicon = $tpl = null;
$setsidebar = $g_alert_bool = false;

//start Session
session_start();
// read URL
$url = $surl = $_SERVER["REQUEST_URI"];
$url = explode("/", $url);
if ($url[1] == "" || $url[1] == "favicon.ico") {
    header('Location: /home');
    exit;
}

// Connent to MYSQL
include('php/class/mysql.class.inc.php');
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);
include('php/inc/auto_update_sql_DB.inc.php');

// Include functions
include('php/functions/allg.func.inc.php');
include('php/functions/check.func.inc.php');

// include classes
include('php/class/helper.class.inc.php');
include('php/class/xml_helper.class.php');
include('php/class/Template.class.inc.php');
include('php/class/alert.class.inc.php');
include('php/class/rcon.class.inc.php');
include('php/class/savefile_reader.class.inc.php');
include('php/class/user.class.inc.php');
include('php/class/steamAPI.class.inc.php');
include('php/class/server.class.inc.php');
include('php/class/jobs.class.inc.php');

//create class_var
$helper = new helper();
$alert = new alert();
$steamapi = new steamapi();
$user = new userclass();
if(isset($_SESSION["id"])) $user->setid($_SESSION['id']);

// include util
include('php/inc/session.inc.php');
include('php/inc/auto_update_sql_DB.inc.php');

//create globals vars
$ckonfig = $helper->file_to_json('php/inc/custom_konfig.json', true);
$API_Key = $ckonfig['apikey'];
$servlocdir = $ckonfig['servlocdir'];
$expert = $user->expert();
$jobs = new jobs();

//check is user banned
if ($user->ban() > 0) {
    $query = "DELETE FROM `ArkAdmin_user_cookies` WHERE (`userid`='".$_SESSION["id"]."')";
    $mycon->query($query);
    session_destroy();
}

if (isset($_SESSION["id"])) {
    $query = 'UPDATE `ArkAdmin_users` SET `lastlogin`=\''.time().'\' WHERE (`id`=\''.$_SESSION["id"].'\')';
    $mycon->query($query);
} 

// Define default page
$page = $url[1];

if (file_exists('php/page/'.$page.'.inc.php')) {
    include('php/page/'.$page.'.inc.php');
} else {
    header("Location: /404");
    exit;
}

// Website
// Load template
$tpl_h = new Template("head.htm", "app/template/core/index/");
$tpl_h->load();

$tpl_b = new Template("body.htm", "app/template/core/index/");
$tpl_b->load();

$tpl_f = new Template("foother.htm", "app/template/core/index/");
$tpl_f->load();

// lade Global_Alerts
include('php/inc/global_alert.inc.php');

// Include
include('php/inc/server.inc.php');
include('php/inc/nav_curr.inc.php');

// Define pagename for login & registration
if ($page == "login" || $page == "registration") {
    $pagename = '{::lang::php::index::pagename_reg}';
    if ($page == "login") $pagename = '{::lang::php::index::pagename_login}';
}
// replace
$tpl_h->r('time', time());

$tpl_b->r('pagename', $pagename);
$tpl_b->r('pageicon', $pageicon);
$tpl_h->r('pagename', $pagename);
$tpl_b->r('aa_version', $version);
$tpl_b->r('lastcheck_webhelper', converttime($helper->gethelpertime(), true));
$tpl_b->r('user', $user->name());
$tpl_b->r('rank', $user->rang());
$tpl_b->r('content', $content);
$tpl_b->r('site_name', $site_name);
$tpl_b->r('btns', $btns);
$tpl_b->r('urltop', $urltop);
$tpl_b->r('g_alert', $g_alert);
$tpl_b->rif ('if_g_alert', $g_alert_bool);
$tpl_b->r("langlist", get_lang_list());

// Server Traffics
$all = $helper->file_to_json("app/json/serverinfo/all.json");
$tpl_b->r('count_server', count($all["cfgs"]));
$tpl_b->r('cpu_perc', cpu_perc());
$tpl_b->r('free', bitrechner(disk_free_space("remote/serv"), "B", "GB"));
$tpl_b->r('ram_used', str_replace("MB", "GB", bitrechner(mem_array()[1], "B", "GB")));
$tpl_b->r('ram_max', str_replace("MB", "GB", bitrechner(mem_array()[0], "B", "GB")));
$tpl_b->r('ram_perc', mem_perc());
$ifnot_traffic = false;
$check = array("changelog", "404");
if (in_array($page, $check)) $ifnot_traffic = true;
$tpl_b->rif ("ifchangelog", $ifnot_traffic);

// Site Builder
if ($page != "login" && $page != "registration" && $page != "crontab" && isset($_SESSION['id']) && file_exists("app/check/done")) {
    $tpl_h->echo();
    $tpl_b->echo();
    $tpl_f->echo();
} else {

    // Login
    if ($page == "login" && file_exists("app/check/done")) {
        if (isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_h->echo();
        $tpl_login->r("langlist", get_lang_list());
        $tpl_login->echo();
    }

    // Registration
    elseif ($page == "registration" && file_exists("app/check/subdone")) {
        if (isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_h->echo();
        $tpl_register->r("langlist", get_lang_list());
        $tpl_register->echo();
    }

    // Crontab
    elseif ($page == "crontab" && file_exists("app/check/done")) {
        $tpl_h->echo();
        $tpl_crontab->echo();
    }

    // Forward installer
    elseif (!file_exists("app/check/subdone")) {
        header('Location: /install.php');
        exit;
    } else {
        // Forward installer (registration)
        if (file_exists("app/check/subdone") && !file_exists("app/check/done")) {
            header('Location: /registration');
            exit;
        }

        // Forward not loggedin
        elseif (file_exists("app/check/done")) {
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
?>
