<?php

$page_tpl = new Template('home.htm', 'tpl/serv/sites/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Startseite</li>';

$user = new userclass();
$user->setid($_SESSION['id']);
$page_tpl->repl('cfg' ,$serv->show_name());
$page_tpl->repl('SESSION_USERNAME' ,$user->name());

$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>