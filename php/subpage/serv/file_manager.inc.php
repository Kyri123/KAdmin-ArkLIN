<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// Prüfe Rechte wenn nicht wird die seite nicht gefunden!
if (!$session_user->perm("$perm/filebrowser/show")) {
    header("Location: /401");
    exit;
}

$pagename   = '{::lang::php::sc::page::file_manager::pagename}';
$page_tpl   = new Template('file_manager.htm', __ADIR__.'/app/template/sub/serv/');
$urltop     = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfgRead('ark_SessionName').'</a></li>';
$urltop     .= '<li class="breadcrumb-item">{::lang::php::sc::page::file_manager::urltop}</li>';
$dir        = __ADIR__."/remote/serv";
$path       = false;
$fdir       = null;

$page_tpl->load();
$page_tpl->r("path", $dir);
$page_tpl->r("cfg", $serv->name());
$panel      = $page_tpl->load_var();