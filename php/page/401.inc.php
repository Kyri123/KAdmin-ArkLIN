<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

// TODO :: DONE 2.1.0 REWORKED


// Vars
$tpl_dir        = __ADIR__.'/app/template/core/error/';
$setsidebar     = false;

$urls           = NULL;
$tpl            = new Template("401.htm", $tpl_dir);
$tpl->load();

$content        = $tpl->load_var();
$pagename       = "{::lang::php::err404::maintitle} 401";
$pageicon       = "<i class=\"fas fa-exclamation-triangle text-danger\" aria-hidden=\"true\"></i>";