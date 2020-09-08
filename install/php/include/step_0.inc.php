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
$limit = $helper->file_to_json("app/json/panel/aas_min.json", true);
$maxi = $helper->file_to_json("app/json/panel/aas_max.json", true);
$json = $check->json;

// Speicher ArkAdmin-Server Einstellungen
if (isset($_POST["savewebhelper"])) {
    $a_key = $_POST["key"];
    $a_value = $_POST["value"];
    $filter_bool = array("install_mod","uninstall_mod");
    $filter_link = array("servlocdir","arklocdir");

    // Prfüne minimalwerte
    $allok = true;
    for ($i=0;$i<count($a_key);$i++) {
        if(isset($limit[$a_key[$i]])) {
            if(!(intval($limit[$a_key[$i]]) <= intval($a_value[$i]))) $allok = false;
        }
        $jsons[$a_key[$i]] = $a_value[$i];
    }

    // Speichern
    $json_str = $helper->json_to_str($jsons);
    if($allok) {
        if (file_put_contents($wpath, $json_str)) {
            header("Location: /install.php/1");
        } else {
            $resp .= $alert->rd(1);
        }
    }
    else {
        $resp .= $alert->rd(2);
    }
}

// Lese Konfig und gebe sie zum bearbeiten frei
$servercfg = $helper->file_to_json('arkadmin_server/config/server.json', true);
foreach($servercfg as $key => $value) {
    $list = new Template("opt.htm", 'app/template/core/konfig/');
    $list->load();
    $list->rif ("ifbool", false);
    $list->rif ("ifnum", is_numeric($value));
    $list->rif ("iftxt", !is_numeric($value));
    $list->rif("ifmin", isset($limit[$key]));
    $list->rif("ifmax", isset($maxi[$key]));
    $list->r("key", $key);
    $list->r("keym", "aa::$key");
    $list->r("value", $value);
    $list->r("min", ((isset($limit[$key])) ? $limit[$key] : 0));
    $list->r("max", ((isset($maxi[$key])) ? $maxi[$key] : 0));
    $list_opt .= $list->load_var();
}


$sitetpl->r ("modal", $modals);
$sitetpl->r ("list_opt", $list_opt);

$title = "{::lang::install::step0::title}";
$content = $sitetpl->load_var();

?>

