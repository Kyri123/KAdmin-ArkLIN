<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2021, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/KAdmin-ArkLIN/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/KAdmin-ArkLIN
 * *******************************************************************************************
*/

$sitetpl    = new Template("step4.htm", $dirs["tpl"]);
$sitetpl->load();
$complete   = true;
$ppath      = __ADIR__."/php/inc/custom_konfig.json";




$sitetpl->r("error", $resp);
$title      = "{::lang::install::step3::title}";
$content    = $sitetpl->load_var();
