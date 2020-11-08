<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/
$resp = null;
$sitetpl= new Template("step3.htm", $dirs["tpl"]);
$sitetpl->load();
$complete = false;
$ppath = __ADIR__."/php/inc/custom_konfig.json";
if(!file_put_contents(__ADIR__."/app/check/subdone", "true")) {
    $resp .= $alert->rd(1);
}


if (!file_exists(__ADIR__."/remote")) mkdir(__ADIR__."/remote");
if (isset($_POST["savepanel"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");
    $check = array(
        "servlocdir",
        "arklocdir",
        "steamcmddir"
    );

    for ($i=0;$i<count($a_key);$i++) {
        if(in_array($a_key[$i], $check)) if(substr($a_value[$i], -1) != "/") $a_value[$i] .= "/";
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "1") $a_value[$i] = 1;
        if (in_array($a_key[$i], $filter_bool) && $a_value[$i] == "0") $a_value[$i] = 0;
        if (in_array($a_key[$i], $filter_link)) {
            if ($a_key[$i] == "servlocdir" && readlink(__ADIR__."/remote/serv") != $a_value[$i]) {
                $loc = __ADIR__."/remote/serv";
                if (file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            elseif ($a_key[$i] == "arklocdir" && readlink(__ADIR__."/remote/arkmanager") != $a_value[$i]) {
                $loc = __ADIR__."/remote/arkmanager";
                if (file_exists($loc)) unlink($loc);
                $target = $a_value[$i];
                symlink($target, $loc);
            }
            $json[$a_key[$i]] = $a_value[$i];
        } else {
            $json[$a_key[$i]] = $a_value[$i];
        }
    }

    $json_str = $helper->json_to_str($json);
    if (file_put_contents($ppath, $json_str)) {
        $link1 = false; $link2 = false;
        if (file_put_contents(__ADIR__."/remote/arkmanager/check", "done")) {
            unlink(__ADIR__."/remote/arkmanager/check");
            $link1 = true;
        }
        else {
            $resp .= $alert->rd(1);
        }
        if (file_put_contents(__ADIR__."/remote/serv/check", "done")) {
            unlink(__ADIR__."/remote/arkmanager/check");
            $link2 = true;
        }
        else {
            $resp .= $alert->rd(1);
        }
        if ($link1 && $link2) {
            header("Location: $ROOT/install.php/4");
            exit;
        } else {
            $resp .= $alert->rd(30);
        }
    } else {
        $resp .= $alert->rd(1);
    }
}


$sitetpl->r("error", $resp);
$title = "{::lang::install::step2::title}";
$content = $sitetpl->load_var();



