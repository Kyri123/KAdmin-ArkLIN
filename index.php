<?php
$pagename = null;
$pageimg = null;
$titlename = null;
$sidebar = null;
$setsidebar = false;
$btns = null;
$urltop = null;

session_start();
#lese URL und schneide sie auf
$url = $_SERVER["REQUEST_URI"];
$url = explode("/", $url);
if($url[1] == "") {
    header('Location: /home');
    exit;
}
$tpl = null;
#Wichtige PHP daten
require_once 'inc/class/tpl.class.inc.php';
require_once 'inc/config.inc.php';
require_once 'inc/class/mysql.class.inc.php';
require_once 'inc/class/rcon.class.inc.php';
require_once 'inc/class/savefile_reader.class.inc.php';
#verbinde zur DB
$mycon = new mysql($dbhost, $dbuser, $dbpass, $dbname);
include('inc/class/helper.class.inc.php');
include('inc/class/user.class.inc.php');
include('inc/class/steamAPI.class.inc.php');
include('inc/func/allg.func.inc.php');
$steamapi = new steamapi();
$helper = new helper();
$user = new userclass();
$user->setid($_SESSION['id']);

if(isset($_SESSION["id"])) {
    $query = 'UPDATE `ArkAdmin_users` SET `lastlogin`=\''.time().'\' WHERE (`id`=\''.$_SESSION["id"].'\')';
    $mycon->query($query);
}
#Include

// if(isset($_SESSION["id"])) include('inc/make_default_datas.inc.php');
include('inc/session.inc.php');

#definiere defaultsite
$page = $url[1];
include('inc/class/server.class.inc.php');

if(file_exists('sites/'.$page.'.inc.php')) {
    include('sites/'.$page.'.inc.php');
}
else {
    $page = '404';
    include('sites/'.$page.'.inc.php');
}

# Webseite Laden
# Das Template laden
$tpl_h = new Template("head.htm", "tpl/index/");
$tpl_h->load();

$tpl_b = new Template("body.htm", "tpl/index/");
$tpl_b->load();

$tpl_f = new Template("foother.htm", "tpl/index/");
$tpl_f->load();
include('inc/server.inc.php');

include('inc/nav_curr.inc.php');

$tpl_f->repl('jahr', date('Y', time()));
$tpl_h->repl('time', time());
$tpl_b->repl('aa_version', $version);

$tpl_b->repl('pagename', $pagename);
$tpl_h->repl('pagename', $pagename);
// replace
$tpl_b->repl('user', $user->name());
$tpl_b->repl('rank', $user->rang());
$tpl_b->repl('content', $content);
$tpl_b->repl('site_name', $site_name);
$tpl_b->repl('btns', $btns);
$tpl_b->repl('urltop', $urltop);

$tpl_h->rplSession();
$tpl_b->rplSession();
$tpl_f->rplSession();

if($page != "login" && $page != "register" && $page != "crontab" && isset($_SESSION['id'])) {
    $tpl_h->display();
    $tpl_b->display();
    $tpl_f->display();
}
else {
    if($page == "login") {
        if(isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_login->rplSession();
        $tpl_h->display();
        $tpl_login->display();
    }
    elseif($page == "register") {
        if(isset($_SESSION["id"])) {
            header('Location: /home');
            exit;
        }
        $tpl_register->rplSession();
        $tpl_h->display();
        $tpl_register->display();
    }
    elseif($page == "crontab") {
        $tpl_crontab->rplSession();
        $tpl_h->display();
        $tpl_crontab->display();
    }
    else {
        header('Location: /login');
        exit;
    }
}
$mycon->close();
?>
