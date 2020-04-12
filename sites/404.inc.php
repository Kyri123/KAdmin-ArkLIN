<?php


// Vars
$tpl_dir = 'tpl/error/';
$setsidebar = false;

$urls = NULL;
$tpl = new Template("404.htm", $tpl_dir);
$tpl->load();

$content = $tpl->loadin();
$pagename = "Error 404";
?>