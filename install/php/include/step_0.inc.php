<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

$sitetpl= new Template("step0.htm", $dirs["tpl"]);
$sitetpl->load();
$list_opt = null;
$tpl_dir = 'app/template/core/konfig/';

$wpath = 'arkadmin_server/config/server.json';
$json = $check->json;

// Speicher ArkAdmin-Server Einstellungen
if (isset($_POST["savewebhelper"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");

    // setzte Vars
    for ($i=0;$i<count($a_key);$i++) {
        $json[$a_key[$i]] = $a_value[$i];
    }

    // Speichern
    $json_str = $helper->json_to_str($json);
    if (file_put_contents($wpath, $json_str)) {
        header("Location: /install.php/1");
    } else {
        $alert->code = 1;
        $resp .= $alert->re();
    }
}

// Lese Konfig und gebe sie zum bearbeiten frei
$servercfg = $helper->file_to_json($wpath, true);
foreach($servercfg as $key => $value) {
    $list = new Template("opt.htm", $tpl_dir);
    $list->load();
    $list->rif ("ifbool", false);
    $list->rif ("ifnum", is_numeric($value));
    $list->rif ("iftxt", !is_numeric($value));
    $list->r("key", $key);
    $list->r("keym", $key);
    $list->r("value", $value);
    $list_opt .= $list->load_var();
}


$sitetpl->r ("modal", $modals);
$sitetpl->r ("list_opt", $list_opt);
$sitetpl->rif ("ifallok", $check->check_all());

$title = "{::lang::install::step0::title}";
$content = $sitetpl->load_var();

?>

