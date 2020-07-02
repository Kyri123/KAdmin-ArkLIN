<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$sitetpl= new Template("step0.htm", $tpl_dir);
$sitetpl->load();
$complete = false;
$ok = false;

if (check_curl()) {
    $sitetpl->r("curl_state_color", "success");
    $sitetpl->r("curl_btn", "up");
    $sitetpl->r("curl_state", "{::lang::install::found}");
    $curl = true;
} else {
    $sitetpl->r("curl_state_color", "danger");
    $sitetpl->r("curl_btn", "down");
    $sitetpl->r("curl_state", "{::lang::install::notfound}");
    $curl = false;
}

if (check_rew()) {
    $sitetpl->r("rew_state_color", "success");
    $sitetpl->r("rew_btn", "up");
    $sitetpl->r("rew_state", "{::lang::install::found}");
    $rew = true;
} else {
    $sitetpl->r("rew_state_color", "danger");
    $sitetpl->r("rew_btn", "down");
    $sitetpl->r("rew_state", "{::lang::install::notfound}");
    $rew = false;
}

if (check_OS()) {
    $sitetpl->r("linux_state_color", "success");
    $sitetpl->r("linux_btn", "up");
    $sitetpl->r("linux_state", "{::lang::install::found}");
    $os = true;
} else {
    $sitetpl->r("linux_state_color", "danger");
    $sitetpl->r("linux_btn", "down");
    $sitetpl->r("linux_state", "{::lang::install::notfound}");
    $os = false;
}

if (check_cmd()) {
    $sitetpl->r("am_state_color", "success");
    $sitetpl->r("am_btn", "up");
    $sitetpl->r("am_state", "{::lang::install::found}");
    $am = true;
} else {
    $sitetpl->r("am_state_color", "danger");
    $sitetpl->r("am_btn", "down");
    $sitetpl->r("am_state", "{::lang::install::notfound}");
    $am = false;
}

if (check_cmd("screen")) {
    $sitetpl->r("screen_state_color", "success");
    $sitetpl->r("screen_btn", "up");
    $sitetpl->r("screen_state", "{::lang::install::found}");
    $screen = true;
} else {
    $sitetpl->r("screen_state_color", "danger");
    $sitetpl->r("screen_btn", "down");
    $sitetpl->r("screen_state", "{::lang::install::notfound}");
    $screen = false;
}

$checkthis = substr(sprintf('%o', fileperms('index.php')), -4);
if (check_server_run() && $checkthis  == "0777") {
    $sitetpl->r("aa_state_color", "success");
    $sitetpl->r("aa_btn", "up");
    $sitetpl->r("aa_state", "{::lang::install::online}");
    $run = true;
} else {
    $sitetpl->r("aa_state_color", "danger");
    $sitetpl->r("aa_btn", "down");
    $sitetpl->r("aa_state", "{::lang::install::offline}");
    $run = false;
}

if (!PHP_VERSION_ID >= 70300) {
    $sitetpl->r("php_state_color", "success");
    $sitetpl->r("php_btn", "up");
    $sitetpl->r("php_state", "{::lang::install::found}");
    $php = true;
} elseif(PHP_VERSION_ID >= 70000) {
    $sitetpl->r("php_state_color", "warning");
    $sitetpl->r("php_btn", "up");
    $sitetpl->r("php_state", "{::lang::install::warning}");
    $php = true;
} else {
    $sitetpl->r("php_state_color", "danger");
    $sitetpl->r("php_btn", "down");
    $sitetpl->r("php_state", "{::lang::install::notfound}");
    $php = false;
}

if(class_exists("mysqli")) {
    $sitetpl->r("MySQLi_state_color", "success");
    $sitetpl->r("MySQLi_btn", "up");
    $sitetpl->r("MySQLi_state", "{::lang::install::found}");
    $MySQLi = true;
} else {
    $sitetpl->r("MySQLi_state_color", "danger");
    $sitetpl->r("MySQLi_btn", "down");
    $sitetpl->r("MySQLi_state", "{::lang::install::notfound}");
    $MySQLi = false;
}

if ($am && $os && $curl && $rew && $php && $run && $screen && $MySQLi) $ok = true;
$sitetpl->rif ("ifallok", $ok);
$sitetpl->rif ("ifcurl", $curl);
$sitetpl->rif ("ifrewrite", $rew);
$sitetpl->rif ("ifos", $os);
$sitetpl->rif ("ifam", $am);
$sitetpl->rif ("aa", $run);

$title = "{::lang::install::step0::title}";
$content = $sitetpl->load_var();

?>

