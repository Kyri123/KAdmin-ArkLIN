<?php
/*
 * *******************************************************************************************
 * @author:  Oliver Kaufmann (Kyri123)
 * @copyright Copyright (c) 2019-2020, Oliver Kaufmann
 * @license MIT License (LICENSE or https://github.com/Kyri123/Arkadmin/blob/master/LICENSE)
 * Github: https://github.com/Kyri123/Arkadmin
 * *******************************************************************************************
*/

require('../main.inc.php');
$cfg = $_POST['cfg'];
$case = $_GET['case'];
$serv = new server($cfg);
$serv->cluster_load();
$perm = "server/$cfg";

switch ($case) {
    // CASE: Mod verschieben
    case "push":
        $bool = false;
        $resp = "";
        if($user->perm("$perm/mods/changeplace")) {
            $cancel = false;
            $action = $_POST["action"];
            $modid = $_POST["modid"];
            // change order
            $mods = explode(',', $serv->cfg_read('ark_GameModIds'));
            // Suche Nach Mods
            for ($i=0;$i<count($mods);$i++) {
                if ($mods[$i] == $modid) {
                    // Move Mod nach oben
                    if ($action == 'down') {
                        $iafter = $i+1;
                        if(!isset($mods[$iafter])) {
                            $cancel = true;
                            break;
                        }
                        $modid_after = $mods[$iafter];
                        $mods[$iafter] = $modid;
                        $mods[$i] = $modid_after;
                        break;
                    }
                    // Move Mod nach unten
                    if ($action == 'up') {
                        $ibefore = $i-1;
                        if(!isset($mods[$ibefore])) {
                            $cancel = true;
                            break;
                        }
                        $modid_before = $mods[$ibefore];
                        $mods[$ibefore] = $modid;
                        $mods[$i] = $modid_before;
                        break;
                    }
                }
            }

            $mod_builder = implode(',', $mods);
            // saver

            $serv->cfg_write('ark_GameModIds', $mod_builder);
            if(!$cancel) {
                $resp = $alert->rd($serv->cfg_save() ? 102 : 1);
                $bool = true;
            }
            else {
                $resp = $alert->rd(16);
            }
        }
        else {
                $resp = $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;

    // CASE: Mod verschieben
    case "pushto":
        $bool = false;
        $resp = "";
        if($user->perm("$perm/mods/changeplace")) {
            $cancel = false;
            $to = intval($_POST["to"]);
            $modid = $_POST["modid"];
            // change order
            $mods = explode(',', $serv->cfg_read('ark_GameModIds'));
            // Suche Nach Mods
            for ($i=0;$i<count($mods);$i++) {
                if ($mods[$i] == $modid) {
                    // Move Mod nach oben
                    $modid_new = $mods[$to];
                    $mods[$to] = $modid;
                    $mods[$i] = $modid_new;
                    break;
                }
            }

            $mod_builder = implode(',', $mods);
            // saver

            $serv->cfg_write('ark_GameModIds', $mod_builder);
            if(!$cancel) {
                $resp = $alert->rd($serv->cfg_save() ? 102 : 1);
                $bool = true;
            }
            else {
                $resp = $alert->rd(16);
            }
        }
        else {
            $resp = $alert->rd(99);
        }
        echo json_encode(array("success" => $bool, "msg" => $resp));
        break;

    default:
        echo "Case not found";
        break;
}
$mycon->close();
