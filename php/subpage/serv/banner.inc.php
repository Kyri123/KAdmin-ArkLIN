<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

if (!$API_ACTIVE) {
    header("Location: /401");
    exit;
}

$pagename = '{::lang::php::sc::page::statistiken::pagename}';
$page_tpl = new Template('banner.htm', __ADIR__.'/app/template/sub/serv/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="{ROOT}/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::servercenter::main::nav::banner}</li>';


$page_tpl->r('url', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/");
$page_tpl->r('apikey', $API_KEY);
$page_tpl->r('ip', $ip);
$panel = $page_tpl->load_var();

$player = null;

