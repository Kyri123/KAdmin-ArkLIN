<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$sitetpl= new Template("step4.htm", $dirs["tpl"]);
$sitetpl->load();
$complete = false;
$ppath = __ADIR__."/php/inc/custom_konfig.json";




$sitetpl->r("error", $resp);
$title = "{::lang::install::step3::title}";
$content = $sitetpl->load_var();

// Abschluss
file_put_contents(__ADIR__."/app/check/done", "true");

if (!file_exists(__ADIR__."/app/json/saves")) mkdir(__ADIR__."/app/json/saves");
if (!file_exists(__ADIR__."/app/data/serv")) mkdir(__ADIR__."/app/data/serv");
if (!file_exists(__ADIR__."/app/data/config")) mkdir(__ADIR__."/app/data/config");
if (!file_exists(__ADIR__."/cache")) mkdir(__ADIR__."/cache");

del_dir(__ADIR__."/install/sites");
del_dir(__ADIR__."/install");
unlink(__ADIR__."/install.php");
