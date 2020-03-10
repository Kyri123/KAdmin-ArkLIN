<?php

// Vars
$tpl_dir = 'tpl/home/';
$tpl_dir_all = 'tpl/all/';
$setsidebar = false;
$pagename = null;
$urltop = '<li class="breadcrumb-item">Startseite</li>';

//tpl
$tpl = new Template('tpl.htm', $tpl_dir);
$tpl->load();




// lade in TPL
$content = $tpl->loadin();
$H_btn_group = null;
$H_btn_extra = null;
$site_name = 'Startseite / Willkommen';
?>