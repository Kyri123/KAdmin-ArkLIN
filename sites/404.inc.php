<?php


// Vars
$tpl_dir = 'tpl/error/';
$setsidebar = false;

$urls = NULL;
$tpl = new Template("404.htm", $tpl_dir);
$tpl->load();

$content = $tpl->loadin();
$pagename = "Error 404";
$pageicon = "<i class=\"fas fa-exclamation-triangle text-danger\" aria-hidden=\"true\"></i>";
?>