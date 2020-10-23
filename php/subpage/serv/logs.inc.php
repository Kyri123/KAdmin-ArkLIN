<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$user->perm("$perm/logs/show")) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::logs::pagename}';
$page_tpl = new Template('logs.htm', __ADIR__.'/app/template/sub/serv/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::logs::urltop}</li>';
$resp = null;

$logpath = $serv->cfg_read('logdir');

$exp = explode('/', $logpath);

for ($i=0;$i<count($exp);$i++) {
    if (strpos($exp[$i], $serv->name()) !== false) {
        $path = $exp[$i];
        break;
    }
}

// Lösche alle Loginhalte
if(isset($_POST["clearlogs"]) && $user->perm("$perm/logs/clear")) {
    if(file_put_contents("$logpath/arkserver.log", " ") && file_put_contents("$logpath/arkmanager.log", " ")) {
        $resp = $alert->rd(101);
    }
    else {
        $resp = $alert->rd(1);
    }
}
elseif(isset($_POST["clearlogs"])) {
    $resp = $alert->rd(99);
}

$page_tpl->r('cfg' ,$serv->name());
$page_tpl->r('path' ,$path);
$page_tpl->r('resp' ,$resp);
$panel = $page_tpl->load_var();
