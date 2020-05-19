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

if (check_arkmanager()) {
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
if ($am && $os && $curl && $rew) $ok = true;
$sitetpl->rif ("ifallok", $ok);
$sitetpl->rif ("ifcurl", $curl);
$sitetpl->rif ("ifrewrite", $rew);
$sitetpl->rif ("ifos", $os);
$sitetpl->rif ("ifam", $am);

$title = "{::lang::install::step0::title}";
$content = $sitetpl->load_var();

?>

