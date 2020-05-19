<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$sitetpl= new Template("step3.htm", $tpl_dir);
$sitetpl->load();
$complete = false;
$ppath = "php/inc/custom_konfig.json";




$sitetpl->r("code", "7c90c6595f7cb4d2aa0e");
$sitetpl->r("error", $resp);
$title = "{::lang::install::step3::title}";
$content = $sitetpl->load_var();

?>

