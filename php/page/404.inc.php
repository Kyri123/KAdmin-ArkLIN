<?php


// Vars
$tpl_dir = 'app/template/error/';
$setsidebar = false;

$urls = NULL;
$tpl = new Template("404.htm", $tpl_dir);
$tpl->load();

$content = $tpl->load_var();
$pagename = "{::lang::php::err404::maintitle} 404";
$pageicon = "<i class=\"fas fa-exclamation-triangle text-danger\" aria-hidden=\"true\"></i>";
?>