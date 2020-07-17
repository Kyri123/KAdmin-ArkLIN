<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$pagename = '{::lang::php::sc::page::logs::pagename}';
$page_tpl = new Template('logs.htm', 'app/template/sub/serv/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/servercenter/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">{::lang::php::sc::page::logs::urltop}</li>';


$logpath = $serv->cfg_read('logdir');

$exp = explode('/', $logpath);

for ($i=0;$i<count($exp);$i++) {
    if (strpos($exp[$i], $serv->name()) !== false) {
        $path = $exp[$i];
        break;
    }
}

$page_tpl->r('cfg' ,$serv->name());
$page_tpl->r('path' ,$path);
$page_tpl->session();
$panel = $page_tpl->load_var();
?>