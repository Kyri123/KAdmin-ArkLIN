<?php

$page_tpl = new Template('logs.htm', 'tpl/serv/sites/');
$page_tpl->load();
$urltop = '<li class="breadcrumb-item"><a href="/serverpage/'.$url[2].'/home">'.$serv->cfg_read('ark_SessionName').'</a></li>';
$urltop .= '<li class="breadcrumb-item">Startseite</li>';


$logpath = $serv->cfg_read('logdir');

$exp = explode('/', $logpath);

for($i=0;$i<count($exp);$i++) {
    if(strpos($exp[$i], $serv->show_name()) !== false) {
        $path = $exp[$i];
        break;
    }
}



$page_tpl->repl('cfg' ,$serv->show_name());
$page_tpl->repl('path' ,$path);









$page_tpl->rplSession();
$panel = $page_tpl->loadin();


?>